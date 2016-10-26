/**
*	@ngdoc module
*/
angular.module( 'google-sign-in', [] ).directive( 'googleSignInButton', googleSignInButton );
/**
*	@ngdoc directive
*/
googleSignInButton.$inject = [ '$rootScope', '$window' ];
function googleSignInButton( $rootScope, $window ) {
	return{
		restrict: 'E',
		transclude: true,
		template: '<span></span>',
		replace: true,
		link: function( iScope, iElement, iAttrs, iCtrl, transcludeFn) {

			// Asynchronously load the Google plus API
			var po = document.createElement('script'); po.type = 'text/javascript'; po.async = true;
			po.src = 'https://apis.google.com/js/client:plusone.js';
			var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(po, s);

			var config = {
				callback: 'signInCallback', // Function handling the callback.
				clientid: iAttrs.clientId + '.apps.googleusercontent.com', // remember to set your client ID from the google developer console to attribute clientid
				requestvisibleactions: 'http://schemas.google.com/AddActivity',
				scope: 'https://www.googleapis.com/auth/plus.login https://www.googleapis.com/auth/userinfo.email',
				cookiepolicy: 'single_host_origin',
				height: 'standard',
				width: 'wide',
			};

			var userObj = {};

			// Default language for the button text is english
			// More languages at https://developers.google.com/+/web/api/supported-languages
			iAttrs.$observe('language', function(value){
				$window.___gcfg = {
					lang: value ? value : 'en'
				};
			});

			userDataCallback = function( data ) {
				// Display data to modify the user object
				// console.log( data );
				if( data.domain === iAttrs.companyDomain ){
						// if the email domain is the same as your company's
						userObj.email = data.emails[0].value;
						userObj.name = data.displayName;
						// broadcast for use in controller
						$rootScope.$broadcast( 'user-auth-success', userObj );
				}
				else{
					// is not a company user
					// you can handle with different ways
					$rootScope.$broadcast( 'user-auth-failure', userObj );
				}
			}

			getUserData = function() {
				gapi.client.request({
					path: 'https://www.googleapis.com/plus/v1/people/me',
					method: 'GET',
					callback: userDataCallback,
				});
			}

			signInCallback = function ( authResult ) {
				// Do a check if authentication has been successful.
				if ( authResult && authResult.access_token ) {
					// store token for server-side verification
					userObj.token = authResult.id_token;
					getUserData();
				}
				else {
					$rootScope.$broadcast( 'user-auth-failure', authResult );
				}
			};

			// Render the sign in button.
			iScope.renderSignInButton = function() {
				transcludeFn( function( element, tScope ) {
					po.onload = function() {
						if ( element.length ) {
							iElement.append( element );
						}
						gapi.signin.render( iElement[0], config );
		          };
		        });
			}

			iScope.renderSignInButton();

		}
	}
}
