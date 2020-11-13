( function( $ ) {

  // Expanders
  $( document ).ready( function(){

    let expanderButtons = document.querySelectorAll( '.cpt-click-to-expand' );

    console.log( expanderButtons );

    if ( expanderButtons.length > 0 ) {

      expanderButtons.forEach ( function( button ){

        button.addEventListener( 'click', function( e ){

        });

        button.nextSibling( '.cpt-expand-this' ){

        }

      });

    }

  });


  // Old Expander Function
  let expandButtonText = $( '.cpt-click-to-expand' ).html();

  $( '.cpt-click-to-expand' ).click( function() {

    let requiredFields = $( this ).next( 'form' )

    $( this ).next( '.cpt-this-expands' ).toggle( 'fast' ).toggleClass( 'cpt-this-is-open' );

    switch ( $( this ).html() ) {

      case expandButtonText:
        $( this ).html( 'Cancel' );
        break;

      case 'Cancel':
      default:
        $( this ).html( expandButtonText );
        break;

    }

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
