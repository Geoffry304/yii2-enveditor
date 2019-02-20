<?php namespace geoffry304\enveditor\helpers;

use geoffry304\enveditor\interfaces\DotenvFormatter as DotenvFormatterInterface;
use geoffry304\enveditor\interfaces\DotenvWriter as DotenvWriterInterface;
use geoffry304\enveditor\exceptions\UnableWriteToFileException;

/**
 * The DotenvWriter writer.
 *
 * @package app\components\dotenveditor
 * @author Jackie Do <anhvudo@gmail.com>
 */
class DotenvWriter implements DotenvWriterInterface
{

    protected $buffer;


    protected $formatter;


    public function __construct(DotenvFormatterInterface $formatter)
    {
        $this->formatter = $formatter;
    }


    protected function ensureFileIsWritable($filePath)
    {
        if ((is_file($filePath) && !is_writable($filePath)) || (!is_file($filePath) && !is_writable(dirname($filePath)))) {
            throw new UnableWriteToFileException(sprintf('Unable to write to the file at %s.', $filePath));
        }
    }


    public function setBuffer($content)
    {
        $this->buffer = $content;
        return $this;
    }


    public function getBuffer()
    {
        return $this->buffer;
    }


    protected function appendLine($text = null)
    {
        $this->buffer .= $text . PHP_EOL;
        return $this;
    }


    public function appendEmptyLine()
    {
        return $this->appendLine();
    }


    public function appendCommentLine($comment)
    {
        return $this->appendLine('# ' . $comment);
    }


    public function appendSetter($key, $value = null, $comment = null, $export = false)
    {
        $line = $this->formatter->formatSetterLine($key, $value, $comment, $export);

        return $this->appendLine($line);
    }


    public function updateSetter($key, $value = null, $comment = null, $export = false)
    {
        $pattern = "/^(export\h)?\h*{$key}=.*/m";
        $line = $this->formatter->formatSetterLine($key, $value, $comment, $export);
        $this->buffer = preg_replace($pattern, $line, $this->buffer);

        return $this;
    }


    public function deleteSetter($key)
    {
        $pattern = "/^(export\h)?\h*{$key}=.*\n/m";
        $this->buffer = preg_replace($pattern, null, $this->buffer);

        return $this;
    }


    public function save($filePath)
    {
        $this->ensureFileIsWritable($filePath);
        file_put_contents($filePath, $this->buffer);

        return $this;
    }
}
