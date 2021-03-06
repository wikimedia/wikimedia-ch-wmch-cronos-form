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
	 * Direct URL to the file on Wikimedia Commons
	 */
	private $fileurl;

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
	 * @param string $fileurl Direct URL to the file on Commons
	 */
	public function __construct( $uid, $name, $filename, $fileurl ) {
		$this->uid = $uid;
		$this->name = $name;
		$this->filename = $filename;
		$this->fileurl = $fileurl;
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
	* @return string
	*/
	public function getImageURL() {
		return $this->fileurl;
	}


	/**
	 * Add a new Category
	 *
	 * @param string $uid User identifier
	 * @param string $name Category name
	 * @param string $filename File name on Wikimedia Commons
	 * @param string $fileurl Direct URL to the file on Commons
	 */
	public static function add( $uid, $name, $filename, $fileurl ) {
		self::$all[ $uid ] = new self( $uid, $name, $filename, $fileurl );
	}

	/**
	 * Add a new Category about "Bla bla initiatives"
	 *
	 * @param string $uid User identifier
	 * @param string $name Category name
	 * @param string $filename File name on Wikimedia Commons
	 * @param string $fileurl Direct URL to the file on Commons
	 */
	public static function addInitiatives( $uid, $name, $filename, $fileurl ) {

		// from 'bla bla'
		// to 'bla bla initiatives'
		$name = sprintf(
			__( "%s initiatives" ),
			$name
		);

		self::add( $uid, $name, $filename, $fileurl );
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
