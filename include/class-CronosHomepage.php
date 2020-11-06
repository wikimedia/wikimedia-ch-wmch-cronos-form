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

class CronosHomepage {

	/**
	 * Check if the user has submitted the form
	 */
	private $saved = false;

	/**
	 * OAuth client
	 */
	private $client;

	/**
	 * Access token
	 */
	private $accessToken;

	/**
	 * The wiki API URL
	 *
	 * @var string
	 */
	private $wikiApi = 'https://meta.wikimedia.org/w/api.php';

	/**
	 * Submitted form data
	 */
	private $post = [];

	/**
	 * Title of the events page of the current date
	 */
	private $eventsPageTitle;

	/**
	 * Constructor
	 */
	public function __construct() {

		// OAuth configuration
		// see oauthclient-php
		$conf = new ClientConfig( 'https://meta.wikimedia.org/w/index.php?title=Special:OAuth' );
		$conf->setConsumer( new Consumer( OAUTH_CONSUMER_KEY, OAUTH_CONSUMER_SECRET ) );
		$this->client = new Client( $conf );

		// Wikimedia Commons API
		// see boz-mw
		$commons = MediaWikis::findFromUID( 'commonswiki' );

		$events_page_title = null;

		// Phase 1
		// check if the user submitted the login form and have to be redirected to the remote OAuth login form
		if( is_action( 'login' ) ) {

			$this->tryOAuthLogin();
		}

		// check if the user wants to logout
		if( is_action( 'logout' ) ) {

			$this->logout();

		}

		// Phase 2
		// check if the user is coming back from the remote OAuth login form
		if( isset( $_GET['oauth_verifier'] ) ) {

			$this->receiveOAuthResponse();

		}

		// prepare the OAuth access token
		$this->prepareOAuthAccessToken();

		// Phase 3: save
		if( $this->accessToken && is_action( 'create-event' ) ) {

			$this->createEvent();
		}
	}

	public function isEventsPageTitleKnown() {
		return isset( $this->eventsPageTitle );
	}

	public function getEventsPageTitle() {
		return $this->eventsPageTitle;
	}

	public function getEventsPageURL() {
		return sprintf(
			'https://meta.wikimedia.org/wiki/%s',
			urlencode( $this->getEventsPageTitle() )
		);
	}

	public function hasSaved() {
		return $this->saved;
	}

	public function isUserUnknown() {
		return empty( $_COOKIE[ COOKIE_WIKI_USERNAME ] );
	}

	public function getAnnouncedUsername() {
		return $_COOKIE[ COOKIE_WIKI_USERNAME ] ?? null;
	}

	/**
	 * Print the website header
	 */
	public function printHeader() {

		enqueue_js( 'cronos' );
		enqueue_css( 'material.icons' );

		template( 'header' );

	}

	/**
	 * Print the website footer
	 */
	public function printFooter() {

		template( 'footer' );

	}

	/**
	 * Get a submitted information
	 *
	 * @param string $key
	 * @param string $default_value
	 */
	public function getPOST( $key, $default_value = null ) {

		// eventually receive submitted data
		if( !array_key_exists( $key, $this->post ) ) {

			// technically it's possible to submit arrays (asd[]=1) so let's clean
			if( isset( $_POST[ $key ] ) && is_string( $_POST[ $key ] ) ) {
				$this->post[ $key ] = $_POST[ $key ];
			}
		}

		return $this->post[ $key ] ?? $default_value;
	}

