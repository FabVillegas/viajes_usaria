angular.module( 'viajesUsaria' ).controller( 'solicitudController', solicitudController );
solicitudController.$inject = [ "$autocompletado", "Solicitud", "Usario", '$state', '$stateParams', '$mdDialog', '$mdMedia', '$scope', '$mdToast' ];
function solicitudController( $autocompletado, Solicitud, Usario, $state, $stateParams, $mdDialog, $mdMedia, $scope, $mdToast ){

	var vm = this;
	var _solicitud = new Solicitud();
	var _usario = new Usario();
	var ID_SOLICITUD = ( $stateParams.id_solicitud ) ? $stateParams.id_solicitud : undefined;

	vm.titulo = "Solicitud de viaje";
	vm.solicitud = {};
	vm.horas_24 = [ "00:00", "01:00", "02:00", "03:00", "04:00", "05:00", "06:00", "07:00", "08:00", "09:00", "10:00", "11:00", "12:00", "13:00", "14:00", "15:00", "16:00", "17:00", "18:00", "19:00", "20:00", "21:00", "22:00", "23:00" ]
	vm.buscador = $autocompletado;
	vm.procesando = false;
	vm.mostrarGeneral = false;
	vm.mostrarViaticos = false;
	vm.mostrarVuelo = false;
	vm.mostrarHotel = false;

	vm.dom_mostrarFormas = dom_mostrarFormas;
	vm.fijarDiferenciaHoras = fijarDiferenciaHoras;

	vm.agregarVuelo = agregarVuelo;
	vm.eliminarVuelo = eliminarVuelo;
	vm.agregarHotel = agregarHotel;
	vm.eliminarHotel = eliminarHotel;
	vm.enviarSolicitud = enviarSolicitud;
	
	activo();
	function activo(){
		_usario.obtener_sesion().then(
			function( respuesta ){
				vm.usario = respuesta;
			},
			function( error ){
				console.log( "Solicitud controller" );
				console.log( error );
				$state.go( "login" );
			}
		);
		$autocompletado.traerProyectos().then(
			function( respuesta ){
				vm.proyectos = respuesta;
			},
			function( error ){
				console.log( vm.titulo, error );
			}
		);
		$autocompletado.traerAeropuertos().then(
			function( respuesta ){
				vm.aeropuertos = respuesta;
			},
			function( error ){
				console.log( vm.titulo, error );
			}
		);
		/*
		vm.buscador.traerAeropuertos().then(
			function( response ){
				vm.buscador.aeropuertos =  response;
			}
		);
		*/

		/*
		var hoy = new Date();
		if( $stateParams.id_solicitud ){
			console.log( 'Hay solicitud' );
			SOLICITUD.traerInformacion( vm.ID_SOLICITUD ).then(
				function( response ){
					console.log( response );
					vm.solicitud = response;
					vm.mostrar_opciones = false;
				},
				function( error ){
					console.log( 'ERROR', error );
				}
			);
		}
		DASHBOARD.obtenerSesion().then(
			function( response ){
				vm.usuario = response;
			}
		);
		SOLICITUD.traerProyectosActivos().then(
			function( respuesta ) {
				vm.proyectos = respuesta;
			},
			function( error ) {
				console.log( 'ERROR', error );
				//$state.go( 'login' );
			}
		);
		
		*/
	}

	function dom_mostrarFormas( formaGeneral, formaViaticos, formaVuelo, formaHotel ){
		if( formaVuelo === true ){
			vm.solicitud.vuelos_solicitados = [ {} ];
		}
		else{
			vm.solicitud.vuelos_solicitados = [];
		}
		if( formaHotel === true ){
			vm.solicitud.hoteles_solicitados = [ {} ];
		}
		else{
			vm.solicitud.hoteles_solicitados = [];
		}
		vm.mostrarGeneral = formaGeneral;
		vm.mostrarViaticos = formaViaticos;
		vm.mostrarVuelo = formaVuelo;
		vm.mostrarHotel = formaHotel;
		/*
		$location.hash( "forma-solicitud" );
		$anchorScroll();
		*/
	}

	function fijarDiferenciaHoras( vuelo  ){
		if( vuelo.hora_max === undefined ){
			try{
				var indice = vm.horas_24.indexOf( vuelo.hora_min );
				vuelo.hora_max = vm.horas_24[ indice + 2 ];
			}
			catch( exception ){
				console.log( exception );
				vuelo.hora_max = vm.horas_24[ vm.horas_24.length - 1 ];
			}
		}
		else if( vuelo.hora_min && vuelo.hora_max){
			vuelo.horario_origen = vuelo.hora_min + " - " + vuelo.hora_max;
		}
	}

	function agregarVuelo(){
		vm.solicitud.vuelos_solicitados.push( {} );
	}

	function eliminarVuelo( indice ){
		vm.solicitud.vuelos_solicitados.splice( indice, 1 );
	}

	function agregarHotel(){
		vm.solicitud.hoteles_solicitados.push( {} );
	}

	function eliminarHotel( indice ){
		vm.solicitud.hoteles_solicitados.splice( indice, 1 );
	}

	function enviarSolicitud( ev ){
		if( vm.forma_general.$valid && ( vm.mostrarVuelo && vm.forma_vuelos.$valid || vm.mostrarHotel && vm.forma_hoteles.$valid || vm.mostrarViaticos && vm.forma_viaticos.$valid ) ){
			var titulo;
			var texto;
			if( !ID_SOLICITUD ){
				titulo = "¿Quiéres enviar la solicitud?";
				texto = "La solicitud de viaje será almacenada y se empezará a cotizar. " +
				"Se notificará mediante correo a Azucena Cruz. No es necesario que hagas otro paso, solo esperar a que te respondan.";
			}
			var confirm = $mdDialog.prompt()
				.title( titulo )
				.textContent( texto )
				.placeholder( "Comentarios adicionales" )
				.targetEvent( ev )
				.ok( "Sí" )
				.cancel( "No" );
			$mdDialog.show( confirm ).then(
				function( resultado ){
					vm.procesando = true;
					vm.solicitud.comentarios_adicionales = ( resultado ) ? resultado : "";
					_solicitud.insertar_nueva( vm.solicitud ).then(
						function( respuesta ){
							console.log( respuesta );
							vm.procesando = false;
							$mdToast.show(
								$mdToast.simple()
								.textContent( "Solicitud guardada y enviada." )
								.position( "bottom right" )
								.hideDelay( 3000 )
							);
							$state.go( "inicio" );
						},
						function( error ){
							console.log( "solicitudController", error );
							vm.procesando = false;
						}
					);
				},
				function(){
					vm.procesando = false;
				}
			);
		}
		else{
			console.log( "Forma no valida" );
			$mdDialog.show(
				$mdDialog.alert()
				.clickOutsideToClose( true )
				.title( "Forma inválida" )
				.textContent( "Los campos se encuentran incompletos." )
				.ok( "Cerrar" )
				.targetEvent( ev )
			);
		}
	}
	/*
	function intercambiarVuelos( vueloObj ){
		if( vueloObj.origen === Object( vueloObj.origen ) && vueloObj.destino === Object( vueloObj.destino ) ){
			var aux = vueloObj.origen;
			vueloObj.origen =  vueloObj.destino;
			vueloObj.destino = aux;
		}
		else{
			var aux = vueloObj.origen_texto;
			vueloObj.origen_texto =  vueloObj.destino_texto;
			vueloObj.destino_texto = aux;
		}
	}
	*/
	/*
	function fijarFechasAVuelos(){
		if( vm.solicitud.vuelos_solicitados && vm.solicitud.necesita_cotizacion_vuelo ){
			vm.solicitud.vuelos_solicitados[ 0 ].fecha = vm.solicitud.fecha_inicio;
			vm.solicitud.vuelos_solicitados[ ( vm.solicitud.vuelos_solicitados.length - 1 ) ].fecha = vm.solicitud.fecha_fin;
		}
	}
	*/
}
