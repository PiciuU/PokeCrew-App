<?php

namespace Framework\View\Engines;

use Framework\Filesystem\Filesystem;
use Framework\View\Compilers\VisionCompiler;

use Closure;
use InvalidArgumentException;

/**
 * Class EngineResolver
 *
 * The EngineResolver class is responsible for managing and resolving view engine instances based on their type.
 *
 * @package Framework\View\Engines
 */
class EngineResolver
{
    /**
     * The array of engine resolvers.
     *
     * @var array
     */
    protected $resolvers;

    /**
     * Create a new EngineResolver instance.
     *
     * @param \Framework\Filesystem\Filesystem $fs The Filesystem instance.
     */
    public function __construct(Filesystem $fs)
    {
        $visionCompiler = new VisionCompiler($fs);

        $this->register('file', FileEngine::class, $fs);
        $this->register('php', PhpEngine::class, $fs);
        $this->register('vision', CompilerEngine::class, $visionCompiler, $fs);
    }

    /**
     * Register a new engine resolver.
     *
     * The engine string typically corresponds to a file extension.
     *
     * @param  string  $engine The engine type or file extension.
     * @param  string  $class The class name of the engine.
     * @param  mixed  ...$parameters Additional parameters for the engine constructor.
     * @return void
     */
    public function register($engine, $class, ...$parameters)
    {
        $this->resolvers[$engine] = new $class(...$parameters);
    }

    /**
     * Resolve an engine instance by name.
     *
     * @param  string  $engine The engine type or file extension.
     * @return \Framework\View\Engines\CompilerEngine|\Framework\View\Engines\PhpEngine|\Framework\View\Engines\FileEngine The resolved engine instance.
     *
     * @throws \InvalidArgumentException If the specified engine is not registered.
     */
    public function resolve($engine)
    {
        return $this->resolvers[$engine];

        throw new InvalidArgumentException("Engine [{$engine}] not found.");
    }
}
