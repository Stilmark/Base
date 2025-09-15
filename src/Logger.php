<?php

namespace Stilmark\Base;

use Stilmark\Base\Env;
use Rollbar\Rollbar;

final class Logger
{
    /**
     * Initialize error reporting and Rollbar configuration
     */
    public static function init(): void
    {
        $rollbarEnabled = Env::get('LOG_API_TOKEN') && Env::get('LOG_API') === 'ROLLBAR';

        ini_set('error_reporting', E_ALL);
        ini_set('display_errors', $rollbarEnabled ? 0 : 1);
        
        if ($rollbarEnabled) {
            ini_set('log_errors', 0);
            
            Rollbar::init([
                'access_token' => Env::get('LOG_API_TOKEN'),
                'environment' => Env::get('MODE', 'NONE'),
                'exception_sample_rates' => [],
                'error_sample_rates' => [],
                'include_error_code_context' => true,
                'include_exception_code_context' => true,
                'capture_error_stacktraces' => true,
                'capture_ip' => 'anonymize'
            ]);
            
            // Register error handlers
            (new \Rollbar\Handlers\ErrorHandler(Rollbar::logger()))->register();
            (new \Rollbar\Handlers\FatalHandler(Rollbar::logger()))->register();
        }
    }

    public static function log(
		string $log = 'log', 
		string $level = 'info', 
	    array $data = [], 
    )
    {
		if (Env::get('LOG_API') === 'ROLLBAR') {

			// Add validation for log levels
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

			Rollbar::log($log, $level, $data);
		}

		return true;
    }
}