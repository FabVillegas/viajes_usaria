angular.module( "viajesUsaria" ).controller( "seleccionController", seleccionController );
seleccionController.$inject = [ "Usario", "Solicitud", "$state", "$stateParams", '$mdToast', "$mdDialog", '$scope', '$timeout' ];
function seleccionController( Usario, Solicitud, $state, $stateParams, $mdToast, $mdDialog, $scope, $timeout ) {

	var vm = this;
	vm.ID_SOLICITUD = $stateParams.id_solicitud;
	vm.usario = {};
	vm.titulo = "Selección";
	vm.hay_seleccion = false;
	vm.procesando = false;
    vm.vuelos_seleccionados = [];
    vm.hoteles_seleccionados = [];

	var _usario = new Usario();
	var _solicitud = new Solicitud();

	vm.desplegar_contenido = desplegar_contenido;
    vm.verificar_seleccion_completa = verificar_seleccion_completa;

	vm.seleccionar_opciones_vuelos = seleccionar_opciones_vuelos;
	vm.seleccionar_opciones_hoteles = seleccionar_opciones_hoteles;
    vm.enviar_seleccion = enviar_seleccion;

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

	function verificar_seleccion_completa(){
        if( vm.vuelos_seleccionados.length === vm.solicitud.vuelos_solicitados.length ){
            vm.hay_seleccion = true;
        }
        else{
            vm.hay_seleccion = false;
        }
        if( vm.hoteles_seleccionados.length === vm.solicitud.hoteles_solicitados.length ){
            vm.hay_seleccion = true;
        }
        else{
            vm.hay_seleccion = false;
        }
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
                vm.verificar_seleccion_completa();
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
                vm.verificar_seleccion_completa();
			},
			function( error ){
				console.log( "Seleccionar hoteles" );
				console.log( error );
				$state.go( "login" );
			}
		);
	}

	function enviar_seleccion( ev ){
		if( vm.hay_seleccion ){
			var confirm = $mdDialog.prompt()
				.title( "Enviar selección" )
				.textContent( "Se enviará un correo a tu líder para notificarle que ya puede autorizar tu selección." )
				.placeholder( "Comentarios adicionales" )
				.targetEvent( ev )
				.ok( "Enviar" )
				.cancel( "Cancelar" );
			$mdDialog.show( confirm ).then(
				function( resultado_input ){
					vm.procesando = true;
					var comentarios_adicionales = ( resultado_input ) ? resultado_input : "";
                    angular.forEach( vm.vuelos_seleccionados, function( vuelo_sel, indice_vuelo_sel ){
                        _solicitud.insertar_vuelo_seleccionado( vm.ID_SOLICITUD, vuelo_sel ).then(
                            function( respuesta ){
                                console.log( respuesta );
                            },
                            function( error ){
                                console.log( "insertar_vuelo_seleccionado" );
                                console.log( error );
                            }
                        );
                    });
                    angular.forEach( vm.hoteles_seleccionados, function( hotel_sel, indice_hotel_sel ){
                        _solicitud.insertar_hotel_seleccionado( vm.ID_SOLICITUD, hotel_sel ).then(
                            function( respuesta ){
                                console.log( respuesta );
                            },
                            function( error ){
                                console.log( "insertar_hotel_seleccionado" );
                                console.log( error );
                            }
                        );
                    });
					_solicitud.enviar_seleccion( vm.ID_SOLICITUD, comentarios_adicionales ).then(
						function( respuesta ){
                            console.log( respuesta );
							vm.procesando = false;
							$state.go( "inicio" );
							$mdToast.show(
								$mdToast.simple()
								.textContent( "Selección enviada." )
								.position( "bottom right" )
								.hideDelay( 3000 )
							);
						},
						function( error ){
							console.log( "enviar_seleccion" );
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
}
