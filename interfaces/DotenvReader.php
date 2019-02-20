<?php namespace geoffry304\enveditor\interfaces;

interface DotenvReader
{
    /**
     * Load .env file
     *
     * @param  string $filePath
     */
    public function load($filePath);

    /**
     * Get content of .env file
     */
    public function content();

    /**
     * Get all lines informations from content of .env file
     */
    public function lines();

    /**
     * Get all key informations in .env file
     */
    public function keys();
}
