angular.module( "viajesUsaria" ).controller( "comprasController", comprasController );
comprasController.$inject = [ "Usario", "Solicitud", "Archivo", "$scope", "$state", "$stateParams", "$mdDialog", "$mdToast", "$http" ];
function comprasController( Usario, Solicitud, Archivo, $scope, $state, $stateParams, $mdDialog, $mdToast, $http ) {
	var vm = this;
	var _usario = new Usario();
	var _solicitud = new Solicitud();
	var _archivo = new Archivo();
	vm.titulo = "Compras";
	vm.ID_SOLICITUD = $stateParams.id_solicitud;
	vm.usaria_token = localStorage.getItem( "usaria_token" );

	vm.activar_boton_file = activar_boton_file;
	vm.subir_archivo = subir_archivo;
	vm.descargar_archivo = descargar_archivo;
	vm.enviar_compra = enviar_compra;








	/**
	*	@ngdoc property
	*	@propertyOf viajesUsaria.controller:comprasController
	*	@name viajesUsaria.controller:comprasController#editarArchivos
	*/
	vm.editarArchivos = false;

	/**
	*	@ngdoc property
	*	@property viajesUsaria.controller:comprasController
	*	@name viajesUsaria.controller:comprasController#solicitud
	*/
	vm.solicitud = {};
	/**
	*	@ngdoc property
	*	@property viajesUsaria.controller:comprasController
	*	@name viajesUsaria.controller:comprasController#vuelo
	*/
	vm.vuelo = {};
	/**
	*	@ngdoc property
	*	@property viajesUsaria.controller:comprasController
	*	@name viajesUsaria.controller:comprasController#vueloCopia
	*/
	vm.vueloCopia = {};


	vm.usuario = {};
	vm.archivo = {};
	vm.procesando = false;
	/**
	*	Funciones que interactuan con el modelo de compras
	*/
	vm.traerVuelosSeleccionados = traerVuelosSeleccionados;
	vm.eliminarArchivo = eliminarArchivo;
	/**
	*	Funciones que interactuan con el DOM
	*/



	
	/**
	*	@ngdoc function
	*	@methodOf viajesUsaria.controller:comprasController
	*	@name viajesUsaria.controller:comprasController#editarInfoVuelo
	*/
	vm.editarInfoVuelo = editarInfoVuelo;
	/**
	*	@ngdoc function
	*	@methodOf viajesUsaria.controller:comprasController
	*	@name viajesUsaria.controller:comprasController#cerrarDialogo
	*/
	vm.cerrarDialogo = cerrarDialogo;
	/**
	*	@ngdoc function
	*	@methodOf viajesUsaria.controller:comprasController
	*	@name viajesUsaria.controller:comprasController#guardarCambiosVuelo
	*/
	vm.guardarCambiosVuelo = guardarCambiosVuelo;
	/**
	*	@ngdoc function
	*	@methodOf viajesUsaria.controller:comprasController
	*	@name viajesUsaria.controller:comprasController#notificarCambiosVuelos
	*/
	vm.notificarCambiosVuelos = notificarCambiosVuelos;


	activo();
	function activo(){
		_usario.obtener_sesion().then(
			function( respuesta ){
				vm.usario = respuesta;
				_solicitud.seleccionar_individual( vm.ID_SOLICITUD ).then(
					function( respuesta ){
						vm.solicitud = respuesta;
					},
					function( error ){
						console.log( error );
					}
				);
			},
			function( error ){
				$state.go( "login" );
			}
		);
	}

	function activar_boton_file(){
		if( vm.descripcion_archivo ){
			var target = document.getElementById( "boton-file" );
			angular.element( target )[ 0 ].click();
		}
	}

	function subir_archivo( archivo, errFiles ) {
		vm.f = archivo;
		vm.errFile = errFiles && errFiles[ 0 ];
		if ( archivo ){
			if( archivo.size < 10000000 ){
				vm.archivo = archivo;
				_archivo.subir( archivo, vm.descripcion_archivo, vm.solicitud.id ).then(
					function( respuesta ){
						console.log( respuesta );
						var aux = {
							id: respuesta,
							nombre: vm.archivo.name,
							descripcion: vm.descripcion_archivo,
							tipo: vm.archivo.type,
						};
						vm.solicitud.archivos_compra.push( aux );
						vm.descripcion_archivo = "";
					},
					function( error ){
						console.log( error );
					}
				);
			}
			else {
				$mdDialog.show(
					$mdDialog.alert()
					.clickOutsideToClose( true )
					.title( 'El archivo es muy grande' )
					.textContent( 'El tamaño del archivo debe ser menor a 10 MB.')
					.ok( 'Ok')
				);
			}
		}
		else{
			vm.file = null;
		}
	}

	function descargar_archivo( archivo ){
		var target = document.getElementById( archivo.id );
		angular.element( target )[ 0 ].click();
	}

	function enviar_compra( ev ){
		var confirm = $mdDialog.prompt()
			.title( "Enviar compra" )
			.textContent( "Se enviará un correo al viajero para notificarle que ya puede descargar los archivos de sus pasajes." )
			.placeholder( "Comentarios adicionales" )
			.targetEvent( ev )
			.ok( "Enviar" )
			.cancel( "Cancelar" );
		$mdDialog.show( confirm ).then(
			function( resultado_input ){
				vm.procesando = true;
				var comentarios_adicionales = ( resultado_input ) ? resultado_input : "";
				_solicitud.enviar_compra( vm.ID_SOLICITUD, comentarios_adicionales ).then(
					function( respuesta ){
						vm.procesando = false;
						$state.go( "inicio" );
					},
					function( error ){
						vm.procesando = false;
						console.log( error );
					}
				);
			},
			function(){
				vm.procesando = false;
			}
		);
	}

	

	
	// MARK: editarInfoVuelo
	function editarInfoVuelo( vueloObj, ev ){
		angular.copy( vueloObj, vm.vueloCopia );
		vm.vuelo = vueloObj;
		$mdDialog.show({
			templateUrl: 'states/compras/dialogs/detalleVuelo.dialog.html',
			targetEvent: ev,
			scope: $scope,
			preserveScope: true,
			clickOutsideToClose: true,
			escapeToClose: true,
			focusOnOpen: false
		});
	}

	// MARK: cerrarDialogo
	function cerrarDialogo(){
		if( vm.formaDetalleVuelo.$invalid ){
			angular.copy( vm.vueloCopia, vm.vuelo );
		}
		$mdDialog.cancel();
	}

	// MARK: guardarCambiosVuelo
	function guardarCambiosVuelo(){
		if( vm.formaDetalleVuelo.$valid ){
			COMPRAS.actualizarVuelo( vm.vuelo ).then(
				function( response ){
					if( response.query ){
						$mdDialog.hide();
						$mdToast.show(
							$mdToast.simple()
							.textContent( 'Vuelo actualizado.' )
							.position( 'bottom right' )
							.hideDelay( 3000 )
						);
					}
					else{
						console.log( response );
					}
				},
				function( error ){
					console.log( 'ERROR', error );
				}
			);
		}
	}

	// MARK: notificarCambiosVuelos
	function notificarCambiosVuelos( ev ){
		var confirm = $mdDialog.prompt()
			.title( 'Notificar sobre cambios de datos en vuelos' )
			.textContent( 'Escribe un mensaje para adjuntar al correo donde indiques que cambios hubo.' )
			.targetEvent( ev )
			.ok( 'Enviar' )
			.cancel( 'Cancelar' );
		$mdDialog.show( confirm ).then(
			function( respuesta ){
				COMPRAS.enviarCorreoCambiosDatosVuelos( vm.ID_SOLICITUD, respuesta ).then(
					function( response ){
						if( response.query ){
							$mdToast.show(
								$mdToast.simple()
								.textContent( 'Correo enviado.' )
								.position( 'bottom right' )
								.hideDelay( 3000 )
							);
						}
						else{
							console.log( response );
						}
					},
					function( error ){
						console.log( 'ERROR', error );
					}
				);
			},
			function() {
				// Dialogo cancelado
			}
		);
	}




































































	/**
	*	@nddoc method
	*	@name traerVuelosSeleccionados
	*	@description
	*
	*/
	function traerVuelosSeleccionados(){
		vm.solicitud.vuelos_seleccionados = [];
		angular.forEach( vm.solicitud.vuelos_solicitados, function( vueloSolicitado, $i ){
			COMPRAS.traerInformacionVuelosSeleccionados( vueloSolicitado.id ).then(
				function( response ){
					vm.solicitud.vuelos_seleccionados.push( response );
				},
				function( error ){
					console.log( 'ERROR', error );
				}
			);
		});
	}

	


	

	function eliminarArchivo( archivo ){
		console.log( archivo.id );
		COMPRAS.eliminarArchivo( archivo.id ).then(
			function( response ){
				console.log( response );
				angular.forEach( vm.solicitud.archivos_compra, function( obj, index ){
					if( obj.id === archivo.id ){
						vm.solicitud.archivos_compra.splice( index, 1 );
					}
				});
			},
			function( rejection ){
				console.log( rejection );
			}
		);
	}

	

	/**
	*	@ngdoc function
	*	@methodOf viajesUsaria.controller:comprasController
	*/
	vm.edicionArchivos = false;
	vm.habilitarControlesArchivos = habilitarControlesArchivos;
	function habilitarControlesArchivos(){
		vm.edicionArchivos = !vm.edicionArchivos;
	}

}
