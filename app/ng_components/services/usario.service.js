angular.module( "viajesUsaria" ).service( "Usario", Usario );
Usario.$inject = [ "$http", "$q", "$state" ];
function Usario( $http, $q, $state ) {
	
	function Usario(){
	}

	Usario.prototype = {
		obtener_sesion: function(){
			var deferred = $q.defer();
			$http({
				url: "restful_api/sesion_iniciada.script.php",
				method: "GET",
				headers:{
					"Content-Type": "application/json"
				},
				params:{
					"usaria_token": localStorage.getItem( "usaria_token" )
				}
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
		},
	};

    return Usario;

}
