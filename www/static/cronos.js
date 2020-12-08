( function() {

	function addHiddenInput( form, name, value ) {
		var input = document.createElement( 'input' );
		input.type = 'hidden';
		input.name = name;
		input.value = value;
		form.appendChild( input );
	};

	// on document ready
	document.addEventListener( 'DOMContentLoaded', function() {

		// DOM elements
		var form = document.getElementById( 'cronos-add-event-form' );
		var tagPickerEl = document.getElementById( 'cronos-tag-picker' );
		var tagPicker = M.Chips.init( tagPickerEl, { } );

		// initialize date pickers
		( function() {
			var elems = document.querySelectorAll( '.datepicker' );
			var instances = M.Datepicker.init( elems, {
				format: 'yyyy-mm-dd',
				firstDay: 1,
				onSelect: function( date ) {

				}
			} );

			if( instances.length ) {
				var start = M.Datepicker.getInstance( document.getElementById( 'event-date-start' ) );
				var stop  = M.Datepicker.getInstance( document.getElementById( 'event-date-end'   ) );

				// avoid to select an end date before the start date
				start.options.onSelect = function( date ) {
					stop.options.minDate = date;
				};
			}
		} )();

		// initialize time pickers
		( function() {
			var elems = document.querySelectorAll( '.timepicker' );
			var instances = M.Timepicker.init( elems, {
				twelveHour: false,
			} );
		} )();

		// initialize the select boxes
		( function() {
			var elems = document.querySelectorAll( 'select' );
			var instances = M.FormSelect.init( elems );
		} )();

		// on form submit inject the chips in as hidden inputs
		// damn MaterializeCSS
		if( form ) {
			form.addEventListener( 'submit', function() {

				var tags = [];
				var chips = tagPicker.chipsData;
				for( var i = 0; i < chips.length; i++ ) {
					tags.push( chips[i].tag );
				}

				var tagsComma = tags.join( ', ' );

				// push this input Tag
				addHiddenInput( form, 'event_tags', tagsComma );
			} );
		}
	} );

} )();
