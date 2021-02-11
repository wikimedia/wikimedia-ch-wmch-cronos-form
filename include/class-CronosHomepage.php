<?php
# Copyright (C) 2020, 2021 Valerio Bozzolan
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
define( 'COOKIE_CRONOS_DATE_START',          'event_date_start'  );
define( 'COOKIE_CRONOS_TAGS',                'event_tags'        );

class CronosHomepage {

	/**
	 * Hard limit for the length of each Tag to be received from HTTP GET
	 *
	 * Note that Tags may be saved in your cookies.
	 */
	const HARD_LIMIT_TAG_LEN = 128;

	/**
	 * Hard limit for the max number of Tags to be received from HTTP GET
	 *
	 * Note that Tags may be saved in your cookies.
	 */
	const HARD_LIMIT_TAG_COUNT = 16;

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

	/**
	 * Get the events page title
	 *
	 * @return string
	 */
	public function getEventsPageTitle() {

		// eventually inherit the title
		if( !$this->eventsPageTitle ) {
			$date = $this->getDateYMD();
			if( $date ) {
				$this->setEventsPageTitleByDateRaw( $date );
			}
		}

		return $this->eventsPageTitle;
	}

	/**
	 * Check if the events page title is knoen
	 *
	 * @return boolean
	 */
	public function isEventsPageTitleKnown() {
		return $this->getEventsPageTitle() !== null;
	}

	/**
	 * Get the Events page URL
	 *
	 * @return string
	 */
	public function getEventsPageURL() {
		return sprintf(
			'https://meta.wikimedia.org/wiki/%s',
			urlencode( $this->getEventsPageTitle() )
		);
	}

	/**
	 * Check if you should see the saved message
	 *
	 * @return boolean
	 */
	public function hasSaved() {
		return $this->saved || isset( $_GET['saved'] );
	}

	/**
	 * Check if the user is not logged in
	 *
	 * @return boolean
	 */
	public function isUserUnknown() {
		return empty( $_COOKIE[ COOKIE_WIKI_USERNAME ] );
	}

	/**
	 * Check if we know the username
	 *
	 * @return string|null
	 */
	public function getAnnouncedUsername() {
		return $_COOKIE[ COOKIE_WIKI_USERNAME ] ?? null;
	}

	/**
	 * Print the website header
	 */
	public function printHeader() {

		// eventually exposes some JavaScript variables
		$this->exposeJavaScriptVariables();

		enqueue_js( 'cronos' );

		enqueue_css( 'material.icons' );

		// print template/header.php
		template( 'header' );

	}

	/**
	 * Print the website footer
	 */
	public function printFooter() {

		// print template/footer.php
		template( 'footer' );

	}

	/**
	 * Get a submitted information
	 *
	 * @param string $key
	 * @param string $default_value
	 */
	public function getUserData( $key, $default_value = null ) {

		// eventually receive submitted data
		if( !array_key_exists( $key, $this->post ) ) {

			// read from POST or from GET
			$value = $_POST[ $key ] ?? $_GET[ $key ] ?? null;

			if( $value ) {
				// technically it's possible to submit arrays (asd[]=1) so let's clean and trim
				$value = luser_input( $value, 254 );
			}

			// remember this
			$this->post[ $key ] = $value;
		}

		return $this->post[ $key ] ?? $default_value;
	}

	/**
	 * Get an array of user Tags
	 *
	 * @return array
	 */
	public function getUserTags() {

		// get a string with comma-separated Tags
		$tags_raw = $this->getUserData( 'event_tags' );

		// eventually inherit from session
		if( !$tags_raw ) {
			$tags_raw = $_COOKIE[ COOKIE_CRONOS_TAGS ] ?? null;
		}

		// convert the string to an array
		return self::str_2_tags( $tags_raw );
	}

	/**
	 * Get the raw date
	 *
	 * This date can be received from GET or from the first visit before OAUTH login.
	 *
	 * This method always return a valid date.
	 *
	 * @return string
	 */
	public function getDateYMD() {

		// retrieve from GET or from a cookie
		$ymd_date = $_GET['event_date_start'] ?? $_COOKIE[ COOKIE_CRONOS_DATE_START ] ?? null;

		// it must be valid
		if( !parse_ymd( $ymd_date ) ) {
			$ymd_date = false;
		}

		return $ymd_date;
	}

