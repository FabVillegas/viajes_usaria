<?php
require_once( __DIR__ . "/Conexion.class.php" );
require_once( __DIR__ . "/../libraries/Mail/Mail.php" );

class Mensajero{

	public static function solicitud_creada( $id_solicitud, $comentarios_adicionales ){
		$solicitud = self::obtener_solicitud( $id_solicitud );
		$from = "$solicitud->nombre_viajero $solicitud->apellido_viajero <$solicitud->correo_viajero>";
		$to = "$solicitud->nombre_administrador $solicitud->apellido_administrador <$solicitud->correo_administrador>";
		$fecha_salida = strftime( "%d-%m-%Y", strtotime( $solicitud->fecha_inicio ) );
		$subject = "Viajes Usaria: Solicitud de viaje para salir el día $fecha_salida";
		$body = "".
		"<strong>Este correo necesita una acción de tu parte</strong>.<br>".
		"Se ha llenado una solicitud de viaje nueva:<br>".
		"Proyecto: <i>$solicitud->nombre_proyecto</i>.<br>".
		"Motivo: <i>$solicitud->motivo</i>.<br>".
		"Comentarios adicionales:<br>".
		"<i>$comentarios_adicionales</i><br><br>".
		"Para subir los datos de la cotización entra a: http://viajes.usaria.mx/#/dashboard/inicio.<br>".
		"Una vez que realices la cotización se continuará con el paso de selección de vuelos y hotel."; // termina body
		self::enviar_correo( $from, $to, $subject, $body );
	}

	public static function enviar_cotizacion( $id_solicitud, $comentarios_adicionales ){
		$solicitud = self::obtener_solicitud( $id_solicitud );
		$from = "$solicitud->nombre_administrador $solicitud->apellido_administrador <$solicitud->correo_administrador>";
		$to = "$solicitud->nombre_viajero $solicitud->apellido_viajero <$solicitud->correo_viajero>";
		$fecha_salida = strftime( "%d-%m-%Y", strtotime( $solicitud->fecha_inicio ) );
		$subject = "Viajes Usaria: Cotización realizada";
		$body = "".
		"<strong>Este correo necesita una acción de tu parte</strong>.<br>".
		"Se ha llenado una cotización de viaje:<br>".
		"Proyecto: <i>$solicitud->nombre_proyecto</i>.<br>".
		"Motivo: <i>$solicitud->motivo</i>.<br>".
		"Comentarios adicionales:<br>".
		"<i>$comentarios_adicionales</i><br><br>".
		"Para elegir los vuelos y hoteles que necesitas entra a: http://viajes.usaria.mx/#/dashboard/inicio.<br>"; // termina body
		self::enviar_correo( $from, $to, $subject, $body );
	}

	public static function enviar_seleccion( $id_solicitud, $comentarios_adicionales ){
		$solicitud = self::obtener_solicitud( $id_solicitud );
		$from = "$solicitud->nombre_viajero $solicitud->apellido_viajero <$solicitud->correo_viajero>";
		$subject = "Viajes Usaria: Selección realizada";
		$body = "".
		"<strong>Este correo necesita una acción de tu parte</strong>.<br>".
		"Se ha seleccionado vuelo y/u hotel para el viaje:<br>".
		"Proyecto: <i>$solicitud->nombre_proyecto</i>.<br>".
		"Motivo: <i>$solicitud->motivo</i>.<br>".
		"Comentarios adicionales:<br>".
		"<i>$comentarios_adicionales</i><br><br>".
		"Una vez que autorices la selección se va a proceder con la compra de los pasajes.<br><br>".
		"Para autorizar la solicitud de viaje, entra a: http://viajes.usaria.mx/#/dashboard/inicio."; // Termina body
		foreach( $solicitud->autorizaciones as &$autorizacion ){
			$autorizacion = ( object ) $autorizacion;
			$to = "$autorizacion->nombre_autorizador $autorizacion->apellido_autorizador <$autorizacion->correo_autorizador>";
			self::enviar_correo( $from, $to, $subject, $body );
		}
	}

