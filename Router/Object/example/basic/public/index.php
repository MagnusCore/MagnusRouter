<?php
if (!function_exists('getallheaders'))  {
    function getallheaders()
    {
        if (!is_array($_SERVER)) {
            return array();
        }
        $headers = array();
        foreach ($_SERVER as $name => $value) {
            if (substr($name, 0, 5) == 'HTTP_') {
                $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
            }
        }
        return $headers;
    }
}

$documentRoot = __DIR__;
$serverRoot   = dirname(__DIR__);
$viewRoot     = $serverRoot . '/src/views/';
$vendorRoot   = $serverRoot . '/vendor';

require_once($serverRoot . '/app/autoload.php');
$config       = require_once($serverRoot . '/app/config/appEnv.php');

$requestPath  = $_SERVER['REQUEST_URI'];
$path         = explode('/', str_replace($documentRoot, '', $requestPath));
$path         = array_values(array_filter($path));

$queryFields  = isset($_GET) ? $_GET : array();
$postFields   = isset($_POST) ? $_POST : array();

$context = array(
	'debug'     => $config['debug'],
	'config'    => $config,
	'assetRoot' => $documentRoot . '/assets',
	'request'   => array(
		'path'    => $path,
		'uri'     => $requestPath,
		'method'  => strtolower($_SERVER['REQUEST_METHOD']),
		'query'   => $queryFields,
		'post'    => $postFields,
		'headers' => getallheaders()
	)
);

$logger     = new \Loggers\ScreenLogger();
$router     = new \MagnusRouter\Router\Object\ObjectRouter($context, $logger);
$rootObject =  new \Http\Controllers\RootController($context);

$context['logger'] = $logger;

$previous   = null;
$current    = null;
$obj        = $rootObject;
$isEndpoint = false;

foreach ($router($context, $rootObject, $path) as list($previous, $obj, $isEndpoint)) {
	//Object router requires us to provide it with an object to inspect for next possible routes or endpoints
	if (!is_object($obj)) {

		if ($context['debug'] && $logger) {
			$logger->debug('Provided is not currently an object', [
				'provided'         => $obj,
				'context'          => $context
			]);
		}
		
		if (class_exists($obj)) {

			if ($context['debug'] && $logger) {
				$logger->debug('Instantiating object reference', [
					'object reference' => $obj,
					'context'          => $context
				]);
			}

			$obj = new $obj($context);

		}

	}
	
	if ($isEndpoint) { break; }
}

//Response preparation
$response = ['view' => 'error/noResource', 'context' => $context];
$dispatchResponse = null;

//Dispatch, to be factored out into own package
if ($isEndpoint) {

	//Instantiate any class reference returned from routing
	if (!is_object($obj) && class_exists($obj)){

		if ($context['debug'] && $logger) {
            $logger->debug('Instantiating routed class reference', [
                'context' => $context,
                'current' => $obj
            ]);
            
        }

		$obj = new $obj($context);

	}

	if (is_object($obj)) {

		if (in_array($previous, get_class_methods($obj))) {

			if ($context['debug'] && $logger) {
		        $logger->debug('Generating dispatch response from obj->method', [
		        	'context' => $context,
		            'obj'     => $obj,
		            'method'  => $previous
		        ]);
		        
        	}

			$dispatchResponse = $obj->$previous();

		} else if (in_array('__invoke', get_class_methods($obj))) {

			if ($context['debug'] && $logger) {
		        $logger->debug('Generating dispatch response from obj->__invoke', [
		        	'context' => $context,
		            'obj'     => $obj
		        ]);
		        
        	}

			$dispatchResponse = $obj->__invoke();

		}

	}

	if (is_array($dispatchResponse)) {

		if ($context['debug'] && $logger) {
	        $logger->debug('Merging array returned from dispatch with response', [
	        	'context' => $context,
	        ]);
    	}

		$response = array_merge($response, $dispatchResponse);

	} else if ($dispatchResponse instanceof \Traversable) {
		/* for sake of simplicity, we assume generators do not emit anything but arrays. In the future, generators may 
		 * yield static elements, functions, objects, generators and more.
		 */
		if ($context['debug'] && $logger) {
	        $logger->debug('Generator returned from dispatch, iterating to build response', [
	        	'context' => $context,
	        ]);
    	}

		foreach ($dispatchResponse() as $chunk) {
			$response = array_merge($response, $chunk);
		}

	}

}

if (!is_null($dispatchResponse)) {
	echo var_export($dispatchResponse, true) . '<br>';
}
