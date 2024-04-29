<?php

namespace Framework\Filesystem;

use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Path;
use Symfony\Component\Finder\Finder;

use Framework\Exceptions\Filesystem\ResourceNotFound;
use Framework\Exceptions\Filesystem\ResourceOutsideScope;
use Framework\Exceptions\Filesystem\ResourceAlreadyExists;

use Exception;

/**
 * Class Disk
 *
 * This class represents a file system with tools for managing files and directories
 * within a specified scope inside a given root.
 *
 * @package Framework\Filesystem
 */
class Disk
{
    /**
     * Filesystem instance.
     *
     * @var \Framework\Filesystem\Filesystem
     */
    private $fs;

    /**
     * The path to the main file system directory (root).
     *
     * @var string
     */
    protected $root;

    /**
     * URL that can be used for generating file access addresses.
     *
     * @var string
     */
    protected $url;

    /**
     * A flag indicating whether exceptions are thrown during file operations.
     *
     * @var bool
     */
    protected $throw;

    /**
     * A flag indicating whether exceptions are logged in an event log during file operations.
     *
     *
     * @var bool
     */
    protected $log_exceptions;

    /**
     * Constructor for the class.
     *
     * Initializes a new Disk instance with the provided properties and Filesystem instance.
     *
     * @param array $properties An array of disk properties, including 'root', 'url', and 'throw'.
     * @param \Framework\Filesystem\Filesystem $fs Filesystem instance used for file operations.
     */
    public function __construct($properties, Filesystem $fs)
    {
        $this->fs = $fs;
        forEach($properties as $key => $property) {
            $this->$key = $property;
        }
    }

    /**
     * Checks if a given path is within the specified scope inside the root.
     *
     * @param string $path The path to check.
     * @return bool True if the path is within the scope, false otherwise.
     */
    private function isInsideScope($path)
    {
        $realScopePath = realpath($this->root);
        $realRequestedPath = realpath($path);
        if (empty($realRequestedPath)) {
            $realRequestedPath = absolute_path($path);
        }
        return strpos($realRequestedPath, $realScopePath) === 0;
    }

    /**
     * Determine if exceptions should be thrown.
     *
     * @return bool
     */
    protected function throwsExceptions(): bool
    {
        return (bool) ($this->throw ?? false);
    }

    /**
     * Log an exception if the log_exceptions flag is enabled and 'throw' flag is not set for the current disk.
     *
     * This method is responsible for logging exceptions when the 'log_exceptions' flag is enabled.
     * If the 'throw' flag is set, it instead uses the `throw_if` method
     * to conditionally log the exception based on the 'throwsExceptions' method.
     *
     * @param Throwable $exception The exception to log if conditions are met.
     */
    protected function logExceptionIfEnabled($exception)
    {
        if (!$this->log_exceptions || $this->throw) return;

        logger()->loggable($exception);
    }

    /**
     * Checks the accessibility of a path within the specified scope and optionally whether it exists.
     *
     * @param string $path The path to check.
     * @param bool $mustExist Specifies whether the path must exist (default is true).
     * @return string The path after checking accessibility.
     * @throws ResourceOutsideScope If the path is outside the allowed scope.
     * @throws ResourceNotFound If the path does not exist (if $mustExist = true).
     */
    private function checkAccessAndExistence($path, $mustExist = true)
    {
        $fullPath = $this->root."/".$path;
        if (!$this->isInsideScope($fullPath)) {
            throw new ResourceOutsideScope('Access denied. Resource is outside the allowed scope.', ['resourcePath' => $fullPath]);
        }

        if ($mustExist && !$this->fs->exists($fullPath)) {
            throw new ResourceNotFound('Resource not found', ['resourcePath' => $fullPath]);
        }

        return $fullPath;
    }

