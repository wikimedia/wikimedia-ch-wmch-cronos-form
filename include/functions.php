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
 * Stupid way to set a cookie
 *
 * @param string $key
 * @param string $value
 * @param int    $hours
 */
function my_set_cookie( $key, $value, $hours = 24 ) {
	setcookie( $key, $value, time()+60*60*$hours, ROOT . _, DOMAIN, true, true );
}

/**
 * Stupid way to unset a cookie
 *
 * @param string $key
 */
function my_unset_cookie( $key ) {
	setcookie( $key, '1', 1, ROOT . _, DOMAIN, true, true );
}

/**
 * Require a certain page from the template directory
 *
 * @param $name string page name (to be sanitized)
 * @param $args mixed arguments to be passed to the page scope
 */
function template( $template_name, $template_args = [] ) {
	extract( $template_args, EXTR_SKIP );
	return require ABSPATH . "/template/$template_name.php";
}
