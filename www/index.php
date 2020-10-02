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

// OAuth PHP library stuff
use MediaWiki\OAuthClient\ClientConfig;
use MediaWiki\OAuthClient\Consumer;
use MediaWiki\OAuthClient\Client;
use MediaWiki\OAuthClient\Token;

// boz-mw stuff
use web\MediaWikis;

// various cookies
// we use cookies instead of PHP sessions because in this way the application is stateless
// moreover, Wikimedia Toolforge's NFS storage is not that quick :^)
define( 'COOKIE_OAUTH_REQUEST_TOKEN_KEY',    'oa_rqtk_key'       );
define( 'COOKIE_OAUTH_REQUEST_TOKEN_SECRET', 'oa_rqtk_sec'       );
define( 'COOKIE_OAUTH_ACCESS_TOKEN_KEY',     'oa_accsstk_key'    );
define( 'COOKIE_OAUTH_ACCESS_TOKEN_SECRET',  'oa_accsstk_secret' );
define( 'COOKIE_OAUTH_ACCESS_NONCE',         'oa_accss_nnc'      );
define( 'COOKIE_WIKI_USERNAME',              'wiki_user'         );
define( 'COOKIE_WIKI_CSRF',                  'wiki_csrf'         );

// enqueue these JavaScript files
enqueue_js( 'cronos' );

// wiki API endpoint URL
$WIKI_API_URL = 'https://meta.wikimedia.org/w/api.php';

// Wikimedia Commons API
// see boz-mw
$commons = MediaWikis::findFromUID( 'commonswiki' );

// OAuth configuration
// see oauthclient-php
$conf = new ClientConfig( 'https://meta.wikimedia.org/w/index.php?title=Special:OAuth' );
$conf->setConsumer( new Consumer( OAUTH_CONSUMER_KEY, OAUTH_CONSUMER_SECRET ) );
$client = new Client( $conf );

// user identity
$identity = null;

// read submitted parameters
$event_title      = $_POST['event_title']      ?? null;
$event_date_start = $_POST['event_date_start'] ?? null;
$event_date_end   = $_POST['event_date_end']   ?? null;
$event_time_start = $_POST['event_time_start'] ?? null;
$event_time_end   = $_POST['event_time_end']   ?? null;
$event_url        = $_POST['event_url']        ?? null;

$saved = false;
$events_page_title = null;

// Phase 1
// check if the user submitted the login form and have to be redirected to the remote OAuth login form
if( is_action( 'login' ) ) {

	// thanks for submitting the form!
	list( $auth_url, $request_token ) = $client->initiate();

	// remember the OAuth request token
	my_set_cookie( COOKIE_OAUTH_REQUEST_TOKEN_KEY,    $request_token->key    );
	my_set_cookie( COOKIE_OAUTH_REQUEST_TOKEN_SECRET, $request_token->secret );

	// here we go!
	http_redirect( $auth_url );
}

// Phase 2
// check if the user is coming back from the remote OAuth login form
if( isset( $_GET['oauth_verifier'] ) ) {

	// welcome back, user, what was the original OAuth request token?
	$request_token_key = $_COOKIE[ COOKIE_OAUTH_REQUEST_TOKEN_KEY    ] ?? null;
	$request_token_sec = $_COOKIE[ COOKIE_OAUTH_REQUEST_TOKEN_SECRET ] ?? null;
	if( !$request_token_key || !$request_token_key ) {
		throw new Exception( "missing request tokens" );
	}

	// rebuild the OAuth request token
	$request_token = new Token( $request_token_key, $request_token_sec );

	// check the OAuth access token
	$access_token = $client->complete( $request_token, $_GET['oauth_verifier'] );
	$identity     = $client->identify( $access_token );

	// clear old cookies now unuseful
	my_unset_cookie( COOKIE_OAUTH_REQUEST_TOKEN_KEY );
	my_unset_cookie( COOKIE_OAUTH_REQUEST_TOKEN_SECRET );

	// save the access token to rebuild it later
	my_set_cookie( COOKIE_OAUTH_ACCESS_TOKEN_KEY,    $access_token->key );
	my_set_cookie( COOKIE_OAUTH_ACCESS_TOKEN_SECRET, $access_token->secret );

	// save other information
	my_set_cookie( COOKIE_OAUTH_ACCESS_NONCE, $identity->nonce );
	my_set_cookie( COOKIE_WIKI_USERNAME,      $identity->username );

	// POST -> redirect -> GET
	http_redirect( '' );
}

