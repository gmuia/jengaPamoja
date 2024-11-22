<?php

class Logger
{
    private $logFile = 'logs/kba.log'; // Default log file path
    private $permissions = 0755; // Default directory permissions
    private $filePermissions = 0644; // Default file permissions

    public function __construct($logFile = null, $logDir = 'logs')
    {
        if ($logFile) {
            $this->logFile = $logFile;
        }

        // Ensure the log directory exists with proper permissions
        if (!is_dir($logDir)) {
            if (!mkdir($logDir, $this->permissions, true)) {
                throw new Exception("Failed to create log directory: $logDir");
            }

            // Set permissions explicitly after creation
            if (!chmod($logDir, $this->permissions)) {
                throw new Exception("Failed to set permissions on log directory: $logDir");
            }
        }

        // Set the full log file path
        $this->logFile = $logDir . DIRECTORY_SEPARATOR . basename($this->logFile);

        // Ensure the log file exists and has correct permissions
        if (!file_exists($this->logFile)) {
            if (file_put_contents($this->logFile, '') === false) {
                throw new Exception("Failed to create log file: $this->logFile");
            }
            chmod($this->logFile, $this->filePermissions);
        }
    }

    /**
     * Logs a message to the kba.log file.
     *
     * @param string $message The message to log.
     * @param string $type The type of log (INFO, ERROR, WARNING, etc.).
     * @return void
     */
    public function logMessage($message, $type = 'INFO')
    {
        $logEntry = sprintf(
            "[%s] [%s]: %s%s",
            date('Y-m-d H:i:s'),
            strtoupper($type),
            $message,
            PHP_EOL
        );

        // Append the log entry to the file
        if (file_put_contents($this->logFile, $logEntry, FILE_APPEND | LOCK_EX) === false) {
            throw new Exception("Failed to write to log file: $this->logFile");
        }
    }

    /**
     * Logs an exception to the log file.
     *
     * @param Exception $exception The exception to log.
     * @return void
     */
    public function logException(Exception $exception)
    {
        $message = sprintf(
            "Exception: %s in %s on line %d",
            $exception->getMessage(),
            $exception->getFile(),
            $exception->getLine()
        );

        $this->logMessage($message, 'ERROR');
    }

    /**
     * Logs an array or object as a formatted JSON string.
     *
     * @param mixed $data The data to log.
     * @param string $type The type of log (INFO, ERROR, WARNING, etc.).
     * @return void
     */
    public function logData($data, $type = 'DEBUG')
    {
        $message = json_encode($data, JSON_PRETTY_PRINT);
        $this->logMessage($message, $type);
    }
}
