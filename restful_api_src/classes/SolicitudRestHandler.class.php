<?php
require_once( __DIR__ . "/SimpleRest.class.php" );
require_once( __DIR__ . "/Solicitud.class.php" );
require_once( __DIR__ . "/Token.class.php" );

class SolicitudRestHandler extends SimpleRest{

	private $_token;

	public function __construct( $token ){
		$this->_token = $token;
	}

	public function insertar_solicitud( $solicitudObj ){
		try{
			$verificacion = Token::verificar( $this->_token );
			if( $verificacion == false ){
				throw new Exception( "Token falso" );
			}
			$infoToken = Token::extraerDatos( $this->_token );
			$solicitud = new Solicitud();
			$data = $solicitud->insertar( $solicitudObj, $infoToken );
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
			return json_encode( $data );
		}
		catch( Exception $e ){
			throw $e;
		}
	}

	public function cancelar_solicitud_individual( $id_solicitud, $comentarios_adicionales ){
		$data = null;
		$status_code;
		try{
			$verificacion = Token::verificar( $this->_token );
			if( $verificacion == false ){ throw new Exception( "Token falso" ); }
			$solicitud = new Solicitud();
			$data = $solicitud->cancelar_individual( $id_solicitud, $comentarios_adicionales );
			if( empty( $data ) ){
				$status_code = 204;
			}
			else if( $data == true || is_array( $data ) ){
				$status_code = 200;
			}
			else{
				throw new Exception( $data );
			}
			return json_encode( $data );
		}
		catch( Exception $e ){
			$status_code = 400;
			throw $e;
		}
		finally{
			$content_type = $_SERVER[ "HTTP_ACCEPT" ];
			$this->setHttpHeaders( $content_type, $status_code );
		}
	}

	public function seleccionar_pendientes( $correo_usario = NULL ){
		$data = null;
		$status_code;
		try{
			$verificacion = Token::verificar( $this->_token );
			if( $verificacion == false ){ throw new Exception( "Token falso" ); }
			$solicitud = new Solicitud();
			$data = $solicitud->seleccionar_pendientes( $correo_usario );
			if( empty( $data ) ){
				$status_code = 204;
			}
			else if( $data == true || is_array( $data ) ){
				$status_code = 200;
			}
			else{
				throw new Exception( $data );
			}
			return json_encode( $data );
		}
		catch( Exception $e ){
			$status_code = 400;
			throw $e;
		}
		finally{
			$content_type = $_SERVER[ "HTTP_ACCEPT" ];
			$this->setHttpHeaders( $content_type, $status_code );
		}
	}

	public function seleccionar_por_autorizar( $correo_usario = NULL ){
		$data = null;
		$status_code;
		try{
			$verificacion = Token::verificar( $this->_token );
			if( $verificacion == false ){ throw new Exception( "Token falso" ); }
			$solicitud = new Solicitud();
			$data = $solicitud->seleccionar_por_autorizar( $correo_usario );
			if( empty( $data ) ){
				$status_code = 204;
			}
			else if( $data == true || is_array( $data ) ){
				$status_code = 200;
			}
			else{
				throw new Exception( $data );
			}
			return json_encode( $data );
		}
		catch( Exception $e ){
			$status_code = 400;
			throw $e;
		}
		finally{
			$content_type = $_SERVER[ "HTTP_ACCEPT" ];
			$this->setHttpHeaders( $content_type, $status_code );
		}
	}

	public function seleccionar_autorizadas( $correo_usario = NULL ){
		$data = null;
		$status_code;
		try{
			$verificacion = Token::verificar( $this->_token );
			if( $verificacion == false ){ throw new Exception( "Token falso" ); }
			$solicitud = new Solicitud();
			$data = $solicitud->seleccionar_autorizadas( $correo_usario );
			if( empty( $data ) ){
				$status_code = 204;
			}
			else if( $data == true || is_array( $data ) ){
				$status_code = 200;
			}
			else{
				throw new Exception( $data );
			}
			return json_encode( $data );
		}
		catch( Exception $e ){
			$status_code = 400;
			throw $e;
		}
		finally{
			$content_type = $_SERVER[ "HTTP_ACCEPT" ];
			$this->setHttpHeaders( $content_type, $status_code );
		}
	}