// eventually build the OAuth access token
$access_token = null;
if( isset(
	$_COOKIE[ COOKIE_OAUTH_ACCESS_TOKEN_KEY ],
	$_COOKIE[ COOKIE_OAUTH_ACCESS_TOKEN_SECRET ]
) ) {
	$access_token = new Token(
		$_COOKIE[ COOKIE_OAUTH_ACCESS_TOKEN_KEY ],
		$_COOKIE[ COOKIE_OAUTH_ACCESS_TOKEN_SECRET ]
	);
}

// eventually retrieve the access token
$csrf_token = $_COOKIE[ COOKIE_WIKI_CSRF ] ?? null;
if( !$csrf_token && $access_token ) {

	// retrieve wiki CSRF token
	$response_tokens =
		$client->makeOAuthCall(
			$access_token,
			http_build_get_query( $WIKI_API_URL, [
				'action' => 'query',
				'format' => 'json',
				'meta'   => 'tokens',
				'type'   => 'csrf',
			] )
		);

	// no CSRF token no party
	$csrf_token = @json_decode( $response_tokens )->query->tokens->csrftoken ?? null;
	if( !$csrf_token ) {
		throw new Exception( "cannot retrieve CSRF token from wiki" );
	}

	// remember the CSRF token for future requests
	my_set_cookie( COOKIE_WIKI_CSRF, $csrf_token );
}

// Phase 3
if( $access_token && is_action( 'create-event' ) ) {

	// no token no party
	if( !$csrf_token ) {
		throw new Exception( "missing CSRF token from session" );
	}

	// no dates no party
	if( empty( $event_date_start ) ) {
		throw new Exception( "missing date start" );
	}

	$events_page_title = "Meta:Cronos/Events/$event_date_start";

	// split date in parts
	$event_date_start_parts = explode( '-', $event_date_start );
	if( count( $event_date_start_parts ) !== 3 ) {
		throw new Exception( "bad start date" );
	}
	list( $start_y, $start_m, $start_d ) = $event_date_start_parts;

	// text of the page
	$text_create = sprintf(
		'{{Cronos day|%d|%d|%d}}',
		$start_y,
		$start_m,
		$start_d
	);

	$template_event =
		"\n" .
		"{{Cronos event\n" .
		"| title = $event_title\n" .
		"| when  = $event_date_start $event_time_start\n" .
		"| end   = $event_date_end $event_time_end\n" .
		"| url   = $event_url\n" .
		"}}";

	$text_create .= $template_event;

	// create the page
	$response_raw = $client->makeOAuthCall( $access_token, $WIKI_API_URL, $isPost = true, [
		'action'     => 'edit',
		'createonly' => 1,
		'format'     => 'json',
		'title'      => $events_page_title,
		'summary'    => "New event: $event_title",
		'text'       => $text_create,
		'token'      => $csrf_token,
	] );

	// eventually edit the page
	$response = @json_decode( $response_raw );
	$result = $response->edit->result ?? null;
	$saved = $result === 'Success';

	// check if the page already exist and append text
	if( !$saved ) {
		$error_code = $response->error->code ?? null;
		if( $error_code === 'articleexists' ) {

			// create the page
			$response_raw = $client->makeOAuthCall( $access_token, $WIKI_API_URL, $isPost = true, [
				'action'     => 'edit',
				'nocreate'   => 1,
				'format'     => 'json',
				'title'      => $events_page_title,
				'summary'    => "New event: $event_title",
				'appendtext' => $template_event,
				'token'      => $csrf_token,
			] );

			// assume success
			$saved = true;
		}
	}
}

