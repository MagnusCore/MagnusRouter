<?php
namespace MagnusApp {
	spl_autoload_register(function ($className) {
		$className = str_replace("\\", '/', $className);
		$vendors   = dirname(__DIR__) . '/vendor/';
		$appdir    = dirname(__DIR__) . '/app/';
		
		if (file_exists($vendors . $className . '.php')) {
			require_once $vendors . $className . '.php';
		} else if (file_exists(__DIR__ . '/' . $className . '.php')) {
			require_once __DIR__ . '/' . $className . '.php';
		} else if (file_exists($appdir . $className . '.php')) {
			require_once $appdir . $className . '.php';
		}
		
	});
}
