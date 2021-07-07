// Modals

let postID        = cpt_frontend_js_vars.postID;
let dashboardID   = cpt_frontend_js_vars.dashboardID;

// Modal Classes
let cptModal      = document.querySelector( '.cpt-modal' );
let modalScreen   = document.querySelector( '.cpt-modal-screen' );
let modalDismiss  = document.querySelector( '.cpt-modal-dismiss-button' );

// Login Modal
let loginLink     = document.querySelectorAll( '.cpt-login-link' );
let loginModal    = document.querySelector( '#cpt-login' );
let loginPanel    = document.querySelector( '#cpt-login-modal-login' );
let resetPWPanel  = document.querySelector( '#cpt-login-modal-resetpw' );
let goToResetPW   = document.querySelector( '#cpt-login-go-to-resetpw' );
let goToLogin     = document.querySelector( '#cpt-login-go-to-login' );
let loggedIn      = document.querySelector( '#cpt-login-modal-already-logged-in' );

// URL
let baseURL       = [ location.protocol, '//', location.host, location.pathname ].join( '' );
const params      = new URLSearchParams( location.search );


if ( loggedIn && loginLink ) {
  loginLink.forEach( function( e ) {
    e.innerHTML = 'Log Out';
  });
}


if ( ! loggedIn && postID == dashboardID ) {
  showLogin();
}


if ( params.has( 'cpt_login' ) ) {

  switch ( params.get( 'cpt_login' ) ) {

    case 'resetpw':
      showResetPW();
      break;

    case 'login':
    default:
      showLogin();
      break;

  }

}


function showLogin() {

  resetPWPanel.style.display = 'none';
  loginPanel.style.display = 'block';

  loginModal.style.display = 'grid';
  modalScreen.style.display = 'block';

}


function showResetPW() {

  resetPWPanel.style.display = 'block';
  loginPanel.style.display = 'none';

  loginModal.style.display = 'grid';
  modalScreen.style.display = 'block';

}


modalDismiss.addEventListener( 'click', function() {

  cptModal.style.display = 'none';
  modalScreen.style.display = 'none';

  /**
  * Removes the cpt_login, cpt_notice, and password set/reset query parameters
  * from the URL just in case the user tries to bookmark it or copy and paste
  * some reason.
  */
  params.delete( 'cpt_login' );
  params.delete( 'cpt_notice' );
  params.delete( 'key' );
  params.delete( 'login' );

  if ( params.toString().length > 0 ) {
    history.replaceState( {}, '', baseURL + '?' + params );
  } else {
    history.replaceState( {}, '', baseURL );
  }

});


if ( loginLink ) {

  loginLink.forEach( function( e ) {

    e.addEventListener( 'click', function( e ) {
      e.preventDefault();
      showLogin();
    });

  });

}


if ( goToResetPW ) {

  goToResetPW.addEventListener( 'click', function( e ) {
    e.preventDefault();
    showResetPW();
  });

}


if ( goToLogin ) {

  goToLogin.addEventListener( 'click', function( e ) {
    e.preventDefault();
    showLogin();
  });

}


// Notices/Inline Modals
// (Not technically modals, but the code overlaps for efficiency.)

let cptInlineModal      = document.querySelector( '.cpt-inline-modal, .cpt-notice' );
let inlineModalDismiss  = document.querySelector( '.cpt-notice-dismiss-button' );

if ( cptInlineModal && inlineModalDismiss ) {

  inlineModalDismiss.addEventListener( 'click', function() {
    cptInlineModal.style.display = 'none';
  });

}
