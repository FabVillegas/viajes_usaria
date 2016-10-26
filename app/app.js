angular.module('viajesUsaria',
[
  'ui.router',
  'ngAnimate',
  'ngAria',
  'ngMaterial',
  'ngMessages',
  'barra-menu-material',
  'ngEnter',
  'ngFileUpload',
  'google-auth',
]).
config( [ '$stateProvider', '$urlRouterProvider', '$mdThemingProvider', '$mdDateLocaleProvider', '$httpProvider',
function( $stateProvider, $urlRouterProvider, $mdThemingProvider, $mdDateLocaleProvider, $httpProvider ) {

	$httpProvider.defaults.headers.post['Content-Type'] = 'application/x-www-form-urlencoded; charset=UTF-8';

	/**
	*	Paletas de colores
	*/
	$mdThemingProvider.definePalette( 'pantone7711', {
		'50': '#b3f7ff',
		'100': '#67efff',
		'200': '#2fe9ff',
		'300': '#00cee6',
		'400': '#00b2c8',
		'500': '#0097a9',
		'600': '#007c8a',
		'700': '#00606c',
		'800': '#00454d',
		'900': '#002a2f',
		'A100': '#b3f7ff',
		'A200': '#67efff',
		'A400': '#00b2c8',
		'A700': '#00606c',
		'contrastDefaultColor': 'light',
		'contrastDarkColors': '50 100 200 300 A100 A200'
	});

	$mdThemingProvider.definePalette( 'pantone368', {
		'50': '#f5fceb',
		'100': '#d1f1aa',
		'200': '#b8e97a',
		'300': '#97de3d',
		'400': '#89d824',
		'500': '#78be20',
		'600': '#67a41c',
		'700': '#578a17',
		'800': '#466f13',
		'900': '#36550e',
		'A100': '#d1f1aa',
		'A200': '#97de3d',
		'A400': '#89d824',
		'A700': '#78be20',
		'contrastDefaultColor': 'light',
		//'contrastDarkColors': '50 100 200 300 400 500 600 A100 A200 A400',
		//'contrastLightColors': undefined
	});

	$mdThemingProvider.definePalette( 'pantone2607', {
		'50': '#d390f9',
		'100': '#b747f4',
		'200': '#a212f1',
		'300': '#770ab2',
		'400': '#630995',
		'500': '#500778',
		'600': '#3d055b',
		'700': '#29043e',
		'800': '#160221',
		'900': '#030004',
		'A100': '#d390f9',
		'A200': '#b747f4',
		'A400': '#630995',
		'A700': '#29043e',
		'contrastDefaultColor': 'light',
		'contrastDarkColors': '50 A100'
	});

	$mdThemingProvider.definePalette( 'pantone7549', {
		'50': '#ffffff',
		'100': '#ffecbd',
		'200': '#ffdb85',
		'300': '#ffc73d',
		'400': '#ffbe1f',
		'500': '#ffb500',
		'600': '#e09f00',
		'700': '#c28a00',
		'800': '#a37400',
		'900': '#855e00',
		'A100': '#ffffff',
		'A200': '#ffecbd',
		'A400': '#ffbe1f',
		'A700': '#c28a00',
		'contrastDefaultColor': 'light',
		'contrastDarkColors': '50 100 200 300 400 500 600 700 A100 A200 A400 A700'
	});

	$mdThemingProvider.definePalette( 'pantone199', {
		'50': '#ffdfe7',
		'100': '#ff93ac',
		'200': '#ff5b81',
		'300': '#ff134b',
		'400': '#f40039',
		'500': '#d50032',
		'600': '#b6002b',
		'700': '#980024',
		'800': '#79001c',
		'900': '#5b0015',
		'A100': '#ffdfe7',
		'A200': '#ff93ac',
		'A400': '#f40039',
		'A700': '#980024',
		'contrastDefaultColor': 'light',
		'contrastDarkColors': '50 100 200 A100 A200'
	});

	/**
	*	Definicion de temas
	*/
	$mdThemingProvider.theme( 'usaria-azul' )
	.primaryPalette( 'pantone7711' )
	.accentPalette( 'pantone368' )
	.warnPalette( 'pantone199' );

	$mdThemingProvider.theme( 'usaria-morado' )
	.primaryPalette( 'pantone2607' )
	.accentPalette( 'pantone368' )
	.warnPalette( 'pantone199' );

	$mdThemingProvider.theme( "usaria-amarillo" )
	.primaryPalette( "pantone7549" )
	.accentPalette( "pantone368" )
	.warnPalette( "pantone199" );

	$mdThemingProvider.setDefaultTheme( 'usaria-azul' );

	$urlRouterProvider.otherwise( function( $injector, $location ){
		$http = $injector.get( '$http' );
		$state = $injector.get( '$state' );
		try{
			var hash = $location.path().substr( 1 );
			var splitted = hash.split( '&' );
			var params = {};
			var access_token;
			for (var i = 0; i < splitted.length; i++) {
				var param  = splitted[ i ].split( '=' );
				var key    = param[ 0 ];
				var value  = param[ 1 ];
				if( key === 'access_token' ){
					access_token = value;
					localStorage.setItem( "google_token", access_token );
				}
			}
			$http({
				url: 'https://www.googleapis.com/oauth2/v3/tokeninfo?access_token=' + access_token,
			})
			.then(
				function( respuesta ){
					var verificado = Boolean( respuesta.data.email_verified );
					var correo = respuesta.data.email;
					if( verificado === true ){
						$http({
							url: 'restful_api/crear_token.script.php',
							method: 'POST',
							data:{
								correo: correo
							}
						})
						.then(
							function( respuesta ){
								localStorage.setItem( "usaria_token", respuesta.data );
								$state.go( 'inicio' );
							},
							function( error ){
								$state.go( 'login' );
							}
						);
					}
					else{
						$state.go( 'login' );
					}
				},
				function( error ){
					console.log( 'ERROR autentificando token', error );
					$state.go( 'login' );
				}
			);
		}
		catch ( exception ){
			$state.go( 'login' );
		}
	});

	$stateProvider.
	state( "login", {
		url: "/login",
		templateUrl: "states/login/login.view.html",
		controller: "loginController"
	}).
	state( "layout", {
		url: "",
		templateUrl: "states/layout/layout.view.html",
		controller: "layoutController",
		resolve: {
			estaAutentificado: [ '$http', '$state', function confimar( $http, $state ){
				var access_token = localStorage.getItem( "google_token" );
				$http({
					url: "https://www.googleapis.com/oauth2/v3/tokeninfo?access_token=" + access_token,
				})
				.then(
					function( respuesta ){
						var verificado = Boolean( respuesta.data.email_verified );
						if( verificado === true ){
							return;
						}
						else{
							$state.go( "login" );
						}
					},
					function( error ){
						$state.go( "login" );
					}
				);
			}]
		},
	}).
	state( "inicio", {
		parent: "layout",
		url: '/inicio',
		templateUrl: 'states/inicio/inicio.view.html',
		controller: 'inicioController'
	}).
	state( 'solicitud', {
		parent: 'layout',
		url: '/solicitud/:id_solicitud',
		templateUrl: 'states/solicitud/solicitud.view.html',
		controller: 'solicitudController'
	}).
	state( "cotizacion", {
		parent: "layout",
		url: "/:id_solicitud/cotizacion/",
		templateUrl: "states/cotizacion/cotizacion.view.html",
		controller: "cotizacionController"
	}).
	state( "seleccion", {
		parent: "layout",
		url: "/:id_solicitud/seleccion/",
		templateUrl: "states/seleccion/seleccion.view.html",
		controller: "seleccionController"
	}).
	state( "autorizacion", {
		parent: "layout",
		url: "/:id_solicitud/autorizacion/",
		templateUrl: "states/autorizacion/autorizacion.view.html",
		controller: "autorizacionController"
	}).
	state( "compras", {
		parent: 'layout',
		url: '/:id_solicitud/compras/',
		templateUrl: 'states/compras/compras.view.html',
		controller: 'comprasController'
	});

	// formato del texto de la fecha en el elemento md-datepicker. Cambia el modo gringo al modo comun de Mexico
	$mdDateLocaleProvider.formatDate = function( date ) {
		return moment( date ).format( 'DD-MM-YYYY' );
	};

} ] );
