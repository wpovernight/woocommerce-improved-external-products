jQuery( function( $ ) {
	$( '.wpo-iepp-pro-notice' ).on( 'click', '.notice-dismiss', function( event ) {
		event.preventDefault();
  		window.location.href = $( '.wpo-iepp-dismiss' ).attr( 'href' );
	} );

	$( '.hidden-input' ).on( 'click',function() {
		$( this ).closest( '.hidden-input' ).prev( '.pro-feature' ).show( 'slow' );
		$( this ).closest( '.hidden-input' ).hide();
	} );

	$( 'input.wcbulkorder-disabled' ).attr( 'disabled', true );
} );