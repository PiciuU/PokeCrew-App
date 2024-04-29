<?php

namespace Framework\Database\Connections;

use Framework\Database\Grammars\MySqlGrammar;
use PDO;
use PDOException;

/**
 * Class MySqlConnection
 *
 * The MySqlConnection class is responsible for establishing a PDO connection to MySQL databases and configuring it based on the provided settings.
 * It handles tasks such as setting character encoding, collation, timezone, and SQL modes for the database connection.
 *
 * @package Framework\Database\Connections
 */
class MySqlConnection extends Connection
{
    /**
     * MySqlConnection constructor.
     *
     * @param array $config Database connection configuration.
     */
    public function __construct($config)
    {
        // Build DSN (Data Source Name) for PDO connection.
        $dsn = 'mysql:dbname=' . $config['database'] . ';host=' . $config['host'] . ';port=' . $config['port'];
        $user = $config['username'];
        $password = $config['password'];

        // Get PDO connection options based on the configuration.
        $options = $this->getOptions($config);

        // Create a new PDO instance for the MySQL connection.
        $pdo = new PDO($dsn, $user, $password, $options);

        // Call the parent constructor to initialize the connection.
        parent::__construct($pdo, $config);

        // Configure character encoding and collation for the connection.
        $this->configureEncoding($config);

        // Configure the timezone for the connection.
        $this->configureTimezone($config);

        // Set SQL modes for the connection based on configuration.
        $this->setModes($config);
    }

    /**
     * Get the connection name.
     *
     * @return string
     */
    public function getName()
    {
        return 'mysql';
    }

    /**
     * Get the default query grammar instance.
     *
     * @return \Framework\Database\Grammars\MySqlGrammar
     */
    protected function getDefaultQueryGrammar()
    {
        ($grammar = new MySqlGrammar)->setConnection($this);

        return $this->withTablePrefix($grammar);
    }

    /**
     * Configure character encoding and collation for the connection.
     *
     * @param array $config Database connection configuration.
     */
    protected function configureEncoding(array $config)
    {
        if (!isset($config['charset'])) {
            return $connection;
        }

        $this->pdo->prepare(
            "set names '{$config['charset']}'".$this->getCollation($config)
        )->execute();
    }

    /**
     * Get collation settings from the configuration.
     *
     * @param array $config Database connection configuration.
     * @return string Collation settings.
     */
    protected function getCollation(array $config)
    {
        return isset($config['collation']) ? " collate '{$config['collation']}'" : '';
    }

    /**
     * Configure the timezone for the connection.
     *
     * @param array $config Database connection configuration.
     */
    protected function configureTimezone(array $config)
    {
        if (isset($config['timezone'])) {
            $this->pdo->prepare('set time_zone="'.$config['timezone'].'"')->execute();
        }
    }

    /**
     * Set SQL modes for the connection based on configuration.
     *
     * @param array $config Database connection configuration.
     */
    protected function setModes(array $config)
    {
        if (isset($config['modes'])) {
            $this->setCustomModes($config);
        } elseif (isset($config['strict'])) {
            if ($config['strict']) {
                $this->pdo->prepare($this->strictMode($config))->execute();
            } else {
                $this->pdo->prepare("set session sql_mode='NO_ENGINE_SUBSTITUTION'")->execute();
            }
        }
    }

    /**
     * Set custom SQL modes for the connection.
     *
     * @param array $config Database connection configuration.
     */
    protected function setCustomModes(array $config)
    {
        $modes = implode(',', $config['modes']);

        $this->pdo->prepare("set session sql_mode='{$modes}'")->execute();
    }

    /**
     * Set strict mode for the connection based on the MySQL version.
     *
     * @param array $config Database connection configuration.
     * @return string SQL mode settings based on MySQL version.
     */
    protected function strictMode($config)
    {
        $version = $config['version'] ?? $this->pdo->getAttribute(PDO::ATTR_SERVER_VERSION);

        if (version_compare($version, '8.0.11') >= 0) {
            return "set session sql_mode='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION'";
        }

        return "set session sql_mode='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION'";
    }
}