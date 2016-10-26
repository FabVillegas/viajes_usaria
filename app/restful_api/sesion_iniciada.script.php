<?php
require_once( __DIR__ . "/../../restful_api_src/classes/Token.class.php" );

try{
    if( !isset( $_GET[ "usaria_token" ] ) ){
        throw new Exception( "Token falso" );
    }
    $token = $_GET[ "usaria_token" ];
    $verificacion = Token::verificar( $token );
    if( $verificacion == false ){
        throw new Exception( "Token falso" );
    }
    $informacion = Token::extraerDatos( $token );
    echo json_encode( $informacion );
}
catch( Exception $e ){
    header( "HTTP/1.0 401 Bad authorization" );
    echo $e->getMessage();
}