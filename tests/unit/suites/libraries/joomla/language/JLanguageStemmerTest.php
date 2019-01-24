<?php
/**
 * @package    Joomla.UnitTest
 *
 * @copyright  Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Test class for JLanguageStemmer.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Language
 * @since       1.7.0
 */
class JLanguageStemmerTest extends \PHPUnit\Framework\TestCase
{
	/**
	 * Test...
	 *
	 * @return void
	 */
	public function testGetInstance()
	{
		$instance = JLanguageStemmer::getInstance('porteren');

		$this->assertInstanceof(
			'JLanguageStemmer',
			$instance
		);

		$this->assertInstanceof(
			'JLanguageStemmerPorteren',
			$instance
		);

		$instance2 = JLanguageStemmer::getInstance('porteren');

		$this->assertSame(
			$instance,
			$instance2
		);
	}

	/**
	 * Test...
	 *
	 * @expectedException  RuntimeException
	 *
	 * @return void
	 */
	public function testGetInstanceException()
	{
		JLanguageStemmer::getInstance('unexisting');
	}
}
