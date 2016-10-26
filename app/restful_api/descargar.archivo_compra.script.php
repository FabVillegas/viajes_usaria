<?php
require_once( __DIR__ . "/../../restful_api_src/classes/Token.class.php" );
require_once( __DIR__ . "/../../restful_api_src/classes/Conexion.class.php" );

ini_set('max_execution_time', 0);
set_time_limit(0);

try{
	if( isset( $_GET[ "usaria_token" ] ) ){
		$token = $_GET[ "usaria_token" ];
		$verificacion = Token::verificar( $token );
		if( $verificacion == false ){
			throw new Exception( "Token invalido" );
		}
		if( isset( $_GET[ "id_archivo" ] ) ){
			$id_archivo = $_GET[ "id_archivo" ];
			$dbh = Conexion::obtenerInstancia();
			$sth = $dbh->prepare(
				"SELECT *
				FROM archivo_compra
				WHERE id = :id;"
			);
			$sth->bindValue( ":id", $id_archivo );
			$sth->execute();
			$archivo = ( object ) $sth->fetch();
			$archivo->contenido_64 = base64_encode( $archivo->contenido );
			file_put_contents( $archivo->nombre, $archivo->contenido_64 );
			header( "Content-length: $archivo->tamano" );
			header( "Content-type: $archivo->tipo" );
			header( "Content-Disposition: attachment; filename=$archivo->nombre" );
			print $archivo->contenido;
			unlink( $archivo->nombre );
		}
		else{
			throw new Exception( "id_archivo: undefined" );
		}

	}
	else{
		throw new Exception( "Token invalido" );
	}
}
catch( Exception $e ){
	echo $e->getMessage();
}



/*

require_once( "../classes/Conexion.class.php" );
ini_set('max_execution_time', 0);
set_time_limit(0);




if( isset( $_GET[ 'id_archivo' ] ) ) {
	$id_archivo = $_GET[ 'id_archivo' ];
	try {
		$dbh->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
		$query = "".
		"SELECT *
		FROM archivo_compra
        WHERE id = '$id_archivo';
		"; // termina query
		$sth = $dbh->prepare( $query );
		$sth->execute();
		$result = $sth->fetch();

		$nombre_archivo = $result['nombre'];
		$datos_archivo = $result['contenido'];
		$tamano_archivo = $result['tamano'];
		$tipo_archivo = $result['tipo'];

		$contenido_archivo = base64_decode( $datos_archivo );

		file_put_contents( $nombre_archivo, $contenido_archivo );

		header( "Content-length: $tamano_archivo" );
		header( "Content-type: $tipo_archivo" );
		header( "Content-Disposition: attachment; filename=$nombre_archivo" );
		print $datos_archivo;
		unlink( $nombre_archivo );
	}
	catch ( Exception $e ) {
		echo "ERROR: " . $e;
	}
}
*/
