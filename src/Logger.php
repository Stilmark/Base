<?php

namespace Stilmark\Base;

final class Logger
{
    public static function log(
		string $log = 'log', 
	    array $data = [], 
	    string $date = 'Ymd', 
	    int $append = FILE_APPEND,
	    bool $timestamp = true,
	    bool $notify = false,
		bool $pretty = false
    )
    {

	    $logfile = ROOT.'/logs/'.$log.($date ? '-'.date($date):'').'.log';

	    // Ensure the log directory exists
	    $logdir = dirname($logfile);
	    if (!is_dir($logdir)) {
	        if (!mkdir($logdir, 0777, true)) {
	            error_log("Logger: Failed to create log directory: " . $logdir);
	            return false;
	        }
	    }
	    
	    // Ensure the directory is writable
	    if (!is_writable($logdir)) {
	        if (!chmod($logdir, 0777)) {
	            error_log("Logger: Failed to set permissions on log directory: " . $logdir);
	            return false;
	        }
	    }

	    $data = '';

	    if ($timestamp) {
	    	$data .= date('Y-m-d H:i:s').' - '.($_SERVER['REMOTE_ADDR'] ?? 'localhost').PHP_EOL;
	    }

		$flag = $pretty ? JSON_PRETTY_PRINT : 0;

	    $data .= json_encode($attr, $flag).PHP_EOL;

	    // Attempt to write to the log file with error handling
	    if (file_put_contents($logfile, $data, $append) === false) {
	        error_log("Logger: Failed to write to log file: " . $logfile . " (Permission denied or disk full)");
	        return false;
	    }

	    if ($notify) {

			if ($pretty) {
				$data = '<pre style="
					margin:0;
					font:14px/1.4 ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
					line-height:1.2;
					white-space:pre-wrap;
					word-wrap:break-word;
					overflow-wrap:anywhere;
					mso-line-height-rule:exactly;
					tab-size: 2; -moz-tab-size: 2;
				  ">'.htmlspecialchars($data, ENT_NOQUOTES, 'UTF-8').'</pre>';	
			}

	    	// Mailer::sendNotification(subject: $log, message: $data);
	    }
	    
	    return true;

    }
}