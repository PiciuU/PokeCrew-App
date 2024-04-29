<?php

namespace Framework\View\Engines;

use Framework\Filesystem\Filesystem;

/**
 * Class FileEngine
 *
 * The FileEngine class is a basic view engine that retrieves and returns the content of a file as is.
 * It relies on the Filesystem component to read the contents of the specified file.
 *
 * @package Framework\View\Engines
 */
class FileEngine
{
    /**
     * The filesystem instance.
     *
     * @var \Framework\Filesystem\Filesystem
     */
    protected $fs;

    /**
     * Create a new FileEngine instance.
     *
     * @param \Framework\Filesystem\Filesystem $fs The Filesystem instance.
     */
    public function __construct(Filesystem $fs)
    {
        $this->fs = $fs;
    }

    /**
     * Get the contents of the view.
     *
     * @param string $path The path to the view file.
     * @param array $data The data to pass to the view (not used in this implementation).
     * @return string The raw content of the specified file.
     */
    public function get($path, array $data = [])
    {
        return $this->fs->get($path);
    }
}