	/**
	 * Forget this OAuth session
	 */
	private function logout() {

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

	/**
	 * Redirect to the OAuth login page
	 */
	private function tryOAuthLogin() {

		// thanks for submitting the form!
		list( $auth_url, $request_token ) = $this->client->initiate();

		// remember the OAuth request token
		my_set_cookie( COOKIE_OAUTH_REQUEST_TOKEN_KEY,    $request_token->key    );
		my_set_cookie( COOKIE_OAUTH_REQUEST_TOKEN_SECRET, $request_token->secret );

		// here we go!
		http_redirect( $auth_url );

	}


	/**
	 * Receive the OAuth response and redirect to the homepage again
	 */
	private function receiveOAuthResponse() {

		// welcome back, user, what was the original OAuth request token?
		$request_token_key = $_COOKIE[ COOKIE_OAUTH_REQUEST_TOKEN_KEY    ] ?? null;
		$request_token_sec = $_COOKIE[ COOKIE_OAUTH_REQUEST_TOKEN_SECRET ] ?? null;
		if( !$request_token_key || !$request_token_key ) {
			throw new Exception( "missing request tokens" );
		}

		// rebuild the OAuth request token
		$request_token = new Token( $request_token_key, $request_token_sec );

		// check the OAuth access token
		$access_token = $this->client->complete( $request_token, $_GET['oauth_verifier'] );
		$identity     = $this->client->identify( $access_token );

		// clear old cookies now unuseful
		my_unset_cookie( COOKIE_OAUTH_REQUEST_TOKEN_KEY );
		my_unset_cookie( COOKIE_OAUTH_REQUEST_TOKEN_SECRET );

		// save the access token to rebuild it later
		my_set_cookie( COOKIE_OAUTH_ACCESS_TOKEN_KEY,    $access_token->key );
		my_set_cookie( COOKIE_OAUTH_ACCESS_TOKEN_SECRET, $access_token->secret );

		// save other information
		my_set_cookie( COOKIE_OAUTH_ACCESS_NONCE, $identity->nonce );
		my_set_cookie( COOKIE_WIKI_USERNAME,      $identity->username );

		// clean the URL
		http_redirect( '' );

	}

	/**
	 * Prepare the OAuth access token from cookies
	 */
	private function prepareOAuthAccessToken() {

		// eventually build the OAuth access token
		if( isset(
			$_COOKIE[ COOKIE_OAUTH_ACCESS_TOKEN_KEY ],
			$_COOKIE[ COOKIE_OAUTH_ACCESS_TOKEN_SECRET ]
		) ) {
			$this->accessToken = new Token(
				$_COOKIE[ COOKIE_OAUTH_ACCESS_TOKEN_KEY ],
				$_COOKIE[ COOKIE_OAUTH_ACCESS_TOKEN_SECRET ]
			);
		}

	}

	/**
	 * Request the wiki's CSRF
	 *
	 * @return string
	 */
	private function requestWikiCSRFToken() {

		// retrieve wiki CSRF token
		$response_tokens =
			$this->makeOAuthPOST( [
				'action' => 'query',
				'format' => 'json',
				'meta'   => 'tokens',
				'type'   => 'csrf',
			] );

		// no CSRF token no party
		$csrf_token = $response_tokens->query->tokens->csrftoken ?? null;
		if( !$csrf_token ) {
			throw new Exception( "cannot retrieve CSRF token from wiki" );
		}

		return $csrf_token;

	}

	/**
	 * Try to create the Event from the POST-ed data
	 */
	private function createEvent() {

		// read POST-ed data
		// assume that this data has sense (the user is logged-in and anyway he/she is just editing a page)
		// in the worst of the cases, the event will be broken and a warning will be shown
		$event_title      = $this->getPOST( 'event_title' );
		$event_date_start = $this->getPOST( 'event_date_start' );
		$event_date_end   = $this->getPOST( 'event_date_end' );
		$event_time_start = $this->getPOST( 'event_time_start' );
		$event_time_end   = $this->getPOST( 'event_time_end' );
		$event_url        = $this->getPOST( 'event_url' );
		$event_category   = $this->getPOST( 'event_category' );
		$event_id         = $this->getPOST( 'event_id' );

		// no dates no party
		if( empty( $event_date_start ) ) {
			throw new Exception( "missing date start" );
		}

		// just create a random identifier
		if( !$event_id ) {
			$event_id = uniqid();
		}

		// get the wiki CSRF token
		$csrf_token = $this->requestWikiCSRFToken();
		if( !$csrf_token ) {
			throw new Exception( "missing CSRF token from session" );
		}

		// this is the page that will host the event infobox
		$this->eventsPageTitle = "Meta:Cronos/Events/$event_date_start";

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
			"|title    = $event_title\n" .
			"|when     = $event_date_start $event_time_start\n" .
			"|end      = $event_date_end $event_time_end\n" .
			"|url      = $event_url\n" .
			"|category = $event_category\n" .
			"|id       = $event_id\n".
			"}}";

		$text_create .= $template_event;

		// try to create the page
		$response = $this->makeOAuthPOST( [
			'action'     => 'edit',
			'createonly' => 1,
			'format'     => 'json',
			'title'      => $this->getEventsPageTitle(),
			'summary'    => "New events page: $event_title",
			'text'       => $text_create,
			'token'      => $csrf_token,
		] );

		// eventually edit the page
		$result = $response->edit->result ?? null;

		$this->saved = $result === 'Success';

		// check if the page already exist and append text
		if( !$this->saved ) {

			$error_code = $response->error->code ?? null;
			if( $error_code === 'articleexists' ) {

				// create the page
				$response = $this->makeOAuthPOST( [
					'action'     => 'edit',
					'nocreate'   => 1,
					'format'     => 'json',
					'title'      => $this->getEventsPageTitle(),
					'summary'    => "New event: $event_title",
					'appendtext' => $template_event,
					'token'      => $csrf_token,
				] );

				// assume success
				$this->saved = true;
			} else {

				throw new Exception( $error_code );

			}
		}
	}

	/**
	 * Make an OAuth HTTP POST request
	 *
	 * @param array $data
	 * @return array
	 */
	private function makeOAuthPOST( $data ) {

		// try to make the HTTP request
		$response_raw = $this->client->makeOAuthCall( $this->accessToken, $this->wikiApi, $isPost = true, $data );
		if( !$response_raw ) {
			throw new Exception( "missing response" );
		}

		// try to parse JSON
		$response = @json_decode( $response_raw );
		if( !$response ) {
			throw new Exception( "response not a JSON" );
		}

		return $response;

	}
}
