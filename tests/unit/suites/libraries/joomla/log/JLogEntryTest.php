<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Log
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for JLogEntry.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Log
 * @since       11.1
 */
class JLogEntryTest extends PHPUnit_Framework_TestCase
{
	/**
	 * Verify the default values for the log entry object.
	 *
	 * @covers  JLogEntry::__construct
	 *
	 * @return void
	 */
	public function testDefaultValues()
	{
		$tmp = new JLogEntry('Lorem ipsum dolor sit amet');
		$date = JFactory::getDate('now');

		// Message.
		$this->assertThat(
			$tmp->message,
			$this->equalTo('Lorem ipsum dolor sit amet'),
			'Line: ' . __LINE__ . '.'
		);

		// Priority.
		$this->assertThat(
			$tmp->priority,
			$this->equalTo(JLog::INFO),
			'Line: ' . __LINE__ . '.'
		);

		// Category.
		$this->assertThat(
			$tmp->category,
			$this->equalTo(''),
			'Line: ' . __LINE__ . '.'
		);

		// Date.
		$this->assertEquals(
			$tmp->date->getTimestamp(),
			$date->getTimestamp(),
			'Line: ' . __LINE__ . '.',
			1
		);
	}

	/**
	 * Verify the priority for the entry object cannot be something not in the approved list.
	 *
	 * @covers  JLogEntry::__construct
	 *
	 * @return void
	 */
	public function testBadPriorityValues()
	{
		$tmp = new JLogEntry('Lorem ipsum dolor sit amet', JLog::ALL);
		$this->assertThat(
			$tmp->priority,
			$this->equalTo(JLog::INFO),
			'Line: ' . __LINE__ . '.'
		);

		$tmp = new JLogEntry('Lorem ipsum dolor sit amet', 23642872);
		$this->assertThat(
			$tmp->priority,
			$this->equalTo(JLog::INFO),
			'Line: ' . __LINE__ . '.'
		);

		$tmp = new JLogEntry('Lorem ipsum dolor sit amet', 'foobar');
		$this->assertThat(
			$tmp->priority,
			$this->equalTo(JLog::INFO),
			'Line: ' . __LINE__ . '.'
		);
	}

	/**
	 * Test that non-standard category values are sanitized.
	 *
	 * @covers  JLogEntry::__construct
	 *
	 * @return void
	 */
	public function testCategorySanitization()
	{
		// Category should always be lowercase.
		$tmp = new JLogEntry('Lorem ipsum dolor sit amet', JLog::INFO, 'TestingTheCategory');
		$this->assertThat(
			$tmp->category,
			$this->equalTo('testingthecategory'),
			'Line: ' . __LINE__ . '.'
		);

		// Category should not have spaces.
		$tmp = new JLogEntry('Lorem ipsum dolor sit amet', JLog::INFO, 'testing the category');
		$this->assertThat(
			$tmp->category,
			$this->equalTo('testingthecategory'),
			'Line: ' . __LINE__ . '.'
		);

		// Category should not have special characters.
		$tmp = new JLogEntry('Lorem ipsum dolor sit amet', JLog::INFO, 'testing@#$^the*&@^#*&category');
		$this->assertThat(
			$tmp->category,
			$this->equalTo('testingthecategory'),
			'Line: ' . __LINE__ . '.'
		);

		// Category should allow numbers.
		$tmp = new JLogEntry('Lorem ipsum dolor sit amet', JLog::INFO, 'testing1the2category');
		$this->assertThat(
			$tmp->category,
			$this->equalTo('testing1the2category'),
			'Line: ' . __LINE__ . '.'
		);
	}
}
