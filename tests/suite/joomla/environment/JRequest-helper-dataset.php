<?php
/**
 * Joomla! v1.5 Unit Test Facility
 *
 * @package Joomla
 * @subpackage UnitTest
 * @copyright Copyright (C) 2005 - 2011 Open Source Matters, Inc.
 * @version $Id: JRequest-helper-dataset.php 20196 2011-01-09 02:40:25Z ian $
 *
 */

class JRequestTest_DataSet {
	/**
	 * Tests for getVar.
	 *
	 * Each element contains $name, $default, $hash, $type, $mask, $expect,
	 * array of JFilterInput expectations.
	 *
	 * Note that this is a JRequest test, not a JFilterInput test. Cases
	 * that exersize data types and string cleaning belong in a test of the
	 * filtering code.
	 *
	 * @var array
	 */
	static public $getVarTests = array(
		/*
		 * Default values tests
		 */
		array(
			'missing',	null,		'default',  'none', 0, null, array()
		),
		array(
			'missing',	'absent',   'default',  'none', 0, 'absent',
			array(
				// Note count is 2 because default values are not cached.
				array('absent', 'NONE', 'absent', 2)
			)
		),
		/*
		 * Data source tests
		 */
		array(
			'tag',  null,		'default',  'none', 0, 'from _REQUEST',
			array(
				array('from _REQUEST', 'NONE', 'from _REQUEST', 1)
			)
		),
		array(
			'tag',  null,		'post',	'none', 0, 'from _POST',
			array(
				array('from _POST', 'NONE', 'from _POST', 1)
			)
		),
		array(
			'tag',  null,		'method',   'none', 0, 'from _POST',
			array(
				array('from _POST', 'NONE', 'from _POST', 1)
			)
		),
		array(
			'tag',  null,		'request',  'none', 0, 'from _REQUEST',
			array(
				array('from _REQUEST', 'NONE', 'from _REQUEST', 1)
			)
		),
		array(
			'tag',  null,		'invalid',  'none', 0, 'from _REQUEST',
			array(
				array('from _REQUEST', 'NONE', 'from _REQUEST', 1)
			)
		),
		array(
			'tag',  null,		'cookie',   'none', 0, 'from _COOKIE',
			array(
				array('from _COOKIE', 'NONE', 'from _COOKIE', 1)
			)
		),
		array(
			'tag',  null,		'files',	'none', 0, 'from _FILES',
			array(
				array('from _FILES', 'NONE', 'from _FILES', 1)
			)
		),
		array(
			'tag',  null,		'env',	'none', 0, 'from _ENV',
			array(
				array('from _ENV', 'NONE', 'from _ENV', 1)
			)
		),
		array(
			'tag',  null,		'server',   'none', 0, 'from _SERVER',
			array(
				array('from _SERVER', 'NONE', 'from _SERVER', 1)
			)
		),
		/*
		 * Test flags
		 */
		array(
			'trim_test',  null,		'default',  'none', 0, 'has  whitespace',
			array(
				array('has  whitespace', 'NONE', 'has  whitespace', 1)
			)
		),
		array(
			'trim_test',  null,		'default',  'none', JREQUEST_NOTRIM, ' has  whitespace ',
			array(
				array(' has  whitespace ', 'NONE', ' has  whitespace ', 1)
			)
		),
		array(
			'raw_test',  null,		'default',  'none', JREQUEST_ALLOWRAW, '<body>stuff</body>',
			array(),
		),
	);

	static function initSuperGlobals() {
		$_GET = array('tag' => 'from _GET');
		$_COOKIE = array('tag' => 'from _COOKIE');
		$_ENV = array('tag' => 'from _ENV');
		$_FILES = array('tag' => 'from _FILES');
		$_POST = array('tag' => 'from _POST');
		$_SERVER = array('tag' => 'from _SERVER','REQUEST_METHOD' => 'POST');
		/**
		 * Merge get and post into request.
		 */
		$_REQUEST = array_merge($_GET, $_POST);
		$_REQUEST['tag'] = 'from _REQUEST';
		$_REQUEST['raw_test'] = '<body>stuff</body>';
		$_REQUEST['trim_test'] = ' has  whitespace ';
	}
}
