<?php

namespace Stilmark\Base;

final class Logger
{
    private static ?string $logPath = null;

    /**
     * Initialize error reporting and file-based logging
     */
    public static function init(): void
    {
        $logPath = Env::get('LOG_PATH');
        
        if ($logPath) {
            self::$logPath = rtrim($logPath, '/');
            
            if (!is_dir(self::$logPath)) {
                if (!mkdir(self::$logPath, 0775, true) && !is_dir(self::$logPath)) {
                    throw new \RuntimeException(sprintf('Log directory "%s" could not be created', self::$logPath));
                }
                chmod(self::$logPath, 0775);
            }
        }

        ini_set('error_reporting', E_ALL);
        ini_set('display_errors', defined('DEV') && DEV ? 1 : 0);
        ini_set('log_errors', 1);
        
        if (self::$logPath) {
            ini_set('error_log', self::$logPath . '/php_errors.log');
        }
    }

    public static function log(
		string $message = 'log', 
		string $level = 'info', 
	    array $data = [],
	    string $filename = 'app.log',
    )
    {
		$level = strtolower($level);
		if (!in_array($level, ['debug', 'info', 'notice', 'warning', 'error', 'critical', 'alert', 'emergency'])) {
			$level = 'info';
		}

		if (Session::has('user')) {
			$data['person'] = [
				'id' => Session::get('user.id'),
				'email' => Session::get('user.email')
			];
		}

		$logEntry = [
			'timestamp' => date('Y-m-d H:i:s'),
			'level' => strtoupper($level),
			'message' => $message,
			'data' => $data ?: null,
		];

		$logLine = json_encode(array_filter($logEntry, fn($v) => $v !== null), JSON_UNESCAPED_SLASHES);

		if (self::$logPath) {
			$logFile = self::$logPath . '/' . $filename;
			$isNew = !file_exists($logFile);
			file_put_contents($logFile, $logLine . PHP_EOL, FILE_APPEND | LOCK_EX);
			if ($isNew) {
				chmod($logFile, 0664);
			}
		}

		return true;
    }
}