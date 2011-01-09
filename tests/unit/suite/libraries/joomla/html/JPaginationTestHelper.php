<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

class JPaginationTestHelper extends PHPUnit_Framework_TestCase
{
	public function constructorData()
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
}