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

// relative URL of the homepage of the project
define( 'ROOT', '/cronos' );

// keep this as-is
define( 'ABSPATH', __DIR__ );

// composer require mediawiki/oauthclient
// https://gerrit.wikimedia.org/g/mediawiki/oauthclient-php
require __DIR__ . '/../oauthclient-php/autoload.php';

// load the suckless-php framework
// https://gitpull.it/source/suckless-php/
require __DIR__ . '/../suckless-php/load.php';

// at this point your load-post.php file will be automagically required
