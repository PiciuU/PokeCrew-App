<?php

namespace Framework\Filesystem;

/**
 * Class FilesystemManager
 *
 * This class provides an interface to manage multiple file storage disks.
 * It allows to access different storage disks defined in the application's configuration.
 * Filesystem instances are primarily used for performing file-related operations, such as reading,
 * writing, copying, and deleting files or directories.
 */
class FilesystemManager
{
    /**
     * The available disks.
     *
     * @var array
     */
    protected $disks = [];

    /**
     * Constructor for the class.
     *
     * This constructor initializes the Filesystem manager by creating Disk instances
     * based on the configuration of available disks.
     *
     * @return void
     */
    public function __construct()
    {
        $fs = new Filesystem();
        foreach(config('storage.disks') as $name => $disk) {
            $this->disks[$name] = new Disk($disk, $fs);
        };
    }

    /**
     * Get a disk instance by name.
     *
     * Retrieve a Disk instance by specifying its name. If no name is provided,
     * it defaults to the disk configured as the default in the application.
     *
     * @param  string|null  $name
     * @return \Framework\Filesystem\Disk
     */
    public function disk($name = null)
    {
        $name = $name ?: $this->getDefaultDisk();

        return $this->disks[$name];
    }

    /**
     * Get the default disk name.
     *
     * Retrieve the name of the default disk as configured in the application.
     *
     * @return string
     */
    public function getDefaultDisk()
    {
        return config('storage.disk');
    }
}