( function( $ ) {

  $( '#cpt-admin .cpt-click-to-expand' ).click( function() {

    switch ( $( this ).html() ) {

      case 'Edit Client':
        $( this ).html( 'Cancel' );
        break;

      case 'Cancel':
      default :
        $( this ).html( 'Edit Client' );
        break;

    }

  });

})( jQuery );
