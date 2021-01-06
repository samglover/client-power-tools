( function( $ ) {

  // Modals
  $( document ).ready( function(){

    let postID        = cpt_frontend_js_vars.postID;
    let dashboardID   = cpt_frontend_js_vars.dashboardID;

    // Modal Classes
    let cptModal      = $( '.cpt-modal' );
    let modalScreen   = $( '.cpt-modal-screen' );
    let modalDismiss  = $( '.cpt-modal-dismiss-button' );

    // Login Modal
    let loginLink     = $( '.cpt-login-link' );
    let loginModal    = $( '#cpt-login' );
    let loginPanel    = $( '#cpt-login-modal-login' );
    let resetPWPanel  = $( '#cpt-login-modal-resetpw' );
    let goToResetPW   = $( '#cpt-login-go-to-resetpw' );
    let goToLogin     = $( '#cpt-login-go-to-login' );
    let loggedIn      = $( '#cpt-login-modal-already-logged-in' );

    // URL
    let baseURL       = [ location.protocol, '//', location.host, location.pathname ].join( '' );
    const params      = new URLSearchParams( location.search );


    if ( loggedIn.length > 0 ) {
      loginLink.html( 'Log Out' );
    }


    if ( ! loggedIn.length > 0 && postID == dashboardID ) {
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

      resetPWPanel.hide();
      loginPanel.show();

      loginModal.show();
      modalScreen.show();

    }


    function showResetPW() {

      resetPWPanel.show();
      loginPanel.hide();

      loginModal.show();
      modalScreen.show();

    }


    modalDismiss.click( function() {

      cptModal.hide( 95 );
      modalScreen.hide();

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


    loginLink.click( function( e ) {
      e.preventDefault();
      showLogin();
    });


    goToResetPW.click( function( e ) {
      e.preventDefault();
      showResetPW();
    });


    goToLogin.click( function( e ) {
      e.preventDefault();
      showLogin();
    });

  });


  // Notices/Inline Modals
  // (Not technically modals, but the code overlaps for efficiency.)
  $( document ).ready( function(){

    let cptInlineModal  = $( '.cpt-inline-modal, .cpt-notice' );
    let modalDismiss    = $( '.cpt-notice-dismiss-button' );

    modalDismiss.click( function() {
      cptInlineModal.hide( 95 );
    });

  });


  // Navigation
  $( document ).ready( function() {

    let currentPageID = cpt_frontend_js_vars.postID;
    let dashboardID   = cpt_frontend_js_vars.dashboardID;

    let menuItems = document.querySelectorAll( '.cpt-nav-menu-item' );

    // Figure out how to decide what page we're on.

  });

})( jQuery );
