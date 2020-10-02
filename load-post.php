<?php
# Copyright (C) 2020 Valerio Bozzolan
#
# This program is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 3 of the License, or
# (at your option) any later version.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU Affero General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program.  If not, see <http://www.gnu.org/licenses/>.

// this file is called after the suckless-php/load.php file

// require some dummy functions
require ABSPATH . '/include/functions.php';

// MaterializeCSS
// https://materializecss.com/
register_js(  'materialize', 'static/materialize/js/materialize.min.js', 'footer' );
register_css( 'materialize', 'static/materialize/css/materialize.min.css' );

// register JavaScript files
register_js( 'cronos', 'static/cronos.js', 'footer', [
	'materialize',
] );
