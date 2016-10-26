angular.module( 'viajesUsaria' ).controller( 'inicioController', inicioController );
inicioController.$inject = [ "$scope", "Solicitud", "Usario", "$state", "$mdDialog", "$mdToast" ];
function inicioController( $scope, Solicitud, Usario, $state, $mdDialog, $mdToast ){

	var _solicitud = new Solicitud();
	var _usario = new Usario();
	var vm = this;

	vm.titulo = 'Inicio';
	vm.procesando = false;
	vm.autorizando = false;
	vm.usario_es_autorizador = false;
	vm.solicitudes = [];

	vm.seleccionar_solicitudes_en_proceso = seleccionar_solicitudes_en_proceso;
	vm.seleccionar_solicitudes_por_autorizar = seleccionar_solicitudes_por_autorizar;
	vm.seleccionar_solicitudes_autorizadas = seleccionar_solicitudes_autorizadas;
	vm.seleccionar_solicitudes_completas = seleccionar_solicitudes_completas;
	vm.enviar_autorizacion_rapida = enviar_autorizacion_rapida;

	vm.mostrar_solicitudes = mostrar_solicitudes;
	vm.ir_cotizacion = ir_cotizacion;
	vm.ir_seleccion = ir_seleccion;
	vm.ir_autorizacion = ir_autorizacion;
	vm.ir_compras = ir_compras;
	vm.ver_detalle = ver_detalle;
	vm.cerrar_detalle = cerrar_detalle;
	vm.eliminar_solicitud = eliminar_solicitud;
	

	activo();
	function activo() {
		_usario.obtener_sesion().then(
			function( respuesta ){
				vm.usario = respuesta;
				vm.seleccionar_solicitudes_en_proceso();
				vm.seleccionar_solicitudes_por_autorizar();
				vm.seleccionar_solicitudes_autorizadas();
				vm.seleccionar_solicitudes_completas();
			},
			function( error ) {
				console.log( "ERROR usario service" );
				console.log( error );
				$state.go( "login" );
			}
		);
	}

	function mostrar_solicitudes( solicitudes, seleccion ){
		vm.solicitudes = solicitudes;
		vm.solicitudes_seleccionadas = seleccion;
	}

	function ir_cotizacion(){
		if( vm.usario.privilegio === 2 ){
			$state.go( "cotizacion", { id_solicitud: vm.solicitud.id } );
		}
	}

	function ir_seleccion(){
		if( vm.usario.correo === vm.solicitud.correo_viajero ){
			$state.go( "seleccion", { id_solicitud: vm.solicitud.id } );
		}
	}

	function ir_autorizacion(){
		angular.forEach( vm.solicitud.autorizaciones, function( autorizacion, indice ){
			if( autorizacion.id_autorizador === vm.usario.correo ){
				$state.go( "autorizacion", { id_solicitud: vm.solicitud.id } );
			}
		});
	}

	function ir_compras(){
		if( vm.usario.privilegio === 2 || vm.usario.correo === vm.solicitud.viajero ){
			$state.go( "compras", { id_solicitud: vm.solicitud.id } );
		}
	}

	function ver_detalle( solicitudObj, ev ){
		vm.solicitud = solicitudObj;
		angular.forEach( vm.solicitud.autorizaciones, function( autorizacion, indice ){
			if( autorizacion.id_autorizador === vm.usario.correo ){
				vm.usario_es_autorizador = true;
			}
		});
		$mdDialog.show({
			templateUrl: './states/inicio/dialogs/detalle_solicitud.dialog.html',
			targetEvent: ev,
			scope: $scope,
			preserveScope: true,
			clickOutsideToClose: true,
			escapeToClose: true,
		});
	}

	function cerrar_detalle(){
		vm.solicitud = {};
		$mdDialog.cancel();
	}

	function seleccionar_solicitudes_en_proceso(){
		_solicitud.seleccionar_pendientes( vm.usario.correo ).then(
			function( respuesta ){
				vm.solicitudes_en_proceso = respuesta;
				vm.solicitudes = vm.solicitudes_en_proceso;
				vm.solicitudes_seleccionadas = "en_proceso";
			},
			function( error ){
				console.log( "seleccionar_pendientes" );
				console.log( error );
			}
		);
		if( vm.usario.privilegio === 2 ){
			_solicitud.seleccionar_equipo_usaria().then(
				function( respuesta ){
					vm.solicitudes_equipo_usaria = respuesta;
					vm.solicitudes = vm.solicitudes_equipo_usaria;
					vm.solicitudes_seleccionadas = "equipo_usaria";
				},
				function( error ){
					console.log( "seleccionar_pendientes_todos" );
					console.log( error );
				}
			);
		}
	}

	function seleccionar_solicitudes_por_autorizar(){
		_solicitud.seleccionar_por_autorizar( vm.usario.correo ).then(
			function( respuesta ){
				vm.solicitudes_por_autorizar = respuesta;
			},
			function( error ){
				console.log( "seleccionar_por_autorizar" );
				console.log( error );
			}
		);
	}

	function seleccionar_solicitudes_autorizadas(){
		_solicitud.seleccionar_autorizadas( vm.usario.correo ).then(
			function( respuesta ){
				vm.solicitudes_autorizadas = respuesta;
			},
			function( error ){
				console.log( "seleccionar_por_autorizar" );
				console.log( error );
			}
		);
	}

	function seleccionar_solicitudes_completas(){
		_solicitud.seleccionar_completas( vm.usario.correo ).then(
			function( respuesta ){
				vm.solicitudes_completadas = respuesta;
			},
			function( error ){
				console.log( "seleccionar_por_autorizar" );
				console.log( error );
			}
		);
	}

	function enviar_autorizacion_rapida( respuesta_autorizacion ){
		vm.autorizando = true;
		_solicitud.enviar_autorizacion( vm.solicitud.id, vm.usario.correo, "", respuesta_autorizacion ).then(
			function( respuesta ){
				vm.autorizando = false;
				var texto;
				if( respuesta_autorizacion ){
					texto = "Autorización enviada.";
				}
				else{
					texto = "Rechazo enviado."
				}
				vm.seleccionar_solicitudes_en_proceso();
				vm.seleccionar_solicitudes_por_autorizar();
				vm.seleccionar_solicitudes_autorizadas();
				angular.forEach( vm.solicitud.autorizaciones, function( autorizacion, indice ){
					if( autorizacion.id_autorizador === vm.usario.correo ){
						autorizacion.solicitud_autorizada = respuesta_autorizacion;
						autorizacion.fecha_autorizacion = new Date();
					}
				});
				$mdToast.show(
					$mdToast.simple()
					.textContent( texto )
					.position( 'bottom right' )
					.hideDelay( 3000 )
				);
			},
			function( error ){
				console.log( "enviar_autorizacion" );
				console.log( error );
			}
		);
	}

	function eliminar_solicitud( solicitudObj ){
		var confirm = $mdDialog.prompt()
			.title( "Eliminar solicitud" )
			.textContent( "Estás a punto de eliminar tu solicitud. Una vez realizada, no se puede revertir. Se notificará mediante correo al administrador y a tu líder." )
			.placeholder( "Motivo de la cancelación" )
			.ok( "Eliminar" )
			.cancel( "Cancelar" );
		$mdDialog.show( confirm ).then(
			function( resultado_input ){
				vm.procesando = true;
				var comentarios_adicionales = ( resultado_input ) ? resultado_input : "";
				_solicitud.cancelar( solicitudObj.id, comentarios_adicionales ).then(
					function( respuesta ){
						console.log( respuesta );
						angular.forEach( vm.solicitudes, function( solicitud, index ){
							if( solicitudObj.id === solicitud.id ){
								vm.solicitudes.splice( index, 1 );
							}
						});
						vm.procesando = false;
						$mdToast.show(
							$mdToast.simple()
							.textContent( "Solicitud eliminada." )
							.position( "bottom right" )
							.hideDelay( 3000 )
						);
					},
					function( error ){
						$state.go( "login" );
					}
				);
			},
			function(){}
	   );
	}


















	/**
	*	@ngdoc function
	*	@methodOf viajesUsaria.controller:inicioController
	*	@name viajesUsaria.controller:inicioController#editarSolicitud
	*/
	vm.editarSolicitud = editarSolicitud;
	function editarSolicitud( solicitudObj ){
		$state.go( 'solicitud', { id_solicitud: solicitudObj.id } );
	}

}
