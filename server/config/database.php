<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Database Connection Name
    |--------------------------------------------------------------------------
    |
    | This value represents the default database connection that will be used
    | for database operations unless a specific connection is specified. It
    | should match one of the keys defined in the "connections" configuration array.
    |
    */

    'default' => env('DB_CONNECTION', 'mysql'),

    /*
    |--------------------------------------------------------------------------
    | Database Connections
    |--------------------------------------------------------------------------
    |
    | Here you can define different database connections for your application.
    | Each connection configuration consists of various options for connecting
    | to a specific database engine.
    |
    | Supported Options:
    | - driver: The database engine driver (e.g., mysql, sqlite).
    | - host: The host address of the database server.
    | - port: The port number for the database connection.
    | - database: The name of the database.
    | - username: The username for the database connection.
    | - password: The password for the database connection.
    | - charset: The character set used for database communication.
    | - collation: The collation used for database communication.
    | - prefix: The prefix applied to all database tables.
    | - strict: A flag indicating whether to use strict mode.
    | - options: Additional options for the database connection (e.g., SSL configuration).
    |
    | Dreamfork utilizes PHP PDO facilities for all database operations. Before
    | starting development, ensure that you have the appropriate driver for
    | your chosen database installed on your machine.
    |
    */

    'connections' => [

        'mysql' => [
            'driver' => 'mysql',
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => env('DB_DATABASE', ''),
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => 'utf8mb4',
            'collation' => null,
            'prefix' => '',
            'strict' => true,
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            ]) : [],
        ],

        'sqlite' => []

    ],

];