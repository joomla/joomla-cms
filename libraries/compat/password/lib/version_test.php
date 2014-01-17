<?php
/**
 * A Compatibility library with PHP 5.5's simplified password hashing API.
 *
 * @author Anthony Ferrara <ircmaxell@php.net>
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 2012 The Authors
 */
/**
 * Modififations
 * Open Source Matters 2013
 * CMS version 3.2
 *
 */
class version_test
{
	public function version_test()
	{
		$hash = '$2y$04$usesomesillystringfore7hnbRJHxXVLeakoG8K30oukPsA.ztMG';
		$test = crypt("password", $hash);
		$pass = $test == $hash;

		return $pass;
	}
}