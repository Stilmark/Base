<?php

namespace Stilmark\Base;

use Stilmark\Base\Env;

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
            
            if (!is_dir(self::$logPath) && !mkdir(self::$logPath, 0755, true) && !is_dir(self::$logPath)) {
                throw new \RuntimeException(sprintf('Log directory "%s" could not be created', self::$logPath));
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
    )
    {
		$level = strtolower($level);
		if (!in_array($level, ['debug', 'info', 'notice', 'warning', 'error', 'critical', 'alert', 'emergency'])) {
			$level = 'info';
		}

		if (isset($_SESSION['user'])) {
			$data['person'] = [
				'id' => $_SESSION['user']['id'] ?? null,
				'email' => $_SESSION['user']['email'] ?? null
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
			$logFile = self::$logPath . '/app.log';
			file_put_contents($logFile, $logLine . PHP_EOL, FILE_APPEND | LOCK_EX);
		}

		return true;
    }
}