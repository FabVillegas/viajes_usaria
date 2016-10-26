angular.module( "viajesUsaria" ).controller( "autorizacionController", autorizacionController );
autorizacionController.$inject = [ "Usario", "Solicitud", "$state", "$stateParams", '$mdToast', "$mdDialog", '$scope', '$timeout' ];
function autorizacionController( Usario, Solicitud, $state, $stateParams, $mdToast, $mdDialog, $scope, $timeout ) {

	var vm = this;
	vm.ID_SOLICITUD = $stateParams.id_solicitud;
	vm.usario = {};
	vm.titulo = "Autorización";
	vm.hay_seleccion = false;
	vm.procesando = false;
    vm.vuelos_seleccionados = [];
    vm.hoteles_seleccionados = [];

	var _usario = new Usario();
	var _solicitud = new Solicitud();

	vm.desplegar_contenido = desplegar_contenido;

	vm.seleccionar_opciones_vuelos = seleccionar_opciones_vuelos;
	vm.seleccionar_opciones_hoteles = seleccionar_opciones_hoteles;
    vm.enviar_autorizacion = enviar_autorizacion;

	activo();
	function activo(){
		_usario.obtener_sesion().then(
			function( respuesta ){
				vm.usario = respuesta;
				_solicitud.seleccionar_individual( vm.ID_SOLICITUD ).then(
					function( respuesta ){
						vm.solicitud = respuesta;
						angular.forEach( vm.solicitud.hoteles_solicitados, function( hotel, index ){
							hotel.mostrar = false;
						});
						angular.forEach( vm.solicitud.vuelos_solicitados, function( vuelo, index ){
							vuelo.mostrar = false;
						});
						vm.seleccionar_opciones_hoteles();
						vm.seleccionar_opciones_vuelos();
						vm.vista_lista = true;
					},
					function( error ){
						console.log( "CotizacionController" );
						console.log( error );
						$state.go( "login" );
					}
				);
			},
			function( error ){
				console.log( "CotizacionController" );
				console.log( error );
				$state.go( "login" );
			}
		);
	}

	function desplegar_contenido( elemento ){
		elemento.mostrar = !elemento.mostrar;
	}

	function seleccionar_opciones_vuelos(){
		_solicitud.seleccionar_opciones_vuelos( vm.ID_SOLICITUD ).then(
			function( respuesta ){
				angular.forEach( vm.solicitud.vuelos_solicitados, function( vuelo, indice ){
					vuelo.opciones = [];
					if( vuelo.id in respuesta ){
						vuelo.opciones = respuesta[ vuelo.id ];
					}
                    angular.forEach( vuelo.opciones, function( opcion, indice_opcion ){
                        angular.forEach( vm.solicitud.vuelos_seleccionados, function( vuelo_sel, indice_vuelo_sel ){
                            if( opcion.id === vuelo_sel.id ){
                                vm.vuelos_seleccionados[ indice ] = opcion;
                            }
                        });
                    });
				});
			},
			function( error ){
				console.log( "Seleccionar vuelos" );
				console.log( error );
			}
		);
	}

	function seleccionar_opciones_hoteles(){
		_solicitud.seleccionar_opciones_hoteles( vm.ID_SOLICITUD ).then(
			function( respuesta ){
				angular.forEach( vm.solicitud.hoteles_solicitados, function( hotel, indice ){
					hotel.opciones = [];
					if( hotel.id in respuesta ){
						hotel.opciones = respuesta[ hotel.id ];
					}
                    angular.forEach( hotel.opciones, function( opcion, indice_opcion ){
                        angular.forEach( vm.solicitud.hoteles_seleccionados, function( hotel_sel, indice_hotel_sel ){
                            if( opcion.id === hotel_sel.id ){
                                vm.hoteles_seleccionados[ indice ] = opcion;
                            }
                        });
                    });
				});
			},
			function( error ){
				console.log( "Seleccionar hoteles" );
				console.log( error );
				$state.go( "login" );
			}
		);
	}

	function enviar_autorizacion( respuesta, ev ){
		var confirm;
		if( respuesta === true ){
			confirm = $mdDialog.prompt()
				.title( "Enviar autorización" )
				.textContent( "Se enviará un correo al administrador para notificarle que ya puede realizar la compra." )
				.placeholder( "Comentarios adicionales" )
				.targetEvent( ev )
				.ok( "Enviar" )
				.cancel( "Cancelar" );
		}
		else if( respuesta === false ){
			confirm = $mdDialog.prompt()
				.title( "Enviar rechazo" )
				.textContent( "Se enviará un correo al administrador y al viajero para notificarle que se deben realizar ajustes a la solicitud." )
				.placeholder( "Comentarios adicionales" )
				.targetEvent( ev )
				.ok( "Enviar" )
				.cancel( "Cancelar" );
		}
		$mdDialog.show( confirm ).then(
			function( resultado_input ){
				vm.procesando = true;
				var comentarios_adicionales = ( resultado_input ) ? resultado_input : "";
				_solicitud.enviar_autorizacion( vm.ID_SOLICITUD, vm.usario.correo, comentarios_adicionales, respuesta ).then(
					function( respuesta ){
						vm.procesando = false;
						$state.go( "inicio" );
						$mdToast.show(
							$mdToast.simple()
							.textContent( "Respuesta enviada." )
							.position( "bottom right" )
							.hideDelay( 3000 )
						);
					},
					function( error ){
						console.log( "enviar_autorizacion" );
						console.log( error );
						vm.procesando = false;
						if( error.status === 401 ){
							$state.go( "login" );
						}
					}
				);
			},
			function(){
				vm.procesando = false;
			}
		);
	}
}