	public static function enviar_autorizacion( $id_solicitud, $autorizador, $comentarios_adicionales, $respuesta ){
		$solicitud = self::obtener_solicitud( $id_solicitud );
		$dbh = Conexion::obtenerInstancia();
		$sth = $dbh->prepare(
			"SELECT usario.correo, usario.nombre, usario.apellido
			FROM autorizacion
			INNER JOIN usario ON autorizacion.id_autorizador = usario.correo
			WHERE autorizacion.id_autorizador = :id_autorizador AND autorizacion.id_solicitud = :id_solicitud;"
		);
		$sth->bindValue( ":id_autorizador", $autorizador );
		$sth->bindValue( ":id_solicitud", $id_solicitud );
		$sth->execute();
		$usario = ( object ) $sth->fetch();
		$from = "$usario->nombre $usario->apellido <$usario->correo>";
		$to = "$solicitud->nombre_administrador $solicitud->apellido_administrador <$solicitud->correo_administrador>";
		if( $respuesta ){
			$subject = "Viajes Usaria: Autorizo la compra del viaje";
			$body = "".
			"<strong>Este correo necesita una acción de tu parte</strong>.<br>".
			"Autorizo la compra de los boletos necesarios para el viaje:<br>".
			"Proyecto: <i>$solicitud->nombre_proyecto</i>.<br>".
			"Motivo: <i>$solicitud->motivo</i>.<br>".
			"Comentarios adicionales:<br>".
			"<i>$comentarios_adicionales</i><br><br>".
			"Para ver lo que $solicitud->nombre_viajero $solicitud->apellido_viajero necesita, entra a: http://viajes.usaria.mx/#/dashboard/inicio"; // Termina body
		}
		else{
			$subject = "Viajes Usaria: No autorizo la compra del viaje";
			$body = "".
			"<strong>Este correo necesita una acción de tu parte</strong>.<br>".
			"Se tienen que revisar algunas cosas antes de proceder con el viaje:<br>".
			"Proyecto: <i>$solicitud->nombre_proyecto</i>.<br>".
			"Motivo: <i>$solicitud->motivo</i>.<br>".
			"Como comentarios adicionales:<br>".
			"<i>$comentarios_adicionales</i>.<br><br>".
			"Para hacer las modificaciones necesarias, entra a: http://viajes.usaria.mx/#/dashboard/inicio"; // Termina body
		}
		self::enviar_correo( $from, $to, $subject, $body );
		if( $respuesta === false ){
			$to = "$solicitud->nombre_viajero $solicitud->apellido_viajero <$solicitud->correo_viajero>";
			self::enviar_correo( $from, $to, $subject, $body );
		}
	}

	public static function enviar_compra( $id_solicitud, $comentarios_adicionales ){
		$solicitud = self::obtener_solicitud( $id_solicitud );
		$from = "$solicitud->nombre_administrador $solicitud->apellido_administrador <$solicitud->correo_administrador>";
		$to = "$solicitud->nombre_viajero $solicitud->apellido_viajero <$solicitud->correo_viajero>";
		$subject = "Viajes Usaria: Compra realizada";
		$body = "".
		"<strong>Este correo necesita una acción de tu parte</strong>.<br>".
		"Se ha realizado la compra de los pasajes para el viaje:<br>".
		"Proyecto: <i>$solicitud->nombre_proyecto</i>.<br>".
		"Motivo: <i>$solicitud->motivo</i>.<br>".
		"Comentarios adicionales:<br>".
		"<i>$comentarios_adicionales</i><br><br>".
		"Feliz viaje.<br><br>".
		"Para ver los archivos de compra, entra a: http://viajes.usaria.mx/#/dashboard/inicio<br>"; // Termina body
		self::enviar_correo( $from, $to, $subject, $body );
	}

	public static function cancelar_solicitud( $id_solicitud, $comentarios_adicionales ){
		$solicitud = self::obtener_solicitud( $id_solicitud );
		$from = "$solicitud->nombre_viajero $solicitud->apellido_viajero <$solicitud->correo_viajero>";
		$to = "$solicitud->nombre_administrador $solicitud->apellido_administrador <$solicitud->correo_administrador>";
		$subject = "Viajes Usaria: Cancelación de solicitud de viaje";
		$body = "".
		"Este correo <strong>no</strong> requiere una acción de tu parte.<br>".
		"Se ha cancelado la solicitud del viaje:<br>".
		"Proyecto: <i>$solicitud->nombre_proyecto</i>.<br>".
		"Motivo: <i>$solicitud->motivo</i>.<br>".
		"Comentarios adicionales:<br>".
		"<i>$comentarios_adicionales</i><br><br>"; // Termina body
		self::enviar_correo( $from, $to, $subject, $body );
		foreach( $solicitud->autorizaciones as &$autorizacion ){
			$autorizacion = ( object ) $autorizacion;
			$to = "$autorizacion->nombre_autorizador $autorizacion->apellido_autorizador <$autorizacion->correo_autorizador>";
			self::enviar_correo( $from, $to, $subject, $body );
		}
	}

