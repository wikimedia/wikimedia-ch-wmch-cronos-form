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
		<div class="container">

			<form method="post">

				<?php form_action( 'logout' ) ?>

				<p class="flow-text"><?= sprintf(
					__( "Press the button below to logout from %s." ),
					'Cronos'
				) ?></p>

				<p><button type="submit" class="btn waves-effect"><i class="material-icons left">exit_to_app</i><?= __( "Logout" ) ?></button></p>

			</form>
		</div>
