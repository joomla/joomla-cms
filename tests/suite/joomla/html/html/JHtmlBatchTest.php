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
	 * Tests the JHtmlBatch::access method.
	 */
	public function testAccess()
	{
		$this->assertThat(
			JHtmlBatch::access(),
			$this->StringContains('<option value="1">Public</option>')
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
			// Element order: extension
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
	public function testItem()
	{
		$this->assertThat(
			JHtmlBatch::item($extension),
			$this->StringContains('<option value="2">Uncategorised</option>')
		);
	}
}