<?php
require_once( "Conexion.class.php" );
require_once( "PushId.class.php" );

class Archivo{

    public static function insertar_compra( $id_solicitud, $descripcion_archivo ){
        try{
            $dbh = Conexion::obtenerInstancia();
            $id_archivo = PushId::generate();
            $nombre_archivo = $_FILES[ "archivo" ][ "name" ];
            $temp_archivo = $_FILES[ "archivo" ][ "tmp_name" ];
            $tamano_archivo = $_FILES[ "archivo" ][ "size" ];
            $tipo_archivo = $_FILES[ "archivo" ][ "type" ];
            $archivo_abierto = fopen( $temp_archivo, 'r' );
            $contenido = fread( $archivo_abierto, filesize( $temp_archivo ) );
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
        catch ( Exception $e ) {
            $dbh->rollback();
            throw $e;
        }
    }

    public static function descargar_compra( $id_archivo ){
        try{
            $dbh = Conexion::obtenerInstancia();
            $sth = $dbh->prepare(
                "SELECT *
                FROM archivo_compra
                WHERE id = :id;"
            );
            $sth->bindValue( ":id", $id_archivo );
            $sth->execute();
            $archivo = ( object ) $sth->fetch();
            header( "Content-length: $archivo->tamano" );
            header( "Content-type: $archivo->tipo" );
            header( "Content-Disposition: attachment; filename=$archivo->nombre" );
            ob_clean();
            flush();
            echo $archivo->contenido;
        }
        catch( Exception $e ){
            throw $e;
        }
    }

}