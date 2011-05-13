<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  HTML
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

require_once JPATH_PLATFORM.'/joomla/html/pagination.php';

class JPaginationTest extends PHPUnit_Framework_TestCase
{
	/**
	 * Provides the data to test the contructor method.
	 *
	 * @return  array
	 *
	 * @since   11.1
	 */
	public function dataTestConstructor()
	{
		return array(
			array(100, 0, 20,
				array(
					'total' => 100,
					'limitstart' => 0,
					'limit' => 20,
					'pages.total' => 5,
					'pages.current' => 1,
					'pages.start' => 1,
					'pages.stop' => 5
				)
			),
			// these tests currently break in trunk, but I set the values to what I thought they should be.
			array(100, 101, 20,
				array(
					'total' => 100,
					'limitstart' => 80,
					'limit' => 20,
					'pages.total' => 5,
					'pages.current' => 5,
					'pages.start' => 1,
					'pages.stop' => 5
				)
			),
			array(100, 201, 20,
				array(
					'total' => 100,
					'limitstart' => 80,
					'limit' => 20,
					'pages.total' => 5,
					'pages.current' => 5,
					'pages.start' => 1,
					'pages.stop' => 5
				)
			)

		);
	}

	/**
	 * This method tests the.
	 *
	 * This is a basic data driven test.  It takes the data passed, runs the constructor
	 * and make sure the appropriate values get setup.
	 *
	 * @return  void
	 *
	 * @since   11.1
	 * @dataProvider dataTestConstructor
	 */
	public function testConstructor($total, $limitstart, $limit, $expected)
	{
		$pagination = new JPagination($total, $limitstart, $limit);

		$this->assertEquals($expected['total'], $pagination->total, 'Wrong Total');

		$this->assertEquals($expected['limitstart'], $pagination->limitstart, 'Wrong Limitstart');

		$this->assertEquals($expected['limit'], $pagination->limit, 'Wrong Limit');

		$this->assertEquals($expected['pages.total'], $pagination->get('pages.total'), 'Wrong Total Pages');

		$this->assertEquals($expected['pages.current'], $pagination->get('pages.current'), 'Wrong Current Page');

		$this->assertEquals($expected['pages.start'], $pagination->get('pages.start'), 'Wrong Start Page');

		$this->assertEquals($expected['pages.stop'], $pagination->get('pages.stop'), 'Wrong Stop Page');

		unset($pagination);
	}
}