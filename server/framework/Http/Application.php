<?php

namespace Framework\Http;

use Framework\Bootstrap\LoadConfiguration;

/**
 * Class Application
 *
 * The main class of the framework application, responsible for managing access to the kernel and router objects.
 * This is the central part of the framework that initializes its operation.
 *
 * @package Framework\Http
 */
class Application extends Container
{
    use LoadConfiguration;

    /**
     * The Dreamfork framework version.
     *
     * @var string
     */
    const VERSION = '1.0.1';

    /**
     * Base path of the application.
     *
     * @var string
     */
    protected $basePath;

    /**
     * Indicates if the application has been bootstrapped before.
     *
     * @var bool
     */
    protected $hasBeenBootstrapped = false;

    /**
     * The custom bootstrap path defined by the developer.
     *
     * @var string
     */
    protected $bootstrapPath;

    /**
     * The custom application path defined by the developer.
     *
     * @var string
     */
    protected $appPath;

    /**
     * The custom configuration path defined by the developer.
     *
     * @var string
     */
    protected $configPath;

    /**
     * The custom public / web path defined by the developer.
     *
     * @var string
     */
    protected $publicPath;

    /**
     * The custom storage path defined by the developer.
     *
     * @var string
     */
    protected $storagePath;

    /**
     * Application constructor.
     *
     * @param string $basePath The base path of the application.
     */
    public function __construct($basePath = null)
    {
        if ($basePath) {
            $this->setBasePath($basePath);
        }

        $this->registerBaseBindings();

        $this->registerCoreContainerAliases();
    }

    /**
     * Determine if the application has been bootstrapped before.
     *
     * @return bool
     */
    public function hasBeenBootstrapped()
    {
        return $this->hasBeenBootstrapped;
    }

    /**
     * Run the bootstrap for application.
     *
     * @return void
     */
    public function bootstrapApplication()
    {
        $this->hasBeenBootstrapped = true;

        $this->bootstrap();
    }

    /**
     * Get the version number of the application.
     *
     * @return string
     */
    public function version()
    {
        return static::VERSION;
    }

    /**
     * Register the basic bindings into the container.
     *
     * @return void
     */
    protected function registerBaseBindings()
    {
        static::setInstance($this);

        $this->instance('app', $this);

        $this->instance(Container::class, $this);

        $this->instance('handler', new \App\Exceptions\Handler());
    }

    /**
     * Get the instance of the application (Singleton).
     *
     * @return Application The application instance.
     * @throws \RuntimeException If the application instance does not exist.
     */
    public static function getInstance()
    {
        if (!self::$app) {
            throw new \RuntimeException("Application instance does not exist.");
        }

        return self::$app;
    }

    /**
     * Get the current application locale.
     *
     * @return string
     */
    public function getLocale()
    {
        return config('app.locale');
    }

    /**
     * Join the given paths together.
     *
     * @param  string  $basePath
     * @param  string  $path
     * @return string
     */
    public function joinPaths($basePath, $path = '')
    {
        return $basePath.($path != '' ? DIRECTORY_SEPARATOR.ltrim($path, DIRECTORY_SEPARATOR) : '');
    }

    /**
     * Get the base path of the application.
     *
     * @param  string  $path
     * @return string
     */
    public function basePath($path = '')
    {
        return $this->joinPaths($this->basePath, $path);
    }

    /**
     * Set the base path for the application.
     *
     * @param  string  $basePath
     */
    public function setBasePath($basePath)
    {
        $this->basePath = rtrim($basePath, '\/');

        $this->bindPathsInContainer();
    }

    /**
     * Get the path to the application "app" directory.
     *
     * @param  string  $path
     * @return string
     */
    public function path($path = '')
    {
       return $this->joinPaths($this->appPath ?: $this->basePath('app'), $path);
    }

    /**
     * Get the path to the application configuration files.
     *
     * @param  string  $path
     * @return string
     */
    public function configPath($path = '')
    {
        return $this->joinPaths($this->configPath ?: $this->basePath('config'), $path);
    }

    /**
     * Get the path to the public / web directory.
     *
     * @param  string  $path
     * @return string
     */
    public function publicPath($path = '')
    {
        return $this->joinPaths($this->publicPath ?: $this->basePath('public'), $path);
    }

    /**
     * Get the path to the storage directory.
     *
     * @param  string  $path
     * @return string
     */
    public function storagePath($path = '')
    {
        return $this->joinPaths($this->storagePath ?: $this->basePath('storage'), $path);
    }

    /**
     * Get the path to the resources directory.
     *
     * @param  string  $path
     * @return string
     */
    public function resourcePath($path = '')
    {
        return $this->joinPaths($this->basePath('resources'), $path);
    }

    /**
     * Get the path to the views directory.
     *
     * This method returns the first configured path in the array of view paths.
     *
     * @param  string  $path
     * @return string
     */
    public function viewPath($path = '')
    {
        $viewPath = rtrim(config('view.paths')[0], DIRECTORY_SEPARATOR);

        return $this->joinPaths($viewPath, $path);
    }

    /**
     * Get the paths to the views directories.
     *
     * @param  string  $path
     * @return string
     */
    public function viewPaths($path = '')
    {
        foreach(config('view.paths') as $viewPath) {
            $viewPaths[] = $this->joinPaths(rtrim($viewPath, DIRECTORY_SEPARATOR), $path);
        }
        return $viewPaths;
    }

    /**
     * Bind all of the application paths in the container.
     *
     * @return void
     */
    protected function bindPathsInContainer()
    {
        $this->instance('path', $this->path());
        $this->instance('path.base', $this->basePath());
        $this->instance('path.config', $this->configPath());
        $this->instance('path.public', $this->publicPath());
        $this->instance('path.resources', $this->resourcePath());
        $this->instance('path.storage', $this->storagePath());
    }

    /**
     * Register the core class aliases in the container.
     *
     * @return void
     */
    public function registerCoreContainerAliases()
    {
        foreach ([
            'auth' => [\Framework\Services\Auth\AuthManager::class],
            'kernel' => [\Framework\Http\Kernel::class],
            'request' => [\Framework\Http\Request::class],
            'response' => [\Framework\Http\Routing\ResponseFactory::class],
            'route' => [\Framework\Http\Router::class],
            'filesystem' => [\Framework\Filesystem\FilesystemManager::class],
            'hash' => [\Framework\Services\Hash\HashManager::class],
            'db' => [\Framework\Database\DatabaseManager::class],
            'url' => [\Framework\Services\URL\UrlGenerator::class],
            'validator' => [\Framework\Services\Validator\Factory::class],
            'view' => [\Framework\View\Factory::class],
        ] as $key => $aliases) {
            foreach ($aliases as $alias) {
                $this->alias($key, $alias);
            }
        }
    }

}