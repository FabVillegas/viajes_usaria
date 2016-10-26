angular.module( 'viajesUsaria' ).controller( 'cotizacionController', cotizacionController );
cotizacionController.$inject = [ "Usario", "Solicitud", 'solicitudModel', "$state", "$stateParams", '$mdToast', "$mdDialog", '$scope', '$timeout' ];
function cotizacionController( Usario, Solicitud, solicitudModel, $state, $stateParams, $mdToast, $mdDialog, $scope, $timeout ) {

	var vm = this;
	vm.ID_SOLICITUD = $stateParams.id_solicitud;
	vm.usario = {};
	vm.titulo = "Cotización";
	vm.hay_cotizacion = false;
	vm.procesando = false;

	var _usario = new Usario();
	var _solicitud = new Solicitud();

	vm.desplegar_contenido = desplegar_contenido;
	vm.copiar_opcion_vuelo = copiar_opcion_vuelo;
	vm.verificar_cotizacion_completa = verificar_cotizacion_completa;

	vm.seleccionar_opciones_vuelos = seleccionar_opciones_vuelos;
	vm.seleccionar_opciones_hoteles = seleccionar_opciones_hoteles;
	vm.insertar_opcion_vuelo = insertar_opcion_vuelo;
	vm.insertar_opcion_hotel = insertar_opcion_hotel;
	vm.eliminar_opcion_vuelo = eliminar_opcion_vuelo;
	vm.eliminar_opcion_hotel = eliminar_opcion_hotel;
	vm.actualizar_opcion_vuelo = actualizar_opcion_vuelo;
	vm.enviar_cotizacion = enviar_cotizacion;

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

	function desplegar_contenido( hotel ){
		hotel.mostrar = !hotel.mostrar;
	}

	function copiar_opcion_vuelo( indice_vuelo_solicitado, opcion_vuelo ){
		var aux = angular.copy( opcion_vuelo );
		vm.opcion_vuelo[ indice_vuelo_solicitado ] = aux;
	}

	function verificar_cotizacion_completa( arreglo_solicitudes ){
		var tiene_opciones = 0;
		angular.forEach( arreglo_solicitudes, function( elemento, indice ){
			if( elemento.opciones.length > 0 ){
				tiene_opciones++;
			}
		});
		if( tiene_opciones === arreglo_solicitudes.length ){
			return true;
		}
		else{
			return false;
		}
	}

	function insertar_opcion_hotel( indice, ev ){
		if( vm.forma_opcion_hotel[ indice ].$valid ){
			vm.opcion_hotel[ indice ].id_hotel_solicitado = vm.solicitud.hoteles_solicitados[ indice ].id;
			_solicitud.insertar_opcion_hotel( vm.ID_SOLICITUD, vm.opcion_hotel[ indice ] ).then(
				function( respuesta ){
					vm.opcion_hotel[ indice ] = {};
					vm.seleccionar_opciones_hoteles();
				},
				function( error ){
					console.log( "insertar_opcion_hotel" );
					console.log( error );
				}
			);
		}
		else{
			$mdDialog.show(
				$mdDialog.alert()
				.parent( angular.element( document.querySelector( '#popupContainer' ) ) )
				.clickOutsideToClose( true )
				.title( "Forma inválida" )
				.textContent( "Los campos se encuentran imcompletos." )
				.ok( "Cerrar" )
				.targetEvent( ev )
			);
		}
	}

	function eliminar_opcion_hotel( opcion_hotel ){
		_solicitud.eliminar_opcion_hotel( vm.ID_SOLICITUD, opcion_hotel.id ).then(
			function( respuesta ){
				angular.forEach( vm.solicitud.hoteles_solicitados, function( hotel, indice ){
					angular.forEach( hotel.opciones, function( opcion, indice_opcion ){
						if( opcion.id === opcion_hotel.id ){
							hotel.opciones.splice( indice_opcion, 1 );
						}
					});
				});
				$mdToast.show(
					$mdToast.simple()
					.textContent( "Opcion de hotel eliminada." )
					.position( "bottom right" )
					.hideDelay( 3000 )
    			);
			},
			function( error ){
				console.log( "eliminar_opcion_hotel" );
				console.log( error );
			}
		);
	}

	function seleccionar_opciones_vuelos(){
		_solicitud.seleccionar_opciones_vuelos( vm.ID_SOLICITUD ).then(
			function( respuesta ){
				angular.forEach( vm.solicitud.vuelos_solicitados, function( vuelo, indice ){
					vm.opcion_vuelo[ indice ] = {
						escalas: 0,
						fecha_salida: vuelo.fecha,
						fecha_llegada: vuelo.fecha,
					};
					vuelo.opciones = [];
					if( vuelo.id in respuesta ){
						vuelo.opciones = respuesta[ vuelo.id ];
					}
				});
				vm.hay_cotizacion = vm.verificar_cotizacion_completa( vm.solicitud.vuelos_solicitados );
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
				});
				vm.hay_cotizacion = vm.verificar_cotizacion_completa( vm.solicitud.hoteles_solicitados );
			},
			function( error ){
				console.log( "Seleccionar hoteles" );
				console.log( error );
				$state.go( "login" );
			}
		);
	}

	function insertar_opcion_vuelo( indice, ev ){
		if( vm.forma_opcion_vuelo[ indice ].$valid ){
			vm.opcion_vuelo[ indice ].id_vuelo_solicitado = vm.solicitud.vuelos_solicitados[ indice ].id;
			vm.opcion_vuelo[ indice ].origen = vm.solicitud.vuelos_solicitados[ indice ].origen;
			vm.opcion_vuelo[ indice ].destino = vm.solicitud.vuelos_solicitados[ indice ].destino;
			_solicitud.insertar_opcion_vuelo( vm.ID_SOLICITUD, vm.opcion_vuelo[ indice ] ).then(
				function( respuesta ){
					vm.opcion_vuelo[ indice ] = {};
					vm.seleccionar_opciones_vuelos();
				},
				function( error ){
					console.log( "insertar_opcion_vuelo" );
					console.log( error );					
				}
			);
		}
		else{
			$mdDialog.show(
				$mdDialog.alert()
				.clickOutsideToClose( true )
				.title( "Forma inválida" )
				.textContent( "Los campos se encuentran imcompletos." )
				.ok( "Cerrar" )
				.targetEvent( ev )
			);
		}
	}

	
	function eliminar_opcion_vuelo( opcion_vuelo ){
		_solicitud.eliminar_opcion_vuelo( vm.ID_SOLICITUD, opcion_vuelo.id ).then(
			function( respuesta ){
				angular.forEach( vm.solicitud.vuelos_solicitados, function( vuelo, indice ){
					angular.forEach( vuelo.opciones, function( opcion, indice_opcion ){
						if( opcion.id === opcion_vuelo.id ){
							vuelo.opciones.splice( indice_opcion, 1 );
						}
					});
				});
				$mdToast.show(
					$mdToast.simple()
					.textContent( "Opcion de vuelo eliminada." )
					.position( "bottom right" )
					.hideDelay( 3000 )
    			);
			},
			function( error ){
				console.log( "eliminar_opcion_vuelo" );
				console.log( error );
			}
		);
	}

	function actualizar_opcion_vuelo( opcion_vuelo ){
		_solicitud.actualizar_opcion_vuelo( vm.ID_SOLICITUD, opcion_vuelo ).then(
			function( respuesta ){
				$mdToast.show(
					$mdToast.simple()
					.textContent( "Opcion de vuelo actualizada." )
					.position( "bottom right" )
					.hideDelay( 3000 )
    			);
			},
			function( error ){
				console.log( "actualizar_opcion_vuelo" );
				console.log( error );
			}
		);
	}

	function enviar_cotizacion( ev ){
		if( vm.hay_cotizacion ){
			var confirm = $mdDialog.prompt()
				.title( "Enviar cotización" )
				.textContent( "Se enviará un correo al viajero para notificarle que ya puede elegir sus vuelo y hotel." )
				.placeholder( "Comentarios adicionales" )
				.targetEvent( ev )
				.ok( "Enviar" )
				.cancel( "Cancelar" );
			$mdDialog.show( confirm ).then(
				function( resultado_input ){
					vm.procesando = true;
					var comentarios_adicionales = ( resultado_input ) ? resultado_input : "";
					_solicitud.enviar_cotizacion( vm.ID_SOLICITUD, comentarios_adicionales ).then(
						function( respuesta ){
							vm.procesando = false;
							$state.go( "inicio" );
							$mdToast.show(
								$mdToast.simple()
								.textContent( "Cotización enviada." )
								.position( "bottom right" )
								.hideDelay( 3000 )
							);
						},
						function( error ){
							console.log( "enviar_cotizacion" );
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



























































	/**
	*	@ngdoc property
	*	@propertyOf viajesUsaria.controller:cotizacionController
	*	@name viajesUsaria.controller:cotizacionController#rol
	*/
	vm.rol = $stateParams.rol;
	/**
	*	@ngdoc property
	*	@propertyOf viajesUsaria.controller:cotizacionController
	*	@name viajesUsaria.controller:cotizacionController#ID_SOLICITUD
	*/
	
	/**
	*	@ngdoc property
	*	@propertyOf viajesUsaria.controller:cotizacionController
	*	@name viajesUsaria.controller:cotizacionController#solicitud
	*/
	vm.solicitud = {};
	/**
	*	@ngdoc property
	*	@propertyOf viajesUsaria.controller:cotizacionController
	*	@name viajesUsaria.controller:cotizacionController#vuelos_seleccionados
	*/
	vm.vuelos_seleccionados = [];
	/**
	*	@ngdoc property
	*	@propertyOf viajesUsaria.controller:cotizacionController
	*	@name viajesUsaria.controller:cotizacionController#hoteles_seleccionados
	*/
	vm.hoteles_seleccionados = [];
	/**
	*	@ngdoc property
	*	@propertyOf viajesUsaria.controller:cotizacionController
	*	@name viajesUsaria.controller:cotizacionController#opcion_vuelo
	*/
	vm.opcion_vuelo = [];
	/**
	*	@ngdoc property
	*	@propertyOf viajesUsaria.controller:cotizacionController
	*	@name viajesUsaria.controller:cotizacionController#opcion_hotel
	*	@description
	*	Arreglo que se adhiere a cada hotel solicitado que contiene la solicitud y es controlado por el indice del arreglo de hoteles_solicitados.
	*	Se hace un arreglo para poder colocarlo dentro de un ng-repeat y hacer el html necesario para una sola forma.
	*	Con el indice, se permite saber que forma se lleno y tiene la informacion para una nueva opcion de hotel a registrar en la base de datos.
	*/
	vm.opcion_hotel = [];
	/**
	*	@ngdoc property
	*	@propertyOf viajesUsaria.controller:cotizacionController
	*	@name viajesUsaria.controller:cotizacionController#mensaje_autorizacion
	*	@description
	*	Variable que contiene el mensaje de respuesta del objeto $mdDialog.prompt() al confirmar dicho dialogo.
	*/
	vm.mensaje_autorizacion;
	/**
	*	@ngdoc property
	*	@propertyOf viajesUsaria.controller:cotizacionController
	*	@name viajesUsaria.controller:cotizacionController#procesando
	*/
	vm.procesando = false;
	vm.esAutorizador = false;
	/**
	*	Funciones que interactuan con modelos
	*/


	vm.vuelosSeleccionados =  []; vm.hotelesSeleccionados = [];
	/**
	*	Funciones que interactuan con el DOM
	*/
	vm.autorizar = autorizar;
	vm.rechazar = rechazar;

	/**
	*	@ngdoc property
	*	@propertyOf viajesUsaria.controller:cotizacionController
	*	@name viajesUsaria.controller:cotizacionController#cargando
	*/
	vm.cargando = true;
	/**
	*	@ngdoc function
	*	@methodOf viajesUsaria.controller:cotizacionController
	*	@name viajesUsaria.controller:cotizacionController#obtenerUsuario
	*/
	vm.obtenerUsuario = obtenerUsuario;
	/**
	*	@ngdoc function
	*	@methodOf viajesUsaria.controller:cotizacionController
	*	@name viajesUsaria.controller:cotizacionController#obtenerSolicitudViaje
	*/
	vm.obtenerSolicitudViaje = obtenerSolicitudViaje;
	/**
	*	@ngdoc function
	*	@methodOf viajesUsaria.controller:cotizacionController
	*	@name viajesUsaria.controller:cotizacionController#obtenerOpcionesVuelos
	*/
	vm.obtenerOpcionesVuelos = obtenerOpcionesVuelos;

	/**
	*	@ngdoc function
	*	@methodOf viajesUsaria.controller:cotizacionController
	*	@name viajesUsaria.controller:cotizacionController#enviarSeleccion
	*/
	vm.enviarSeleccion = enviarSeleccion;





	// MARK: enviarSeleccion
	function enviarSeleccion( ev ){
		var textoDialogo = 'Se notificará mediante correo a ';
		angular.forEach( vm.solicitud.autorizaciones, function( autorizacion, $index ){
			textoDialogo += ( autorizacion.nombre_autorizador + ' ' + autorizacion.apellido_autorizador + ', ' );
		});
		var confirm = $mdDialog.confirm()
			.title( '¿Quiéres enviar tu selección?')
			.textContent( textoDialogo )
			.targetEvent( ev )
			.ok( 'Si')
			.cancel( 'No' );
		$mdDialog.show( confirm ).then(
			function(){
				vm.procesando = true;
				COTIZACION.registrarSeleccion( $stateParams.id_solicitud, vm.vuelos_seleccionados, vm.hoteles_seleccionados ).then(
					function( response ){
						vm.procesando = false;
						$mdToast.show(
							$mdToast.simple()
							.textContent( 'Selección enviada' )
							.position( 'bottom right' )
							.hideDelay( 3000 )
						);
						$state.go( 'inicio' );
					}
				);
			},
			function(){}
		);
	}

	// MARK: obtenerUsuario
	function obtenerUsuario(){
		DASHBOARD.obtenerSesion().then(
			function( response ) {
				vm.usuario = response;
			},
			function( error ) {
				console.log( 'ERROR', error );
			}
		);
	}

	// MARK: obtenerOpcionesVuelos
	function obtenerOpcionesVuelos(){
		angular.forEach( vm.solicitud.vuelos_solicitados, function( vueloSolicitado, $index ){
			vueloSolicitado.opciones = [];
			COTIZACION.traerOpcionesVuelos( vueloSolicitado.id ).then(
				function( response ){
					vueloSolicitado.opciones  = response;
					vm.opcion_vuelo[ $index ] = {};
					vm.opcion_vuelo[ $index ].fecha_salida = vueloSolicitado.fecha;
					vm.opcion_vuelo[ $index ].escalas = 0;
					angular.forEach( response, function( opcion, $j_index ){
						if( opcion.seleccionada ){
							vm.vuelos_seleccionados[ $index ] = opcion;
						}
					});
				},
				function( error ){
					console.log( 'ERROR', error );
				}
			);
		});
	}

	/**
	*	@ngdoc function
	*	@methodOf viajesUsaria.controller:cotizacionController
	*	@name obtenerOpcionesHoteles
	*	@requires cotizacionModel
	*	@description
	*	Obtiene las opciones de hotel para un hotel_solicitado, se realiza la busqueda de aquella opcion seleccionada para mostrarla al usuario
	*/
	vm.obtenerOpcionesHoteles = obtenerOpcionesHoteles;
	function obtenerOpcionesHoteles(){
		angular.forEach( vm.solicitud.hoteles_solicitados, function( hotelSolicitado, $index ){
			hotelSolicitado.opciones = [];
			COTIZACION.traerOpcionesHoteles( hotelSolicitado.id ).then(
				function( response ){
					hotelSolicitado.opciones = response;
					angular.forEach( response, function( opcion, $j_index ){
						if( opcion.seleccionada ){
							vm.hoteles_seleccionados[ $index ] = opcion;
						}
					});
				},
				function( error ){
					console.log( 'ERROR', error );
				}
			);
		});
	}



















	/**
	*	@ngdoc function
	*	@methodOf viajesUsaria.controller:cotizacionController
	*	@name viajesUsaria.controller:cotizacionController#agregarOpcionVuelo
	*/
	vm.agregarOpcionVuelo = agregarOpcionVuelo;
	/**
	*	@ngdoc function
	*	@methodOf viajesUsaria.controller:cotizacionController
	*	@name viajesUsaria.controller:cotizacionController#editarOpcionVuelo
	*/
	vm.editarOpcionVuelo = editarOpcionVuelo;
	/**
	*	@ngdoc function
	*	@methdOf viajesUsaria.controller:cotizacionController
	*	@name viajesUsaria.controller:cotizacionController#eliminarOpcionVuelo
	*/
	vm.eliminarOpcionVuelo = eliminarOpcionVuelo;
	/**
	*	@ngdoc function
	*	@methodOf viajesUsaria.controller:cotizacionController
	*	@name viajesUsaria.controller:cotizacionController#copiarOpcionVueloAForma
	*/
	vm.copiarOpcionVueloAForma = copiarOpcionVueloAForma;
	
	/**
	*	@ngdoc function
	*	@methodOf viajesUsaria.controller:cotizacionController
	*	@name viajesUsaria.controller:cotizacionController#eliminarOpcionHotel
	*/
	vm.eliminarOpcionHotel = eliminarOpcionHotel;
	/**
	*	@ngdoc function
	*	@methodOf viajesUsaria.controller:cotizacionController
	*	@name viajesUsaria.controller:cotizacionController#enviarCotizacion
	*/
	vm.enviarCotizacion = enviarCotizacion;
	/**
	*	@ngdoc function
	*	@methodOf viajesUsaria.controller:cotizacionController
	*	@name viajesUsaria.controller:cotizacionController#cancelarDialogo
	*/
	vm.cancelarDialogo = cancelarDialogo;
	/**
	*	@ngdoc function
	*	@methodOf viajesUsaria.controller:cotizacionController
	*	@name viajesUsaria.controller:cotizacionController#responderDialogo
	*/
	vm.responderDialogo = responderDialogo;


	// MARK: obtenerSolicitudViaje
	function obtenerSolicitudViaje(){
		SOLICITUD.traerInformacion( vm.ID_SOLICITUD ).then(
			function( respuesta ){
				console.log( respuesta );
				vm.solicitud = respuesta;
				angular.forEach( vm.solicitud.autorizaciones, function( autorizacion, $index ){
					if( vm.usuario.correo === autorizacion.id_autorizador ){
						vm.esAutorizador = true;
					}
				});
				if( vm.usuario.privilegio === 2 && vm.rol === 'administrador' ){
					vm.titulo = 'Cotización';
				}
				else if( vm.usuario.correo  === vm.solicitud.viajero && vm.rol === 'viajero' ){
					vm.titulo = 'Selección'
				}
				else if( vm.esAutorizador && vm.rol === 'autorizador' ){
					vm.titulo = 'Autorización';
				}
				vm.obtenerOpcionesVuelos();
				vm.obtenerOpcionesHoteles();
			},
			function( error ){
				if( error.token_validado === false ){
					$state.go( 'login' );
				}
				console.log( 'ERROR', error );
			}
		);
	}

	// MARK: agregarOpcionVuelo
	function agregarOpcionVuelo( $index ){
		console.log( $index );
		console.log( vm.solicitud.vuelos_solicitados[ $index ].id );
		if( vm.forma_nuevoVuelo[ $index ].$valid ){
			if( vm.opcion_vuelo[ $index ].id_vuelo_solicitado === undefined ){
				vm.opcion_vuelo[ $index ].id_vuelo_solicitado = vm.solicitud.vuelos_solicitados[ $index ].id;
			}
			vm.opcion_vuelo[ $index ].origen = vm.solicitud.vuelos_solicitados[ $index ].origen;
			vm.opcion_vuelo[ $index ].destino = vm.solicitud.vuelos_solicitados[ $index ].destino;
			COTIZACION.insertarOpcionVuelo( vm.opcion_vuelo[ $index ] ).then(
				function( respuesta ){
					var aux = respuesta.objeto_insertado;
					vm.solicitud.vuelos_solicitados[ $index ].opciones.push( aux );
					vm.opcion_vuelo[ $index ] = {};
					vm.opcion_vuelo[ $index ].fecha_salida = vm.solicitud.vuelos_solicitados[ $index ].fecha;
					vm.opcion_vuelo[ $index ].escalas = 0;
				},
				function( error ){
					if( error.token_validado === false ){
						$state.go( 'login' );
					}
					else{
						console.log( 'ERROR', error );
					}
				}
			);
		}
	}

	// MARK: editarOpcionVuelo
	function editarOpcionVuelo( opcionVuelo, ev ){
		vm.opcionVuelo = opcionVuelo;
		$mdDialog.show({
			templateUrl: 'states/cotizacion/dialog/detalleOpcionVuelo.dialog.html',
			scope: $scope,
			preserveScope: true,
			targetEvent: ev,
			clickOutsideToClose: true,
			escapeToClose: true,
			focusOnOpen: false,
		}).then(
			function( respuesta ){
				COTIZACION.actualizarOpcionVuelo( vm.opcionVuelo ).then(
					function( response ){
						if( response.query ){
							$mdToast.show(
								$mdToast.simple()
								.textContent( 'Opción actualizada.' )
								.position( 'bottom right' )
								.hideDelay( 3000 )
							);
						}
						else{
							console.log( response );
						}
					}
				);
			},
			function(){}
		);
	}

	// MARK: eliminarOpcionVuelo
	function eliminarOpcionVuelo( opcionVueloObj ){
		COTIZACION.eliminarOpcionVuelo( opcionVueloObj ).then(
			function( response ){
				angular.forEach( vm.solicitud.vuelos_solicitados, function( vueloSolicitado, $i ){
					angular.forEach( vueloSolicitado.opciones, function( opcion, $j ){
						if( opcion.id === opcionVueloObj.id ){
							vueloSolicitado.opciones.splice( $j, 1 );
						}
					});
				});
				$mdToast.show(
					$mdToast.simple()
					.textContent( 'Opción eliminada.' )
					.position( 'bottom right' )
					.hideDelay( 3000 )
				);
			}
		);
	}


	

	// MARK: copiarOpcionVueloAForma
	function copiarOpcionVueloAForma( opcionVueloObj ){
		angular.forEach( vm.solicitud.vuelos_solicitados, function( vueloSolicitado, $index ){
			if( vueloSolicitado.id === opcionVueloObj.id_vuelo_solicitado ){
				angular.copy( opcionVueloObj, vm.opcion_vuelo[ $index ] );
			}
		});
	}

	// MARK: eliminarOpcionHotel
	function eliminarOpcionHotel( opcion_hotelObj ){
		COTIZACION.eliminarOpcionHotel( opcion_hotelObj ).then(
			function( response ){
				angular.forEach( vm.solicitud.hoteles_solicitados, function( hotelSolicitado, $i ){
					angular.forEach( hotelSolicitado.opciones, function( opcion, $j ){
						if( opcion.id === opcion_hotelObj.id ){
							hotelSolicitado.opciones.splice( $j, 1 );
							$mdToast.show(
								$mdToast.simple()
								.textContent( 'Opción eliminada.' )
								.position( 'bottom right' )
								.hideDelay( 3000 )
							);
						}
					});
				});
			}
		);
	}

	// MARK: enviarCotizacion
	function enviarCotizacion( ev ){
		var confirm = $mdDialog.confirm()
			.title( '¿Quiéres enviar la cotización?')
			.textContent( 'Se notificará mediante correo a ' + vm.solicitud.nombre_viajero + ' ' + vm.solicitud.apellido_viajero + '.' )
			.targetEvent( ev )
			.ok( 'Si')
			.cancel( 'No' );
		$mdDialog.show( confirm ).then(
			function(){
				vm.procesando = true;
				COTIZACION.registrarCotizacion( $stateParams.id_solicitud ).then(
					function( response ){
						vm.procesando = false;
						$mdToast.show(
							$mdToast.simple()
							.textContent( 'Cotización enviada')
							.position( 'bottom right' )
							.hideDelay( 3000 )
						);
						$state.go( 'inicio' ); // Ir a estado inicio
					}
				);
			},
			function(){} // Dialogo fue cancelado
		);
	}

	function cancelarDialogo(){
		$mdDialog.cancel();
	}

	function responderDialogo(){
		$mdDialog.hide();
	}



	/**
	*	Cotizacion
	*/
	vm.sobreescribirAutorizador = sobreescribirAutorizador;













	function sobreescribirAutorizador( ev ){
		// objeto confirm del servicio $mdDialog
		var confirm = $mdDialog.confirm()
			.title( '¿Quiéres cambiar al autorizador de la solicitud de viaje?')
			.textContent( 'Se notificará mediante correo que ' + vm.solicitud.nombre_autorizador + ' ' + vm.solicitud.apellido_autorizador + ' ya no se hará cargo de esta solicitud' )
			.targetEvent( ev )
			.ok( 'Si, deseo cambiarlo')
			.cancel( 'No, todavía no' );
		$mdDialog.show( confirm ).then(
			function(){
				COTIZACION.sobreescribirAutorizador( $stateParams.id_solicitud ).then(
					function( response ){
						vm.obtenerSolicitudViaje();
					},
					function( rejection ){
						console.log( rejection );
					}
				);

			},
			function(){}
		);
	}



	/**
	*	@ngdoc method
	*	@name traerSeleccionDeVuelos
	*	@methodOf viajesUsaria.cotizacionController
	*	@description
	*	Realiza el callback para traer un objeto con los ids de los vuelos seleccionados y reflejarlos en el arreglo de
	*	los vuelos solicitados de la solicitud de viaje
	*/
	function traerSeleccionDeVuelos(){
		angular.forEach( vm.solicitud.vuelos_solicitados, function( vueloSolicitado, $i ){
			/**
			*	Modelo Cotizacion {@link viajesUsaria.service:cotizacionModel cotizacionModel}
			*/
			COTIZACION.traerSeleccionDeVuelos( vueloSolicitado.id ).then(
				function( response ){
					angular.forEach( vueloSolicitado.opciones, function( opcionVuelo, $j ){
						if( opcionVuelo.id === response.id_opcion_vuelo ){
							vm.vuelosSeleccionados[ $i ] = opcionVuelo;
						}
					});
				},
				function( error ){
					console.log( 'ERROR', error );
				}
			);
		});
	}

	/**
	*	@ngdoc method
	*	@name traerSeleccionDeHoteles
	*	@methodOf viajesUsaria.cotizacionController
	*	@description
	*	Realiza el callback para traer un objeto con los ids de los hoteles seleccionados y reflejarlos en el arreglo de
	*	los vuelos solicitados de la solicitud de viaje
	*/
	function traerSeleccionDeHoteles(){
		angular.forEach( vm.solicitud.vuelos_solicitados, function( vueloSolicitado, $i ){
			/**
			*	Modelo Cotizacion {@link viajesUsaria.service:cotizacionModel cotizacionModel}
			*/
			COTIZACION.traerSeleccionDeHoteles( vueloSolicitado.id ).then(
				function( response ){
					angular.forEach( vueloSolicitado.hoteles, function( opcion_hotel, $j ){
						if( opcion_hotel.id === response.id_opcion_hotel ){
							vm.hotelesSeleccionados[ $i ] = opcion_hotel;
						}
					});
				},
				function( error ){
					console.log( 'ERROR', error );
				}
			);
		});
	}

	/**
	*	Bloque de modo Autorizacion
	*/
	vm.cancelarDialogo = cancelarDialogo;
	vm.contestarDialogo = contestarDialogo;
	vm.enviarAutorizacion = enviarAutorizacion;

	/**
	*	@ngdoc function
	*	@methodOf viajesUsaria.controller:cotizacionController
	*	@name autorizar
	*	@description
	*	Abre un dialogo de prompt para confirmar la autorizacion y escribir un comentario adicional opcional
	*
	*	@param {object} ev Objeto tipo evento de donde se utiliza como origen para la animacion del dialogo
	*/
	function autorizar( ev ){
		var prompt = $mdDialog.prompt()
			.title( 'Autorización de la solicitud de viaje' )
			.textContent( 'El comentario es opcional' )
			.placeholder( 'Incluye un mensaje' )
			.targetEvent( ev )
			.ok( 'Autorizar' )
			.cancel( 'Cancelar' );
		$mdDialog.show( prompt ).then(
			function( respuesta ){
				vm.mensaje_autorizacion = respuesta;
				vm.enviarAutorizacion( true );
			},
			function(){} // Dialogo cancelado
		);
	}

	/**
	*	@ngdoc method
	*	@methodOf viajesUsaria.controller:cotizacionController
	*	@name rechazar
	*	@requires $mdDialog
	*	@description
	*	Abre un dialogo de prompt para confirmar el rechazo y escribir un comentario adicional opcional
	*
	*	@param {object} ev Objeto tipo evento de donde se utiliza como origen para la animacion del dialogo
	*/
	function rechazar( ev ){
		var prompt = $mdDialog.prompt()
			.title( 'Rechazo de la compra de pasajes' )
			.textContent( 'El comentario es opcional' )
			.placeholder( 'Incluye un mensaje' )
			.targetEvent( ev )
			.ok( 'Rechazar' )
			.cancel( 'Cancelar' );
		$mdDialog.show( prompt ).then(
			function( respuesta ){
				vm.mensaje_autorizacion = respuesta;
				vm.enviarAutorizacion( false );
			},
			function(){} // Dialogo cancelado
		);
	}

	/**
	*	@ngdoc method
	*	@name cancelarDialogo
	*	@methodOf viajesUsaria.cotizacionController
	*	@description
	*	Maneja el evento de cancelacion de dialogo, donde se destruye y detona el callback de onFailed del metodo then()
	*/
	function cancelarDialogo(){
		$mdDialog.cancel();
	}

	/**
	*	@ngdoc method
	*	@name contestarDialogo
	*	@methodOf viajesUsaria.cotizacionController
	*	@description
	*	Maneja el evento de confirmacion de dialogo, donde se destruye y detona el callback de onComplete del metodo then()
	*/
	function contestarDialogo(){
		$mdDialog.hide();
	}

	/**
	*	@ngdoc function
	*	@methodOf viajesUsaria.controller:cotizacionController
	*	@name enviarAutorizacion
	*	@requires cotizacionModel
	*	@requires $state
	*	@requires $stateParams
	*	@requires $mdToast
	*	@description
	*	Envia la respuesta del dialogo de confirmacion al modelo y a los recursos de php
	*
	*	@param {boolean} respuesta, valor que representa si se autoriza o no la compra de los pasajes
	*/
	function enviarAutorizacion( respuesta ){
		vm.procesando = true;
		COTIZACION.actualizarAutorizacion( $stateParams.id_solicitud, respuesta, vm.mensaje_autorizacion ).then(
			function( response ){
				vm.procesando = false;
				$mdToast.show(
					$mdToast.simple()
					.textContent( 'Autorización enviada.' )
					.position( 'bottom right' )
					.hideDelay( 3000 )
				);
				$state.go( 'inicio' );
			},
			function( error ){
				console.log( 'ERROR', error );
			}
		);
	}
}
