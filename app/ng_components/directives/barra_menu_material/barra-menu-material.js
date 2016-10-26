angular.module( 'barra-menu-material', [] ).directive( 'barraMenuMaterial', barraMenuMaterial );
barraMenuMaterial.$inject = [ '$mdSticky', '$compile', '$mdSidenav', '$q', '$http', '$state' ];
function barraMenuMaterial( $mdSticky, $compile, $mdSidenav, $q, $http, $state ){

	var TEMPLATE = '<div flex layout layout-align="start center" class="md-barra-menu-contenedor">'+
		'<md-toolbar class="md-barra-menu" layout layout-padding layout-align="start center" md-whiteframe="5">'+
			'<md-button class="md-icon-button" ng-click="abrirMenu()">'+
				'<md-icon md-menu-origin md-font-set="material-icons">menu</md-icon>'+
			'</md-button>'+
			'<span class="md-subhead">{{ titulo }}</span>'+
			'<span flex></span>'+
			'<span class="md-body-1">'+
				'{{ usuario.correo }}'+
			'</span>'+
		'</md-toolbar>'+
		'<md-sidenav md-component-id="menu-usuario" class="md-sidenav-left" md-whiteframe="4">'+
			'<md-toolbar class="md-primary">'+
				'<div layout layout-align="start center" layout-padding>'+
					'<img class="icono-usario" src="{{ usuario.foto }}" />'+
					'<div flex layout="column" layout-align="center start">'+
						'<span class="md-subhead">{{ usuario.nombre_completo }}</span>'+
						'<span class ="md-caption">{{ usuario.correo }}</span>'+
					'</div>'+
				'</div>'+
			'</md-toolbar>'+
			'<md-content layout="column" layout-align="center start" layout-padding>'+
				'<md-button ui-sref="inicio">'+
					'<md-icon md-font-set="material-icons">home</md-icon>'+
					'inicio'+
				'</md-button>'+
				'<md-button ui-sref="solicitud">'+
					'<md-icon md-font-set="material-icons">assignment</md-icon>'+
					'Crear solicitud'+
				'</md-button>'+
				'<md-button ng-click=" cerrarSesion()">'+
					'<md-icon md-font-set="material-icons">bug_report</md-icon>'+
					'Reportar error'+
				'</md-button>'+
				'<md-button ng-click=" cerrarSesion()">'+
					'<md-icon md-font-set="material-icons">exit_to_app</md-icon>'+
					'Salir'+
				'</md-button>'+
			'</md-content>'+
		'</md-sidenav>'+
	'</div>';

	var directive = {
		restrict: 'E',
		scope: {
			usuario: '=sesion',
			titulo: '=titulo'
		},
		template: TEMPLATE,
		compile: compile,
	};

	return directive;

	function compile( element, attrs ){
		if( attrs.tema !==  undefined ){
			element.attr( 'md-theme', attrs.tema );
		}

		return{
			pre: function( scope, element,attrs, ctrl, transcludeFn ){

			},
			post: function( scope, element, attrs, ctrl, transcludeFn ){

				scope.abrirMenu = function(){
					$mdSidenav( 'menu-usuario' ).toggle();
				}

				scope.cerrarSesion = function(){
					localStorage.removeItem( "usaria_token" );
					$state.go( "login" );
					/*
					$http({
						url: 'restful_api/destruir_sesion.php',
					})
					.then(
						function( response ){
							$state.go( 'login' );
						},
						function( error ){
							console.log( error );
						}
					);
					*/
				}

			}
		}
	}

}
