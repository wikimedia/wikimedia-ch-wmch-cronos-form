( function() {
	document.addEventListener( 'DOMContentLoaded', function() {

		// initialize date pickers
		( function() {
			var elems = document.querySelectorAll( '.datepicker' );
			var instances = M.Datepicker.init( elems, {
				format: 'yyyy-mm-dd',
				firstDay: 1,
				onSelect: function( date ) {

				}
			} );
		} )();

		// initialize time pickers
		( function() {
			var elems = document.querySelectorAll( '.timepicker' );
			var instances = M.Timepicker.init( elems, {
				twelveHour: false,
			} );
		} )();
	} );
} )();