	private static function obtener_solicitud( $id_solicitud ){
		try{
			$dbh = Conexion::obtenerInstancia();
			$sth = $dbh->prepare(
				"SELECT
					S.*,
					CP.nombre as nombre_proyecto,
					U.nombre as nombre_viajero, U.apellido as apellido_viajero, U.correo as correo_viajero,
					ADMIN.nombre as nombre_administrador, ADMIN.apellido as apellido_administrador, ADMIN.correo as correo_administrador
				FROM solicitud_viaje as S
					JOIN usario as U
						ON U.correo = S.viajero
					JOIN catalogo_proyecto as CP
						ON S.id_proyecto = CP.id
					JOIN usario as ADMIN
						ON ADMIN.correo = S.administrador
				WHERE S.id = :id_solicitud;"
			);
			$sth->bindValue( ":id_solicitud", $id_solicitud );
			$sth->execute();
			$solicitud = ( object ) $sth->fetch();
			$sth = $dbh->prepare(
				"SELECT
					A.*, U.nombre as nombre_autorizador, U.apellido as apellido_autorizador, A.id_autorizador as correo_autorizador
				FROM autorizacion as A
					INNER JOIN usario as U
					ON A.id_autorizador = U.correo
				WHERE id_solicitud = :id_solicitud;"
			);
			$sth->bindValue( ":id_solicitud", $id_solicitud );
			$sth->execute();
			$solicitud->autorizaciones = $sth->fetchAll();
			return $solicitud;
		}
		catch( PDOException $e ){
			throw $e;
		}
    }

	private static function enviar_correo( $from, $to, $subject, $body ){
		try{
			$params = array(
				"host" => "ssl://smtp.googlemail.com",
				"port" => 465,
				"auth" => true,
				"username" => "soporte@usaria.mx",
				"password" => "Soporte123!"
			);
			$headers = array(
				"From" => $from,
				"To" => $to,
				"Subject" => $subject,
				"Content-Type"  => "text/html; charset=UTF-8\r\n\r\n"
			);
			// Dependencia de Mail
			$smtp = Mail::factory( 'smtp', $params );
			$mail = $smtp->send( $to, $headers, $body );
			if ( PEAR::isError( $mail ) ){
				throw new Exception( "ERROR: " . $mail->getMessage() );
			}
			return;
		}
		catch ( Exception $e ){
			throw $e;
		}
	}

	

	public static function notificarModificacionSolicitud( $id_solicitud ){
		try{
			$solicitud = self::obtenerSolicitud( $id_solicitud );
			$estatus_correos = array();
			$from = "$solicitud->nombre_viajero $solicitud->apellido_viajero <$solicitud->correo_viajero>";
			# Correo para Administrador
			$to = "$solicitud->nombre_administrador $solicitud->apellido_administrador <$solicitud->correo_administrador>";
			#$fecha_salida = strftime( "%d-%m-%Y", strtotime( $solicitud->fecha_inicio ) );
			$subject = "Viajes Usaria: Se ha modificado una solicitud de viaje";
			$body = "".
			"Este correo necesita una acción de tu parte.<br>".
			"Se ha modificado la solicitud para pedir la autorización de la compra de vuelos y hoteles para viajar:<br>".
			"Proyecto: <i>$solicitud->nombre_proyecto</i>.<br>".
			"Motivo: <i>$solicitud->motivo</i>.<br><br>".
			"Para subir los datos de la cotización entra a: http://viajes.usaria.mx/#/dashboard/inicio.<br>".
			"Una vez que realices la cotización se continuará con el paso de selección de vuelos y hotel."; // termina body
			$correo = self::enviarCorreo( $from, $to, $subject, $body );
			array_push( $estatus_correos, $correo );
			# Correo para autorizador
			$body = "".
			"Este correo <strong>no</strong> necesita una acción de tu parte.<br>".
			"Se ha modificado la solicitud para pedir la autorización de la compra de vuelos y hoteles para viajar: <br>".
			"Proyecto: <i>$solicitud->nombre_proyecto</i>.<br>".
			"Motivo: <i>$solicitud->motivo</i>.<br><br>"; // Termina body
			foreach ( $solicitud->autorizaciones as &$autorizacion ){
				$autorizacion = ( object ) $autorizacion;
				$to = "$autorizacion->nombre_autorizador $autorizacion->apellido_autorizador <$autorizacion->correo_autorizador>";
				$correo = self::enviarCorreo( $from, $to, $subject, $body );
				array_push( $estatus_correos, $correo );
			}
			return $estatus_correos;
		}
		catch( Exception $e ){
			echo $e;
			return false;
		}
	}

}
