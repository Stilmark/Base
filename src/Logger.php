<?php

namespace Stilmark\Base;

use Stilmark\Base\Env;
use Rollbar\Rollbar;

final class Logger
{
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