	/**
	 * Eventually exposes some variables to JavaScript
	 */
	private function exposeJavaScriptVariables() {

		// make the Tags visible to the cronos.js script
		register_js_var( 'cronos', 'window.CRONOS_PREFILL_TAGS', $this->getUserTags() );
	}

	/**
	 * Forget this OAuth session
	 */
	private function logout() {

		// clear all known cookies
		my_unset_cookie( COOKIE_OAUTH_REQUEST_TOKEN_KEY );
		my_unset_cookie( COOKIE_OAUTH_REQUEST_TOKEN_SECRET );
		my_unset_cookie( COOKIE_OAUTH_ACCESS_TOKEN_KEY );
		my_unset_cookie( COOKIE_OAUTH_ACCESS_TOKEN_SECRET );
		my_unset_cookie( COOKIE_OAUTH_ACCESS_NONCE );
		my_unset_cookie( COOKIE_WIKI_USERNAME );
		my_unset_cookie( COOKIE_WIKI_CSRF );
		my_unset_cookie( COOKIE_CRONOS_DATE_START );
		my_unset_cookie( COOKIE_CRONOS_TAGS );

		// POST -> redirect -> GET
		http_redirect( '' );
	}

	/**
	 * Redirect to the OAuth login page
	 *
	 * Note that when the user is sent to the OAuth login
	 * then the query string is lost.
	 *
	 * Actually we want a stateless application so we do
	 * not rely on session().
	 *
	 * Anyway storing stuff in a cookie is easy, so we do that.
	 */
	private function tryOAuthLogin() {

		// thanks for submitting the form!
		list( $auth_url, $request_token ) = $this->client->initiate();

		// remember the OAuth request token
		my_set_cookie( COOKIE_OAUTH_REQUEST_TOKEN_KEY,    $request_token->key    );
		my_set_cookie( COOKIE_OAUTH_REQUEST_TOKEN_SECRET, $request_token->secret );

		// try to persist the query string before redirecting the user to the OAUTH
		$this->persistQueryStringInSession();

		// here we go!
		http_redirect( $auth_url );

	}

	/**
	 * Try to persist the query string in the session
	 *
	 * This is useful before redirecting the user to
	 * another page, to get the information back.
	 *
	 * This method is called only by #tryOAuthLogin()
	 */
	private function persistQueryStringInSession() {

		// persist Date provided in query string
		$this->persistQueryStringInSessionDate();

		// persist Tags provided in query string
		$this->persistQueryStringInSessionTags();
	}

	/**
	 * Persist the Date provided in query string
	 *
	 * This is useful before redirecting the user to
	 * another page, to get the information back.
	 *
	 * This method is called only by #persistQueryStringInSession()
	 */
	private function persistQueryStringInSessionDate() {

		// if the user is providing a date, remember it
		$date = $_GET['event_date_start'] ?? null;

		// validate the date before storing in a cookie
		if( $date && parse_ymd( $date ) ) {
			my_set_cookie( COOKIE_CRONOS_DATE_START, $date );
		}
	}

