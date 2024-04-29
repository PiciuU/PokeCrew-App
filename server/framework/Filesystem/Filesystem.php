<?php

namespace Framework\Filesystem;

use Symfony\Component\Filesystem\Filesystem as SymfonyFilesystem;
use Exception;

/**
 * Class Filesystem
 *
 * Extends Symfony's Filesystem class to provide additional file-related functionality.
 *
 * @package Framework\Filesystem
 */
class Filesystem extends SymfonyFilesystem
{
    /**
     * Checks if the given path points to a regular file.
     *
     * @param string $path The path to check.
     * @return bool True if the path points to a file, false otherwise.
     */
    public function isFile($path)
    {
        return is_file($path);
    }

    /**
     * Checks if the given path points to a directory.
     *
     * @param string $path The path to check.
     * @return bool True if the path points to a directory, false otherwise.
     */
    public function isDir($path)
    {
        return is_dir($path);
    }

    /**
     * Retrieves the size of a file in bytes.
     *
     * @param string $path The path to the file.
     * @return int The file size in bytes.
     */
    public function size($path)
    {
        return filesize($path);
    }

    /**
     * Retrieves the last modified timestamp of a file.
     *
     * @param string $path The path to the file.
     * @return int The Unix timestamp representing the last modification time.
     */
    public function lastModified($path)
    {
        return filemtime($path);
    }

    /**
     * Writes contents to a file.
     *
     * @param string $path The path to the file.
     * @param string $contents The contents to write to the file.
     * @return int|bool The number of bytes written to the file or false on failure.
     */
    public function put($path, $contents)
    {
        return file_put_contents($path, $contents);
    }

    /**
     * Reads the contents of a file.
     *
     * @param string $path The path to the file.
     * @return string The contents of the file.
     * @throws Exception If the file does not exist.
     */
    public function get($path)
    {
        if ($this->isFile($path)) return file_get_contents($path);

        throw new Exception("File does not exist at path {$path}.");
    }

    /**
     * Requires the given file and returns its content.
     *
     * @param string $path The path to the file.
     * @param array $data An associative array of data to extract.
     * @return mixed The result of requiring the file.
     * @throws Exception If the file does not exist.
     */
    public function getRequire($path, array $data = [])
    {
        if ($this->isFile($path)) {
            $__path = $path;
            $__data = $data;

            return (static function () use ($__path, $__data) {
                extract($__data, EXTR_SKIP);

                return require $__path;
            })();
        }

        throw new Exception("File does not exist at path {$path}.");
    }

}