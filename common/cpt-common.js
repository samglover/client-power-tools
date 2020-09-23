( function( $ ) {

  // Expander
  $( '.cpt-click-to-expand' ).click( function() {
    $( this ).next( '.cpt-this-expands' ).toggle( 'fast' );
  });

  // Adjust anchor targets.
  $( document ).ready( function(){

    let target = $( location.hash );

    if ( target.length > 0 ) {

      let adminBar  = $ ( '#wpadminbar' ).outerHeight();
      let offset    = target.offset();
      let scrollTo  = offset.top - ( 20 + adminBar );

      $( 'html, body' ).animate( { scrollTop: scrollTo } );

    }

  });

})( jQuery );
