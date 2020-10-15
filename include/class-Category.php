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

/**
 * Describes a Cronos Calendar Category
 */
class Category {

	/**
	 * Category UID
	 */
	private $uid;

	/**
	 * Category name
	 */
	private $name;

	/**
	 * Category filename on Wikimedia Commons without File: prefix
	 */
	private $filename;

	/**
	 * URL in sprintf format
	 *
	 * The %d argument can be replaced with the width in pixels.
	 */
	private $urlFormat;

	/**
	 * All the known categories
	 */
	private static $all = [];

	/**
	 * All the known aliases
	 *
	 * Associative array of starting Category UID and destination Category UID
	 */
	private static $aliases = [];

	/**
	 * Costructor
	 *
	 * @param string $uid User identifier
	 * @param string $name Category name
	 * @param string $filename File name on Wikimedia Commons
	 * @param string $url_format URL in sprintf format with '%d' that can be sobstituted with the width in pixels
	 */
	public function __construct( $uid, $name, $filename, $url_format ) {
		$this->uid = $uid;
		$this->name = $name;
		$this->filename = $filename;
		$this->urlFormat = $url_format;
	}

	/**
	 * Get the Category UID
	 *
	 * @return string
	 */
	public function getUID() {
		return $this->uid;
	}

	/**
	 * Get the Category Name
	 *
	 * @return string
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * Get the Category filename
	 *
	 * @return string
	 */
	public function getFilename() {
		return $this->filename;
	}

	/**
	 * Get Commons URL
	 *
	 * @return string
	 */
	public function getCommonsURL() {
		return sprintf(
			'https://commons.wikimedia.org/wiki/%s',
			str_replace( ' ', '_', $this->filename )
		);
	}

	/**
	 * Get the image URL
	 *
	 * @param $size int You can set the width in pixels
	 * @return string
	 */
	public function getImageURL( $size = 64 ) {
		return sprintf(
			$this->urlFormat,
			$size
		);
	}

	/**
	 * Add a new Category
	 *
	 * @param string $uid User identifier
	 * @param string $name Category name
	 * @param string $filename File name on Wikimedia Commons
	 * @param string $url_format URL in sprintf format with '%d' that can be sobstituted with the width in pixels
	 */
	public static function add( $uid, $name, $filename, $url_format ) {
		self::$all[ $uid ] = new self( $uid, $name, $filename, $url_format );
	}

	/**
	 * Add a new Category about "Bla bla initiatives"
	 *
	 * @param string $uid User identifier
	 * @param string $name Category name
	 * @param string $filename File name on Wikimedia Commons
	 * @param string $url_format URL in sprintf format with '%d' that can be sobstituted with the width in pixels
	 */
	public static function addInitiatives( $uid, $name, $filename, $url_format ) {

		// from 'bla bla'
		// to 'bla bla initiatives'
		$name = sprintf(
			__( "%s initiatives" ),
			$name
		);

		self::add( $uid, $name, $filename, $url_format );
	}

	/**
	 * Add a Category alias
	 *
	 * @param $from string Category UID to start from
	 * @param $to   string Category UID to start to
	 */
	public static function addAliasFromTO( $from, $to ) {
		self::$aliases[ $from ] = $to;
	}

	/**
	 * Get all the known categories
	 *
	 * @return array
	 */
	public static function all() {
		return self::$all;
	}

	/**
	 * Find a Category by UID
	 *
	 * @return Category|false
	 */
	public static function find( $uid ) {

		// eventually replace with its existing alias
		$uid = self::$aliases[ $uid ] ?? $uid;

		// return the existing Category or false
		return self::$all[ $uid ] ?? false;
	}
}
