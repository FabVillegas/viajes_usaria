<?php
require_once( 'Conexion.class.php' );

class Usario{

	public function get_usario_info( $correo ){
		try{
			$dbh = Conexion::obtenerInstancia();
			$sth = $dbh->prepare(
				""
			);
		}
		catch( Exception $e ){

		}
	}


    private function obtenerInformacionAdicional( $solicitud ){
        $dbh = Conexion::obtenerInstancia();
		$solicitud->id_proyecto = ( int ) $solicitud->id_proyecto;
		/**
		*	obtener las autorizaciones requeridas
		*/
		$query = "".
		"SELECT A.*, E.nombre as nombre, E.apellido as apellido
		FROM autorizacion as A, empleado as E
		WHERE id_solicitud = '$solicitud->id' AND A.id_autorizador = E.correo
		ORDER BY fecha_autorizacion DESC;
		"; // Termina query
		$sth = $dbh->prepare( $query );
		$sth->execute();
		$solicitud->autorizaciones = $sth->fetchAll();
		$solicitud->autorizada = null;
		$solicitud->paso_autorizacion_realizado = false;
		$solicitud->necesita_cotizacion_vuelo = ( int ) $solicitud->necesita_cotizacion_vuelo;
		$solicitud->necesita_cotizacion_hotel = ( int ) $solicitud->necesita_cotizacion_hotel;
		foreach ( $solicitud->autorizaciones as &$autorizacion ){
			$autorizacion = ( object ) $autorizacion;
			$autorizacion->solicitud_autorizada = ( int ) $autorizacion->solicitud_autorizada;
			( $autorizacion->fecha_autorizacion ) ? date( 'Y/m/d H:i:s', strtotime( $autorizacion->fecha_autorizacion ) ) : null;
			$solicitud->autorizada = $autorizacion->solicitud_autorizada;
			if( $autorizacion->fecha_autorizacion ){
				$solicitud->paso_autorizacion_realizado = true;
			}
			else{
				$solicitud->paso_autorizacion_realizado = false;
			}
		}
		/**
		*	obtener los vuelos solicitados
		*/
		$query = "".
		"SELECT *
		FROM vuelo_solicitado
		WHERE id_solicitud = '$solicitud->id'
		ORDER BY fecha ASC;
		"; // Termina query
		$sth = $dbh->prepare( $query );
		$sth->execute();
		$solicitud->vuelos_solicitados = $sth->fetchAll();
		foreach ( $solicitud->vuelos_solicitados as &$vuelo_solicitado ){
			$vuelo_solicitado = ( object ) $vuelo_solicitado;
			$vuelo_solicitado->fecha = date( 'Y/m/d H:i:s', strtotime( $vuelo_solicitado->fecha ) );
		}
		/**
		*	obtener los hoteles solicitados
		*/
		$query = "".
		"SELECT *
		FROM hotel_solicitado
		WHERE id_solicitud = '$solicitud->id';
		"; // Termina query
		$sth = $dbh->prepare( $query );
		$sth->execute();
		$solicitud->hoteles_solicitados = $sth->fetchAll();
		/**
		*	Obtener duracion de dias
		*/
		$diaEnSegundos = 86400;
		$fecha_uno_s = strtotime( $solicitud->fecha_inicio );
		$fecha_dos_s = strtotime( $solicitud->fecha_fin );
		$diferencia = $fecha_dos_s - $fecha_uno_s;
		$duracion = round( $diferencia / $diaEnSegundos );
		$duracion++;
		$solicitud->duracion_dias = $duracion;
		/**
		*	Ver la proximidad del inicio
		*/
		$dia_de_hoy = strtotime( date( 'y-m-d' ) );
		$diferencia = $fecha_uno_s - $dia_de_hoy;
		$duracion = round( $diferencia / $diaEnSegundos );
		$duracion++;
		$solicitud->dias_antes_inicio = $duracion;
		/*
		*	Ver si ya paso de la fecha fin
		*/
		$fecha_fin_viaje = strtotime( $solicitud->fecha_fin );
		$diferencia = $dia_de_hoy - $fecha_fin_viaje;
		$duracion = round( $diferencia / $diaEnSegundos );
		$solicitud->dias_despues_de_viaje = $duracion;

		if( !$solicitud->necesita_cotizacion_vuelo && !$solicitud->necesita_cotizacion_hotel && !$solicitud->autorizada ){
			$solicitud->paso = 3;
		}
		else if( $solicitud->fecha_compra && $solicitud->dias_despues_de_viaje > 0 ){
			$solicitud->paso = 5;
		}
		else if( $solicitud->autorizada ){
			$solicitud->paso = 4;
		}
		else if( $solicitud->fecha_seleccion ){
			$solicitud->paso = 3;
			$solicitud->fecha_seleccion = date( 'Y/m/d H:i:s', strtotime( $solicitud->fecha_seleccion ) );
		}
		else if( $solicitud->fecha_cotizacion ){
			$solicitud->paso = 2;
			$solicitud->fecha_cotizacion = date( 'Y/m/d H:i:s', strtotime( $solicitud->fecha_cotizacion ) );
		}
		else if( $solicitud->fecha_creacion ){
			$solicitud->paso = 1;
		}
		return $solicitud;
    }

