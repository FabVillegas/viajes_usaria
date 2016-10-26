<?php
require_once( __DIR__ . "/../../restful_api_src/classes/Conexion.class.php" );
require_once( __DIR__ . "/../../restful_api_src/classes/Token.class.php" );

try{
    $_POST = json_decode( file_get_contents( 'php://input' ), true );
    $token = Token::crear( $_POST[ 'correo' ] );
	echo $token;
}
catch( Exception $e ){
	$resultado = array( 'error' => $e );
	echo json_encode( $resultado );
}
