<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Pagination
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for JPaginationObject.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Pagination
 * @since       3.2
 */
class JPaginationObjectTest extends TestCase
{
	/**
	 * Provides the data to test the constructor method.
	 *
	 * @return  array
	 *
	 * @since   3.1
	 */
	public function dataTestConstructor()
	{
		return array(
			array(JText::_('JPREV'), '', null, null, false,
				array(
					'text' => 'Prev',
					'prefix' => '',
					'base' => null,
					'link' => null,
					'active' => false,
				)
			),
			array(JText::_('JLIB_HTML_START'), 4, 2, 'http://www.example.com', true,
				array(
					'text' => 'JLIB_HTML_START',
					'prefix' => 4,
					'base' => 2,
					'link' => 'http://www.example.com',
					'active' => true,
				)
			),
		);
	}

	/**
	 * This method tests the constructor.
	 *
	 * This is a basic data driven test. It takes the data passed, runs the constructor
	 * and make sure the appropriate values get setup.
	 *
     * @param   string   $text      The link text.
     * @param   integer  $prefix    The prefix used for request variables.
     * @param   integer  $base      The number of rows as a base offset.
     * @param   string   $link      The link URL.
     * @param   boolean  $active    Flag whether the object is the 'active' page
	 * @param   array    $expected  The expected results for the JPagination object
	 *
	 * @return  void
	 *
	 * @covers        JPaginationObject::__construct
	 * @dataProvider  dataTestConstructor
	 * @since         3.1
	 */
	public function testConstructor($text, $prefix = '', $base = null, $link = null, $active = false, $expected)
	{
		$pagination = new JPaginationObject($text, $prefix, $base, $link, $active);

		$this->assertEquals($expected['text'], $pagination->text, 'Wrong Text');

		$this->assertEquals($expected['prefix'], $pagination->prefix, 'Wrong Prefix');

		$this->assertEquals($expected['base'], $pagination->base, 'Wrong Base');

		$this->assertEquals($expected['link'], $pagination->link, 'Wrong Link');

		$this->assertEquals($expected['active'], $pagination->active, 'Wrong Active');

		unset($pagination);
	}
}
