<?php
require_once( __DIR__ . "/Conexion.class.php" );

class Proyecto{

    public function get_todos(){
        try{
            $dbh = Conexion::obtenerInstancia();
            $sth = $dbh->prepare(
                "SELECT *
                FROM catalogo_proyecto
                WHERE activo = 1
                ORDER BY nombre ASC;"
            );
            $sth->execute();
            $proyectos = $sth->fetchAll();
            foreach( $proyectos as &$proyecto ){
                $proyecto = ( object ) $proyecto;
                $proyecto->id = ( int ) $proyecto->id;
            }
            return $proyectos;
        }
        catch( Exception $e ){
            return array( "error" => $e );
        }
    }
    
}