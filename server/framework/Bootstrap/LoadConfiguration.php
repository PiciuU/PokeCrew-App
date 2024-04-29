<?php

namespace Framework\Bootstrap;

use Framework\Support\Env;
use Symfony\Component\Finder\Finder;
use Dotenv\Dotenv;
use SplFileInfo;

/**
 * Trait LoadConfiguration
 *
 * This trait provides methods for loading configuration data from various sources,
 * such as environment variables and configuration files.
 *
 * @package Framework\Bootstrap
 */
trait LoadConfiguration
{
    /**
     * Bootstrap the application by loading environment variables,
     * configuration files, and setting the default timezone.
     *
     * @return void
     */
    protected function bootstrap()
    {
        $this->loadEnvironmentVariables();
        $this->loadConfigurationFiles();

        // Set the default timezone based on the 'app.timezone' configuration,
        // or fallback to 'UTC' if not specified.
        date_default_timezone_set(config('app.timezone', 'UTC'));
    }
    /**
     * Load environment variables from the .env file.
     *
     * @throws \RuntimeException If the .env file is not found.
     */
    protected function loadEnvironmentVariables()
    {
        // Check if the .env file exists in the application's base path.
        if (!file_exists($this->basePath . '/.env')) {
            throw new \RuntimeException('.env file not found.');
        }

        Dotenv::create(Env::getRepository(), $this->basePath, '.env')->safeLoad();
    }

    /**
     * Load configuration files and populate the configuration repository.
     */
    protected function loadConfigurationFiles()
    {
        // Get an array of configuration file paths.
        $files = $this->getConfigurationFiles();

        // Create an instance of the configuration repository.
        $this->instance('config', new \Framework\Config\Repository());

        $repository = $this->get('config');

        // Ensure that the "app" configuration file is present.
        if (!isset($files['app'])) {
            throw new \Exception('Unable to load the "app" configuration file.');
        }

        // Load and set configuration values into the repository.
        foreach ($files as $key => $path) {
            $repository->set($key, require $path);
        }
    }

    /**
     * Get an array of configuration file paths within the config directory.
     *
     * @return array An array of configuration file paths.
     */
    protected function getConfigurationFiles()
    {
        $files = [];

        $configPath = realpath($this->configPath());

        foreach (Finder::create()->files()->name('*.php')->in($configPath) as $file) {
            $directory = $this->getNestedDirectory($file, $configPath);

            $files[$directory.basename($file->getRealPath(), '.php')] = $file->getRealPath();
        }

        // Sort configuration files by natural order.
        ksort($files, SORT_NATURAL);

        return $files;
    }

    /**
     * Get the nested directory structure of a configuration file.
     *
     * @param SplFileInfo $file       The configuration file.
     * @param string       $configPath The base path of the config directory.
     *
     * @return string The nested directory structure.
     */
    protected function getNestedDirectory(SplFileInfo $file, $configPath)
    {
        $directory = $file->getPath();

        if ($nested = trim(str_replace($configPath, '', $directory), DIRECTORY_SEPARATOR)) {
            $nested = str_replace(DIRECTORY_SEPARATOR, '.', $nested).'.';
        }

        return $nested;
    }
}