// check if the user wants to logout
if( is_action( 'logout' ) ) {

	// clear all cookies
	my_unset_cookie( COOKIE_OAUTH_REQUEST_TOKEN_KEY );
	my_unset_cookie( COOKIE_OAUTH_REQUEST_TOKEN_SECRET );
	my_unset_cookie( COOKIE_OAUTH_ACCESS_TOKEN_KEY );
	my_unset_cookie( COOKIE_OAUTH_ACCESS_TOKEN_SECRET );
	my_unset_cookie( COOKIE_OAUTH_ACCESS_NONCE );
	my_unset_cookie( COOKIE_WIKI_USERNAME );
	my_unset_cookie( COOKIE_WIKI_CSRF );

	// POST -> redirect -> GET
	http_redirect( '' );
}

// check if we know who you declare to be
$known_user = $_COOKIE[ COOKIE_WIKI_USERNAME ];

template( 'header' );
?>
	<div class="container">
		<h1>Wikimedia CH Cronos</h1>

		<?php /* check if the user has saved the page */ ?>
		<?php if( $events_page_title ): ?>

			<div class="card-panel green">

				<?php if( $saved ): ?>
					<h2><?= __( "Success!" ) ?></h2>
				<?php endif ?>

				<p class="flow-text"><?= __( "See/Edit your Event:" ) ?></p>

				<p><?= HTML::a(
					"https://meta.wikimedia.org/wiki/$events_page_title",
					$events_page_title,
					__( "Events page" )
				) ?></p>

				<p><?= __( "Thank you!" ) ?>
			</div>

		<?php endif ?>
		<?php /* end if the user has saved the page */ ?>


		<?php /* check if the user IS NOT probably authenticated */ ?>
		<?php if( empty( $known_user ) ): ?>

			<?php template( 'form-login' ) ?>

		<?php /* check if the user IS authenticated but has not saved */ ?>
		<?php elseif( !$saved ): ?>

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
				<p class="flow-text"><?= sprintf(
					__( "Welcome %s!" ),
					esc_html( $known_user )
				) ?></p>
			</div>

			<!-- start form create event -->
			<form method="post" class="card-panel">

				<h2><?= __( "Create Event" ) ?></h2>

				<?php form_action( 'create-event' ) ?>

				<div class="row">
					<div class="col s12 m6 input-field">

						<input type="text" name="event_title" id="event-title" class="validate" required="required"<?= value( $event_title ) ?> />
						<label for="event-title"><?= __( "Event Title" ) ?></label>

					</div>
				</div>

				<div class="row">

					<div class="col s12 m6 input-field">

						<input type="text" name="event_date_start" id="event-date-start" class="datepicker" required="required" />
						<label for="event-date-start"><?= __( "Start Date" ) ?></label>

					</div>

					<div class="col s12 m6 input-field">

						<input type="text" name="event_time_start" id="event-time-start" class="timepicker" required="required" />
						<label for="event-time-start"><?= __( "Start Time" ) ?></label>

					</div>

				</div>

				<div class="row">

					<div class="col s12 m6 input-field">

						<input type="text" name="event_date_end" id="event-date-end" class="datepicker" data-copydate="event-time-end" required="required" />
						<label for="event-date-end"><?= __( "End Date" ) ?></label>

					</div>


					<div class="col s12 m6 input-field">

						<input type="text" name="event_time_end" id="event-time-end" class="timepicker" required="required" />
						<label for="event-time-end"><?= __( "End Time" ) ?></label>

					</div>

				</div>

				<div class="row">

					<div class="col s12 input-field">

						<input type="text" placeholder="https://" name="event_url" id="event-url" required="required" />
						<label for="event-url"><?= __( "External URL" ) ?></label>

					</div>

				</div>

				<div class="row">

					<div class="col s12 m6 input-field">

						<button type="submit" class="btn waves-effect"><?= __( "Save Event" ) ?></button>

						<p><?= __( "Note: your edit will be published under the terms of Meta-wiki. Do not press if unsure." ) ?></p>

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

template( 'footer' );