	public function seleccionar_completas( $correo_usario = NULL ){
		$data = null;
		$status_code;
		try{
			$verificacion = Token::verificar( $this->_token );
			if( $verificacion == false ){ throw new Exception( "Token falso" ); }
			$solicitud = new Solicitud();
			$data = $solicitud->seleccionar_completas( $correo_usario );
			if( empty( $data ) ){
				$status_code = 204;
			}
			else if( $data == true || is_array( $data ) ){
				$status_code = 200;
			}
			else{
				throw new Exception( $data );
			}
			return json_encode( $data );
		}
		catch( Exception $e ){
			$status_code = 400;
			throw $e;
		}
		finally{
			$content_type = $_SERVER[ "HTTP_ACCEPT" ];
			$this->setHttpHeaders( $content_type, $status_code );
		}
	}

	public function seleccionar_solicitud_individual( $id_solicitud ){
		try{
			$verificacion = Token::verificar( $this->_token );
			if( $verificacion == false ){
				throw new Exception( "Token falso" );
			}
			$solicitud = new Solicitud();
			$data = $solicitud->seleccionar_individual( $id_solicitud );
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
			return json_encode( $data );
		}
		catch( Exception $e ){
			throw $e;
		}
	}

	public function insertar_opcion_hotel( $opcion_hotel ){
		try{
			$verificacion = Token::verificar( $this->_token );
			if( $verificacion == false ){
				throw new Exception( "Token falso" );
			}
			$solicitud = new Solicitud();
			$data = $solicitud->insertar_opcion_hotel( $opcion_hotel );
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
			return json_encode( $data );
		}
		catch( Exception $e ){
			throw $e;
		}
	}

	public function eliminar_opcion_hotel( $id_opcion_hotel ){
		try{
			$verificacion = Token::verificar( $this->_token );
			if( $verificacion == false ){
				throw new Exception( "Token falso" );
			}
			$solicitud = new Solicitud();
			$data = $solicitud->eliminar_opcion_hotel( $id_opcion_hotel );
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
			return ( $data );
		}
		catch( Exception $e ){
			throw $e;
		}
	}

	public function seleccionar_opciones_hotel( $id_solicitud ){
		try{
			$verificacion = Token::verificar( $this->_token );
			if( $verificacion == false ){
				throw new Exception( "Token falso" );
			}
			$solicitud = new Solicitud();
			$data = $solicitud->seleccionar_opciones_hotel( $id_solicitud );
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
			return json_encode( $data );
		}
		catch( Exception $e ){
			throw $e;
		}
	}

	public function insertar_opcion_vuelo( $opcion_vuelo ){
		try{
			$verificacion = Token::verificar( $this->_token );
			if( $verificacion == false ){
				throw new Exception( "Token falso" );
			}
			$solicitud = new Solicitud();
			$data = $solicitud->insertar_opcion_vuelo( $opcion_vuelo );
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
			return ( $data );
		}
		catch( Exception $e ){
			throw $e;
		}
	}

	public function eliminar_opcion_vuelo( $id_opcion_vuelo ){
		try{
			$verificacion = Token::verificar( $this->_token );
			if( $verificacion == false ){
				throw new Exception( "Token falso" );
			}
			$solicitud = new Solicitud();
			$data = $solicitud->eliminar_opcion_vuelo( $id_opcion_vuelo );
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
			return ( $data );
		}
		catch( Exception $e ){
			throw $e;
		}
	}

	public function actualizar_opcion_vuelo( $opcion_vuelo ){
		try{
			$verificacion = Token::verificar( $this->_token );
			if( $verificacion == false ){
				throw new Exception( "Token falso" );
			}
			$solicitud = new Solicitud();
			$data = $solicitud->actualizar_opcion_vuelo( $opcion_vuelo );
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
			return ( $data );
		}
		catch( Exception $e ){
			throw $e;
		}
	}

	public function seleccionar_opciones_vuelo( $id_solicitud ){
		try{
			$verificacion = Token::verificar( $this->_token );
			if( $verificacion == false ){
				throw new Exception( "Token falso" );
			}
			$solicitud = new Solicitud();
			$data = $solicitud->seleccionar_opciones_vuelo( $id_solicitud );
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
			return json_encode( $data );
		}
		catch( Exception $e ){
			throw $e;
		}
	}

