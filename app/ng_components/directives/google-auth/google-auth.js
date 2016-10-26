/**
*	@ngdoc module
*	@name google-auth
*/
var module = angular.module( 'google-auth', [] );
/**
*	@ngdoc directive
*	@name googleAuthTestButton
*/
module.directive( 'googleAuthTestButton', googleAuthTestButton );
googleAuthTestButton.$inject = [ '$http' ];
function googleAuthTestButton( $http ){
	var directive = {
		restrict: 'E',
		link: link,
	};

	return directive;

	function link( scope, element, attrs, ctrl, transcludeFn ){
		var client_id = '595821871208-40d63hjmkl9uo5luhog6te62abd2h8hm.apps.googleusercontent.com';
		var prompt_parameter = 'select_account';
		var scope_parameter = 'email';
		var state_parameter = 'profile';
		var redirect_uri = 'http://localhost/webapps/viajesUsaria/app/';

		scope.redirectAuth = function(){
			var auth_url = 'https://accounts.google.com/o/oauth2/v2/auth?' +
				'scope=' + scope_parameter +
				'&state=' + state_parameter +
				'&redirect_uri=' + redirect_uri +
				'&response_type=token' +
				'&prompt=' + prompt_parameter +
				'&client_id=' + client_id;
			window.location.replace( auth_url );
		};
	}
}

/**
*	@ngdoc directive
*	@name googleAuthButton
*/
module.directive( 'googleAuthButton', googleAuthButton );
googleAuthButton.$inject = [ '$http' ];
function googleAuthButton( $http ){
	var directive = {
		restrict: 'E',
		link: link,
	};

	return directive;

	function link( scope, el, attrs, ctrl, transcludeFn ){
		// config the auth call
		var client_id = '595821871208-8qv78br75efk6j5co0hiklguegqn8la2.apps.googleusercontent.com';
		var prompt_parameter = 'select_account';
		var scope_parameter = 'email';
		var state_parameter = 'profile';
		//var redirect_uri = 'http://localhost/webapps/productividadUsaria/app/';
		var redirect_uri = 'http://productividad.usaria.mx';

		scope.triggerAuth = function(){
			var auth_url = 'https://accounts.google.com/o/oauth2/v2/auth?' +
				'scope=' + scope_parameter +
				'&state=' + state_parameter +
				'&redirect_uri=' + redirect_uri +
				'&response_type=token' +
				'&prompt=' + prompt_parameter +
				'&client_id=' + client_id;
			window.location.replace( auth_url );
		}
	}
}
