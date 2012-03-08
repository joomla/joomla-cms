<?php
/**
 * @version   $Id: JControllerHelper.php 20196 2011-01-09 02:40:25Z ian $
 * @copyright Copyright (C) 2005 - 2012 Open Source Matters. All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 */

class TestController extends JController
{
	public function task1()
	{
	}

	public function task2()
	{
	}

	protected function task3()
	{
	}

	private function _task4()
	{
	}
}

class TestTestController extends TestController
{
	public function task5()
	{
	}
}