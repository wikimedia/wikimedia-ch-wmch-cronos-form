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

?>
			<!-- start form login -->
			<form method="post" class="card-panel">

				<?php form_action( 'login' ) ?>

				<div class="row">
					<div class="col s12 m6 input-field">

						<h2><?= __( "Create Event") ?></h2>

						<p class="flow-text"><?= __( "Please login to create an Event.") ?></p>
						<button type="submit" class="btn-large waves-effect"><?= __( "Quick Login" ) ?></button>
					</div>
				</div>
			</form>
			<!-- end form login -->
