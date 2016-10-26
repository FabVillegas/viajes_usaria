angular.module( 'viajesUsaria' ).factory( '$autocompletado', $autocompletado );
$autocompletado.$inject = [ '$http', '$q' ];
function $autocompletado( $http, $q ) {

	var autocompletado = {
		traerProyectos: traerProyectos,
		traerAeropuertos: traerAeropuertos,
		buscarAeropuerto: buscarAeropuerto,
	};

	return autocompletado;

	function traerProyectos(){
		var deferred = $q.defer();
		$http({
			url: "http://localhost/webapps/viajesUsaria/api/proyectos/" ,
			method: "GET",
			headers: {
				"Authorization": "Bearer " + localStorage.getItem( "usaria_token" )
			}
		})
		.then(
			function( respuesta ){
				console.log( respuesta );
				return deferred.resolve( respuesta.data );
			},
			function( error ){
				return deferred.reject( error );
			}
		);
		return deferred.promise;
	}

	function traerAeropuertos(){
		var aeropuertos = [];
		var deferred = $q.defer();
		$http({
			url: 'assets/aeropuertos.json',
		})
		.then(
			function( response ){
				angular.forEach( response.data, function( aeropuerto, $index ){
					var busqueda = aeropuerto.city;
					busqueda = busqueda.replace( "á", "a" ).replace( "é", "e" ).replace( "í", "i" ).replace( "ó", "o" ).replace( "ú", "u" );
					var aux = {
						value: busqueda.toLowerCase(),
						abreviatura: aeropuerto.iata.toLowerCase(),
						display: ( aeropuerto.city + ', ' + aeropuerto.country + ' (' + aeropuerto.iata + ')') ,
						id: aeropuerto.iata,
					};
					aeropuertos.push( aux );
				});
				return deferred.resolve( aeropuertos );
			},
			function( error ){
				return deferred.reject( error );
			}
		);
		return deferred.promise;
	}

	function createFilterFor( query ) {
		var lowercaseQuery = angular.lowercase( query );
		return function filterFn( element ) {
			//console.log( element.value.indexOf( lowercaseQuery ) );
			var encontrado = false;
			if( element.value.indexOf( lowercaseQuery ) === 0 ){
				encontrado = true;
			}
			else if( element.abreviatura.indexOf( lowercaseQuery ) === 0 ){
				encontrado = true;
			}
			return encontrado;
		};
	}

	function buscarAeropuerto( query, array ) {
		var results = query ? array.filter( createFilterFor( query ) ) : [];
		return results;
	}
}
