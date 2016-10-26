<?php
require_once( "Conexion.class.php" );
require_once( "Jwt.class.php" );


class Token extends JWT{

	/**
	* Referencia:
	* http://stackoverflow.com/questions/1846202/php-how-to-generate-a-random-unique-alphanumeric-string/13733588#13733588
	*/
	private function crypto_rand_secure( $min, $max ){
		$rango = $max - $min;
		if( $rango < 1 ){
			return $min;
		}
		$log = ceil( log( $rango, 2 ) );
		$bytes = ( int ) ( $log / 8 ) + 1;
		$bits = ( int ) $log + 1;
		$filter = ( int ) ( 1 << $bits ) - 1;
		do{
			$rnd = hexdec( bin2hex( openssl_random_pseudo_bytes( $bytes ) ) );
			$rnd = $rnd & $filter;
		} while( $rnd >= $rango );
		return $min + $rnd;
	}

	private function generarLlave( $longitud ){
		$llave = "";
		$alfabeto = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
		$alfabeto .= "abcdefghijklmnopqrstuvwxyz";
		$alfabeto .= "0123456789";
		$alfabeto .= "!@#$%^&*()_";
		$max = strlen( $alfabeto );
		for( $i = 0; $i < $longitud; $i++ ){
			$llave .= $alfabeto[ self::crypto_rand_secure( 0, $max ) ];
		}
		return $llave;
	}


	public static function crear( $correo ){
		$llave = self::generarLlave( 20 );
		$jsonTokenId = base64_encode( mcrypt_create_iv( 32 ) );
		$issuedAtTime = time();
		$expires = $issuedAtTime + 3600;
		$issuer = gethostbyaddr( $_SERVER[ "REMOTE_ADDR" ] );
		$dbh = Conexion::obtenerInstancia();
		$sth = $dbh->prepare(
			"SELECT *
			FROM usario
			WHERE correo = :correo;"
		);
		$sth->bindParam( ":correo", $correo );
		$sth->execute();
		$usario = ( object ) $sth->fetch();
		$info = array(
			"jti" => $jsonTokenId,
			"iss" => $issuer,
			"iat" => $issuedAtTime,
			"exp" => $expires,
			"data" => [
				"correo" => $usario->correo,
				"designacion" => $usario->designacion,
				"privilegio" => ( int ) $usario->privilegio,
				"nombre" => $usario->nombre,
				"apellido" => $usario->apellido,
				"foto" => $usario->foto,
				"superior" => $usario->superior
			]
		);
		$token = self::encode( $info, $llave, "HS256" );
		$domain = ( $_SERVER['HTTP_HOST'] != 'localhost' ) ? $_SERVER[ 'HTTP_HOST' ] : false;
		setcookie( "usaria_designacion", $usario->designacion, time() + 3600, '/', $domain, false, true  );
		$archivo = __DIR__ . "/../../archivos/$usario->designacion.txt";
		file_put_contents( $archivo, $llave );
		unset( $archivo );
		unset( $llave );
		return $token;
	}

	public static function verificar( $jwt ){
		$usaria_designacion = $_COOKIE[ "usaria_designacion" ];
		$archivo = __DIR__ . "/../../archivos/$usaria_designacion.txt";
		$llave = file_get_contents( $archivo, true );
		unset( $archivo );
		$token = explode( '.', $jwt );
		$validacion = true;
		if( count( $token ) != 3 ){
			$validacion = false;
		}
		list( $headb64, $bodyb64, $cryptob64 ) = $token;
		if( null === ( $header = JWT::jsonDecode( JWT::urlsafeB64Decode( $headb64 ) ) ) ){		
			$validacion = false;
		}
		if( null === $payload = JWT::jsonDecode( JWT::urlsafeB64Decode( $bodyb64 ) ) ){
			$validacion = false;
		}
		$sig = JWT::urlsafeB64Decode( $cryptob64 );
		if( empty( $header->alg ) ){
			$validacion = false;
		}
		if( $sig != JWT::sign( "$headb64.$bodyb64", $llave, $header->alg ) ){
			$validacion = false;
		}
		if( $payload->exp < time() || $payload->iss == null ){
			$validacion = false;
		}
		unset( $llave );
		if( $validacion == true ){
			return true;
		}
		else{
			header( "HTTP/1.0 401 Bad authorization" );
			return false;			
		}
	}


	public static function extraerDatos( $token ){
		try{
			$usaria_designacion = $_COOKIE[ "usaria_designacion" ];
			$archivo = __DIR__ . "/../../archivos/$usaria_designacion.txt";
			$key = file_get_contents( $archivo, true );
			$revelado = JWT::decode( $token, $key, true );
			return $revelado->data;
		}
		catch( Exception $e ){
			header( "HTTP/1.0 401 Bad authorization" );
			throw $e;
		}
	}

	public static function validar( $token ){
		try{
			$revelado = JWT::decode( $token, self::$key, true );
			if( $revelado->exp >= time() && $revelado->iss != null ){
				return true;
			}
			else{
				return false;
			}
		}
		catch( Exception $e ){
			return false;
		}
	}

}