    /**
     * Lists only files in a given directory, optionally including hidden files.
     *
     * @param string $directory The directory path.
     * @param bool $hidden Indicates whether hidden files should be included.
     * @return array An array of file paths.
     * @throws \Error If access is denied or the directory does not exist.
     */
    public function files($directory = '', $hidden = false)
    {
        try {
            $sourceDirectory = $this->checkAccessAndExistence($directory);
        } catch (ResourceNotFound|ResourceOutsideScope $e) {
            $this->logExceptionIfEnabled($e);
            throw_if($this->throwsExceptions(), $e);

            return false;
        }

        return iterator_to_array(
            Finder::create()->files()->ignoreDotFiles(!$hidden)->in($sourceDirectory)->depth(0)->sortByName(),
            false
        );
    }

    /**
     * Lists all files in a given directory with depth, optionally including hidden files.
     *
     * @param string $directory The directory path.
     * @param bool $hidden Indicates whether hidden files should be included.
     * @return array An array of file paths.
     * @throws \Error If access is denied or the directory does not exist.
     */
    public function allFiles($directory = '', $hidden = false)
    {
        try {
            $sourceDirectory = $this->checkAccessAndExistence($directory);
        } catch (ResourceNotFound|ResourceOutsideScope $e) {
            $this->logExceptionIfEnabled($e);
            throw_if($this->throwsExceptions(), $e);

            return false;
        }

        return iterator_to_array(
            Finder::create()->files()->ignoreDotFiles(!$hidden)->in($sourceDirectory)->sortByName(),
            false
        );
    }

    /**
     * Lists all directories in a given directory.
     *
     * @param string $directory The directory path.
     * @return array An array of directory paths.
     * @throws \Error If access is denied or the directory does not exist.
     */
    public function directories($directory = '')
    {
        try {
            $sourceDirectory = $this->checkAccessAndExistence($directory);
        } catch (ResourceNotFound|ResourceOutsideScope $e) {
            $this->logExceptionIfEnabled($e);
            throw_if($this->throwsExceptions(), $e);

            return false;
        }

        $directories = [];

        foreach (Finder::create()->in($sourceDirectory)->directories()->depth(0)->sortByName() as $dir) {
            $directories[] = $dir->getPathname();
        }

        return $directories;
    }

    /**
     * Creates a new directory at the specified path.
     *
     * @param string $path The directory path.
     * @param int $mode The directory mode (permissions).
     * @param bool $recursive Whether to create parent directories if they do not exist.
     * @return bool True on success, false on failure.
     * @throws \Error If access is denied or the directory already exists.
     */
    public function makeDirectory($path, $mode = 0775, $recursive = false)
    {
        try {
            $sourcePath = $this->checkAccessAndExistence($path, false);
        } catch (ResourceNotFound|ResourceOutsideScope $e) {
            $this->logExceptionIfEnabled($e);
            throw_if($this->throwsExceptions(), $e);

            return false;
        }

        if ($this->fs->exists($sourcePath)) {
            $e = new ResourceAlreadyExists('Resource already exists.', ['resourcePath' => $sourcePath]);
            $this->logExceptionIfEnabled($e);
            throw_if($this->throwsExceptions(), $e);
            return false;
        }

        try {
            $result = $this->fs->mkdir($sourcePath, 0775, $recursive);
        } catch (IOException $e) {
            $this->logExceptionIfEnabled($e);
            throw_if($this->throwsExceptions(), $e);

            return false;
        }

        return $result;
    }

    /**
     * Puts the given content into a file at the specified destination path.
     *
     * @param string $destination The path where the file should be stored.
     * @param string $content The content to be stored in the file.
     * @return bool True on success, false on failure.
     * @throws \Error If access is denied or there's an error in storing the content.
     */
    public function put($destination, $content)
    {
        try {
            $destinationPath = $this->checkAccessAndExistence($destination, false);
        } catch (ResourceNotFound|ResourceOutsideScope $e) {
            $this->logExceptionIfEnabled($e);
            throw_if($this->throwsExceptions(), $e);

            return false;
        }

        try {
            $result = $this->fs->put($destinationPath, $content);
        } catch (IOException $e) {
            $this->logExceptionIfEnabled($e);
            throw_if($this->throwsExceptions(), $e);

            return false;
        }
    }

