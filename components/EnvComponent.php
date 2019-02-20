<?php

namespace geoffry304\enveditor\components;


use geoffry304\enveditor\helpers\DotenvFormatter;
use geoffry304\enveditor\helpers\DotenvReader;
use geoffry304\enveditor\helpers\DotenvWriter;
use geoffry304\enveditor\exceptions\FileNotFoundException;
use geoffry304\enveditor\exceptions\KeyNotFoundException;
use geoffry304\enveditor\exceptions\NoBackupAvailableException;
use Yii;
use yii\base\Component;
use yii\web\NotFoundHttpException;

class EnvComponent extends Component
{
    /**
     * The file path
     *
     * @var string
     */
    public $filePath;

    /**
     * The auto backup status
     *
     * @var bool
     */
    public $autoBackup = true;

    /**
     * The backup path
     *
     * @var string
     */
    public $backupPath = "backups";



    /**
     * The backup filename prefix
     */
    const BACKUP_FILENAME_PREFIX = '.env.backup_';

    /**
     * The backup filename suffix
     */
    const BACKUP_FILENAME_SUFFIX = '';

    protected $formatter;
    protected $reader;
    protected $writer;


    public function init()
    {
        parent::init();

        $this->formatter = new DotenvFormatter();
        $this->reader = new DotenvReader($this->formatter);
        $this->writer = new DotenvWriter($this->formatter);

        $backupPath = Yii::$app->basePath . "/" . $this->backupPath. "/";
        if (!is_dir($backupPath)) {
            mkdir($backupPath, 0777, true);
        }
        $this->backupPath = $backupPath;

        $this->load();
    }

    public function load($filePath = null)
    {
        $this->resetContent();
        if (!is_null($filePath)) {
            $this->filePath = $filePath;
        } else {
            $this->filePath = Yii::$app->basePath . "/" . ".env";

        }
        $this->reader->load($this->filePath);

        if (file_exists($this->filePath)) {
            $this->writer->setBuffer($this->getContent());
        }
        return $this;
    }

    public function backupFilePath($file){
        return $this->backupPath . $file;
    }

    protected function resetContent()
    {
        $this->filePath = null;
        $this->reader->load(null);
        $this->writer->setBuffer(null);
    }

    /**
     * Get raw content of file
     *
     * @return string
     */
    public function getContent()
    {
        return $this->reader->content();
    }

    /**
     * Get all lines from file
     *
     * @return array
     */
    public function getLines()
    {
        return $this->reader->lines();
    }

    /**
     * Get all or exists given keys in file content
     *
     * @param  array $keys
     *
     * @return array
     */
    public function getKeys($keys = [])
    {
        $allKeys = $this->reader->keys();

        return array_filter($allKeys, function ($key) use ($keys) {
            if (!empty($keys)) {
                return in_array($key, $keys);
            }
            return true;
        }, ARRAY_FILTER_USE_KEY);
    }

    /**
     * Check, if a given key is exists in the file content
     *
     * @param  string $keys
     *
     * @return bool
     */
    public function keyExists($key)
    {
        $allKeys = $this->getKeys();
        if (array_key_exists($key, $allKeys)) {
            return true;
        }
        return false;
    }

    /**
     * Return the value matching to a given key in the file content
     *
     * @param  $key
     *
     * @throws \app\components\dotenveditor\Exceptions\KeyNotFoundException
     *
     * @return string
     */
    public function getValue($key)
    {
        $allKeys = $this->getKeys([$key]);
        if (array_key_exists($key, $allKeys)) {
            return $allKeys[$key]['value'];
        }
        throw new KeyNotFoundException('Requested key not found in your file.');
    }

    /*
    |--------------------------------------------------------------------------
    | Working with writing
    |--------------------------------------------------------------------------
    |
    | getBuffer()
    | addEmpty()
    | addComment($comment)
    | setKeys($data)
    | setKey($key, $value = null, $comment = null, $export = false)
    | deleteKeys($keys = [])
    | deleteKey($key)
    | save()
    |
    */

    /**
     * Return content in buffer
     *
     * @return string
     */
    public function getBuffer()
    {
        return $this->writer->getBuffer();
    }

    /**
     * Add empty line to buffer
     *
     * @return EnvComponent
     */
    public function addEmpty()
    {
        $this->writer->appendEmptyLine();
        return $this;
    }

    /**
     * Add comment line to buffer
     *
     * @param object
     */
    public function addComment($comment)
    {
        $this->writer->appendCommentLine($comment);
        return $this;
    }

    /**
     * Set many keys to buffer
     *
     * @param  array $data
     *
     * @return EnvComponent
     */
    public function setKeys($data)
    {
        foreach ($data as $setter) {
            if (array_key_exists('key', $setter)) {
                $key = $this->formatter->formatKey($setter['key']);
                $value = array_key_exists('value', $setter) ? $setter['value'] : null;
                $comment = array_key_exists('comment', $setter) ? $setter['comment'] : null;
                $export = array_key_exists('export', $setter) ? $setter['export'] : false;

                if (!is_file($this->filePath) || !$this->keyExists($key)) {
                    $this->writer->appendSetter($key, $value, $comment, $export);
                } else {
                    $oldInfo = $this->getKeys([$key]);
                    $comment = is_null($comment) ? $oldInfo[$key]['comment'] : $comment;
                    $this->writer->updateSetter($key, $value, $comment, $export);
                }
            }
        }

        return $this;
    }

