<?php 

class SimpleRest{

	private $httpVersion = "HTTP/1.1";

	public function setHttpHeaders( $contentType, $statusCode ){
		$statusMessage = $this->getHttpStatusMessage( $statusCode );
		header( $this->httpVersion . " " . $statusCode . " " . $statusMessage );		
		//header( "Content-Type:" . $contentType );
	}
	
	public function getHttpStatusMessage( $statusCode ){
		$httpStatus = array(
			100 => 'Continue',  
			101 => 'Switching Protocols',  
			200 => 'OK', // General success status code. This is the most common code. Used to indicate success.
			201 => 'Created', // Successful creation occurred (via either POST or PUT). Set the Location header to contain a link to the newly-created resource (on POST). Response body content may or may not be present.
			202 => 'Accepted',  
			203 => 'Non-Authoritative Information',  
			204 => 'No Content', // Indicates success but nothing is in the response body, often used for DELETE and PUT operations.
			205 => 'Reset Content',  
			206 => 'Partial Content',  
			300 => 'Multiple Choices',  
			301 => 'Moved Permanently',  
			302 => 'Found',  
			303 => 'See Other',  
			304 => 'Not Modified',  
			305 => 'Use Proxy',  
			306 => '(Unused)',  
			307 => 'Temporary Redirect',  
			400 => 'Bad Request', // General error for when fulfilling the request would cause an invalid state. Domain validation errors, missing data, etc. are some examples.
			401 => 'Unauthorized', // Error code response for missing or invalid authentication token.
			402 => 'Payment Required',  
			403 => 'Forbidden', // Error code for when the user is not authorized to perform the operation or the resource is unavailable for some reason (e.g. time constraints, etc.).
			404 => 'Not Found', // Used when the requested resource is not found, whether it doesn't exist or if there was a 401 or 403 that, for security reasons, the service wants to mask.
			405 => 'Method Not Allowed', // Used to indicate that the requested URL exists, but the requested HTTP method is not applicable. For example, POST /users/12345 where the API doesn't support creation of resources this way (with a provided ID). The Allow HTTP header must be set when returning a 405 to indicate the HTTP methods that are supported. In the previous case, the header would look like "Allow: GET, PUT, DELETE"
			406 => 'Not Acceptable',  
			407 => 'Proxy Authentication Required',  
			408 => 'Request Timeout',  
			409 => 'Conflict', // Whenever a resource conflict would be caused by fulfilling the request. Duplicate entries, such as trying to create two customers with the same information, and deleting root objects when cascade-delete is not supported are a couple of examples.
			410 => 'Gone',  
			411 => 'Length Required',  
			412 => 'Precondition Failed',  
			413 => 'Request Entity Too Large',  
			414 => 'Request-URI Too Long',  
			415 => 'Unsupported Media Type',  
			416 => 'Requested Range Not Satisfiable',  
			417 => 'Expectation Failed',  
			500 => 'Internal Server Error', // Never return this intentionally. The general catch-all error when the server-side throws an exception. Use this only for errors that the consumer cannot address from their end.
			501 => 'Not Implemented',  
			502 => 'Bad Gateway',  
			503 => 'Service Unavailable',  
			504 => 'Gateway Timeout',  
			505 => 'HTTP Version Not Supported');
		return( $httpStatus[ $statusCode ] ) ? $httpStatus[ $statusCode ] : $status[ 500 ];
	}
}