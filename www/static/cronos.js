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

			// avoid to select an end date before the start date
			var start = M.Datepicker.getInstance( document.getElementById( 'event-date-start' ) );
			var stop  = M.Datepicker.getInstance( document.getElementById( 'event-date-end'   ) );
			start.options.onSelect = function( date ) {
				stop.options.minDate = date;
			};
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

