<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  HTML
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

require_once JPATH_PLATFORM.'/joomla/html/html/batch.php';

/**
 * Test class for JHtmlBatch.
 *
 * @since  11.3
 */
class JHtmlBatchTest extends PHPUnit_Framework_TestCase
{
	/**
	 * @todo Implement testAccess().
	 */
	public function testAccess()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
		'This test has not been implemented yet.'
		);
	}

	/**
	 * @return	array
	 *
	 * @since   11.3
	 */
	public function dataTestItem()
	{
		return array(
			// Element order: result, extension
			array(
				'com_content',
			)
		);
	}

	/**
	 * Tests the JHtmlBatch::item method.
	 *
	 * @param	string	$extension
	 *
	 * @dataProvider dataTestItem
	 */
	public function testItem($extension)
	{
		$this->assertThat(
			JHtmlBatch::item($extension),
			$this->StringContains('<option value="2">Uncategorised</option>')
		);
	}
}