<?php

class Conexion{

	private static $_instance = NULL;

	private function __construct(){}

	public static function obtenerInstancia(){
		if( !isset( self::$_instance ) ){
			date_default_timezone_set( 'America/Mexico_City' );
			/*
			$dsn = 'mysql:dbname=usariavi_produccion;host=localhost';
			$user = 'usariavi_usario';
			$password = '?[ow{TFishQQ';

			$dsn = 'mysql:dbname=usariavi_distribucion;host=localhost';
			$user = 'usariavi_usario';
			$password = '?[ow{TFishQQ';
			*/
			$dsn = 'mysql:dbname=test;host=localhost';
			$user = 'fab';
			$password = '141009';
			self::$_instance = new PDO( $dsn, $user, $password );
			self::$_instance->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
		}
		return self::$_instance;
	}

}
