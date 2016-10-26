<?php
require_once( "Conexion.class.php" );
require_once( "PushId.class.php" );
require_once( "Mensajero.class.php" );

class Solicitud{

	public function insertar( $solicitudData, $usarioData ){
		try{
            $dbh = Conexion::obtenerInstancia();
			$solicitud = ( object ) $solicitudData;
			$usario = ( object ) $usarioData;
			$solicitud->id = PushId::generate();
			$dbh->beginTransaction();
			// inserta informacion de la solicitud
			$sth = $dbh->prepare(
				"INSERT INTO solicitud_viaje(
					id, viajero, id_proyecto, motivo, fecha_inicio, fecha_fin, fecha_creacion, locacion
				)
				VALUES(
					:id, :viajero, :id_proyecto, :motivo, :fecha_inicio, :fecha_fin, :fecha_creacion, :locacion
				);"
			);
			$sth->bindValue( ":id", $solicitud->id );
			$sth->bindValue( ":viajero", $usario->correo );
			$sth->bindValue( ":id_proyecto", $solicitud->id_proyecto );
			$sth->bindValue( ":motivo", $solicitud->motivo );
			$solicitud->fecha_inicio = date( "Y-m-d", strtotime( $solicitud->fecha_inicio ) );
			$sth->bindValue( ":fecha_inicio", $solicitud->fecha_inicio );
			$solicitud->fecha_fin = date( "Y-m-d", strtotime( $solicitud->fecha_fin ) );
			$sth->bindValue( ":fecha_fin", $solicitud->fecha_fin );
			$solicitud->fecha_creacion = date( "y-m-d H:i:s" );
			$sth->bindValue( ":fecha_creacion", $solicitud->fecha_creacion );
			$solicitud->locacion = ( isset( $solicitud->locacion ) ? $solicitud->locacion : null );
			$sth->bindValue( ":locacion", $solicitud->locacion );
			$sth->execute();
			// inserta los vuelos solicitados
			if( !empty( $solicitud->vuelos_solicitados ) ){
				foreach( $solicitud->vuelos_solicitados as &$vuelo ){
					$vuelo = ( object ) $vuelo;
					if( isset( $vuelo->fecha ) ){
						$sth = $dbh->prepare(
							"INSERT INTO vuelo_solicitado(
								id, id_solicitud, origen, destino, horario_origen, fecha
							)
							VALUES(
								:id, :id_solicitud, :origen, :destino, :horario_origen, :fecha
							);"
						);
						$vuelo->id = PushId::generate();
						$sth->bindValue( ":id", $vuelo->id );
						$sth->bindValue( ":id_solicitud", $solicitud->id );
						$sth->bindValue( ":origen", $vuelo->origen_texto );
						$sth->bindValue( ":destino", $vuelo->destino_texto );
						$horario_origen = $vuelo->hora_min . " - " . $vuelo->hora_max;
						$sth->bindValue( ":horario_origen", $horario_origen );
						$vuelo->fecha = date( "Y-m-d", strtotime( $vuelo->fecha ) );
						$sth->bindValue( ":fecha", $vuelo->fecha );
						$sth->execute();
					}
				}
			}
			// inserta los hoteles solicitados
			if( !empty( $solicitud->hoteles_solicitados ) ){
				foreach ( $solicitud->hoteles_solicitados as &$hotel ){
					$hotel = ( object ) $hotel;
					$sth = $dbh->prepare(
						"INSERT INTO hotel_solicitado(
							id, id_solicitud, ciudad, direccion, web, numero_noches
						)
						VALUES(
							:id, :id_solicitud, :ciudad, :direccion, :web, :numero_noches
						);"
					);
					$hotel->id = PushId::generate();
					$sth->bindValue( ":id", $hotel->id );
					$sth->bindValue( ":id_solicitud", $solicitud->id );
					$sth->bindValue( ":ciudad", $hotel->ciudad_texto );
					$hotel->direccion = ( isset( $hotel->direccion ) ? $hotel->direccion : "" );
					$sth->bindValue( ":direccion", $hotel->direccion );
					$hotel->web = ( isset( $hotel->web ) ? $hotel->web : "" );
					$sth->bindValue( ":web", $hotel->web );
					$sth->bindValue( ":numero_noches", $hotel->numero_noches );
					$sth->execute();
				}
			}
			// inserta ciudades destino
			if( $solicitud->locacion ){
				$sth = $dbh->prepare(
					"INSERT INTO ciudad( id, id_solicitud, nombre )
					VALUES( :id, :id_solicitud, :nombre );"
				);
				$id = PushId::generate();
				$sth->bindValue( ":id", $id );
				$sth->bindValue( ":id_solicitud", $solicitud->id );
				$sth->bindValue( ":nombre", $solicitud->locacion );
				$sth->execute();
			}
			if( empty( $solicitud->vuelos_solicitados ) && empty( $solicitud->hoteles_solicitados ) ){
				$fecha_hoy = date( "y-m-d H:i:s" );
				$sth = $dbh->prepare(
					"UPDATE solicitud_viaje SET
						necesita_cotizacion = 0,
						fecha_cotizacion = :fecha_hoy,
						fecha_seleccion = :fecha_hoy
					WHERE id = :id_solicitud;"
				);
				$sth->bindValue( ":id_solicitud", $solicitud->id );
				$sth->bindValue( ":fecha_hoy", $fecha_hoy );
				$sth->execute();
			}
			// insertan autorizaciones requeridas
			$correo_usario = $usario->correo;
			do{
				$sth = $dbh->prepare(
					"SELECT U.superior as correo FROM usario as U
					WHERE U.correo = :correo;"
				);
				$sth->bindValue( ":correo", $correo_usario );
				$sth->execute();
				$superior = ( object ) $sth->fetch();
				$sth = $dbh->prepare(
					"INSERT INTO autorizacion(
						id_autorizador, id_solicitud
					)
					VALUES(
						:id_autorizador, :id_solicitud
					);"
				);
				$sth->bindValue( ":id_autorizador", $superior->correo );
				$sth->bindValue( ":id_solicitud", $solicitud->id );
				$sth->execute();
				$correo_usario = $superior->correo;
			}
			while ( $superior->correo !== "soporte@usaria.mx" );
			$dbh->commit();
			// se envian correos
			Mensajero::solicitud_creada( $solicitud->id, $solicitud->comentarios_adicionales );
			return true;
        }
		catch( Exception $e ){
            return array( "error" => $e );
		}
	}

	public function seleccionar_pendientes( $correo_usario ){
		try{
			$dbh = Conexion::obtenerInstancia();
			if( isset( $correo_usario ) ){
				$sth = $dbh->prepare(
					"SELECT
						S.*,
						U.nombre as nombre_viajero, U.apellido as apellido_viajero, U.correo as correo_viajero,
						CP.nombre as nombre_proyecto,
						ADMIN.nombre as nombre_administrador, ADMIN.apellido as apellido_administrador, ADMIN.correo as correo_administrador
					FROM solicitud_viaje as S
						JOIN usario as U ON U.correo = S.viajero
						JOIN catalogo_proyecto as CP ON CP.id = S.id_proyecto
						JOIN usario as ADMIN ON ADMIN.correo = S.administrador
					WHERE S.viajero = :correo AND S.viaje_cancelado = 0 AND S.viaje_realizado = 0 AND ( S.fecha_compra IS NULL )
					ORDER BY S.fecha_creacion DESC;"
				);
				$sth->bindValue( ":correo", $correo_usario );
			}
			else{
				$sth = $dbh->prepare(
					"SELECT
						S.*,
						U.nombre as nombre_viajero, U.apellido as apellido_viajero, U.correo as correo_viajero,
						CP.nombre as nombre_proyecto,
						ADMIN.nombre as nombre_administrador, ADMIN.apellido as apellido_administrador, ADMIN.correo as correo_administrador
					FROM solicitud_viaje as S
						JOIN usario as U ON U.correo = S.viajero
						JOIN catalogo_proyecto as CP ON CP.id = S.id_proyecto
						JOIN usario as ADMIN ON ADMIN.correo = S.administrador
					WHERE S.viaje_cancelado = 0 AND S.viaje_realizado = 0 AND ( S.fecha_compra IS NULL )
					ORDER BY S.fecha_creacion DESC;"
				);
			}
			$sth->execute();
			$solicitudes = $sth->fetchAll();
            foreach ( $solicitudes as &$solicitud ) {
				$solicitud = $this->informacion_adicional( $solicitud );
			}
            return $solicitudes;
		}
		catch( Exception $e ){
			return array( "error" => $e );
		}
	}

	public function seleccionar_por_autorizar( $correo_usario ){
		try{
			$dbh = Conexion::obtenerInstancia();
			if( isset( $correo_usario ) ){
				$sth = $dbh->prepare(
					"SELECT DISTINCT
						S.*,
						U.nombre as nombre_viajero, U.apellido as apellido_viajero, U.correo as correo_viajero,
						CP.nombre as nombre_proyecto,
						ADMIN.nombre as nombre_administrador, ADMIN.apellido as apellido_administrador, ADMIN.correo as correo_administrador
					FROM solicitud_viaje as S
						INNER JOIN usario as U ON U.correo = S.viajero
						INNER JOIN catalogo_proyecto as CP ON CP.id = S.id_proyecto
						INNER JOIN usario as ADMIN ON ADMIN.correo = S.administrador
						INNER JOIN (
							SELECT *
							FROM autorizacion
							WHERE solicitud_autorizada = 0 OR fecha_autorizacion IS NULL
						) as AUTORIZACION ON S.id = AUTORIZACION.id_solicitud
					WHERE ( AUTORIZACION.id_autorizador = :correo_autorizador ) AND
						S.viaje_cancelado = 0 AND S.viaje_realizado = 0 AND
						( S.fecha_cotizacion IS NOT NULL AND S.fecha_seleccion IS NOT NULL )
					ORDER BY S.fecha_creacion DESC;"
				);
				$sth->bindValue( ":correo_autorizador", $correo_usario );		
			}
			else{
				$sth = $dbh->prepare(
					"SELECT DISTINCT
						S.*,
						U.nombre as nombre_viajero, U.apellido as apellido_viajero, U.correo as correo_viajero,
						CP.nombre as nombre_proyecto,
						ADMIN.nombre as nombre_administrador, ADMIN.apellido as apellido_administrador, ADMIN.correo as correo_administrador
					FROM solicitud_viaje as S
						INNER JOIN usario as U ON U.correo = S.viajero
						INNER JOIN catalogo_proyecto as CP ON CP.id = S.id_proyecto
						INNER JOIN usario as ADMIN ON ADMIN.correo = S.administrador
						INNER JOIN (
							SELECT *
							FROM autorizacion
							WHERE solicitud_autorizada = 0 OR fecha_autorizacion IS NULL
						) as AUTORIZACION ON S.id = AUTORIZACION.id_solicitud
					WHERE 
						S.viaje_cancelado = 0 AND S.viaje_realizado = 0 AND
						( S.fecha_cotizacion IS NOT NULL AND S.fecha_seleccion IS NOT NULL )
					ORDER BY S.fecha_creacion DESC;"
				);
			}
			$sth->execute();
			$solicitudes = $sth->fetchAll();
            foreach ( $solicitudes as &$solicitud ) {
				$solicitud = $this->informacion_adicional( $solicitud );
			}
            return $solicitudes;
		}
		catch( Exception $e ){
			return array( "error" => $e );
		}
	}

	public function seleccionar_autorizadas( $correo_usario ){
		try{
			$dbh = Conexion::obtenerInstancia();
			$sth = $dbh->prepare(
				"SELECT DISTINCT
					S.*,
					U.nombre as nombre_viajero, U.apellido as apellido_viajero, U.correo as correo_viajero,
					CP.nombre as nombre_proyecto,
					ADMIN.nombre as nombre_administrador, ADMIN.apellido as apellido_administrador, ADMIN.correo as correo_administrador
				FROM solicitud_viaje as S
					INNER JOIN usario as U ON U.correo = S.viajero
					INNER JOIN catalogo_proyecto as CP ON CP.id = S.id_proyecto
					INNER JOIN usario as ADMIN ON ADMIN.correo = S.administrador
					INNER JOIN (
						SELECT *
						FROM autorizacion
						WHERE solicitud_autorizada = 1 AND fecha_autorizacion IS NOT NULL
					) as AUTORIZACION ON S.id = AUTORIZACION.id_solicitud
				WHERE ( AUTORIZACION.id_autorizador = :correo_autorizador ) AND
					S.viaje_cancelado = 0 AND S.viaje_realizado = 0 AND ( S.fecha_seleccion IS NOT NULL ) AND ( S.fecha_compra IS NULL )
				ORDER BY S.fecha_creacion DESC;"
			);
			$sth->bindValue( ":correo_autorizador", $correo_usario );	
			/*
			if( isset( $correo_usario ) ){
				$sth = $dbh->prepare(
					"SELECT DISTINCT
						S.*,
						U.nombre as nombre_viajero, U.apellido as apellido_viajero, U.correo as correo_viajero,
						CP.nombre as nombre_proyecto,
						ADMIN.nombre as nombre_administrador, ADMIN.apellido as apellido_administrador, ADMIN.correo as correo_administrador
					FROM solicitud_viaje as S
						INNER JOIN usario as U ON U.correo = S.viajero
						INNER JOIN catalogo_proyecto as CP ON CP.id = S.id_proyecto
						INNER JOIN usario as ADMIN ON ADMIN.correo = S.administrador
						INNER JOIN (
							SELECT *
							FROM autorizacion
							WHERE solicitud_autorizada = 1 AND fecha_autorizacion IS NOT NULL
						) as AUTORIZACION ON S.id = AUTORIZACION.id_solicitud
					WHERE ( AUTORIZACION.id_autorizador = :correo_autorizador ) AND
						S.viaje_cancelado = 0 AND S.viaje_realizado = 0 AND
						( S.fecha_cotizacion IS NULL AND S.fecha_seleccion IS NOT NULL )
					ORDER BY S.fecha_creacion DESC;"
				);
				$sth->bindValue( ":correo_autorizador", $correo_usario );		
			}
			else{
				$sth = $dbh->prepare(
					"SELECT DISTINCT
						S.*,
						U.nombre as nombre_viajero, U.apellido as apellido_viajero, U.correo as correo_viajero,
						CP.nombre as nombre_proyecto,
						ADMIN.nombre as nombre_administrador, ADMIN.apellido as apellido_administrador, ADMIN.correo as correo_administrador
					FROM solicitud_viaje as S
						INNER JOIN usario as U ON U.correo = S.viajero
						INNER JOIN catalogo_proyecto as CP ON CP.id = S.id_proyecto
						INNER JOIN usario as ADMIN ON ADMIN.correo = S.administrador
						INNER JOIN (
							SELECT *
							FROM autorizacion
							WHERE solicitud_autorizada = 0 OR fecha_autorizacion IS NULL
						) as AUTORIZACION ON S.id = AUTORIZACION.id_solicitud
					WHERE 
						S.viaje_cancelado = 0 AND S.viaje_realizado = 0 AND
						( S.fecha_cotizacion IS NOT NULL AND S.fecha_seleccion IS NOT NULL )
					ORDER BY S.fecha_creacion DESC;"
				);
			}
			*/
			$sth->execute();
			$solicitudes = $sth->fetchAll();
            foreach ( $solicitudes as &$solicitud ) {
				$solicitud = $this->informacion_adicional( $solicitud );
			}
            return $solicitudes;
		}
		catch( Exception $e ){
			return array( "error" => $e );
		}
	}

	public function seleccionar_completas( $correo_usario ){
		try{
            $dbh = Conexion::obtenerInstancia();
			$sth = $dbh->prepare(
				"SELECT
					S.*,
					U.nombre as nombre_viajero, U.apellido as apellido_viajero, U.correo as correo_viajero,
					CP.nombre as nombre_proyecto,
					ADMIN.nombre as nombre_administrador, ADMIN.apellido as apellido_administrador, ADMIN.correo as correo_administrador
				FROM solicitud_viaje as S
					JOIN usario as U ON U.correo = S.viajero
					JOIN catalogo_proyecto as CP ON CP.id = S.id_proyecto
					JOIN usario as ADMIN ON ADMIN.correo = S.administrador
				WHERE S.viajero = :correo AND S.viaje_cancelado = 0 AND S.viaje_realizado = 0 AND ( S.fecha_compra IS NOT NULL )
				ORDER BY S.fecha_creacion DESC;"
			);
			$sth->bindValue( ":correo", $correo_usario );
            $sth->execute();
			$solicitudes = $sth->fetchAll();
            foreach ( $solicitudes as &$solicitud ) {
				$solicitud = $this->informacion_adicional( $solicitud );
			}
            return $solicitudes;
        }
		catch( Exception $e ){
            return array( "error" => $e );
		}
	}

	public function seleccionar_individual( $id_solicitud ){
		try{
            $dbh = Conexion::obtenerInstancia();
			$sth = $dbh->prepare(
				"SELECT
					S.*,
					E.nombre as nombre_viajero, E.apellido as apellido_viajero,
					ADMIN.nombre as nombre_administrador, ADMIN.apellido as apellido_administrador
				FROM solicitud_viaje as S
					JOIN usario as E
						ON E.correo = S.viajero
					JOIN catalogo_proyecto as CP
						ON S.id_proyecto = CP.id
					JOIN usario as ADMIN
						ON ADMIN.correo = S.administrador
				WHERE S.id = :id_solicitud"
			);
			$sth->bindValue( ':id_solicitud', $id_solicitud );
            $sth->execute();
			$solicitud = $sth->fetch();
			$solicitud = $this->informacion_adicional( $solicitud );
			return ( array ) $solicitud;
        }
		catch( Exception $e ){
            return array( "error" => $e );
		}
	}

	public function insertar_opcion_hotel( $opcion_hotel ){
		try{
			$dbh = Conexion::obtenerInstancia();
			$opcion_hotel = ( object ) $opcion_hotel;
			$dbh->beginTransaction();
			$opcion_hotel->id = PushId::generate();
			$opcion_hotel->fecha_registro = date( "y-m-d H:i:s" );
			$sth = $dbh->prepare(
				"INSERT INTO opcion_hotel(
					id,
					id_hotel_solicitado,
					nombre,
					direccion,
					sitioweb,
					costo,
					fecha_registro
				)
				VALUES (
					:hotel_id,
					:hotel_idHotelSolicitado,
					:hotel_nombre,
					:hotel_direccion,
					:hotel_sitioweb,
					:hotel_costo,
					:hotel_fechaRegistro
				);"
			);
			$sth->bindValue( ":hotel_id", $opcion_hotel->id );
			$sth->bindValue( ":hotel_idHotelSolicitado", $opcion_hotel->id_hotel_solicitado );
			$sth->bindValue( ":hotel_nombre", $opcion_hotel->nombre );
			$sth->bindValue( ":hotel_direccion", $opcion_hotel->direccion );
			$opcion_hotel->sitioweb = ( isset( $opcion_hotel->sitioweb ) ) ? $opcion_hotel->sitioweb : "";
			$sth->bindValue( ":hotel_sitioweb", $opcion_hotel->sitioweb );
			$sth->bindValue( ":hotel_costo", $opcion_hotel->costo );
			$sth->bindValue( ":hotel_fechaRegistro", $opcion_hotel->fecha_registro );	
			$sth->execute();
			$dbh->commit();
			return true;
		}
		catch( Exception $e ){
			return array( "error" => $e );
		}
	}

	public function seleccionar_opciones_hotel( $id_solicitud ){
		try{
			$dbh = Conexion::obtenerInstancia();
			$sth = $dbh->prepare(
				"SELECT id
				FROM hotel_solicitado
				WHERE id_solicitud = :id_solicitud;"
			);
			$sth->bindValue( ":id_solicitud", $id_solicitud );
			$sth->execute();
			$hoteles_solicitados = $sth->fetchAll();
			$opciones_hoteles = array();
			foreach( $hoteles_solicitados as &$hotelSolicitado ){
				$hotelSolicitado = ( object ) $hotelSolicitado;
				$aux = array();
				$sth = $dbh->prepare(
					"SELECT *
					FROM opcion_hotel
					WHERE id_hotel_solicitado = :hotelSolicitado_id;"
				);
				$sth->bindValue( ":hotelSolicitado_id", $hotelSolicitado->id, PDO::PARAM_STR );
				$sth->execute();
				$opciones = $sth->fetchAll();
				foreach( $opciones as &$opcion ){
					array_push( $aux, $opcion );
				}
				$opciones_hoteles[ $hotelSolicitado->id ] = $aux;
			}
			return $opciones_hoteles;
		}
		catch( Exception $e ){
			return array( "error" => $e );
		}
	}

	public function eliminar_opcion_hotel( $id_opcion_hotel ){
		try{
			$dbh = Conexion::obtenerInstancia();
			$dbh->beginTransaction();
			$sth = $dbh->prepare( "DELETE FROM opcion_hotel WHERE id = :id;" );
			$sth->bindValue( ":id", $id_opcion_hotel );
			$sth->execute();
			$dbh->commit();
			return true;
		}
		catch( Exception $e ){
			throw $e;
		}
	}

	public function insertar_opcion_vuelo( $opcion_vuelo ){
		try{
			$dbh = Conexion::obtenerInstancia();
			$opcion_vuelo = ( object ) $opcion_vuelo;
			$dbh->beginTransaction();
			$opcion_vuelo->id = PushId::generate();
			$opcion_vuelo->fecha_registro = date( "y-m-d H:i:s" );
			$sth = $dbh->prepare(
				"INSERT INTO opcion_vuelo(
					id,
					id_vuelo_solicitado,
					aerolinea,
					origen,
					destino,
					fecha_salida,
					hora_salida,
					fecha_llegada,
					hora_llegada,
					costo,
					escalas, 
					comentarios,
					fecha_registro
				) VALUES (
					:id,
					:id_vuelo_solicitado,
					:aerolinea,
					:origen,
					:destino,
					:fecha_salida,
					:hora_salida,
					:fecha_llegada,
					:hora_llegada,
					:costo,
					:escalas, 
					:comentarios,
					:fecha_registro
				);"
			);
			$sth->bindValue( ":id", $opcion_vuelo->id );
			$sth->bindValue( ":id_vuelo_solicitado", $opcion_vuelo->id_vuelo_solicitado );
			$sth->bindValue( ":aerolinea", $opcion_vuelo->aerolinea );
			$sth->bindValue( ":origen", $opcion_vuelo->origen );
			$sth->bindValue( ":destino", $opcion_vuelo->destino );
			$sth->bindValue( ":fecha_salida", $opcion_vuelo->fecha_salida );
			$sth->bindValue( ":hora_salida", $opcion_vuelo->hora_salida );
			$sth->bindValue( ":fecha_llegada", $opcion_vuelo->fecha_llegada );
			$sth->bindValue( ":hora_llegada", $opcion_vuelo->hora_llegada );
			$sth->bindValue( ":costo", $opcion_vuelo->costo );
			$sth->bindValue( ":escalas", $opcion_vuelo->escalas );
			$opcion_vuelo->comentarios = ( isset( $opcion_vuelo->comentarios ) ) ? $opcion_vuelo->comentarios : "";
			$sth->bindValue( ":comentarios", $opcion_vuelo->comentarios );
			$sth->bindValue( ":fecha_registro", $opcion_vuelo->fecha_registro );
			$sth->execute();
			$dbh->commit();
			return true;//$opcion_vuelo->id;
		}
		catch( Exception $e ){
			return array( "error" => $e );
		}
	}

	public function seleccionar_opciones_vuelo( $id_solicitud ){
		try{
			$dbh = Conexion::obtenerInstancia();
			$sth = $dbh->prepare(
				"SELECT id
				FROM vuelo_solicitado
				WHERE id_solicitud = :id_solicitud;"
			);
			$sth->bindValue( ":id_solicitud", $id_solicitud );
			$sth->execute();
			$vuelos_solicitados = $sth->fetchAll();
			$opciones_vuelos = array();
			foreach( $vuelos_solicitados as &$vuelo_solicitado ){
				$vuelo_solicitado = ( object ) $vuelo_solicitado;
				$aux = array();
				$sth = $dbh->prepare(
					"SELECT *
					FROM opcion_vuelo
					WHERE id_vuelo_solicitado = :id_vuelo_solicitado;"
				);
				$sth->bindValue( ":id_vuelo_solicitado", $vuelo_solicitado->id, PDO::PARAM_STR );
				$sth->execute();
				$opciones = $sth->fetchAll();
				foreach( $opciones as &$opcion ){
					$opcion = ( object ) $opcion;
					$opcion->fecha_salida = str_replace( "-", "/", $opcion->fecha_salida );
					$opcion->fecha_llegada = str_replace( "-", "/", $opcion->fecha_llegada );
					$opcion->costo = ( float ) $opcion->costo;
					$opcion->escalas = ( int ) $opcion->escalas;
					if( substr_count( $opcion->origen, "(" ) == 1 && substr_count( $opcion->origen, ")" ) == 1 ){
						$inicio = strpos( $opcion->origen, "(" );
						$fin = strpos( $opcion->origen, ")" );
						$opcion->origen = substr( $opcion->origen, $inicio + 1, $fin - $inicio - 1 );
					}
					if( substr_count( $opcion->destino, "(" ) == 1 && substr_count( $opcion->destino, ")" ) == 1 ){
						$inicio = strpos( $opcion->destino, "(" );
						$fin = strpos( $opcion->destino, ")" );
						$opcion->destino = substr( $opcion->destino, $inicio + 1, $fin - $inicio - 1 );
					}
					array_push( $aux, $opcion );
				}
				$opciones_vuelo[ $vuelo_solicitado->id ] = $aux;
			}
			return $opciones_vuelo;
		}
		catch( Exception $e ){
			return array( "error" => $e );
		}
	}

	public function eliminar_opcion_vuelo( $id_opcion_vuelo ){
		try{
			$dbh = Conexion::obtenerInstancia();
			$dbh->beginTransaction();
			$sth = $dbh->prepare( "DELETE FROM opcion_vuelo WHERE id = :id;" );
			$sth->bindValue( ":id", $id_opcion_vuelo );
			$sth->execute();
			$dbh->commit();
			return true;
		}
		catch( Exception $e ){
			throw $e;
		}
	}

	public function actualizar_opcion_vuelo( $opcion_vuelo ){
		try{
			$dbh = Conexion::obtenerInstancia();
			$opcion_vuelo = ( object ) $opcion_vuelo;
			$dbh->beginTransaction();
			$sth = $dbh->prepare( "UPDATE opcion_vuelo SET costo = :costo WHERE id = :id;" );
			$sth->bindValue( ":costo", $opcion_vuelo->costo );
			$sth->bindValue( ":id", $opcion_vuelo->id );
			$sth->execute();
			$dbh->commit();
			return true;
		}
		catch( Exception $e ){
			throw $e;
		}
	}

	public function insertar_vuelo_seleccionado( $vuelo_seleccionado ){
		try{
			$dbh = Conexion::obtenerInstancia();
			$dbh->beginTransaction();
			$vuelo_seleccionado = ( object ) $vuelo_seleccionado;
			$sth = $dbh->prepare(
				"INSERT INTO vuelo_seleccionado( id_vuelo_solicitado, id_opcion_vuelo )
				VALUES( :id_vuelo_solicitado, :id_opcion_vuelo )
				ON DUPLICATE KEY UPDATE id_opcion_vuelo = :id_opcion_vuelo;"
			);
			$sth->bindValue( ":id_vuelo_solicitado", $vuelo_seleccionado->id_vuelo_solicitado );
			$sth->bindValue( ":id_opcion_vuelo", $vuelo_seleccionado->id );
			$sth->execute();
			$dbh->commit();
			return true;
		}
		catch( Exception $e ){
			throw $e;
		}
	}

	public function insertar_hotel_seleccionado( $hotel_seleccionado ){
		try{
			$dbh = Conexion::obtenerInstancia();
			$dbh->beginTransaction();
			$hotel_seleccionado = ( object ) $hotel_seleccionado;
			$sth = $dbh->prepare(
				"INSERT INTO hotel_seleccionado( id_hotel_solicitado, id_opcion_hotel )
				VALUES( :id_hotel_solicitado, :id_opcion_hotel )
				ON DUPLICATE KEY UPDATE id_opcion_hotel = :id_opcion_hotel;"
			);
			$sth->bindValue( ":id_hotel_solicitado", $hotel_seleccionado->id_hotel_solicitado );
			$sth->bindValue( ":id_opcion_hotel", $hotel_seleccionado->id );
			$sth->execute();
			$dbh->commit();
			return true;
		}
		catch( Exception $e ){
			throw $e;
		}
	}

	public function insertar_archivo_compra( $id_solicitud, $archivo, $descripcion_archivo ){
		try{
			$dbh = Conexion::obtenerInstancia();
			$id_archivo = PushId::generate();
			$nombre_archivo = $archivo[ "name" ];
			$temp_archivo = $archivo[ "tmp_name" ];
			$tamano_archivo = $archivo[ "size" ];
			$tipo_archivo = $archivo[ "type" ];
			$archivo_abierto = fopen( $temp_archivo, "r" );
			$contenido = fread( $archivo_abierto, filesize( $temp_archivo ) );
			$contenido = addslashes( $contenido );
			$dbh->beginTransaction();
			$sth = $dbh->prepare(
				"INSERT INTO archivo_compra(
					id,
					id_solicitud,
					nombre,
					descripcion,
					tipo,
					tamano,
					contenido
				)
				VALUES (
					:id,
					:id_solicitud,
					:nombre_archivo,
					:descripcion_archivo,
					:tipo_archivo,
					:tamano_archivo,
					:contenido
				);"
			);
			$sth->bindValue( ":id", $id_archivo );
			$sth->bindValue( ":id_solicitud", $id_solicitud );
			$sth->bindValue( ":nombre_archivo", $nombre_archivo );
			$sth->bindValue( ":descripcion_archivo", $descripcion_archivo );
			$sth->bindValue( ":tipo_archivo", $tipo_archivo );
			$sth->bindValue( ":tamano_archivo", $tamano_archivo );
			$sth->bindValue( ":contenido", $contenido );
			$sth->execute();
			$dbh->commit();
			return $id_archivo;
		}
		catch( Exception $e ){
			throw $e;
		}
	}

	public function descargar_archivo( $id_archivo ){
		try{
			$dbh = Conexion::obtenerInstancia();
			$sth = $dbh->prepare(
				"SELECT *
				FROM archivo_compra
				WHERE id = :id_archivo;"
			);
			$sth->bindValue( ":id_archivo", $id_archivo );
			$sth->execute();
			$archivo = ( object ) $sth->fetch();

			$archivo->contenido = base64_encode( $archivo->contenido );
			file_put_contents( $archivo->nombre, $archivo->contenido );
			header( "Content-length: $archivo->tamano" );
			header( "Content-type: $archivo->tipo" );
			header( "Content-Disposition: attachment; filename=$archivo->nombre" );
			print( $archivo->contenido );
		}
		catch( Exception $e ){
			throw $e;
		}
	}

	public function enviar_cotizacion( $id_solicitud, $comentarios_adicionales ){
		try{
			$dbh = Conexion::obtenerInstancia();
			$dbh->beginTransaction();
			$fecha_hoy = date( "y-m-d H:i:s" );
			$sth = $dbh->prepare(
				"UPDATE solicitud_viaje SET fecha_cotizacion = :fecha_hoy WHERE id = :id;"
			);
			$sth->bindValue( ":fecha_hoy", $fecha_hoy );
			$sth->bindValue( ":id", $id_solicitud );
			$sth->execute();
			$dbh->commit();
			Mensajero::enviar_cotizacion( $id_solicitud, $comentarios_adicionales );
			return true;
		}
		catch( Exception $e ){
			throw $e;
		}
	}

	public function enviar_seleccion( $id_solicitud, $comentarios_adicionales ){
		try{
			$dbh = Conexion::obtenerInstancia();
			$dbh->beginTransaction();
			$fecha_hoy = date( "y-m-d H:i:s" );
			$sth = $dbh->prepare(
				"UPDATE solicitud_viaje SET fecha_seleccion = :fecha_hoy WHERE id = :id;"
			);
			$sth->bindValue( ":fecha_hoy", $fecha_hoy );
			$sth->bindValue( ":id", $id_solicitud );
			$sth->execute();
			$dbh->commit();
			Mensajero::enviar_seleccion( $id_solicitud, $comentarios_adicionales );
			return true;
		}
		catch( Exception $e ){
			throw $e;
		}
	}

	public function enviar_autorizacion( $id_solicitud, $autorizador, $comentarios_adicionales, $respuesta ){
		try{
			$dbh = Conexion::obtenerInstancia();
			$dbh->beginTransaction();
			$fecha_hoy = date( "y-m-d H:i:s" );
			$sth = $dbh->prepare(
				"UPDATE autorizacion
				SET solicitud_autorizada = :solicitud_autorizada, mensaje = :mensaje, fecha_autorizacion = :fecha_autorizacion
				WHERE id_autorizador = :id_autorizador AND id_solicitud = :id_solicitud;"
			);
			$sth->bindValue( ":solicitud_autorizada", $respuesta );
			$sth->bindValue( ":mensaje", $comentarios_adicionales );
			$sth->bindValue( ":fecha_autorizacion", $fecha_hoy );
			$sth->bindValue( ":id_autorizador", $autorizador );
			$sth->bindValue( ":id_solicitud", $id_solicitud );
			$sth->execute();
			$dbh->commit();
			Mensajero::enviar_autorizacion( $id_solicitud, $autorizador, $comentarios_adicionales, $respuesta );
			return true;
		}
		catch( Exception $e ){
			throw $e;
		}
	}

	public function enviar_compra( $id_solicitud, $comentarios_adicionales ){
		try{
			$dbh = Conexion::obtenerInstancia();
			$dbh->beginTransaction();
			$fecha_hoy = date( "y-m-d H:i:s" );
			$sth = $dbh->prepare(
				"UPDATE solicitud_viaje SET fecha_compra = :fecha_hoy WHERE id = :id;"
			);
			$sth->bindValue( ":fecha_hoy", $fecha_hoy );
			$sth->bindValue( ":id", $id_solicitud );
			$sth->execute();
			$dbh->commit();
			Mensajero::enviar_compra( $id_solicitud, $comentarios_adicionales );
			return true;
		}
		catch( Exception $e ){
			throw $e;
		}
	}

	public function cancelar_individual( $id_solicitud, $comentarios_adicionales ){
		try{
			$dbh = Conexion::obtenerInstancia();
			$dbh->beginTransaction();
			$sth = $dbh->prepare(
				"UPDATE solicitud_viaje SET viaje_cancelado = 1
				WHERE id = :id_solicitud;"
			);
			$sth->bindValue( ":id_solicitud", $id_solicitud );
			$sth->execute();
			Mensajero::cancelar_solicitud( $id_solicitud, $comentarios_adicionales );
			$dbh->commit();
			return true;
		}
		catch( Exception $e ){
			$dbh->rollback();
			throw $e;
		}
	}

	private function informacion_adicional( $solicitud ){
		try{
			$dbh = Conexion::obtenerInstancia();
			$solicitud = ( object ) $solicitud;
			$solicitud->fecha_creacion = date( 'Y/m/d H:i:s', strtotime( $solicitud->fecha_creacion ) );
			$solicitud->fecha_inicio = date( 'Y/m/d H:i:s', strtotime( $solicitud->fecha_inicio ) );
			$solicitud->fecha_fin = date( 'Y/m/d H:i:s', strtotime( $solicitud->fecha_fin ) );
			$solicitud->necesita_cotizacion = ( bool ) $solicitud->necesita_cotizacion;
			
			
			// Autorizaciones -------------------------------------------------------------------
			$sth = $dbh->prepare(
				"SELECT A.*, E.nombre as nombre, E.apellido as apellido
				FROM autorizacion as A, usario as E
				WHERE id_solicitud = :id_solicitud AND A.id_autorizador = E.correo;"
			);
			$sth->bindValue( ":id_solicitud", $solicitud->id );
			$sth->execute();
			$solicitud->autorizaciones = $sth->fetchAll();
			$solicitudes_autorizadas = 0;
			#$solicitud->paso_autorizacion_realizado = false;
			#$solicitud->necesita_cotizacion_vuelo = ( int ) $solicitud->necesita_cotizacion_vuelo;
			#$solicitud->necesita_cotizacion_hotel = ( int ) $solicitud->necesita_cotizacion_hotel;
			foreach ( $solicitud->autorizaciones as &$autorizacion ){
				$autorizacion = ( object ) $autorizacion;
				( $autorizacion->fecha_autorizacion ) ? date( 'Y/m/d H:i:s', strtotime( $autorizacion->fecha_autorizacion ) ) : null;
				$autorizacion->solicitud_autorizada = ( bool ) $autorizacion->solicitud_autorizada;
				$solicitud->autorizada = $autorizacion->solicitud_autorizada;
				if( $autorizacion->solicitud_autorizada ){
					$solicitudes_autorizadas++;
				}
			}
			if( $solicitudes_autorizadas == count( $solicitud->autorizaciones ) ){
				$solicitud->autorizada = true;
			}
			else{
				$solicitud->autorizada = false;
			}


			// Vuelos solicitados y seleccionados -------------------------------------------
			$sth = $dbh->prepare(
				"SELECT *
				FROM vuelo_solicitado
				WHERE id_solicitud = :id_solicitud
				ORDER BY fecha ASC;"
			);
			$sth->bindValue( ":id_solicitud", $solicitud->id );
			$sth->execute();
			$solicitud->vuelos_solicitados = $sth->fetchAll();
			$solicitud->vuelos_seleccionados = array();
			foreach ( $solicitud->vuelos_solicitados as &$vuelo_solicitado ){
				$vuelo_solicitado = ( object ) $vuelo_solicitado;
				$vuelo_solicitado->fecha = date( 'Y/m/d H:i:s', strtotime( $vuelo_solicitado->fecha ) );
				$sth = $dbh->prepare(
					"SELECT *
					FROM opcion_vuelo as OV
					WHERE id_vuelo_solicitado = :id_vuelo_solicitado AND EXISTS(
						SELECT * 
						FROM vuelo_seleccionado
						WHERE id_vuelo_solicitado = :id_vuelo_solicitado AND id_opcion_vuelo = OV.id
					);"
				);
				$sth->bindValue( ":id_vuelo_solicitado", $vuelo_solicitado->id );
				$sth->execute();
				$vuelo_seleccionado = ( object ) $sth->fetch();
				if( isset( $vuelo_seleccionado->id ) ){
					$vuelo_seleccionado->fecha_salida = date( 'Y/m/d H:i:s', strtotime( $vuelo_seleccionado->fecha_salida ) );
					$vuelo_seleccionado->fecha_llegada = date( 'Y/m/d H:i:s', strtotime( $vuelo_seleccionado->fecha_llegada ) );
					$vuelo_seleccionado->costo = ( float ) $vuelo_seleccionado->costo;
					$vuelo_seleccionado->escalas = ( int ) $vuelo_seleccionado->escalas;
					if( substr_count( $vuelo_seleccionado->origen, "(" ) == 1 && substr_count( $vuelo_seleccionado->origen, ")" ) == 1 ){
						$inicio = strpos( $vuelo_seleccionado->origen, "(" );
						$fin = strpos( $vuelo_seleccionado->origen, ")" );
						$vuelo_seleccionado->origen = substr( $vuelo_seleccionado->origen, $inicio + 1, $fin - $inicio - 1 );
					}
					if( substr_count( $vuelo_seleccionado->destino, "(" ) == 1 && substr_count( $vuelo_seleccionado->destino, ")" ) == 1 ){
						$inicio = strpos( $vuelo_seleccionado->destino, "(" );
						$fin = strpos( $vuelo_seleccionado->destino, ")" );
						$vuelo_seleccionado->destino = substr( $vuelo_seleccionado->destino, $inicio + 1, $fin - $inicio - 1 );
					}
					array_push( $solicitud->vuelos_seleccionados, $vuelo_seleccionado );
				}
			}


			// Hoteles solicitados --------------------------------------------
			$sth = $dbh->prepare(
				"SELECT *
				FROM hotel_solicitado
				WHERE id_solicitud = :id_solicitud;"
			);
			$sth->bindValue( ":id_solicitud", $solicitud->id );
			$sth->execute();
			$solicitud->hoteles_solicitados = $sth->fetchAll();
			$solicitud->hoteles_seleccionados = array();
			foreach( $solicitud->hoteles_solicitados as &$hotel_solicitado ){
				$hotel_solicitado = ( object ) $hotel_solicitado;
				$sth = $dbh->prepare(
					"SELECT *
					FROM opcion_hotel as OH
					WHERE id_hotel_solicitado = :id_hotel_solicitado AND EXISTS(
						SELECT * 
						FROM hotel_seleccionado
						WHERE id_hotel_solicitado = :id_hotel_solicitado AND id_opcion_hotel = OH.id
					);"
				);
				$sth->bindValue( ":id_hotel_solicitado", $hotel_solicitado->id );
				$sth->execute();
				$hotel_seleccionado = ( object ) $sth->fetch();
				if( isset( $hotel_seleccionado->id ) ){
					$hotel_seleccionado->costo = ( int ) $hotel_seleccionado->costo;
					array_push( $solicitud->hoteles_seleccionados, $hotel_seleccionado );
				}
			}


			// Ciudades destino -------------------------------------------------
			$sth = $dbh->prepare(
				"SELECT *
				FROM ciudad
				WHERE id_solicitud = :id_solicitud;"
			);
			$sth->bindValue( ":id_solicitud", $solicitud->id );
			$sth->execute();
			$solicitud->ciudades_destino = $sth->fetchAll();


			// Archivos de compra -------------------------------------------------
			$sth = $dbh->prepare(
				"SELECT id, nombre, descripcion, tipo, tamano
				FROM archivo_compra
				WHERE id_solicitud = :id_solicitud;"
			);
			$sth->bindValue( ":id_solicitud", $solicitud->id );
			$sth->execute();
			$solicitud->archivos_compra = $sth->fetchAll();


			// Fin ciudades destino
			// Obtener duracion de dias
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

			// Pasos ------------------------------------------------------------------------------
			$solicitud->paso = null;
			$solicitud->fecha_seleccion = ( $solicitud->fecha_seleccion ) ? date( 'Y/m/d H:i:s', strtotime( $solicitud->fecha_seleccion ) ) : null;
			$solicitud->fecha_cotizacion = ( $solicitud->fecha_cotizacion ) ? date( 'Y/m/d H:i:s', strtotime( $solicitud->fecha_cotizacion ) ) : null;
			if( $solicitud->autorizada ){
				$solicitud->paso = 4;
			}
			else if( $solicitud->fecha_seleccion ){
				$solicitud->paso = 3;
			}
			else if( $solicitud->fecha_cotizacion ){
				$solicitud->paso = 2;
			}
			else if( $solicitud->fecha_creacion ){
				$solicitud->paso = 1;
			}


			/*
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
			*/
			return $solicitud;
		}
		catch( Exception $e ){
			throw $e;
		}
	}

}