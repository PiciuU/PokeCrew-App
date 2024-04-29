<?php

namespace Framework\View;

use Framework\Filesystem\Filesystem;
use Framework\Support\Arr;
use Framework\Database\ORM\Collection;

use Framework\View\Engines\EngineResolver;

use InvalidArgumentException;

/**
 * Class Factory
 *
 * The Factory class is responsible for creating view instances and managing view engines.
 *
 * @package Framework\View
 */
class Factory
{
    /**
     * The view finder instance.
     *
     * @var \Framework\View\Finder
     */
    protected $finder;

    /**
     * The engine resolver instance.
     *
     * @var \Framework\View\Engines\EngineResolver
     */
    protected $engines;

    /**
     * The supported file extensions and their corresponding engines.
     *
     * @var array
     */
    protected $extensions = [
        'vision.php' => 'vision',
        'php' => 'php',
        'js' => 'file',
        'css' => 'file',
        'html' => 'file',
    ];

    /**
     * Create a new Factory instance.
     */
    public function __construct()
    {
        $fs = new Filesystem();
        $this->finder = new Finder($fs, app()->viewPaths(), array_keys($this->extensions));
        $this->engines = new EngineResolver($fs);
    }

    /**
     * Make a new view instance.
     *
     * @param  string  $view The view name or path.
     * @param  array  $data The data to pass to the view.
     * @param  array  $mergeData Additional data to merge with the view data.
     * @return \Framework\View\View The created view instance.
     */
    public function make($view, $data = [], $mergeData = [])
    {
        $path = $this->finder->find(
            $view = $this->normalizeName($view)
        );

        $data = array_merge($mergeData, $this->parseData($data));

        return $this->viewInstance($view, $path, $data);
    }

    /**
     * Normalize the view name.
     *
     * @param  string  $name The original view name.
     * @return string The normalized view name.
     */
    protected function normalizeName($name)
    {
        return str_replace('/', '.', $name);
    }

    /**
     * Parse the view data.
     *
     * @param  mixed  $data The original data.
     * @return array The parsed data.
     */
    protected function parseData($data)
    {
        return $data instanceof Collection ? $data->toArray() : $data;
    }

    /**
     * Create a new view instance.
     *
     * @param  string  $view The view name.
     * @param  string  $path The path to the view file.
     * @param  array  $data The data to pass to the view.
     * @return \Framework\View\View The created view instance.
     */
    protected function viewInstance($view, $path, $data)
    {
        return new View($this, $this->getEngineFromPath($path), $view, $path, $data);
    }

    /**
     * Get the engine based on the file extension.
     *
     * @param  string  $path The path to the view file.
     * @return \Framework\View\Engines\CompilerEngine|\Framework\View\Engines\PhpEngine|\Framework\View\Engines\FileEngine The resolved engine instance.
     *
     * @throws \InvalidArgumentException If the extension is not recognized.
     */
    public function getEngineFromPath($path)
    {
        if (!$extension = $this->getExtension($path)) {
            throw new InvalidArgumentException("Unrecognized extension in file: {$path}.");
        }

        $engine = $this->extensions[$extension];

        return $this->engines->resolve($engine);
    }

    /**
     * Get the file extension from the path.
     *
     * @param  string  $path The path to the file.
     * @return string|null The file extension or null if not found.
     */
    protected function getExtension($path)
    {
        $extensions = array_keys($this->extensions);

        return Arr::first($extensions, function ($value) use ($path) {
            return str_ends_with($path, '.'.$value);
        });
    }
}