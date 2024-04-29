<?php

namespace Framework\View\Compilers;

use Framework\Filesystem\Filesystem;
use Framework\Support\Str;

/**
 * Class Compiler
 *
 * The base class for view compilers in the framework.
 * Handles common functionality such as checking expiration, determining compiled paths, and providing a constructor.
 *
 * @package Framework\View\Compilers
 */
abstract class Compiler
{
    /**
     * The Filesystem instance used for file operations.
     *
     * @var \Framework\Filesystem\Filesystem
     */
    protected $fs;

    /**
     * The base path for views.
     *
     * @var string
     */
    protected $basePath;

    /**
     * The extension for compiled views.
     *
     * @var string
     */
    protected $compiledExtension;

    /**
     * Compiler constructor.
     *
     * @param Filesystem $fs                The Filesystem instance for file operations.
     * @param string     $basePath          The base path for views.
     * @param string     $compiledExtension The extension for compiled views.
     */
    public function __construct(Filesystem $fs, $basePath = '', $compiledExtension = 'php')
    {
        $this->fs = $fs;
        $this->basePath = $basePath;
        $this->compiledExtension = $compiledExtension;
    }

    /**
     * Checks if the compiled version of the view is expired and needs recompilation.
     *
     * @param string $path The path to the original view file.
     *
     * @return bool Returns true if the compiled view is expired, otherwise false.
     */
    public function isExpired($path)
    {
        $compiled = $this->getCompiledPath($path);

        if (!$this->fs->exists($compiled)) {
            return true;
        }

        return $this->fs->lastModified($path) >= $this->fs->lastModified($compiled);
    }

    /**
     * Generates the compiled path for a given view file.
     *
     * @param string $path The path to the original view file.
     *
     * @return string The compiled path for the view.
     */
    public function getCompiledPath($path)
    {
        return app()->storagePath('framework/views').'/'.hash('xxh128', 'v2'.Str::after($path, $this->basePath)).'.'.$this->compiledExtension;
    }
}