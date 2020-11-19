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

// enqueue default stylesheet
enqueue_js( 'cronos' );

template( 'header' );
?>
	<div class="container">
		<h1>Wikimedia CH Cronos &mdash; <?= __( "Credits" ) ?></h1>

		<div class="card-panel">
			<h2><?= __( "Image credits" ) ?></h2>
			<p class="flow-text"><?= __( "Visit the related page to see the image author and the license." ) ?></p>
			<ul class="collection">
				<?php foreach( Category::all() as $category ): ?>
					<li class="collection-item"><a href="<?= esc_attr( $category->getCommonsURL() ) ?>"><img src="<?= esc_attr( $category->getImageURL() ) ?>" /> <?= $category->getFilename() ?></a></li>
				<?php endforeach ?>
			</ul>
		</div>

		<div class="card-panel">
			<h3><?= __( "Nerd stuff" ) ?></h3>
			<p><?= __( "This block of information can be used inside your wiki to inherit these official categories. Ignore this part if you are not a truly nerd, to avoid brain explosion." ) ?></p>
			<textarea class="materialize-textarea"><?php

				echo "local CRONOS_CATEGORIES = {\n";

				// print some Lua code
				foreach( Category::all() as $category ) {
					printf(
						"\t['%1\$s'] = { uid = '%1\$s', name = '%2\$s', filename = 'File:%3\$s' },\n",
						$category->getUID(),
						$category->getName(),
						$category->getFilename()
					);
				}

				echo "}";
			?></textarea>
		</div>

		<div class="divider"></div>

		<p><a href="<?= ROOT ?>/" class="btn waves-effect"><?= __( "Home" ) ?></a></p>
	</div>

<?php

template( 'footer' );