    /**
     * Moves a file or directory to a new location.
     *
     * @param string $source The source path.
     * @param string $destination The destination path.
     * @return bool True on success, false on failure.
     * @throws \Error If access is denied, the source does not exist, or the destination already exists.
     *
     * @note This method is an alias for `move`. They have the same functionality.
     */
    public function rename($source, $destination)
    {
        return $this->move($source, $destination);
    }

    /**
     * Moves a file or directory to a new location.
     *
     * @param string $source The source path.
     * @param string $destination The destination path.
     * @return bool True on success, false on failure.
     * @throws \Error If access is denied, the source does not exist, or the destination already exists.
     */
    public function move($source, $destination)
    {
        try {
            $sourcePath = $this->checkAccessAndExistence($source);
            $destinationPath = $this->checkAccessAndExistence($destination, false);
        } catch (ResourceNotFound|ResourceOutsideScope $e) {
            $this->logExceptionIfEnabled($e);
            throw_if($this->throwsExceptions(), $e);

            return false;
        }

        if ($this->fs->exists($destinationPath)) {
            $e = new ResourceAlreadyExists('Resource already exists.', ['resourcePath' => $destinationPath]);
            $this->logExceptionIfEnabled($e);
            throw_if($this->throwsExceptions(), $e);
            return false;
        }

        try {
            $result = $this->fs->rename($sourcePath, $destinationPath);
        } catch (IOException $e) {
            $this->logExceptionIfEnabled($e);
            throw_if($this->throwsExceptions(), $e);

            return false;
        }

        return $result;
    }

    /**
     * Copies a file or directory to a new location.
     *
     * @param string $source The source path.
     * @param string $destination The destination path.
     * @return bool True on success, false on failure.
     * @throws \Error If access is denied, the source does not exist, or the destination already exists.
     */
    public function copy($source, $destination)
    {
        try {
            $sourcePath = $this->checkAccessAndExistence($source);
            $destinationPath = $this->checkAccessAndExistence($destination, false);
        } catch (ResourceNotFound|ResourceOutsideScope $e) {
            $this->logExceptionIfEnabled($e);
            throw_if($this->throwsExceptions(), $e);

            return false;
        }

        if ($this->fs->exists($destinationPath)) {
            $e = new ResourceAlreadyExists('Resource already exists.', ['resourcePath' => $destinationPath]);
            $this->logExceptionIfEnabled($e);
            throw_if($this->throwsExceptions(), $e);
            return false;
        }

        try {
            if (is_dir($sourcePath)) {
                $result = $this->fs->mirror($sourcePath, $destinationPath);
            } else {
                $result = $this->fs->copy($sourcePath, $destinationPath);
            }
        } catch (IOException $e) {
            $this->logExceptionIfEnabled($e);
            throw_if($this->throwsExceptions(), $e);

            return false;
        }

        return $result;
    }

    /**
     * Removes a file or directory.
     *
     * @param string $source The source path.
     * @return bool True on success, false on failure.
     * @throws \Error If access is denied or the source does not exist.
     *
     * @note This method is an alias for `remove`. They have the same functionality.
     */
    public function delete($source)
    {
        return $this->remove($source);
    }

    /**
     * Removes a file or directory.
     *
     * @param string $source The source path.
     * @return bool True on success, false on failure.
     * @throws \Error If access is denied or the source does not exist.
     */
    public function remove($source)
    {
        try {
            $sourcePath = $this->checkAccessAndExistence($source);
        } catch (ResourceNotFound|ResourceOutsideScope $e) {
            $this->logExceptionIfEnabled($e);
            throw_if($this->throwsExceptions(), $e);

            return false;
        }

        try {
            $result = $this->fs->remove($sourcePath);
        } catch (IOException $e) {
            $this->logExceptionIfEnabled($e);
            throw_if($this->throwsExceptions(), $e);

            return false;
        }

        return $result;
    }

