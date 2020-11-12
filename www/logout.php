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

// load the configuration file and autoload classes
require 'load.php';

$page = new CronosHomepage();

// if logged-out, redirect to homepage
if( !$page->getAnnouncedUsername() ) {
	http_redirect( '' );
}

$page->printHeader();
?>
	<div class="container">
		<h1>Wikimedia CH Cronos</h1>

		<div class="card-panel">
			<?php template( 'form-logout' ) ?>
		</div>
	</div>
<?php

$page->printFooter();
