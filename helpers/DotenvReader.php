<?php namespace geoffry304\enveditor\helpers;

use geoffry304\enveditor\interfaces\DotenvFormatter as DotenvFormatterInterface;
use geoffry304\enveditor\interfaces\DotenvReader as DotenvReaderInterface;
use geoffry304\enveditor\exceptions\UnableReadFileException;

/**
 * The DotenvReader class.
 *
 * @package app\components\dotenveditor
 * @author Jackie Do <anhvudo@gmail.com>
 */
class DotenvReader implements DotenvReaderInterface
{

    protected $filePath;


    protected $formatter;

    public function __construct(DotenvFormatterInterface $formatter)
    {
        $this->formatter = $formatter;
    }


    public function load($filePath)
    {
        $this->filePath = $filePath;
        return $this;
    }


    protected function ensureFileIsReadable()
    {
        if (!is_readable($this->filePath) || !is_file($this->filePath)) {
            throw new UnableReadFileException(sprintf('Unable to read the file at %s.', $this->filePath));
        }
    }


    public function content()
    {
        $this->ensureFileIsReadable();

        return file_get_contents($this->filePath);
    }


    public function lines()
    {
        $content = [];
        $lines   = $this->readLinesFromFile();

        foreach ($lines as $row => $line) {
            $data = [
                'line'        => $row+1,
                'raw_data'    => $line,
                'parsed_data' => $this->formatter->parseLine($line)
            ];

            $content[] = $data;
        }

        return $content;
    }


    public function keys()
    {
        $content = [];
        $lines   = $this->readLinesFromFile();

        foreach ($lines as $row => $line) {
            $data = $this->formatter->parseLine($line);
            if ($data['type'] == 'setter') {
                $content[$data['key']] = [
                    'line'    => $row+1,
                    'export'  => $data['export'],
                    'value'   => $data['value'],
                    'comment' => $data['comment']
                ];
            }
        }

        return $content;
    }


    protected function readLinesFromFile()
    {
        $this->ensureFileIsReadable();

        $autodetect = ini_get('auto_detect_line_endings');
        ini_set('auto_detect_line_endings', '1');
        $lines = file($this->filePath, FILE_IGNORE_NEW_LINES);
        ini_set('auto_detect_line_endings', $autodetect);

        return $lines;
    }
}
