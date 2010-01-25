<?php
/**
 * Joomla! v1.5 Unit Test Facility
 *
 * @package Joomla
 * @subpackage UnitTest
 * @copyright Copyright (C) 2005 - 2008 Open Source Matters, Inc.
 * @version $Id$
 *
 */

class JStringTest_DataSet {
	/**
	 * Tests for JString::strpos.
	 *
	 * Each element contains $haystack, $needle, $offset, $expect,
	 *
	 * @var array
	 */
	static public $strposTests = array(
		array('missing',    'sing', 0,  3),
		array('missing',    'sting', 0,  false),
		array('missing',    'ing', 0,  4),
		array(' объектов на карте с',    'на карте', 0,  10),
		array('на карте с',    'на карте', 0,  0),
		array('на карте с',    'на каррте', 0,  false),
		array('на карте с',    'на карте', 2,  false)
	);

	static public $strrposTests = array(
		array('missing',    'sing', 0,  3),
		array('missing',    'sting', 0,  false),
		array('missing',    'ing', 0,  4),
		array(' объектов на карте с',    'на карте', 0,  10),
		array('на карте с',    'на карте', 0,  0),
		array('на карте с',    'на каррте', 0,  false),
		array('на карте с',    'карт', 2,  3)
	);

	static public $substrTests = array(
		array('Mississauga', 4, false, 'issauga')
	);

	static public $strtolowerTests = array(
		array('Joomla! Rocks', 'joomla! rocks')
	);

	static public $strtoupperTests = array(
		array('Joomla! Rocks', 'JOOMLA! ROCKS')
	);

	static public $strlenTests = array(
		array('Joomla! Rocks', 13)
	);
}
