/**
*	@ngdoc service
*	@name viajesUsaria.service:solicitudModel
*/
angular.module( 'viajesUsaria' ).service( 'solicitudModel', solicitudModel );
solicitudModel.$inject = [ '$q', '$http' ];
function solicitudModel( $q, $http ) {
	/**
	*	@ngdoc property
	*	@propertyOf viajesUsaria.service:solicitudModel
	*	@name viajesUsaria.service.solicitudModel#self
	*/
	var self = this;

	function solicitudModel(){
		self.PATH = './resources/models/solicitud/';
		this.PATH = 'resources/models/solicitud/';
	}

	solicitudModel.prototype = {
		/**
		*	@ngdoc method
		*	@methodOf viajesUsaria.service:solicitudModel
		*	@name viajesUsaria.service:solicitudModel#traerProyectosActivos
		*/
		traerProyectosActivos: function(){
			var deferred = $q.defer();
			$http({
				url: this.PATH + 'select.proyectosActivos.script.php',
			})
			.then(
				function( respuesta ){
					if( respuesta.data.token_validado === true ){
						return deferred.resolve( respuesta.data.datos );
					}
					else{
						return deferred.reject( respuesta.data );
					}
				},
				function( error ){
					return deferred.reject( error );
				}
			);
			return deferred.promise;
		},
		/**
		*	@ngdoc method
		*	@methodOf viajesUsaria.service:solicitudModel
		*	@name viajesUsaria.service:solicitudModel#traerInformacion
		*/
		traerInformacion: function( id_solicitud ){
			var deferred = $q.defer();
			$http({
				url: self.PATH + 'select.solicitud.script.php',
				method: 'GET',
				params:{
					id_solicitud: id_solicitud
				}
			})
			.then(
				function( respuesta ){
					if( respuesta.data.token_validado === false ){
						return deferred.reject( respuesta.data );
					}
					else{
						var solicitud = respuesta.data.solicitud;
						solicitud.fecha_inicio = new Date( solicitud.fecha_inicio );
						solicitud.fecha_fin = new Date( solicitud.fecha_fin );
						angular.forEach( solicitud.vuelos_solicitados, function( vueloSolicitado, index ){
							vueloSolicitado.fecha = new Date( vueloSolicitado.fecha );
						});
						angular.forEach( solicitud.vuelos_seleccionados, function( vueloSeleccionado, index ){
							vueloSeleccionado.fecha_salida = new Date( vueloSeleccionado.fecha_salida );
						});
						return deferred.resolve( solicitud );
					}
				},
				function( error ){
					return deferred.reject( error );
				}
			);
			return deferred.promise;
		},
		/**
		*	@ngdoc method
		*	@methodOf viajesUsaria.service:solicitudModel
		*	@name viajesUsaria.service:solicitudModel#enviarInformacion
		*/
		enviarInformacion: function( solicitudObj, comentarios_adicionales ){
			var script;
			if( solicitudObj.id ){
				script = 'update.solicitud.script.php';
			}
			else {
				script = 'insert.solicitud.script.php';
			}
			var deferred = $q.defer();
			$http({
				url: this.PATH + script,
				method: 'POST',
				data: {
					solicitud: solicitudObj,
					comentarios_adicionales: comentarios_adicionales
				}
			})
			.then(
				function( respuesta ){
					if( respuesta.data.token_validado === false ){
						return deferred.reject( respuesta.data );
					}
					else{
						return deferred.resolve( respuesta.data );
					}
				},
				function( error ){
					return deferred.reject( error );
				}
			);
			return deferred.promise;
		},
	};

	return solicitudModel;

}
