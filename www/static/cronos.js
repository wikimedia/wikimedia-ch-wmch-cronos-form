/**
 * Copyright (C) 2020, 2021 Valerio Bozzolan
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @license magnet:?xt=urn:btih:1f739d935676111cfff4b4693e3816e664797050&dn=gpl-3.0.txt
 */
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
