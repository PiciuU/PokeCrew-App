<?php

namespace Framework\View;

use Framework\Filesystem\Filesystem;
use InvalidArgumentException;

/**
 * Class Finder
 *
 * The Finder class is responsible for locating views in specified paths based on given names and extensions.
 *
 * @package Framework\View
 */
class Finder
{
    /**
     * The filesystem instance.
     *
     * @var \Framework\Filesystem\Filesystem
     */
    protected $fs;

    /**
     * The array of view paths.
     *
     * @var array
     */
    protected $paths;

    /**
     * Cache of found views.
     *
     * @var array
     */
    protected $views = [];

    /**
     * The supported file extensions for views.
     *
     * @var array
     */
    protected $extensions = ['vision.php', 'php', 'js', 'css', 'html'];

    /**
     * Create a new Finder instance.
     *
     * @param  \Framework\Filesystem\Filesystem  $fs The filesystem instance.
     * @param  array|string  $paths The view paths.
     * @param  array|null  $extensions The supported file extensions for views.
     */
    public function __construct(Filesystem $fs, array|string $paths, array $extensions = null)
    {
        $this->fs = $fs;

        $this->paths = is_array($paths) ? $paths : [$paths];

        if (isset($extensions)) {
            $this->extensions = $extensions;
        }
    }

    /**
     * Find the path to a view based on its name.
     *
     * @param  string  $name The name of the view.
     * @return string The path to the view file.
     *
     * @throws \InvalidArgumentException If the view is not found.
     */
    public function find($name)
    {
        if (isset($this->views[$name])) {
            return $this->views[$name];
        }

        return $this->views[$name] = $this->findInPaths($name, $this->paths);
    }

    /**
     * Find a view within the given paths.
     *
     * @param  string  $name The name of the view.
     * @param  array  $paths The paths to search for the view.
     * @return string The path to the view file.
     *
     * @throws \InvalidArgumentException If the view is not found.
     */
    protected function findInPaths($name, $paths)
    {
        foreach ((array) $paths as $path) {
            foreach ($this->getPossibleViewFiles($name) as $file) {
                if ($this->fs->exists($viewPath = $path.'/'.$file)) {
                    return $viewPath;
                }
            }
        }

        throw new InvalidArgumentException("View [{$name}] not found.");
    }

    /**
     * Get an array of possible view file names with different extensions.
     *
     * @param  string  $name The name of the view.
     * @return array The possible view file names.
     */
    protected function getPossibleViewFiles($name)
    {
        return array_map(fn ($extension) => str_replace('.', '/', $name).'.'.$extension, $this->extensions);
    }

    /**
     * Get the supported file extensions for views.
     *
     * @return array The supported file extensions.
     */
    public function getExtensions()
    {
        return $this->extensions;
    }
}