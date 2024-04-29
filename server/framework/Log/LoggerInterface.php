<?php

namespace Framework\Log;

/**
 * Interface LoggerInterface
 *
 * This interface defines a contract for logging messages with different log levels.
 * Implementations of this interface should provide methods for each log level.
 *
 * @package Framework\Log
 */
interface LoggerInterface {
    /**
     * Log an emergency message.
     *
     * @param string $message The log message.
     * @param array $context Additional context data.
     */
    public function emergency(string $message, array $context = []);

    /**
     * Log an alert message.
     *
     * @param string $message The log message.
     * @param array $context Additional context data.
     */
    public function alert(string $message, array $context = []);

    /**
     * Log a critical message.
     *
     * @param string $message The log message.
     * @param array $context Additional context data.
     */
    public function critical(string $message, array $context = []);

    /**
     * Log an error message.
     *
     * @param string $message The log message.
     * @param array $context Additional context data.
     */
    public function error(string $message, array $context = []);

    /**
     * Log a warning message.
     *
     * @param string $message The log message.
     * @param array $context Additional context data.
     */
    public function warning(string $message, array $context = []);

    /**
     * Log a notice message.
     *
     * @param string $message The log message.
     * @param array $context Additional context data.
     */
    public function notice(string $message, array $context = []);

    /**
     * Log an informational message.
     *
     * @param string $message The log message.
     * @param array $context Additional context data.
     */
    public function info(string $message, array $context = []);

    /**
     * Log a debug message.
     *
     * @param string $message The log message.
     * @param array $context Additional context data.
     */
    public function debug(string $message, array $context = []);

    /**
     * Log a message with a custom log level.
     *
     * @param string $level The custom log level.
     * @param string $message The log message.
     * @param array $context Additional context data.
     */
    public function log(string $level, string $message, array $context = []);
}