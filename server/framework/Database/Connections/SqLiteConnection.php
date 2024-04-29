<?php

namespace Framework\Database\Connections;

use PDO;
use PDOException;

class SqLiteConnection extends Connection
{
    public function __construct($config)
    {
        throw new \Exception('The SqLite driver is not yet supported in this version of the framework.');
    }
}