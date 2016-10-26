angular.module( "viajesUsaria" ).service( "Archivo", Archivo );
Archivo.$inject = [ "$http", "$q", "Upload" ];
function Archivo( $http, $q, Upload ) {
	
	function Archivo(){
		this.API_PATH = "http://localhost/webapps/viajesUsaria/api/";
	}

	Archivo.prototype = {
        subir: function( archivo, descripcion, id_solicitud ) {
            var deferred = $q.defer();
            archivo.upload = Upload.upload({
                url: this.API_PATH + "solicitudes/" + id_solicitud + "/archivos",
                headers:{
					"Authorization": "Bearer " + localStorage.getItem( "usaria_token" )
				},
                data: {
                    archivo: archivo,
                    descripcion_archivo: descripcion,
                    id_solicitud: id_solicitud
                }
            });
            archivo.upload.then(
                function( respuesta ) {
                    return deferred.resolve( respuesta.data );
                },
                function( error ) {
                if ( error.status > 0 ) {
                    var errorMsg = error.status + ': ' + error.data;
                    return deferred.reject( errorMsg );
                }
                }
            );
            return deferred.promise;
        },
        descargar: function( id_archivo ){
            var deferred = $q.defer();
            $http({
                url: this.API_PATH + "archivos/" + id_archivo,
                method: "GET",
                headers:{
					"Authorization": "Bearer " + localStorage.getItem( "usaria_token" )
				},
            })
            .then(
                function( respuesta ){
                    return deferred.resolve( respuesta.data );
                },
                function( error ){
                    return deferred.reject( error );
                }
            );
            return deferred.promise;
        }
	};

    return Archivo;

}
