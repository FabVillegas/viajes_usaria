/**
*	@ngdoc service
*	@name viajesUsaria.service:comprasModel
*	@return {object} comprasModel Instancia del modelo
*/
angular.module( 'viajesUsaria' ).service( 'comprasModel', comprasModel );
comprasModel.$inject = [ '$http', '$q', 'Upload' ];
function comprasModel( $http, $q, Upload ) {

	var self = this;

	function comprasModel(){
		self.PATH = './resources/models/compras/';
		this.PATH = 'resources/models/compras/';
	}

	comprasModel.prototype = {
		/**
		*	@ngdoc method
		*	@methodOf viajesUsaria.service:comprasModel
		*	@name viajesUsaria.service:comprasModel#actualizarVuelo
		*/
		// MARK: actualizarVuelo
		actualizarVuelo: function( vueloObj ){
			var deferred = $q.defer();
			$http({
				url: this.PATH + 'update.vueloSeleccionado.script.php',
				method: 'POST',
				data: {
					vuelo: vueloObj
				}
			})
			.then(
				function( response ){
					return deferred.resolve( response.data );
				},
				function( error ){
					return deferred.reject( error );
				}
			);
			return deferred.promise;
		},
		/**
		*	@ngdoc method
		*	@methodOf viajesUsaria.service:comprasModel
		*	@name viajesUsaria.service:comprasModel#enviarCorreoCambiosDatosVuelos
		*/
		enviarCorreoCambiosDatosVuelos: function( id_solicitud, mensaje ){
			var deferred = $q.defer();
			$http({
				url: this.PATH + 'email.cambiosVuelosSeleccionados.script.php',
				method: 'POST',
				data:{
					id_solicitud: id_solicitud,
					mensaje: mensaje
				}
			})
			.then(
				function( response ){
					return deferred.resolve( response.data );
				},
				function( error ){
					return deferred.reject( error );
				}
			);
			return deferred.promise;
		},















		/**
		*	@nddoc method
		*	@name traerInformacionVuelosSeleccionados
		*	@description
		*	Consulta y obtiene un arreglo con la informacion de los vuelos que fueron seleccionados para la compra
		*
		*	@param {string} id del vuelo solicitado
		*
		*	@return {object} Promesa resuelta, contiene el arreglo con los objetos vuelos seleccionados
		*/
		traerInformacionVuelosSeleccionados: function( idVueloSolicitado ){
			var deferred = $q.defer();
			$http({
				url: self.PATH + 'select.informacionVuelosSeleccionados.script.php',
				method: 'GET',
				params: {
					id_vuelo_solicitado: idVueloSolicitado
				}
			})
			.then(
				function( response ){
					return deferred.resolve( response.data );
				},
				function( error ){
					return deferred.reject( error );
				}
			);
			return deferred.promise;
		},
		subirArchivo: function( archivo, descripcion, idSolicitud ) {
			var deferred = $q.defer();
			archivo.upload = Upload.upload({
			  url: self.PATH + 'insert.archivoCompra.script.php',
			  data: {
				archivo: archivo,
				descripcion_archivo: descripcion,
				id_solicitud: idSolicitud
			  }
			});
			archivo.upload.then(
			  function( response ) {
				return deferred.resolve( response.data );
			  },
			  function( response ) {
				if ( response.status > 0 ) {
				  var errorMsg = response.status + ': ' + response.data;
				  return deferred.reject( errorMsg );
				}
			  }
			);
			return deferred.promise;
		},
		eliminarArchivo: function( id ){
			var deferred = $q.defer();
			$http({
				url: self.PATH + 'delete.archivoCompra.script.php',
				method: 'GET',
				params: {
					id_archivo: id
				}
			})
			.success( function( response ){
				return deferred.resolve( response );
			})
			.error( function( response ){
				return deferred.reject( response );
			});
			return deferred.promise;
		},
		/**
		*	@ngdoc method
		*	@methodOf viajesUsaria.service:comprasModel
		*/
		registrarCompra: function( id_solicitud ){
			var deferred = $q.defer();
			$http({
				url: self.PATH + 'update.fechaCompra.script.php',
				method: 'POST',
				data: {
					id_solicitud: id_solicitud
				}
			})
			.then(
				function( response ){
					console.log( response.data );
					return deferred.resolve( response.data );
				},
				function( error ){
					return deferred.reject( error );
				}
			);
			return deferred.promise;
		}
	};

	return comprasModel;
}