    public function get_usario_solicitudesEnProceso(){
        try{
            $dbh = Conexion::obtenerInstancia();
			$sth = $dbh->prepare(
				"SELECT
					S.*,
					E.nombre as nombre_viajero, E.apellido as apellido_viajero, E.correo as correo_viajero,
					CP.nombre as nombre_proyecto,
					ADMIN.nombre as nombre_administrador, ADMIN.apellido as apellido_administrador, ADMIN.correo as correo_administrador
				FROM solicitud_viaje as S
					JOIN empleado as E ON E.correo = S.viajero
					JOIN catalogo_proyecto as CP ON CP.id = S.id_proyecto
					JOIN empleado as ADMIN ON ADMIN.correo = S.administrador
				WHERE S.viajero = :correo AND S.viaje_cancelado = 0 AND S.viaje_realizado = 0 AND ( S.fecha_compra IS NULL )
				ORDER BY S.fecha_creacion DESC;"
			);
			#$sth->bindValue( ':correo', $usario->correo );
            $sth->bindValue( ':correo', 'fabritzio.villegas@usaria.mx' );
            $sth->execute();
            $resultados = $sth->fetchAll();
            foreach ( $resultados as &$solicitud ) {
				$solicitud = ( object ) $solicitud;
				$solicitud = $this->obtenerInformacionAdicional( $solicitud );
			}
			#$respuesta = array( 'token_validado' => true, 'datos' => $resultados );
			#echo json_encode( $respuesta );
            return $resultados;
        }
		catch( Exception $e ){
            return [];
		}
    }

    public function get_todos_solicitudesEnProceso(){
        try{
            $dbh = Conexion::obtenerInstancia();
			$sth = $dbh->prepare(
				"SELECT
					S.*,
					E.nombre as nombre_viajero, E.apellido as apellido_viajero, E.correo as correo_viajero,
					CP.nombre as nombre_proyecto,
					ADMIN.nombre as nombre_administrador, ADMIN.apellido as apellido_administrador, ADMIN.correo as correo_administrador
				FROM solicitud_viaje as S
					JOIN empleado as E ON E.correo = S.viajero
					JOIN catalogo_proyecto as CP ON CP.id = S.id_proyecto
					JOIN empleado as ADMIN ON ADMIN.correo = S.administrador
				WHERE S.viaje_cancelado = 0 AND S.viaje_realizado = 0 AND ( S.fecha_compra IS NULL )
				ORDER BY S.fecha_creacion DESC;"
			);
            $sth->execute();
            $resultados = $sth->fetchAll();
            foreach ( $resultados as &$solicitud ) {
				$solicitud = ( object ) $solicitud;
				$solicitud = $this->obtenerInformacionAdicional( $solicitud );
			}
			#$respuesta = array( 'token_validado' => true, 'datos' => $resultados );
			#echo json_encode( $respuesta );
            return $resultados;
        }
		catch( Exception $e ){
            return [];
		}
    }

