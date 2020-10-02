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

/*
 * Please save this file as 'load.php'
 */

// OAUTH stuff
// See [[mw:Extension:OAuth]]
define( 'OAUTH_CONSUMER_KEY', x'insert-here-key' );
define( 'OAUTH_CONSUMER_SECRET', 'insert-here-secret' );

// suckless-php information
define( 'ABSPATH', __DIR__ );
define( 'ROOT', '/cronos' );

// composer require mediawiki/oauthclient
require '/my/path/to/oauthclient-php-vendor/autoload.php';

// load the boz-mw framework
// https://gitpull.it/source/boz-mw/
require '/my/path/to/boz-mw/autoload.php';

// load the suckless-php framework
// https://gitpull.it/source/suckless-php/
require '/my/path/to/suckless-php/load.php';

// at this point your load-post.php file will be automagically required
