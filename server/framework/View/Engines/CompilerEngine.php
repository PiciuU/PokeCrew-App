<?php

namespace Framework\View\Engines;

use Framework\Filesystem\Filesystem;

use Framework\View\Compilers\VisionCompiler;

use Throwable;

/**
 * Class CompilerEngine
 *
 * The CompilerEngine class extends the PhpEngine and provides a view engine that uses the VisionCompiler for compiling
 * Vision template files. It checks if the compiled file is expired and recompiles it if needed.
 *
 * @package Framework\View\Engines
 */
class CompilerEngine extends PhpEngine
{
    /**
     * The VisionCompiler instance.
     *
     * @var \Framework\View\Compilers\VisionCompiler
     */
    protected $compiler;

     /**
     * Create a new CompilerEngine instance.
     *
     * @param \Framework\View\Compilers\VisionCompiler $compiler The VisionCompiler instance.
     * @param \Framework\Filesystem\Filesystem|null $fs The Filesystem instance.
     */
    public function __construct(VisionCompiler $compiler, Filesystem $fs = null)
    {
        parent::__construct($fs ?: new Filesystem());

        $this->compiler = $compiler;
    }

    /**
     * Get the evaluated contents of the view.
     *
     * @param string $path The path to the view file.
     * @param array $data The data to pass to the view.
     * @return string The evaluated content of the view.
     * @throws \Exception If an exception occurs during evaluation.
     */
    public function get($path, array $data = [])
    {
        if ($this->compiler->isExpired($path)) {
            $this->compiler->compile($path);
        }

        try {
            $result = $this->evaluatePath($this->compiler->getCompiledPath($path), $data);
        } catch (Exception $e) {
            throw $e;
        }

        return $result;
    }
}