    /**
     * Stores the content in a text file at the specified path.
     *
     * @param string $source The path to the text file.
     * @param string $content The content to be stored.
     * @return bool True on success, false on failure.
     * @throws \Error If access is denied, the file does not exist, or there's an error in storing the content.
     */
    public function storeTextFile($source, $content)
    {
        try {
            $sourcePath = $this->checkAccessAndExistence($source.'.txt', false);
        } catch (ResourceNotFound|ResourceOutsideScope $e) {
            $this->logExceptionIfEnabled($e);
            throw_if($this->throwsExceptions(), $e);

            return false;
        }

        try {
            $result = $this->fs->dumpFile($sourcePath, $content);
        } catch (IOException $e) {
            $this->logExceptionIfEnabled($e);
            throw_if($this->throwsExceptions(), $e);

            return false;
        }

        return $result;
    }

    /**
     * Writes content to a text file at the specified path, optionally appending to an existing file.
     *
     * @param string $source The path to the text file.
     * @param string $content The content to be written.
     * @param bool $newLine Indicates whether a new line character should be added before the content.
     * @return bool True on success, false on failure.
     * @throws \Error If access is denied, the file does not exist, or there's an error in writing the content.
     */
    public function writeTextFile($source, $content, $writeAtBeginning = false, $newLine = true)
    {
        try {
            $sourcePath = $this->checkAccessAndExistence($source.'.txt', false);
        } catch (ResourceNotFound|ResourceOutsideScope $e) {
            $this->logExceptionIfEnabled($e);
            throw_if($this->throwsExceptions(), $e);

            return false;
        }

        try {
            if (!$this->fs->exists($sourcePath)) {
                $result = $this->fs->dumpFile($sourcePath, $content);
            } else {
                if ($writeAtBeginning) {
                    $existingContent = $this->fs->get($sourcePath);

                    if ($newLine) $content = $content.PHP_EOL;

                    $content = $content.$existingContent;

                    $result = $this->fs->dumpFile($sourcePath, $content);
                } else {
                    if ($newLine) $content = PHP_EOL.$content;
                    $result = $this->fs->appendToFile($sourcePath, $content);
                }
            }
        } catch (IOException $e) {
            $this->logExceptionIfEnabled($e);
            throw_if($this->throwsExceptions(), $e);

            return false;
        }

        return $result;
    }

    /**
     * Saves an uploaded file to the specified directory with the specified filename, optionally overwriting an existing file.
     *
     * @param $file The file to be saved.
     * @param string $source The path to the destination directory.
     * @param string $filename The name of the file.
     * @param bool $overwrite Indicates whether to overwrite an existing file with the same name.
     * @return bool True on success, false on failure.
     * @throws \Error If access is denied, the source directory does not exist, or there's an error in moving the file.
     */
    public function saveFile($file, $source, $filename, $overwrite = false)
    {
        try {
            $sourcePath = $this->checkAccessAndExistence($source);
        } catch (ResourceNotFound|ResourceOutsideScope $e) {
            $this->logExceptionIfEnabled($e);
            throw_if($this->throwsExceptions(), $e);

            return false;
        }

        if (substr($sourcePath, -1) !== '/') {
            $sourcePath = rtrim($sourcePath, '/') . '/';
        }

        if (!$overwrite && $this->fs->exists($sourcePath.$filename)) {
            $e = new ResourceAlreadyExists('Resource already exists.', ['resourcePath' => $sourcePath.$filename]);
            $this->logExceptionIfEnabled($e);
            throw_if($this->throwsExceptions(), $e);
            return false;
        }

        try {
            $result = $file->move($sourcePath, $filename);
        } catch (IOException $e) {
            $this->logExceptionIfEnabled($e);
            throw_if($this->throwsExceptions(), $e);

            return false;
        }

        return $result;
    }

