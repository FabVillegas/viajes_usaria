<?php
require_once( __DIR__ . "/SimpleRest.class.php" );
require_once( __DIR__ . "/Token.class.php" );
require_once( __DIR__ . "/Proyecto.class.php" );

class ProyectoRestHandler extends SimpleRest{

	private $_token;

	public function __construct( $token ){
		$this->_token = $token;
	}

	public function seleccionar_proyectos(){
		try{
			$verificacion = Token::verificar( $this->_token );
			if( $verificacion == false ){
				throw new Exception( "Token falso" );
			}
			$proyecto = new Proyecto();
			$data;
			$data = $proyecto->get_todos();
			if( isset( $data[ "error" ] ) ){
				$statusCode = 400;
			}
			else if( empty( $data ) ){
				$statusCode = 204;
			}
			else{
				$statusCode = 200;
			}
			$request_ContentType = $_SERVER[ "HTTP_ACCEPT" ];
			$this->setHttpHeaders( $request_ContentType, $statusCode );
			if( strpos( $request_ContentType, "application/json" ) !== false ){
				return $this->encodeJson( $data );
			}
		}
		catch( Exception $e ){
			throw $e;
		}
	}

	private function encodeJson( $data ){
		$respuesta_json = json_encode( $data );
		return $respuesta_json;
	}
	
}