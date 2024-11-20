<?php

class Log
  {
    public function logMessage($message, $logDir = 'logs', $permissions = 0755) {
        // Ensure the log directory exists with proper permissions
        if (!is_dir($logDir)) {
            if (!mkdir($logDir, $permissions, true)) {
                throw new Exception("Failed to create log directory: $logDir");
            }
            // Set permissions explicitly after creation
            if (!chmod($logDir, $permissions)) {
                throw new Exception("Failed to set permissions on log directory: $logDir");
            }
        } else {
            // If directory already exists, ensure it has correct permissions
            if (!chmod($logDir, $permissions)) {
                throw new Exception("Failed to update permissions on existing log directory: $logDir");
            }
        }
    
        // Generate timestamp for the filename
        $timestamp = date('Y-m-d');
        $filename = $logDir . DIRECTORY_SEPARATOR . $timestamp . '.log';
    
        // Prepare the log entry
        $logEntry = date('Y-m-d H:i:s') . ' - ' . $message . PHP_EOL;
    
        // Append the log entry to the file
        if (file_put_contents($filename, $logEntry, FILE_APPEND | LOCK_EX) === false) {
            throw new Exception("Failed to write to log file: $filename");
        }
    
        // Ensure the log file has correct permissions (typically 0644 for files)
        if (!chmod($filename, 0644)) {
            throw new Exception("Failed to set permissions on log file: $filename");
        }

        var_dump('hello');
    
        return true;
    }

    public function wh_log($log_msg){
        $log_filename = "log";
        
        if (!file_exists($log_filename)) {
            // create directory/folder uploads.
            mkdir($log_filename, 0777, true);
        }

        $log_file_data = $log_filename.'/log_' . date('d-M-Y') . '.log';
        // if you don't add `FILE_APPEND`, the file will be erased each time you add a log
        file_put_contents($log_file_data, $log_msg . "\n", FILE_APPEND);
    } 

    // public function __construct($log_name,$page_name) {
    //     if(!file_exists('/your/directory/'.$log_name)){ $log_name='a_default_log.log'; }
    //     $this->log_name=$log_name;

    //     $this->app_id=uniqid();//give each process a unique ID for differentiation
    //     $this->page_name=$page_name;

    //     $this->log_file='/your/directory/'.$this->log_name;
    //     $this->log=fopen($this->log_file,'a');
    // }
  
    // public function log_msg($msg) {
    //     // the action
    //     $log_line=join(' : ', array( date(DATE_RFC822), $this->page_name, $this->app_id, $msg ) );
    //     fwrite($this->log, $log_line."\n");
    // }

    // public function __destruct() {
    //     // makes sure to close the file and write lines when the process ends.
    //     $this->log_msg("Closing log");
    //     fclose($this->log);
    // }
  }




