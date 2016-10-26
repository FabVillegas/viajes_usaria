var module = angular.module( "google-auth", [] );
module.directive( "googleAuthTestButton", googleAuthTestButton );
googleAuthTestButton.$inject = [ "$http" ];
function googleAuthTestButton( $http ){
	var directive = {
		restrict: "E",
		link: link,
	};

	return directive;

	function link( scope, element, attrs, ctrl, transcludeFn ){
		var client_id = "212275670360-uq6kjfdho1ateovnfffs7grrm5g0uvm5.apps.googleusercontent.com";
		var prompt_parameter = "select_account";
		var scope_parameter = "email";
		var state_parameter = "profile";
		var redirect_uri = "http://localhost/webapps/viajesUsaria/app/";

		scope.redirectAuth = function(){
			var auth_url = "https://accounts.google.com/o/oauth2/auth?" +
				"scope=" + scope_parameter +
				"&state=" + state_parameter +
				"&redirect_uri=" + redirect_uri +
				"&response_type=token" +
				"&prompt=" + prompt_parameter +
				"&client_id=" + client_id;
			window.location.replace( auth_url );
		};
	}
}

module.directive( "googleAuthButton", googleAuthButton );
googleAuthButton.$inject = [ "$http" ];
function googleAuthButton( $http ){
	var directive = {
		restrict: "E",
		link: link,
	};

	return directive;

	function link( scope, el, attrs, ctrl, transcludeFn ){
		var client_id = "212275670360-uq6kjfdho1ateovnfffs7grrm5g0uvm5.apps.googleusercontent.com";
		var prompt_parameter = "select_account";
		var scope_parameter = "email";
		var state_parameter = "profile";
		var redirect_uri = "http://viajes.usaria.mx";

		scope.triggerAuth = function(){
			var auth_url = "https://accounts.google.com/o/oauth2/auth?" +
				"scope=" + scope_parameter +
				"&state=" + state_parameter +
				"&redirect_uri=" + redirect_uri +
				"&response_type=token" +
				"&prompt=" + prompt_parameter +
				"&client_id=" + client_id;
			window.location.replace( auth_url );
		}
	}
}
