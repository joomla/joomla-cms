<?php
/**
 * @copyright  Copyright (C) 2005 - 2013 Open Source Matters. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Uri\Tests;

use Joomla\Uri\UriHelper;

/**
 * Tests for the Joomla\Uri\UriHelper class.
 *
 * @since  1.0
 */
class UriHelperTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * Test the parse_url method.
	 *
	 * @return  array
	 *
	 * @covers  Joomla\Uri\UriHelper::parse_url
	 * @since   1.0
	 */
	public function testParse_Url()
	{
		$url = 'http://localhost/joomla_development/j16_trunk/administrator/index.php?option=com_contact&view=contact&layout=edit&id=5';
		$expected = parse_url($url);
		$actual = UriHelper::parse_url($url);
		$this->assertEquals($expected, $actual, 'Line: ' . __LINE__ . ' Results should be equal');

		// Test all parts of query
		$url = 'https://john:doe@www.google.com:80/folder/page.html#id?var=kay&var2=key&true';
		$expected = parse_url($url);
		$actual = UriHelper::parse_url($url);
		$this->assertEquals($expected, $actual, 'Line: ' . __LINE__ . ' Results should be equal');

		// Test special characters in URL
		$url = 'http://joomla.org/mytestpath/È';
		$expected = parse_url($url);

		// Fix up path for UTF-8 characters
		$expected['path'] = '/mytestpath/È';
		$actual = UriHelper::parse_url($url);
		$this->assertEquals($expected, $actual, 'Line: ' . __LINE__ . ' Results should be equal');

		// Test special characters in URL
		$url = 'http://mydomain.com/!*\'();:@&=+$,/?%#[]" \\';
		$expected = parse_url($url);
		$actual = UriHelper::parse_url($url);
		$this->assertEquals($expected, $actual, 'Line: ' . __LINE__ . ' Results should be equal');

		// Test url encoding in URL
		$url = 'http://mydomain.com/%21%2A%27%28%29%3B%3A%40%26%3D%24%2C%2F%3F%25%23%5B%22%20%5C';
		$expected = parse_url($url);
		$actual = UriHelper::parse_url($url);
		$this->assertEquals($expected, $actual, 'Line: ' . __LINE__ . ' Results should be equal');

		// Test a mix of the above
		$url = 'http://john:doe@mydomain.com:80/%È21%25È3*%(';
		$expected = parse_url($url);

		// Fix up path for UTF-8 characters
		$expected['path'] = '/%È21%25È3*%(';
		$actual = UriHelper::parse_url($url);
		$this->assertEquals($expected, $actual, 'Line: ' . __LINE__ . ' Results should be equal');

		// Test invalild URL
		$url = 'http:///mydomain.com';
		$expected = parse_url($url);
		$actual = UriHelper::parse_url($url);
		$this->assertEquals($expected, $actual, 'Line: ' . __LINE__ . ' Results should be equal');
	}
}