    /**
     * Set one key to buffer
     *
     * @param string $key Key name of setter
     * @param string|null $value Value of setter
     * @param string|null $comment Comment of setter
     * @param boolean $export Leading key name by "export "
     *
     * @return EnvComponent
     */
    public function setKey($key, $value = null, $comment = null, $export = false)
    {
        $data = [compact('key', 'value', 'comment', 'export')];

        return $this->setKeys($data);
    }

    /**
     * Delete many keys in buffer
     *
     * @param  array $keys
     *
     * @return EnvComponent
     */
    public function deleteKeys($keys = [])
    {
        foreach ($keys as $key) {
            $this->writer->deleteSetter($key);
        }

        return $this;
    }

    /**
     * Delete on key in buffer
     *
     * @param  string $key
     *
     * @return EnvComponent
     */
    public function deleteKey($key)
    {
        $keys = [$key];

        return $this->deleteKeys($keys);
    }

    /**
     * Save buffer to file
     *
     * @return EnvComponent
     */
    public function save()
    {
        if (is_file($this->filePath) && $this->autoBackup) {
            $this->backup();
        }

        $this->writer->save($this->filePath);
        return $this;
    }

    /*
    |--------------------------------------------------------------------------
    | Working with backups
    |--------------------------------------------------------------------------
    |
    | autoBackup($on)
    | backup()
    | getBackups()
    | getLatestBackup()
    | restore($filePath = null)
    | deleteBackups($filePaths = [])
    | deleteBackup($filePath)
    |
    */

    /**
     * Switching of the auto backup mode
     *
     * @param  boolean $on
     *
     * @return EnvComponent
     */
    public function autoBackup($on = true)
    {
        $this->autoBackup = $on;
        return $this;
    }

    /**
     * Create one backup of loaded file
     *
     * @return EnvComponent
     */
    public function backup()
    {
        if (!is_file($this->filePath)) {
            throw new FileNotFoundException("File does not exist at path {$this->filePath}");
            return false;
        }
        copy(
            $this->filePath,
            $this->backupPath . self::BACKUP_FILENAME_PREFIX . date('Y_m_d_His') . self::BACKUP_FILENAME_SUFFIX
        );

        return $this;
    }

    /**
     * Return an array with all available backups
     *
     * @return array
     */
    public function getBackups()
    {
        $backups = array_diff(scandir($this->backupPath), array('..', '.'));
        $output = [];

        foreach ($backups as $backup) {
            $filenamePrefix = preg_quote(self::BACKUP_FILENAME_PREFIX, '/');
            $filenameSuffix = preg_quote(self::BACKUP_FILENAME_SUFFIX, '/');
            $filenameRegex = '/^' . $filenamePrefix . '(\d{4})_(\d{2})_(\d{2})_(\d{2})(\d{2})(\d{2})' . $filenameSuffix . '$/';

            $datetime = preg_replace($filenameRegex, '$1-$2-$3 $4:$5:$6', $backup);

            $data = [
                'filename' => $backup,
                'filepath' => $this->backupPath . $backup,
                'created_at' => $datetime,
            ];

            $output[] = $data;
        }

        return $output;
    }

    /**
     * Return the information of the latest backup file
     *
     * @return array
     */
    public function getLatestBackup()
    {
        $backups = $this->getBackups();

        if (empty($backups)) {
            return null;
        }

        $latestBackup = 0;
        foreach ($backups as $backup) {
            $timestamp = strtotime($backup['created_at']);
            if ($timestamp > $latestBackup) {
                $latestBackup = $timestamp;
            }
        }

        $fileName = self::BACKUP_FILENAME_PREFIX . date("Y_m_d_His", $latestBackup) . self::BACKUP_FILENAME_SUFFIX;
        $filePath = $this->backupPath . $fileName;
        $createdAt = date("Y-m-d H:i:s", $latestBackup);

        $output = [
            'filename' => $fileName,
            'filepath' => $filePath,
            'created_at' => $createdAt
        ];

        return $output;
    }

    /**
     * Restore the loaded file from latest backup file or from special file.
     *
     * @param  string|null $filePath
     *
     * @return EnvComponent
     */
    public function restore($filePath = null)
    {
        if (is_null($filePath)) {
            $latestBackup = $this->getLatestBackup();
            if (is_null($latestBackup)) {
                throw new NoBackupAvailableException("There are no available backups!");
            }
            $filePath = $latestBackup['filepath'];
        }

        if (!is_file($filePath)) {
            throw new FileNotFoundException("File does not exist at path {$filePath}");
        }

        copy($filePath, $this->filePath);
        $this->writer->setBuffer($this->getContent());

        return $this;
    }

    /**
     * Delete all or the given backup files
     *
     * @param  array $filePaths
     *
     * @return EnvComponent
     */
    public function deleteBackups($filePaths = [])
    {
        if (empty($filePaths)) {
            $allBackups = $this->getBackups();
            foreach ($allBackups as $backup) {
                $filePaths[] = $backup['filepath'];
            }
        }

        foreach ($filePaths as $filePath) {
            if (is_file($filePath)) {
                unlink($filePath);
            }
        }
        return $this;
    }

    /**
     * Delete the given backup file
     *
     * @param  string $filePath
     *
     * @return EnvComponent
     */
    public function deleteBackup($filePath)
    {
        return $this->deleteBackups([$filePath]);
    }

}