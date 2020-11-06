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

// require some dummy classes
require ABSPATH . '/include/class-CronosHomepage.php';
require ABSPATH . '/include/class-Category.php';

// MaterializeCSS
// https://materializecss.com/
register_js(  'materialize', 'static/materialize/js/materialize.min.js', 'footer' );
register_css( 'materialize', 'static/materialize/css/materialize.min.css' );

// material design icons
register_css( 'material.icons', 'static/material-design-icons/material-icons.css' );

// register JavaScript files
register_js( 'cronos', 'static/cronos.js', 'footer', [
	'materialize',
] );

// register some dummy categories in order of appearance
Category::addInitiatives( 'com',    __( "Community" ),                     'Wikimedia Community Logo.svg', 'https://upload.wikimedia.org/wikipedia/commons/thumb/7/75/Wikimedia_Community_Logo.svg/%dpx-Wikimedia_Community_Logo.svg.png' );
Category::addInitiatives( 'dat',    __( "Wikidata" ),                      'Wikidata Favicon color.svg', 'https://upload.wikimedia.org/wikipedia/commons/thumb/4/4a/Wikidata_Favicon_color.svg/%spx-Wikidata_Favicon_color.svg.png' );
Category::add(            'edu',    __( "Wikimedia Education Program" ),   'WikipediaEduBelow.svg', 'https://upload.wikimedia.org/wikipedia/commons/thumb/6/62/WikipediaEduBelow.svg/%dpx-WikipediaEduBelow.svg.png' );
Category::addInitiatives( 'libre',  __( "Free Software and Open Source" ), 'Heckert GNU white.svg', 'https://upload.wikimedia.org/wikipedia/commons/thumb/2/22/Heckert_GNU_white.svg/%dpx-Heckert_GNU_white.svg.png' );
Category::add(            'osm',      "OpenStreetMap",                     'Openstreetmap logo.svg', 'https://upload.wikimedia.org/wikipedia/commons/thumb/b/b0/Openstreetmap_logo.svg/%dpx-Openstreetmap_logo.svg.png' );
Category::add(            'glam',   __( "Wikimedia GLAM Program" ),        'GLAM logo.png', 'https://upload.wikimedia.org/wikipedia/commons/thumb/0/06/GLAM_logo.png/%dpx-GLAM_logo.png' );
Category::addInitiatives( 'wmch',   __( "Wikimedia CH" ),                  'WikimediaCHLogo.svg', 'https://upload.wikimedia.org/wikipedia/commons/thumb/f/fe/WikimediaCHLogo.svg/%dpx-WikimediaCHLogo.svg.png' );
Category::addInitiatives( 'wmf',    __( "Wikimedia Foundation" ),          'Wikimedia-logo black.svg', 'https://upload.wikimedia.org/wikipedia/commons/thumb/8/8b/Wikimedia-logo_black.svg/%dpx-Wikimedia-logo_black.svg.png' );

// this should be an alias of libre
// Category::addInitiatives( 'osi',  __( "Open Source" ),                 'Opensource.svg', 'https://upload.wikimedia.org/wikipedia/commons/thumb/4/42/Opensource.svg/%dpx-Opensource.svg.png' );
