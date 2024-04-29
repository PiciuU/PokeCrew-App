<?php

namespace Framework\Log;

/**
 * Class LogLevel
 *
 * This class defines log levels for logging messages.
 * Log levels are used to categorize the severity of log messages.
 * Each log level has a unique identifier.
 *
 * @package Framework\Log
 */
class LogLevel {
    const DEBUG = 'debug';         // Debug-level messages for detailed debugging information.
    const INFO = 'info';           // Informational messages about normal application operation.
    const NOTICE = 'notice';       // Normal but significant events that require attention.
    const WARNING = 'warning';     // Warning messages indicating a potential issue.
    const ERROR = 'error';         // Error messages indicating a failure or error in the application.
    const CRITICAL = 'critical';   // Critical error messages requiring immediate attention.
    const ALERT = 'alert';         // Alerts that need to be addressed immediately.
    const EMERGENCY = 'emergency'; // Emergency messages indicating a severe application failure.
}