	public function insertar_vuelo_seleccionado( $vuelo_seleccionado ){
		$data = null;
		$status_code;
		try{
			$verificacion = Token::verificar( $this->_token );
			if( $verificacion == false ){ throw new Exception( "Token falso" ); }
			$solicitud = new Solicitud();
			if( !isset( $vuelo_seleccionado ) ){ throw new Exception( "Undefined: vuelo_seleccionado" ); }
			$data = $solicitud->insertar_vuelo_seleccionado( $vuelo_seleccionado );
			if( empty( $data ) ){
				$status_code = 204;
			}
			else if( $data == true || is_array( $data ) ){
				$status_code = 200;
			}
			else{
				throw new Exception( $data );
			}
			return json_encode( $data );
		}
		catch( Exception $e ){
			$status_code = 400;
			throw $e;
		}
		finally{
			$content_type = $_SERVER[ "HTTP_ACCEPT" ];
			$this->setHttpHeaders( $content_type, $status_code );
		}
	}

	public function insertar_hotel_seleccionado( $hotel_seleccionado ){
		$data = null;
		$status_code;
		try{
			$verificacion = Token::verificar( $this->_token );
			if( $verificacion == false ){ throw new Exception( "Token falso" ); }
			$solicitud = new Solicitud();
			if( !isset( $hotel_seleccionado ) ){ throw new Exception( "Undefined: insertar_hotel_seleccionado" ); }
			$data = $solicitud->insertar_hotel_seleccionado( $hotel_seleccionado );
			if( empty( $data ) ){
				$status_code = 204;
			}
			else if( $data == true || is_array( $data ) ){
				$status_code = 200;
			}
			else{
				throw new Exception( $data );
			}
			return json_encode( $data );
		}
		catch( Exception $e ){
			$status_code = 400;
			throw $e;
		}
		finally{
			$content_type = $_SERVER[ "HTTP_ACCEPT" ];
			$this->setHttpHeaders( $content_type, $status_code );
		}
	}

	public function insertar_archivo_compra( $id_solicitud, $archivo, $descripcion_archivo ){
		$data = null;
		$status_code;
		try{
			$verificacion = Token::verificar( $this->_token );
			if( $verificacion == false ){ throw new Exception( "Token falso" ); }
			$solicitud = new Solicitud();
			if( !isset( $id_solicitud ) ){ throw new Exception( "Undefined: id_solicitud" ); }
			if( !isset( $archivo ) ){ throw new Exception( "Undefined: archivo" ); }
			if( !isset( $descripcion_archivo ) ){ throw new Exception( "Undefined: descripcion_archivo" ); }
			$data = $solicitud->insertar_archivo_compra( $id_solicitud, $archivo, $descripcion_archivo );
			if( empty( $data ) ){
				$status_code = 204;
			}
			else if( $data == true || is_array( $data ) ){
				$status_code = 200;
			}
			else{
				throw new Exception( $data );
			}
			return json_encode( $data );
		}
		catch( Exception $e ){
			$status_code = 400;
			throw $e;
		}
	}

	public function descargar_archivo( $id_archivo ){
		$data = null;
		$status_code;
		try{
			$verificacion = Token::verificar( $this->_token );
			if( $verificacion == false ){ throw new Exception( "Token falso" ); }
			$solicitud = new Solicitud();
			if( !isset( $id_archivo ) ){ throw new Exception( "Undefined: id_archivo" ); }
			$data = $solicitud->descargar_archivo( $id_archivo );
			if( empty( $data ) ){
				$status_code = 204;
			}
			else if( $data == true || is_array( $data ) ){
				$status_code = 200;
			}
			else{
				throw new Exception( $data );
			}
			return $data;
		}
		catch( Exception $e ){
			$status_code = 400;
			throw $e;
		}
	}

