( function( $ ) {

  $( '#cpt-admin .cpt-click-to-expand' ).click( function() {

    switch ( $( this ).html() ) {

      case 'Edit Client':
        $( this ).html( 'Cancel' );
        break;

      case 'Cancel':
      default:
        $( this ).html( 'Edit Client' );
        break;

    }

  });


  // Admin Delete-Client Warning
  let deleteClientLink    = $( '#cpt-delete-client-link' );

  let cptAdminModal       = $( '.cpt-admin-modal' );
  let adminModalScreen    = $( '.cpt-admin-modal-screen' );
  let deleteCancelButton  = $( '.cpt-cancel-delete-client' );

  deleteClientLink.click( function() {

    cptAdminModal.show();
    adminModalScreen.show();

  });

  deleteCancelButton.click( function() {

    cptAdminModal.hide();
    adminModalScreen.hide();

  });


})( jQuery );
