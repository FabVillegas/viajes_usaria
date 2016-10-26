<?php
require_once( __DIR__ . "/../../restful_api_src/classes/Token.class.php" );
require_once( __DIR__ . "/../../restful_api_src/classes/ProyectoRestHandler.class.php" );
require_once( __DIR__ . "/../../restful_api_src/classes/SolicitudRestHandler.class.php" );
require_once( __DIR__ . "/../../restful_api_src/classes/Archivo.class.php" );

function obtener_token(){
    // para uso con servidores apache
    $headers = apache_request_headers();
    if( isset( $headers[ "Authorization" ] ) ){
        $matches = array();
        preg_match( "/Bearer (.*)/", $headers[ "Authorization" ], $matches );
        if( isset( $matches[ 1 ] ) ){
            $token = $matches[ 1 ];
            return $token;
        }
        else{
            throw new Exception( "Token falso" );
        }
    }
    else{
        throw new Exception( "Token falso" );
    }
}

try{
    $vista;    
    if( isset( $_GET[ 'vista' ] ) ){
        $vista = $_GET[ 'vista' ];
    }
    switch( $vista ){
        case "solicitudes":
            if( $_SERVER[ "REQUEST_METHOD" ] == "POST" ){
                $_POST = json_decode( file_get_contents( 'php://input' ), true );
                $token = obtener_token();
                $verificacion = Token::verificar( $token );
                if( $verificacion == false ){
                    throw new Exception( "Token invalido" );
                }
                $handler = new SolicitudRestHandler( $token );
                echo $handler->insertar_solicitud( $_POST[ "solicitud" ] );
            }
        break;
        case "solicitud_individual":
            $token = obtener_token();
            $verificacion = Token::verificar( $token );
            if( $verificacion == false ){
                throw new Exception( "Token falso" );
            }
            $handler = new SolicitudRestHandler( $token );
            if( $_SERVER[ "REQUEST_METHOD" ] == "GET" ){
                echo $handler->seleccionar_solicitud_individual( $_GET[ "id_solicitud" ] );
            }
            else if( $_SERVER[ "REQUEST_METHOD" ] == "PUT" ){
                $_PUT = json_decode( file_get_contents( 'php://input' ), true );
                echo $handler->cancelar_solicitud_individual( $_GET[ "id_solicitud" ], $_PUT[ "comentarios_adicionales" ] );
            }
        break;
        case "solicitudes_pendientes":
            if( $_SERVER[ "REQUEST_METHOD" ] == "GET" ){
                $token = obtener_token();
                $verificacion = Token::verificar( $token );
                if( $verificacion == false ){
                    throw new Exception( "Token falso" );
                }
                $handler = new SolicitudRestHandler( $token );
                if( isset( $_GET[ "correo_usario" ] ) ){
                    echo $handler->seleccionar_pendientes( $_GET[ "correo_usario" ] );
                }
                else{
                    echo $handler->seleccionar_pendientes();
                }
            }
        break;
        case "solicitudes_por_autorizar":
            if( $_SERVER[ "REQUEST_METHOD" ] == "GET" ){
                $token = obtener_token();
                $verificacion = Token::verificar( $token );
                if( $verificacion == false ){
                    throw new Exception( "Token falso" );
                }
                $handler = new SolicitudRestHandler( $token );
                if( isset( $_GET[ "correo_usario" ] ) ){
                    echo $handler->seleccionar_por_autorizar( $_GET[ "correo_usario" ] );
                }
            }
        break;
        case "solicitudes_autorizadas":
            if( $_SERVER[ "REQUEST_METHOD" ] == "GET" ){
                $token = obtener_token();
                $verificacion = Token::verificar( $token );
                if( $verificacion == false ){
                    throw new Exception( "Token falso" );
                }
                $handler = new SolicitudRestHandler( $token );
                if( isset( $_GET[ "correo_usario" ] ) ){
                    echo $handler->seleccionar_autorizadas( $_GET[ "correo_usario" ] );
                }
            }
        break;
        case "solicitudes_completas":
            if( $_SERVER[ "REQUEST_METHOD" ] == "GET" ){
                $token = obtener_token();
                $verificacion = Token::verificar( $token );
                if( $verificacion == false ){
                    throw new Exception( "Token falso" );
                }
                $handler = new SolicitudRestHandler( $token );
                if( isset( $_GET[ "correo_usario" ] ) ){
                    echo $handler->seleccionar_completas( $_GET[ "correo_usario" ] );
                }
            }
        break;
        /* Cotizacion, seleccion y autorizacion */
        case "vuelos_seleccionados":
             if( $_SERVER[ "REQUEST_METHOD" ] == "POST" ){
                $_POST = json_decode( file_get_contents( 'php://input' ), true );
                $token = obtener_token();
                $verificacion = Token::verificar( $token );
                if( $verificacion == false ){
                    throw new Exception( "Token falso" );
                }
                $handler = new SolicitudRestHandler( $token );
                echo $handler->insertar_vuelo_seleccionado( $_POST[ "vuelo_seleccionado" ] );
            }
        break;
        case "hoteles_seleccionados":
             if( $_SERVER[ "REQUEST_METHOD" ] == "POST" ){
                $_POST = json_decode( file_get_contents( 'php://input' ), true );
                $token = obtener_token();
                $verificacion = Token::verificar( $token );
                if( $verificacion == false ){
                    throw new Exception( "Token falso" );
                }
                $handler = new SolicitudRestHandler( $token );
                echo $handler->insertar_hotel_seleccionado( $_POST[ "hotel_seleccionado" ] );
            }
        break;
        case "cotizacion":
            if( $_SERVER[ "REQUEST_METHOD" ] == "PUT" ){
                $_PUT = json_decode( file_get_contents( 'php://input' ), true );
                $token = obtener_token();
                $verificacion = Token::verificar( $token );
                if( $verificacion == false ){
                    throw new Exception( "Token falso" );
                }
                $handler = new SolicitudRestHandler( $token );
                echo $handler->enviar_cotizacion( $_GET[ "id_solicitud" ], $_PUT[ "comentarios_adicionales" ] );
            }
        break;
        case "seleccion":
            if( $_SERVER[ "REQUEST_METHOD" ] == "PUT" ){
                $_PUT = json_decode( file_get_contents( 'php://input' ), true );
                $token = obtener_token();
                $verificacion = Token::verificar( $token );
                if( $verificacion == false ){
                    throw new Exception( "Token falso" );
                }
                $handler = new SolicitudRestHandler( $token );
                echo $handler->enviar_seleccion( $_GET[ "id_solicitud" ], $_PUT[ "comentarios_adicionales" ] );
            }
        break;
        case "autorizacion":
            if( $_SERVER[ "REQUEST_METHOD" ] == "PUT" ){
                $_PUT = json_decode( file_get_contents( 'php://input' ), true );
                $token = obtener_token();
                $verificacion = Token::verificar( $token );
                if( $verificacion == false ){
                    throw new Exception( "Token falso" );
                }
                $handler = new SolicitudRestHandler( $token );
                echo $handler->enviar_autorizacion( $_GET[ "id_solicitud" ], $_GET[ "autorizador" ], $_PUT[ "comentarios_adicionales" ], $_PUT[ "respuesta" ] );
            }
        break;
        case "compra":
            if( $_SERVER[ "REQUEST_METHOD" ] == "PUT" ){
                $_PUT = json_decode( file_get_contents( 'php://input' ), true );
                $token = obtener_token();
                $verificacion = Token::verificar( $token );
                if( $verificacion == false ){
                    throw new Exception( "Token falso" );
                }
                $handler = new SolicitudRestHandler( $token );
                echo $handler->enviar_compra( $_GET[ "id_solicitud" ], $_PUT[ "comentarios_adicionales" ] );
            }
        break;
        case "opciones_hoteles":
            if( $_SERVER[ "REQUEST_METHOD" ] == "POST" ){
                // insertar opcion de hotel
                $_POST = json_decode( file_get_contents( 'php://input' ), true );
                $token = obtener_token();
                $verificacion = Token::verificar( $token );
                if( $verificacion == false ){
                    throw new Exception( "Token falso" );
                }
                $handler = new SolicitudRestHandler( $token );
                echo $handler->insertar_opcion_hotel( $_POST[ "opcion_hotel" ] );
            }
            else if( $_SERVER[ "REQUEST_METHOD" ] == "GET" ){
                // seleccionar las opciones de hotel
                $token = obtener_token();
                $verificacion = Token::verificar( $token );
                if( $verificacion == false ){
                    throw new Exception( "Token falso" );
                }
                $handler = new SolicitudRestHandler( $token );
                echo $handler->seleccionar_opciones_hotel( $_GET[ "id_solicitud" ] );
            }
        break;
        case "opciones_vuelos":
            if( $_SERVER[ "REQUEST_METHOD" ] == "POST" ){
                // insertar opcion de vuelo
                $_POST = json_decode( file_get_contents( 'php://input' ), true );
                $token = obtener_token();
                $verificacion = Token::verificar( $token );
                if( $verificacion == false ){
                    throw new Exception( "Token falso" );
                }
                $handler = new SolicitudRestHandler( $token );
                echo $handler->insertar_opcion_vuelo( $_POST[ "opcion_vuelo" ] );
            }
            else if( $_SERVER[ "REQUEST_METHOD" ] == "GET" ){
                // seleccionar las opciones de vuelos
                $token = obtener_token();
                $verificacion = Token::verificar( $token );
                if( $verificacion == false ){
                    throw new Exception( "Token falso" );
                }
                $handler = new SolicitudRestHandler( $token );
                echo $handler->seleccionar_opciones_vuelo( $_GET[ "id_solicitud" ] );
            }
        break;
        case "opcion_vuelo_especifica":
            if( $_SERVER[ "REQUEST_METHOD" ] == "DELETE" ){
                $token = obtener_token();
                $verificacion = Token::verificar( $token );
                if( $verificacion == false ){
                    throw new Exception( "Token falso" );
                }
                $handler = new SolicitudRestHandler( $token );
                echo $handler->eliminar_opcion_vuelo( $_GET[ "id_opcion_vuelo" ] );
            }
            else if( $_SERVER[ "REQUEST_METHOD" ] == "PUT" ){
                $token = obtener_token();
                $verificacion = Token::verificar( $token );
                if( $verificacion == false ){
                    throw new Exception( "Token falso" );
                }
                $_PUT = json_decode( file_get_contents( 'php://input' ), true );
                $handler = new SolicitudRestHandler( $token );
                echo $handler->actualizar_opcion_vuelo( $_PUT[ "opcion_vuelo" ] );
            }
        break;
        case "opcion_hotel_especifica":
            if( $_SERVER[ "REQUEST_METHOD" ] == "DELETE" ){
                $token = obtener_token();
                $verificacion = Token::verificar( $token );
                if( $verificacion == false ){
                    throw new Exception( "Token falso" );
                }
                $handler = new SolicitudRestHandler( $token );
                echo $handler->eliminar_opcion_hotel( $_GET[ "id_opcion_hotel" ] );
            }
        break;
        case "archivos_solicitud":
            $token = obtener_token();
            $verificacion = Token::verificar( $token );
            if( $verificacion == false ){
                throw new Exception( "Token falso" );
            }
            $handler = new SolicitudRestHandler( $token );
            if( $_SERVER[ "REQUEST_METHOD" ] == "POST" ){
                $data = Archivo::insertar_compra( $_POST[ "id_solicitud" ], $_POST[ "descripcion_archivo" ] );
                echo json_encode( $data );
            }
            else if( $_SERVER[ "REQUEST_METHOD" ] == "GET" ){
                Archivo::descargar_compra( $_GET[ "id_archivo" ] );
            }
        break;
        case "proyectos":
            if( $_SERVER[ "REQUEST_METHOD" ] === "GET" ){
                $token = obtener_token();
                $verificacion = Token::verificar( $token );
                if( $verificacion == false ){
                    throw new Exception( "Token falso" );
                }
                $handler = new ProyectoRestHandler( $token );
                echo $handler->seleccionar_proyectos(); 
            }
        break;
    }
}
catch( Exception $e ){
    echo $e->getMessage();
}
