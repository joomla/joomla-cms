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
			$this->StringContains('<span id="cloak'),
			'Cloak email with mailto link'
		);

		$this->assertThat(
			JHtmlEmail::cloak('admin@joomla.org', false),
			$this->StringContains('<span id="cloak'),
			'Cloak email with no mailto link'
		);

		$this->assertThat(
			JHtmlEmail::cloak('admin@joomla.org', true, 'administrator@joomla.org'),
			$this->StringContains('<span id="cloak'),
			'Cloak email with mailto link and separate email address text'
		);

		$this->assertThat(
			JHtmlEmail::cloak('admin@joomla.org', true, 'Joomla! Administrator', false),
			$this->StringContains('<span id="cloak'),
			'Cloak email with mailto link and separate non-email address text'
		);
	}
}