	/**
	 * Persist the Tags provided in query string
	 *
	 * This is useful before redirecting the user to
	 * another page, to get the information back.
	 *
	 * This method is called only by #persistQueryStringInSession()
	 */
	private function persistQueryStringInSessionTags() {

		// if the user is providing some tags
		$tags_raw = $_GET['tags'] ?? null;

		// validate the Tags before storing them in a cookie
		if( $tags_raw ) {

			// filter invalid tags
			$tags = self::str_2_tags( $tags_raw );

			// rebuild again
			$tags_raw_clean = self::tags_2_str( $tags );

			// save these damn sanitized Tags
			if( $tags_raw_clean ) {
				my_set_cookie( COOKIE_CRONOS_TAGS, $tags_raw_clean );
			}
		}
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
		$event_title      = $this->getUserData( 'event_title' );
		$event_date_start = $this->getUserData( 'event_date_start' );
		$event_date_end   = $this->getUserData( 'event_date_end' );
		$event_time_start = $this->getUserData( 'event_time_start' );
		$event_time_end   = $this->getUserData( 'event_time_end' );
		$event_url        = $this->getUserData( 'event_url' );
		$event_where      = $this->getUserData( 'event_where' );
		$event_abstract   = $this->getUserData( 'event_abstract' );
		$event_category   = $this->getUserData( 'event_category' );
		$event_tags       = $this->getUserData( 'event_tags' );
		$event_id         = $this->getUserData( 'event_id' );

		// no dates no party
		if( empty( $event_date_start ) ) {
			throw new Exception( "missing date start" );
		}

		// just create a random identifier
		if( !$event_id ) {
			$event_id = 'cronos-' . uniqid();
		}

		// get the wiki CSRF token
		$csrf_token = $this->requestWikiCSRFToken();
		if( !$csrf_token ) {
			throw new Exception( "missing CSRF token from session" );
		}

		// validate the date
		$event_date_start_parts = parse_ymd( $event_date_start );
		if( !$event_date_start_parts ) {
			throw new Exception( "bad start date" );
		}

		// split date in parts
		list( $start_y, $start_m, $start_d ) = $event_date_start_parts;

		// remember this date
		$this->setEventsPageTitleByDateRaw( $event_date_start );

		// remember this last date
		my_set_cookie( COOKIE_CRONOS_DATE_START, $event_date_start );

		// text of the page
		$text_create = sprintf(
			'{{Cronos day|%d|%d|%d}}',
			$start_y,
			$start_m,
			$start_d
		);

		// Cronos event end argument with date (optional) and time
		$end_argument = trim( "$event_date_end $event_time_end" );

		$template_event =
			"\n" .
			"{{Cronos event\n" .
			"|title    = $event_title\n" .
			"|when     = $event_date_start $event_time_start\n" .
			"|end      = $end_argument\n" .
			"|where    = $event_where\n" .
			"|url      = $event_url\n" .
			"|category = $event_category\n" .
			"|tags     = $event_tags\n" .
			"|id       = $event_id\n".
			"|abstract = $event_abstract\n".
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

		if( $this->saved ) {

			// do a POST->redirect->GET in case of success
			http_redirect( http_build_get_query( '', [
				'event_date_start' => $event_date_start,
				'saved' => 1,
			] ) );
		} else {

			// TODO:
			// do a POST->redirect->GET somehow telling the error
		}
	}

	/**
	 * Set the Events page title by the raw Y-m-d date
	 *
	 * @param string $event_date_start_ymd
	 */
	private function setEventsPageTitleByDateRaw( $event_date_start_ymd ) {
		// this is the page that will host the event infobox
		$this->eventsPageTitle = "Meta:Cronos/Events/$event_date_start_ymd";
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

	/**
	 * Parse a string of tags comma-separated
	 *
	 * @param string $tags
	 * @return array
	 */
	private static function str_2_tags( $tags_raw ) {

		$good_tags = [];

		// discard rubbish
		if( is_string( $tags_raw ) ) {

			// make an array from a comma-separated string
			$tags = explode( ',', $tags_raw, self::HARD_LIMIT_TAG_COUNT );

			// validate each Tag
			foreach( $tags as $tag ) {

				// remove whitespaces before/after Tag name
				$tag = trim( $tag );

				// discard empty Tags
				if( $tag ) {

					$len = strlen( $tag );

					//  discard nonsense Tags
					if( $len <= self::HARD_LIMIT_TAG_LEN ) {
						$good_tags[] = $tag;
					} else {
						error_log( sprintf(
							"dropped Tag longer than %d chars",
							$len
						) );
					}

				}

			}
		}

		// eventualy strip unuseful duplicates
		if( $good_tags ) {
			$good_tags = array_unique( $good_tags );
		}

		return $good_tags;
	}

	/**
	 * Make an array of Tags flat
	 *
	 * @param array $tags
	 * @return string
	 */
	private static function tags_2_str( $tags ) {

		return implode( ',', $tags );

	}
}
