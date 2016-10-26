angular.module( 'viajesUsaria' ).service( 'Solicitud', Solicitud );
Solicitud.$inject = [ '$http', '$q', '$state' ];
function Solicitud( $http, $q, $state ) {
	
	function Solicitud(){
		this.API_PATH = "http://localhost/webapps/viajesUsaria/api/";
	}

	function crear_fechas_javascript( solicitudes ){
		angular.forEach( solicitudes, function( solicitud, indice_solicitud ){
			solicitud.fecha_creacion = new Date( solicitud.fecha_creacion );
			solicitud.fecha_inicio = new Date( solicitud.fecha_inicio );
			solicitud.fecha_fin = new Date( solicitud.fecha_fin );
			solicitud.fecha_seleccion = ( solicitud.fecha_seleccion ) ? new Date( solicitud.fecha_seleccion ) : null;
			solicitud.fecha_cotizacion = ( solicitud.fecha_cotizacion ) ? new Date( solicitud.fecha_cotizacion ) : null;
			angular.forEach( solicitud.vuelos_seleccionados, function( vuelo_sel, indice_vuelo_sel ){
				vuelo_sel.fecha_salida = new Date( vuelo_sel.fecha_salida );
				vuelo_sel.fecha_llegada = new Date( vuelo_sel.fecha_llegada );
			});
			angular.forEach( solicitud.vuelos_solicitados, function( vuelo_sol, indice_vuelo_sol ){
				vuelo_sol.fecha = new Date( vuelo_sol.fecha );
			});
			angular.forEach( solicitud.autorizaciones, function( autorizacion, indice_autorizacion ){
				if( autorizacion.fecha_autorizacion !==  null ){
					autorizacion.fecha_autorizacion = new Date( autorizacion.fecha_autorizacion );
				}
			});
		});
		return solicitudes;
	} 

	Solicitud.prototype = {
		insertar_nueva: function( solicitud_obj ){
			var deferred = $q.defer();
			$http({
				url: this.API_PATH + "solicitudes/",
				method: "POST",
				headers:{
					"Accept": "application/json",
					"Authorization": "Bearer " + localStorage.getItem( "usaria_token" )
				},
				data:{
					solicitud: solicitud_obj
				}
			})
			.then(
				function( respuesta ){
					return deferred.resolve( respuesta );
				},
				function( error ){
					return deferred.reject( error );
				}
			);
			return deferred.promise;
		},
		seleccionar_individual: function( id_solicitud ){
			var deferred = $q.defer();
			$http({
				url: this.API_PATH + "solicitudes/" + id_solicitud,
				method: "GET",
				headers:{
					"Accept": "application/json",
					"Authorization": "Bearer " + localStorage.getItem( "usaria_token" )
				}
			})
			.then(
				function( respuesta ){
					var arreglo = [ respuesta.data ];
					arreglo = crear_fechas_javascript( arreglo );
					respuesta.data = arreglo[ 0 ];
					return deferred.resolve( respuesta.data );
				},
				function( error ){
					return deferred.reject( error );
				}
			);
			return deferred.promise;
		},
		cancelar: function( id_solicitud, comentarios_adicionales ){
			var deferred = $q.defer();
			$http({
				url: this.API_PATH + "solicitudes/" + id_solicitud,
				method: "PUT",
				headers:{
					"Authorization": "Bearer " + localStorage.getItem( "usaria_token" )
				},
				data:{
					comentarios_adicionales: comentarios_adicionales
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
		seleccionar_equipo_usaria: function(){
			var deferred = $q.defer();
			$http({
				url: this.API_PATH + "solicitudes/pendientes/",
				method: "GET",
				headers:{
					"Accept": "application/json",
					"Authorization": "Bearer " + localStorage.getItem( "usaria_token" )
				}
			})
			.then(
				function( respuesta ){
					respuesta.data = crear_fechas_javascript( respuesta.data );
					return deferred.resolve( respuesta.data );
				},
				function( error ){
					return deferred.reject( error );
				}
			);
			return deferred.promise;
		},
		seleccionar_pendientes: function( correo_usario = null ){
			var deferred = $q.defer();
			$http({
				url: this.API_PATH + "solicitudes/pendientes/" + correo_usario,
				method: "GET",
				headers:{
					"Accept": "application/json",
					"Authorization": "Bearer " + localStorage.getItem( "usaria_token" )
				}
			})
			.then(
				function( respuesta ){
					respuesta.data = crear_fechas_javascript( respuesta.data );
					return deferred.resolve( respuesta.data );
				},
				function( error ){
					return deferred.reject( error );
				}
			);
			return deferred.promise;
		},
		seleccionar_por_autorizar: function( correo_usario = null ){
			var deferred = $q.defer();
			$http({
				url: this.API_PATH + "solicitudes/por_autorizar/" + correo_usario,
				method: "GET",
				headers:{
					"Accept": "application/json",
					"Authorization": "Bearer " + localStorage.getItem( "usaria_token" )
				}
			})
			.then(
				function( respuesta ){
					respuesta.data = crear_fechas_javascript( respuesta.data );
					return deferred.resolve( respuesta.data );
				},
				function( error ){
					return deferred.reject( error );
				}
			);
			return deferred.promise;
		},
		seleccionar_autorizadas: function( correo_usario = null ){
			var deferred = $q.defer();
			$http({
				url: this.API_PATH + "solicitudes/autorizadas/" + correo_usario,
				method: "GET",
				headers:{
					"Accept": "application/json",
					"Authorization": "Bearer " + localStorage.getItem( "usaria_token" )
				}
			})
			.then(
				function( respuesta ){
					respuesta.data = crear_fechas_javascript( respuesta.data );
					return deferred.resolve( respuesta.data );
				},
				function( error ){
					return deferred.reject( error );
				}
			);
			return deferred.promise;
		},
		seleccionar_completas: function( correo_usario = null){
			var deferred = $q.defer();
			$http({
				url: this.API_PATH + "solicitudes/completas/" + correo_usario,
				method: "GET",
				headers:{
					"Accept": "application/json",
					"Authorization": "Bearer " + localStorage.getItem( "usaria_token" )
				}
			})
			.then(
				function( respuesta ){
					respuesta.data = crear_fechas_javascript( respuesta.data );
					return deferred.resolve( respuesta.data );
				},
				function( error ){
					return deferred.reject( error );
				}
			);
			return deferred.promise;
		},
		insertar_opcion_hotel: function( id_solicitud, opcion_hotel ){
			var deferred = $q.defer();
			$http({
				url: this.API_PATH + "solicitudes/" + id_solicitud + "/opciones_hoteles",
				method: "POST",
				headers: {
					"Accept": "application/json",
					"Authorization": "Bearer " + localStorage.getItem( "usaria_token" )
				},
				data: {
					id_solicitud: id_solicitud,
					opcion_hotel: opcion_hotel
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
		eliminar_opcion_hotel: function( id_solicitud, id_opcion_hotel ){
			var deferred = $q.defer();
			$http({
				url: this.API_PATH + "solicitudes/" + id_solicitud + "/opciones_hoteles/" + id_opcion_hotel,
				method: "DELETE",
				headers: {
					"Accept": "application/json",
					"Authorization": "Bearer " + localStorage.getItem( "usaria_token" )
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
		seleccionar_opciones_hoteles: function( id_solicitud ){
			var deferred = $q.defer();
			$http({
				url: this.API_PATH + "solicitudes/" + id_solicitud + "/opciones_hoteles",
				method: "GET",
				headers:{
					"Accept": "application/json",
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
		},
		insertar_opcion_vuelo: function( id_solicitud, opcion_vuelo ){
			var deferred = $q.defer();
			$http({
				url: this.API_PATH + "solicitudes/" + id_solicitud + "/opciones_vuelos",
				method: "POST",
				headers: {
					"Accept": "application/json",
					"Authorization": "Bearer " + localStorage.getItem( "usaria_token" )
				},
				data: {
					id_solicitud: id_solicitud,
					opcion_vuelo: opcion_vuelo
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
		seleccionar_opciones_vuelos: function( id_solicitud ){
			var deferred = $q.defer();
			$http({
				url: this.API_PATH + "solicitudes/" + id_solicitud + "/opciones_vuelos",
				method: "GET",
				headers:{
					"Accept": "application/json",
					"Authorization": "Bearer " + localStorage.getItem( "usaria_token" )
				},
			})
			.then(
				function( respuesta ){
					angular.forEach( respuesta.data, function( vuelo_solicitado, index ){
						angular.forEach( vuelo_solicitado, function( opcion_vuelo, index_2 ){
							opcion_vuelo.fecha_salida = new Date( opcion_vuelo.fecha_salida );
							opcion_vuelo.fecha_llegada = new Date( opcion_vuelo.fecha_llegada );
						});
					});
					return deferred.resolve( respuesta.data );
				},
				function( error ){
					return deferred.reject( error );
				}
			);
			return deferred.promise;
		},
		eliminar_opcion_vuelo: function( id_solicitud, id_opcion_vuelo ){
			var deferred = $q.defer();
			$http({
				url: this.API_PATH + "solicitudes/" + id_solicitud + "/opciones_vuelos/" + id_opcion_vuelo,
				method: "DELETE",
				headers: {
					"Accept": "application/json",
					"Authorization": "Bearer " + localStorage.getItem( "usaria_token" )
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
		actualizar_opcion_vuelo: function( id_solicitud, opcion_vuelo ){
			var deferred = $q.defer();
			$http({
				url: this.API_PATH + "solicitudes/" + id_solicitud + "/opciones_vuelos/" + opcion_vuelo.id,
				method: "PUT",
				headers: {
					"Accept": "application/json",
					"Authorization": "Bearer " + localStorage.getItem( "usaria_token" )
				},
				data: {
					opcion_vuelo: opcion_vuelo
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
		insertar_vuelo_seleccionado: function( id_solicitud, vuelo_seleccionado ){
			var deferred = $q.defer();
			$http({
				url: this.API_PATH + "solicitudes/" + id_solicitud + "/vuelos_seleccionados",
				method: "POST",
				headers:{
					"Accept": "application/json",
					"Authorization": "Bearer " + localStorage.getItem( "usaria_token" )
				},
				data: {
					vuelo_seleccionado: vuelo_seleccionado
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
		insertar_hotel_seleccionado: function( id_solicitud, hotel_seleccionado ){
			var deferred = $q.defer();
			$http({
				url: this.API_PATH + "solicitudes/" + id_solicitud + "/hoteles_seleccionados",
				method: "POST",
				headers:{
					"Accept": "application/json",
					"Authorization": "Bearer " + localStorage.getItem( "usaria_token" )
				},
				data: {
					hotel_seleccionado: hotel_seleccionado
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
		enviar_cotizacion: function( id_solicitud, comentarios_adicionales ){
			var deferred = $q.defer();
			$http({
				url: this.API_PATH + "solicitudes/" + id_solicitud + "/cotizacion/",
				method: "PUT",
				headers: {
					"Accept": "application/json",
					"Authorization": "Bearer " + localStorage.getItem( "usaria_token" )
				},
				data: {
					comentarios_adicionales: comentarios_adicionales
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
		enviar_seleccion: function( id_solicitud, comentarios_adicionales ){
			var deferred = $q.defer();
			$http({
				url: this.API_PATH + "solicitudes/" + id_solicitud + "/seleccion/",
				method: "PUT",
				headers: {
					"Accept": "application/json",
					"Authorization": "Bearer " + localStorage.getItem( "usaria_token" )
				},
				data: {
					comentarios_adicionales: comentarios_adicionales
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
		enviar_autorizacion: function( id_solicitud, usario, comentarios_adicionales, respuesta ){
			var deferred = $q.defer();
			$http({
				url: this.API_PATH + "solicitudes/" + id_solicitud + "/autorizacion/" + usario,
				method: "PUT",
				headers: {
					"Accept": "application/json",
					"Authorization": "Bearer " + localStorage.getItem( "usaria_token" )
				},
				data: {
					comentarios_adicionales: comentarios_adicionales,
					respuesta: respuesta
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
		enviar_compra: function( id_solicitud, comentarios_adicionales ){
			var deferred = $q.defer();
			$http({
				url: this.API_PATH + "solicitudes/" + id_solicitud + "/compra/",
				method: "PUT",
				headers: {
					"Accept": "application/json",
					"Authorization": "Bearer " + localStorage.getItem( "usaria_token" )
				},
				data: {
					comentarios_adicionales: comentarios_adicionales,
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

    return Solicitud;

}
