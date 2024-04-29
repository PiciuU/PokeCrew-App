<?php

namespace Framework\Console\Commands;

/**
 * Class Storage
 *
 * This class provides commands for managing storage links. It allows checking the status of a storage link,
 * creating a new link, and removing an existing link. These commands are useful for controlling the visibility
 * of storage resources to the public, such as user-uploaded files, in the framework's public directory.
 *
 * @package Framework\Console\Commands
 */
class Storage
{
    /**
     * Check the status of a storage link.
     *
     * @param string $linkName The name of the storage link (default is 'storage').
     * @throws \Exception If the link is outside the allowed scope.
     */
    public function execute($linkName = 'storage') : void
    {
        $storagePath = realpath('storage/app/public');
        $publicPath = realpath('public').'/'.$linkName;

        if (is_link($publicPath) || file_exists($publicPath)) {
            echo "The [$publicPath] link is connected to [$storagePath].";
        } else {
            echo "The [$publicPath] link does not exist.";
        }
    }

    /**
     * Create a new storage link.
     *
     * @param string $linkName The name of the storage link (default is 'storage').
     * @throws \Exception If the link is outside the allowed scope or if an error occurs during link creation.
     */
    public function link($linkName = 'storage') : void
    {
        $storagePath = realpath('storage/app/public');
        $publicPath = realpath('public').'/'.$linkName;

        if (!$this->isInsideScope($publicPath, realpath('public'))) {
            throw new \Exception("The [$publicPath] link is outside the allowed scope and cannot be unlinked.");
        }

        if (PHP_OS_FAMILY !== 'Windows') {
            if (is_link($publicPath)) {
                throw new \Exception("The [$publicPath] link already exists.");
            }

            symlink($storagePath, $publicPath);
            return;
        }

        if (file_exists($publicPath)) {
            throw new \Exception("The [$publicPath] link already exists.");
        }

        $mode = is_dir($storagePath) ? 'J' : 'H';

        exec("mklink /{$mode} " . escapeshellarg($publicPath) . ' ' . escapeshellarg($storagePath) . ' 2>&1', $output, $returnCode);

        if ($returnCode !== 0) {
            throw new \Exception("An error occurred while creating the link: ".PHP_EOL."Return Code: $returnCode".PHP_EOL."Error Message: ".implode(PHP_EOL,$output));
        }

        echo "The [$publicPath] link has been connected to [$storagePath].";
    }

    /**
     * Remove an existing storage link.
     *
     * @param string $linkName The name of the storage link (default is 'storage').
     * @throws \Exception If the link is outside the allowed scope or if an error occurs during link removal.
     */
    public function unlink($linkName = 'storage') : void
    {
        $publicPath = realpath('public').'/'.$linkName;

        if (!$this->isInsideScope($publicPath, realpath('public'))) {
            throw new \Exception("The [$publicPath] link is outside the allowed scope and cannot be unlinked.");
        }

        if (PHP_OS_FAMILY !== 'Windows') {
            if (!file_exists($publicPath)) {
                throw new \Exception("The [$publicPath] link does not exist.");
            }

            unlink($publicPath);
            return;
        }

        if (!file_exists($publicPath)) {
            throw new \Exception("The [$publicPath] link does not exist.");
        }

        exec("rmdir /q /s " . escapeshellarg($publicPath) . ' 2>&1', $output, $returnCode);

        if ($returnCode !== 0) {
            throw new \Exception("An error occurred while deleting the link: ".PHP_EOL."Return Code: $returnCode".PHP_EOL."Error Message: ".implode(PHP_EOL,$output));
        }

        echo "The [$publicPath] link has been deleted.";
    }

    /**
     * Checks if a given path is within the specified scope.
     *
     * @param string $path The path to check.
     * @param string $scope The allowed scope path (root).
     * @return bool True if the path is within the scope, false otherwise.
     */
    private function isInsideScope($path, $scope) : bool
    {
        $realPath = $this->getAbsoluteFilePath($path);
        $realScope = realpath($scope);

        return $realPath !== $realScope && strpos($realPath, $realScope) === 0;
    }

    /**
     * Converts a path to an absolute path, removing references to parent directories.
     *
     * @param string $path The path to convert.
     * @return string The absolute path after conversion.
     */
    private function getAbsoluteFilePath($path) : string
    {
        $path = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);
        $parts = explode(DIRECTORY_SEPARATOR, $path);

        $absolutes = [];
        foreach ($parts as $part) {
            if ($part === '.' || $part === '') {
                continue;
            }

            if ($part === '..') {
                array_pop($absolutes);
            } else {
                $absolutes[] = $part;
            }
        }

        $absolutePath = implode(DIRECTORY_SEPARATOR, $absolutes);

        if (PHP_OS_FAMILY !== "Windows") {
            $absolutePath = DIRECTORY_SEPARATOR . $absolutePath;
        }

        return $absolutePath;
    }

}