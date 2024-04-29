<?php

namespace Framework\Log;

use Framework\Support\Facades\Storage;

/**
 * Class Logger
 *
 * The Logger class implements the LoggerInterface and provides methods for logging messages
 * at different log levels. It also supports custom log levels.
 *
 * @package Framework\Log
 */
class Logger implements LoggerInterface {

    /**
     * Log an emergency message.
     *
     * @param string $message The log message.
     * @param array $context Additional context data.
     */
    public function emergency(string $message, array $context = [])
    {
        $this->addRecord(LogLevel::EMERGENCY, $message, $context);
    }

    /**
     * Log an alert message.
     *
     * @param string $message The log message.
     * @param array $context Additional context data.
     */
    public function alert(string $message, array $context = [])
    {
        $this->addRecord(LogLevel::ALERT, $message, $context);
    }

    /**
     * Log a critical message.
     *
     * @param string $message The log message.
     * @param array $context Additional context data.
     */
    public function critical(string $message, array $context = [])
    {
        $this->addRecord(LogLevel::CRITICAL, $message, $context);
    }

    /**
     * Log an error message.
     *
     * @param string $message The log message.
     * @param array $context Additional context data.
     */
    public function error(string $message, array $context = [])
    {
        $this->addRecord(LogLevel::ERROR, $message, $context);
    }

    /**
     * Log a warning message.
     *
     * @param string $message The log message.
     * @param array $context Additional context data.
     */
    public function warning(string $message, array $context = [])
    {
        $this->addRecord(LogLevel::WARNING, $message, $context);
    }

    /**
     * Log a notice message.
     *
     * @param string $message The log message.
     * @param array $context Additional context data.
     */
    public function notice(string $message, array $context = [])
    {
        $this->addRecord(LogLevel::NOTICE, $message, $context);
    }

    /**
     * Log an informational message.
     *
     * @param string $message The log message.
     * @param array $context Additional context data.
     */
    public function info(string $message, array $context = [])
    {
        $this->addRecord(LogLevel::INFO, $message, $context);
    }

    /**
     * Log a debug message.
     *
     * @param string $message The log message.
     * @param array $context Additional context data.
     */
    public function debug(string $message, array $context = [])
    {
        $this->addRecord(LogLevel::DEBUG, $message, $context);
    }

    /**
     * Log a message with a custom log level.
     *
     * @param string $level The custom log level.
     * @param string $message The log message.
     * @param array $context Additional context data.
     */
    public function log(string $level, string $message, array $context = [])
    {
        // Check if the provided log level is valid
        $object = new \ReflectionClass(LogLevel::class);
        $validLogLevelsArray = $object->getConstants();
        if (!in_array($level, $validLogLevelsArray)) {
            throw new InvalidLogLevelArgument($level, $validLogLevelsArray);
        }

        // Log the message
        $this->addRecord($level, $message, $context);
    }

    /**
     * Add a log record.
     *
     * @param string $level The log level.
     * @param string $message The log message.
     * @param array $context Additional context data.
     */
    private function addRecord(string $level, string $message, array $context = [])
    {
        $date = new \DateTime();
        $date = $date->format('Y-m-d H:i:s');

        $details = sprintf(
            "[%s] Level: %s - Message: %s " . PHP_EOL . "Exception Content: %s, Additional Context: %s" . PHP_EOL . "[Backtrace] " . PHP_EOL . "%s",
            $date,
            $level,
            $message,
            isset($context['exception_context']) ? json_encode($context['exception_context'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : "[]",
            isset($context['additional_context']) ? json_encode($context['additional_context'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : "[]",
            $this->debug_backtrace_string($context['exception']->getTrace())
        );

        Storage::disk('log')->writeTextFile("dreamfork.log", $details, true, true);
    }

    /**
     * Generate a stack trace as a string from the provided trace data.
     *
     * @param array $trace The trace data to be converted to a string.
     * @return array The stack trace as a string.
     */
    private function debug_backtrace_string($trace) {
        $stack = "";
        $i = 1;

        foreach($trace as $node) {
            $entry = "#" . $i . (isset($node['file']) && isset($node['line']) ? " " . $node['file'] . "(" . $node['line'] . "): " : " ");
            if(isset($node['class'])) {
                $entry .= $node['class']."->";
            }
            $entry .= $node['function']."()";
            $stack .= $entry.PHP_EOL;
            $i += 1;
        }
        return $stack;
    }
}