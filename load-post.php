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

// register CSS files
register_css( 'cronos', 'static/cronos.css' );

// register some dummy categories in order of appearance
Category::addInitiatives( 'com',    __( "Community" ),                     'Wikimedia Community Logo.svg', 'https://upload.wikimedia.org/wikipedia/commons/7/75/Wikimedia_Community_Logo.svg' );
Category::addInitiatives( 'dat',    __( "Wikidata" ),                      'Wikidata Favicon color.svg', 'https://upload.wikimedia.org/wikipedia/commons/4/4a/Wikidata_Favicon_color.svg' );
Category::add(            'edu',    __( "Wikimedia Education Program" ),   'WikipediaEduBelow.svg', 'https://upload.wikimedia.org/wikipedia/commons/6/62/WikipediaEduBelow.svg' );
Category::addInitiatives( 'libre',  __( "Free Software and Open Source" ), 'Heckert GNU white.svg', 'https://upload.wikimedia.org/wikipedia/commons/2/22/Heckert_GNU_white.svg' );
Category::add(            'osm',      "OpenStreetMap",                     'Openstreetmap logo.svg', 'https://upload.wikimedia.org/wikipedia/commons/b/b0/Openstreetmap_logo.svg' );
Category::add(            'glam',   __( "Wikimedia GLAM Program" ),        'GLAM logo.png', 'https://upload.wikimedia.org/wikipedia/commons/3/34/2019-01-26_GLAM_logo_black_positive_space.svg' );
Category::addInitiatives( 'wmch',   __( "Wikimedia CH" ),                  'WikimediaCHLogo.svg', 'https://upload.wikimedia.org/wikipedia/commons/f/fe/WikimediaCHLogo.svg' );
Category::addInitiatives( 'wbg',    __( "West Bengal Wikimedians" ),       'Logo of West Bengal Wikimedians User Group.svg', 'https://upload.wikimedia.org/wikipedia/commons/d/d4/Logo_of_West_Bengal_Wikimedians_User_Group.svg' );
Category::addInitiatives( 'wmno',   __( "Wikimedia Norge initiatives" ),   'Wikimedia Norge-logo svart nb.svg', 'https://upload.wikimedia.org/wikipedia/commons/d/d0/Wikimedia_Norge-logo_svart_nb.svg' );
Category::addInitiatives( 'wmf',    __( "Wikimedia Foundation" ),          'Wikimedia-logo black.svg', 'https://upload.wikimedia.org/wikipedia/commons/8/8b/Wikimedia-logo_black.svg' );

// this should be an alias of libre
// Category::addInitiatives( 'osi',  __( "Open Source" ),                 'Opensource.svg', 'https://upload.wikimedia.org/wikipedia/commons/thumb/4/42/Opensource.svg/%dpx-Opensource.svg.png' );
