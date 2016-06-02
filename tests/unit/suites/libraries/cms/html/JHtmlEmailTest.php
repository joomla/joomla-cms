<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  HTML
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for JHtmlEmail.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Html
 * @since       3.1
 */
class JHtmlEmailTest extends PHPUnit_Framework_TestCase
{
	/**
	 * Tests the JHtmlEmail::cloak method.
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	public function testCloak()
	{
		$this->assertThat(
			JHtmlEmail::cloak('admin@joomla.org'),
			$this->StringContains(".innerHTML += '<a ' + path + '\'' + prefix + ':' + addy"),
			'Cloak e-mail with mailto link'
		);

		$this->assertThat(
			JHtmlEmail::cloak('admin@joomla.org', false),
			$this->StringContains("var path = 'hr' + 'ef' + '=';"),
			'Cloak e-mail with no mailto link'
		);

		$this->assertThat(
			JHtmlEmail::cloak('admin@joomla.org', true, 'administrator@joomla.org'),
			$this->StringContains("var addy_text"),
			'Cloak e-mail with mailto link and separate e-mail address text'
		);

		$this->assertThat(
			JHtmlEmail::cloak('admin@joomla.org', true, 'Joomla! Administrator', false),
			$this->StringContains("var addy_text"),
			'Cloak e-mail with mailto link and separate non-e-mail address text'
		);
	}
}