	public function enviar_cotizacion( $id_solicitud, $comentarios_adicionales ){
		$data = null;
		$status_code;
		try{
			$verificacion = Token::verificar( $this->_token );
			if( $verificacion == false ){ throw new Exception( "Token falso" ); }
			$solicitud = new Solicitud();
			if( !isset( $id_solicitud ) ){ throw new Exception( "Undefined: id_solicitud" ); }
			if( !isset( $comentarios_adicionales ) ){ throw new Exception( "Undefined: comentarios_adicionales" ); }
			$data = $solicitud->enviar_cotizacion( $id_solicitud, $comentarios_adicionales );
			if( empty( $data ) ){
				$status_code = 204;
			}
			else if( $data == true || is_array( $data ) ){
				$status_code = 200;
			}
			else{
				throw new Exception( $data );
			}
			return json_encode( $data );
		}
		catch( Exception $e ){
			$status_code = 400;
			throw $e;
		}
		finally{
			$content_type = $_SERVER[ "HTTP_ACCEPT" ];
			$this->setHttpHeaders( $content_type, $status_code );
		}
	}

	public function enviar_seleccion( $id_solicitud, $comentarios_adicionales ){
		$data = null;
		$status_code;
		try{
			$verificacion = Token::verificar( $this->_token );
			if( $verificacion == false ){ throw new Exception( "Token falso" ); }
			$solicitud = new Solicitud();
			if( !isset( $id_solicitud ) ){ throw new Exception( "Undefined: id_solicitud" ); }
			if( !isset( $comentarios_adicionales ) ){ throw new Exception( "Undefined: comentarios_adicionales" ); }
			$data = $solicitud->enviar_seleccion( $id_solicitud, $comentarios_adicionales );
			if( empty( $data ) ){
				$status_code = 204;
			}
			else if( $data == true || is_array( $data ) ){
				$status_code = 200;
			}
			else{
				throw new Exception( $data );
			}
			return json_encode( $data );
		}
		catch( Exception $e ){
			$status_code = 400;
			throw $e;
		}
		finally{
			$content_type = $_SERVER[ "HTTP_ACCEPT" ];
			$this->setHttpHeaders( $content_type, $status_code );
		}
	}

	public function enviar_autorizacion( $id_solicitud, $autorizador, $comentarios_adicionales, $respuesta ){
		$data = null;
		$status_code;
		try{
			$verificacion = Token::verificar( $this->_token );
			if( $verificacion == false ){ throw new Exception( "Token falso" ); }
			$solicitud = new Solicitud();
			if( !isset( $id_solicitud ) ){ throw new Exception( "Undefined: id_solicitud" ); }
			if( !isset( $autorizador ) ){ throw new Exception( "Undefined: autorizador" ); }
			if( !isset( $comentarios_adicionales ) ){ throw new Exception( "Undefined: comentarios_adicionales" ); }
			if( !isset( $respuesta ) ){ throw new Exception( "Undefined: respuesta" ); }
			$data = $solicitud->enviar_autorizacion( $id_solicitud, $autorizador, $comentarios_adicionales, $respuesta );
			if( empty( $data ) ){
				$status_code = 204;
			}
			else if( $data == true || is_array( $data ) ){
				$status_code = 200;
			}
			else{
				throw new Exception( $data );
			}
			return json_encode( $data );
		}
		catch( Exception $e ){
			$status_code = 400;
			throw $e;
		}
		finally{
			$content_type = $_SERVER[ "HTTP_ACCEPT" ];
			$this->setHttpHeaders( $content_type, $status_code );
		}
	}

	public function enviar_compra( $id_solicitud, $comentarios_adicionales ){
		$data = null;
		$status_code;
		try{
			$verificacion = Token::verificar( $this->_token );
			if( $verificacion == false ){ throw new Exception( "Token falso" ); }
			$solicitud = new Solicitud();
			if( !isset( $id_solicitud ) ){ throw new Exception( "Undefined: id_solicitud" ); }
			if( !isset( $comentarios_adicionales ) ){ throw new Exception( "Undefined: comentarios_adicionales" ); }
			$data = $solicitud->enviar_compra( $id_solicitud, $comentarios_adicionales );
			if( empty( $data ) ){
				$status_code = 204;
			}
			else if( $data == true || is_array( $data ) ){
				$status_code = 200;
			}
			else{
				throw new Exception( $data );
			}
			return json_encode( $data );
		}
		catch( Exception $e ){
			$status_code = 400;
			throw $e;
		}
		finally{
			$content_type = $_SERVER[ "HTTP_ACCEPT" ];
			$this->setHttpHeaders( $content_type, $status_code );
		}
	}

	private function encodeJson( $data ){
		$respuesta_json = json_encode( $data );
		return $respuesta_json;
	}
	
}
