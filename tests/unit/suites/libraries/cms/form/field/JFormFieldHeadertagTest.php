<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for JFormFieldHeadertag.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Form
 * @since       3.1
 */
class JFormFieldHeadertagTest extends PHPUnit_Framework_TestCase
{
	/**
	 * Tests the getInput method.
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	public function testGetInput()
	{
		$field = new JFormFieldHeadertag;
		$field->setup(
			new SimpleXMLElement('<field name="headertag" type="headertag" label="Header Tag" description="Header Tag listing" />'),
			'value'
		);

		$this->assertContains(
			'<option value="h3">h3</option>',
			$field->input,
			'The getInput method should return an option with the header tags, verify H3 tag is in list.'
		);
	}
}
