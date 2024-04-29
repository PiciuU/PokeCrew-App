<?php

namespace Framework\View\Engines;

use Framework\Filesystem\Filesystem;
use Exception;
use Throwable;

/**
 * Class PhpEngine
 *
 * The PhpEngine class is a simple view engine that evaluates the contents of a PHP view file.
 * It provides methods to get the evaluated content and handle view exceptions.
 *
 * @package Framework\View\Engines
 */
class PhpEngine
{
    /**
     * The filesystem instance.
     *
     * @var \Framework\Filesystem\Filesystem
     */
    protected $fs;

    /**
     * Create a new PhpEngine instance.
     *
     * @param \Framework\Filesystem\Filesystem $fs The Filesystem instance.
     */
    public function __construct(Filesystem $fs)
    {
        $this->fs = $fs;
    }

    /**
     * Get the evaluated contents of the view.
     *
     * @param string $path The path to the view file.
     * @param array $data The data to pass to the view.
     * @return string The evaluated content of the view.
     */
    public function get($path, array $data = [])
    {
        return $this->evaluatePath($path, $data);
    }

    /**
     * Get the evaluated contents of the view at the given path.
     *
     * @param string $path The path to the view file.
     * @param array $data The data to pass to the view.
     * @return string The evaluated content of the view.
     */
    public function evaluatePath($path, $data)
    {
        $obLevel = ob_get_level();

        ob_start();

        try {
            $this->fs->getRequire($path, $data);
        } catch (Throwable $e) {
            $this->handleViewException($e, $obLevel);
        }

        return ltrim(ob_get_clean());
    }

     /**
     * Handle a view exception.
     *
     * @param \Throwable $e The exception that occurred during view evaluation.
     * @param int $obLevel The output buffer level.
     * @return void
     *
     * @throws \Throwable
     */
    protected function handleViewException(Throwable $e, $obLevel)
    {
        while (ob_get_level() > $obLevel) {
            ob_end_clean();
        }

        throw $e;
    }
}