    public function get_usario_solicitudesPendientesAutorizar(){
        try{
            $dbh = Conexion::obtenerInstancia();
			$sth = $dbh->prepare(
                "SELECT DISTINCT
                    S.*,
                    E.nombre as nombre_viajero, E.apellido as apellido_viajero, E.correo as correo_viajero,
                    CP.nombre as nombre_proyecto,
                    ADMIN.nombre as nombre_administrador, ADMIN.apellido as apellido_administrador, ADMIN.correo as correo_administrador
                FROM solicitud_viaje as S
                    INNER JOIN empleado as E ON E.correo = S.viajero
                    INNER JOIN catalogo_proyecto as CP ON CP.id = S.id_proyecto
                    INNER JOIN empleado as ADMIN ON ADMIN.correo = S.administrador
                    INNER JOIN (
                        SELECT *
                        FROM autorizacion
                        WHERE solicitud_autorizada = 0 OR fecha_autorizacion IS NULL
                    ) as AUTORIZACION ON S.id = AUTORIZACION.id_solicitud
                WHERE ( AUTORIZACION.id_autorizador = :correo_autorizador ) AND
                    S.viaje_cancelado = 0 AND S.viaje_realizado = 0 AND
                    ( S.fecha_cotizacion IS NOT NULL AND S.fecha_seleccion IS NOT NULL AND S.fecha_compra IS NULL )
                ORDER BY S.fecha_creacion DESC;"
            );
            #$sth->bindValue( ':correo_autorizador', $usario->correo );
            $sth->bindValue( ':correo_autorizador', 'fabritzio.villegas@usaria.mx' );
            $sth->execute();
            $resultados = $sth->fetchAll();
            foreach ( $resultados as &$solicitud ) {
				$solicitud = ( object ) $solicitud;
				$solicitud = $this->obtenerInformacionAdicional( $solicitud );
			}
			#$respuesta = array( 'token_validado' => true, 'datos' => $resultados );
			#echo json_encode( $respuesta );
            return $resultados;
        }
		catch( Exception $e ){
            return [];
		}
    }

    public function get_usario_solicitudesAutorizadas(){
        try{
            $dbh = Conexion::obtenerInstancia();
			$sth = $dbh->prepare(
                "SELECT DISTINCT
                    S.*,
                    E.nombre as nombre_viajero, E.apellido as apellido_viajero, E.correo as correo_viajero,
                    CP.nombre as nombre_proyecto,
                    ADMIN.nombre as nombre_administrador, ADMIN.apellido as apellido_administrador
                FROM solicitud_viaje as S
                    INNER JOIN empleado as E ON E.correo = S.viajero
                    INNER JOIN catalogo_proyecto as CP ON CP.id = S.id_proyecto
                    INNER JOIN empleado as ADMIN ON ADMIN.correo = S.administrador
                    INNER JOIN (
                        SELECT *
                        FROM autorizacion
                        WHERE solicitud_autorizada IS NOT NULL AND solicitud_autorizada = 1 AND id_autorizador = :correo_autorizador
                    ) as AUTH ON S.id = AUTH.id_solicitud
                WHERE S.viaje_cancelado = 0 AND S.viaje_realizado = 0 AND
                    ( S.fecha_compra IS NULL )
                ORDER BY S.fecha_creacion DESC;"
            );
            #$sth->bindValue( ':correo_autorizador', $usario->correo );
            $sth->bindValue( ':correo_autorizador', 'fabritzio.villegas@usaria.mx' );
            $sth->execute();
            $resultados = $sth->fetchAll();
            foreach ( $resultados as &$solicitud ) {
				$solicitud = ( object ) $solicitud;
				$solicitud = $this->obtenerInformacionAdicional( $solicitud );
			}
			#$respuesta = array( 'token_validado' => true, 'datos' => $resultados );
			#echo json_encode( $respuesta );
            return $resultados;
        }
		catch( Exception $e ){
            return [];
		}
    }

}