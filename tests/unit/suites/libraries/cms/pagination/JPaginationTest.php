<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Pagination
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for JPagination.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Pagination
 * @since       3.1
 */
class JPaginationTest extends TestCase
{
	/**
	 * Setup for testing.
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	public function setUp()
	{
		parent::setUp();

		// We are only coupled to Application and Language in JFactory.
		$this->saveFactoryState();

		JFactory::$language = $this->getMockLanguage();
		JFactory::$application = $this->getMockApplication();
	}

	/**
	 * Overrides the parent tearDown method.
	 *
	 * @return  void
	 *
	 * @see     PHPUnit_Framework_TestCase::tearDown()
	 * @since   3.1
	 */
	protected function tearDown()
	{
		$this->restoreFactoryState();

		parent::tearDown();
	}

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
			),
			
			array(10, 20, 20,
				array(
					'total' => 10,
					'limitstart' => 0,
					'limit' => 20,
					'pages.total' => 1,
					'pages.current' => 1,
					'pages.start' => 1,
					'pages.stop' => 1
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
	 * @param   integer  $total       The total number of items.
	 * @param   integer  $limitstart  The offset of the item to start at.
	 * @param   integer  $limit       The number of items to display per page.
	 * @param   string   $expected    The expected results for the JPagination object
	 *
	 * @return  void
	 *
	 * @dataProvider  dataTestConstructor
	 * @since         3.1
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

	/**
	 * Provides the data to test the getPagesLinks method.
	 *
	 * @return  array
	 *
	 * @since   3.2
	 */
	public function dataTestGetPagesLinks()
	{
		return array(
			array(100, 50, 20, '<ul><li class="pagination-start"><a class="hasTooltip" title="JLIB_HTML_START" href="" class="pagenav">JLIB_HTML_START</a></li><li class="pagination-prev"><a class="hasTooltip" title="JPREV" href="" class="pagenav">JPREV</a></li><li><a href="" class="pagenav">1</a></li><li><a href="" class="pagenav">2</a></li><li><span class="pagenav">3</span></li><li><a href="" class="pagenav">4</a></li><li><a href="" class="pagenav">5</a></li><li class="pagination-next"><a class="hasTooltip" title="JNEXT" href="" class="pagenav">JNEXT</a></li><li class="pagination-end"><a class="hasTooltip" title="JLIB_HTML_END" href="" class="pagenav">JLIB_HTML_END</a></li></ul>'),
		);
	}

	/**
	 * This method tests the getPagesLinks method.
	 *
	 * This is a basic data driven test.  It takes the data passed, runs the constructor
	 * and make sure the appropriate values get setup.
	 *
	 * @param   integer  $total       The total number of items.
	 * @param   integer  $limitstart  The offset of the item to start at.
	 * @param   integer  $limit       The number of items to display per page.
	 * @param   string   $expected    The expected results for the JPagination object
	 *
	 * @return  void
	 *
	 * @dataProvider  dataTestGetPagesLinks
	 * @since         3.2
	 */
	public function testGetPagesLinks($total, $limitstart, $limit, $expected)
	{
		$pagination = new JPagination($total, $limitstart, $limit);

		$result = $pagination->getPagesLinks();

		$this->assertEquals($result, $expected, 'The expected output of the pagination is incorrect');

		unset($pagination);
	}
}
