<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

require_once dirname(__FILE__).'/JPaginationTestHelper.php';

class JPaginationTest extends PHPUnit_Framework_TestCase
{
	public function setUp()
	{
		jimport('joomla.html.pagination');
	}

	public function constructorProvider()
	{
		return JPaginationTestHelper::constructorData();
	}

	/**
	 * This method tests the.
	 *
	 * This is a basic data driven test.  It takes the data passed, runs the constructor
	 * and make sure the appropriate values get setup.
	 *
	 * @dataProvider constructorProvider
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