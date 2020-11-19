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

$page->printHeader();
?>
	<div class="container">
		<h1>Cronos Calendar</h1>

		<?php /* check if the user has saved the page */ ?>
		<?php if( $page->isEventsPageTitleKnown() ): ?>

			<div class="card-panel green">

				<?php if( $page->hasSaved() ): ?>
					<h2><?= __( "Success!" ) ?></h2>
				<?php endif ?>

				<p class="flow-text"><?= __( "See/Edit your Event:" ) ?></p>

				<p><?= HTML::a(
					$page->getEventsPageURL(),
					$page->getEventsPageTitle(),
					__( "Show on wiki" )
				) ?></p>

				<p><?= __( "Thank you!" ) ?>
			</div>

		<?php endif ?>
		<?php /* end if the user has saved the page */ ?>


		<?php /* check if the user IS NOT probably authenticated */ ?>
		<?php if( $page->isUserUnknown() ): ?>

			<?php template( 'form-login' ) ?>

		<?php /* check if the user IS authenticated but has not saved */ ?>
		<?php elseif( !$page->hasSaved() ): ?>

			<?php
			/**
			 * Note: here we just trust the username in the cookie
			 *
			 * Possible stupid concerns:
			 * 1. «hey look at my computer! it shows "Welcome administrator!" whoa I'm an hacker!!»
			 *    Well, in this page there is nothing to be protected so you can also be "my mother".
			 *    Moreover, when you will save, the other wiki will throw:
			 *       «f**k you, you are not "administrator", you cannot do this edit»
			 *    So even if you play with your cookies you have hacked nothing.
			 /    Anyway this field is XSS safe.
			 */
			?>
			<div class="card-panel">

				<div class="row">
					<div class="col s12 m10">
						<p class="flow-text"><?= sprintf(
							__( "Welcome %s!" ),
							esc_html( $page->getAnnouncedUsername() )
						) ?></p>
					</div>
					<div class="col s12 m2">
						<p><a class="btn-flat waves-effect" href="<?= ROOT ?>/logout.php"><i class="material-icons right">exit_to_app</i><?= __( "Logout" ) ?></a></p>
					</div>
				</div>
			</div>

			<!-- start form create event -->
			<form method="post" class="card-panel">

				<h2><?= __( "Create Event" ) ?></h2>

				<?php form_action( 'create-event' ) ?>

				<div class="row">
					<div class="col s12 m6 input-field">

						<i class="material-icons prefix">edit</i>
						<input type="text" name="event_title" id="event-title" class="validate"<?= value( $page->getUserData( 'event_title' ) ) ?> />
						<label for="event-title"><?= __( "Event Title" ) ?> *</label>

					</div>
				</div>

				<div class="row">

					<div class="col s12 m6 input-field">


						<i class="material-icons prefix">event</i>
						<input type="text" name="event_date_start" id="event-date-start" class="datepicker" required="required" />
						<label for="event-date-start"><?= __( "Start Date" ) ?> *</label>

					</div>

					<div class="col s12 m6 input-field">

						<i class="material-icons prefix">access_time</i>
						<input type="text" name="event_time_start" id="event-time-start" class="timepicker" required="required" />
						<label for="event-time-start"><?= __( "Start Time" ) ?> *</label>

					</div>

				</div>

				<div class="row">

					<div class="col s12 m6 input-field">

						<i class="material-icons prefix">event</i>
						<input type="text" name="event_date_end" id="event-date-end" class="datepicker" />
						<label for="event-date-end"><?= __( "End Date" ) ?></label>

					</div>


					<div class="col s12 m6 input-field">

						<i class="material-icons prefix">access_time</i>
						<input type="text" name="event_time_end" id="event-time-end" class="timepicker" required="required" />
						<label for="event-time-end"><?= __( "End Time" ) ?> *</label>

					</div>

				</div>

				<div class="row">

					<div class="col s12 m6 input-field">

						<i class="material-icons prefix">label</i>
						<select name="event_category" id="event-category" class="icons" required="required">
							<option value="" disabled selected><?= __( "Choose your option" ) ?></option>
							<?php foreach( Category::all() as $category ): ?>
								<option value="<?= $category->getUID() ?>" data-icon="<?= $category->getImageURL() ?>" class="left"><?= esc_html( $category->getName() ) ?> (<?= esc_html( $category->getUID() ) ?>)</option>
							<?php endforeach ?>
						</select>
						<label for="event-category"><?= __( "Category" ) ?> *</label>

					</div>

				</div>

				<div class="row">

					<div class="col s12 input-field">

						<i class="material-icons prefix">insert_link</i>
						<input type="text" placeholder="https://www.example.com/" name="event_url" id="event-url" />
						<label for="event-url"><?= __( "External URL" ) ?></label>

					</div>

				</div>

				<div class="row">

					<div class="col s12 input-field">


						<p>
							<i class="material-icons left">info</i>
							<?= __( "Note: your edit will be published under the terms of Meta-wiki." ) ?>
						</p>

						<p><button type="submit" class="btn-large waves-effect">
							<i class="material-icons right">save</i>
							<?= __( "Save" ) ?></button>
						</p>
					</div>

				</div>

			</form>
			<!-- end form create event -->

		<?php /* end if the user IS probably authenticated - end */ ?>
		<?php endif ?>

	</div>

	<!-- start logout form -->
	<?php if( isset( $known_user ) ): ?>

		<?php template( 'form-logout' ) ?>

	<?php endif ?>
	<!-- end logout form -->

<?php

$page->printFooter();