    /**
     * Generates a fully qualified URL for the given file or directory path.
     *
     * @param string $path The path of the file or directory.
     * @return string The fully qualified URL.
     */
    public function url($path)
    {
        return rtrim($this->url,'/').'/'.ltrim($path, '/');
    }

    /**
     * Retrieves the content of a file at the specified path within the allowed scope.
     *
     * @param string $path The path to the file.
     * @return string|null The content of the file, or null if the file is not found or an error occurs.
     */
    public function get($path)
    {
        $sourcePath = $this->checkAccessAndExistence($path, false);

        try {
            return $this->fs->get($sourcePath);
        } catch(Exception $e) {
            return null;
        }
    }

    /**
     * Checks if a file or directory exists at the specified path within the allowed scope.
     *
     * @param string $source The path to check for existence.
     * @return bool True if the file or directory exists, false otherwise.
     * @throws \Error If access is denied.
     */
    public function exists($source)
    {
        $sourcePath = $this->checkAccessAndExistence($source, false);

        return $this->fs->exists($sourcePath);
    }

    /**
     * Checks if a path points to a file.
     *
     * @param string $source The path to check.
     * @return bool True if the path is a file, false if not.
     * @throws \Error If access is denied or the file does not exist.
     */
    public function isFile($source)
    {
        $sourcePath = $this->checkAccessAndExistence($source);

        return $this->fs->isFile($sourcePath);
    }

    /**
     * Checks if a path points to a directory.
     *
     * @param string $source The path to check.
     * @return bool True if the path is a directory, false if not.
     * @throws \Error If access is denied or the directory does not exist.
     */
    public function isDir($source)
    {
        $sourcePath = $this->checkAccessAndExistence($source);

        return $this->fs->isDir($sourcePath);
    }

    /**
     * Retrieves the size of a file in bytes.
     *
     * @param string $source The file path.
     * @return int The file size in bytes.
     * @throws \Error If access is denied or the file does not exist.
     */
    public function fileSize($source)
    {
        $sourcePath = $this->checkAccessAndExistence($source);

        return $this->fs->size($sourcePath);
    }

    /**
     * Retrieves the size of a file in a human-readable format (e.g., "2.50MB").
     *
     * @param string $source The file path.
     * @return string The formatted file size.
     * @throws \Error If access is denied or the file does not exist.
     */
    public function formattedFileSize($source) {
        $bytes = $this->fileSize($source);
        $sz = 'BKMGTP';
        $factor = floor((strlen($bytes) - 1) / 3);
        return sprintf("%.2f", $bytes / pow(1024, $factor)) . @$sz[$factor];
    }

    /**
     * Retrieves the last modified timestamp of a file or directory.
     *
     * @param string $source The path to the file or directory.
     * @return int The Unix timestamp representing the last modification time.
     * @throws \Error If access is denied or the file or directory does not exist.
     */
    public function lastModified($source)
    {
        $sourcePath = $this->checkAccessAndExistence($source);

        return $this->fs->lastModified($sourcePath);
    }

    /**
     * Retrieves the size of a directory in bytes.
     *
     * @param string $directory The directory path.
     * @return int The directory size in bytes.
     * @throws \Error If access is denied, the directory does not exist, or it is not a directory.
     */
    public function dirSize($directory)
    {
        $sourceDirectory = $this->checkAccessAndExistence($directory);
        $totalSize = 0;

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($sourceDirectory),
            \RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $totalSize += $file->getSize();
            }
        }

        return $totalSize;
    }

    /**
     * Retrieves the size of a directory in a human-readable format (e.g., "2.50MB").
     *
     * @param string $directory The directory path.
     * @return string The formatted directory size.
     * @throws \Error If access is denied, the directory does not exist, or it is not a directory.
     */
    public function formattedDirSize($source) {
        $bytes = $this->dirSize($source);
        $sz = 'BKMGTP';
        $factor = floor((strlen($bytes) - 1) / 3);
        return sprintf("%.2f", $bytes / pow(1024, $factor)) . @$sz[$factor];
    }

}