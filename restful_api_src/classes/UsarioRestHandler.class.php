<?php
require_once( 'SimpleRest.class.php' );
require_once( 'Usario.class.php' );

class UsarioRestHandler extends SimpleRest{

	public function get_usario_info(){
		$usario = new Usario();
		$data = $usario->get_usario_info();
		if( empty( $data ) ){
			$statusCode = 404;
			$data = array( 'error' => 'No resultados' );
		}
		else{
			$statusCode = 200;
		}
		$requestContentType = $_SERVER[ 'HTTP_ACCEPT' ];
		$this->setHttpHeaders( $requestContentType, $statusCode );
		if( strpos( $requestContentType, 'application/json' ) !== false ){
			$respuesta = $this->encodeJson( $data );
			return $respuesta;
		}
	}

	private function encodeJson( $data ){
		$respuesta_json = json_encode( $data );
		return $respuesta_json;
	}

}