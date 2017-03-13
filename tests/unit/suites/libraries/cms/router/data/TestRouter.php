<?php

/**
 * @package     Joomla.UnitTest
 * @subpackage  Router
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */
class TestRouter implements JComponentRouterInterface
{
	public function preprocess($query)
	{
		$query['testvar'] = 'testvalue';

		return $query;
	}

	public function parse(&$segments)
	{
		return array();
	}

	public function build(&$query)
	{
		return array();
	}
}

class Test2Router implements JComponentRouterInterface
{
	public function preprocess($query)
	{
		return $query;
	}

	public function parse(&$segments)
	{
		return array('testvar' => 'testvalue');
	}

	public function build(&$query)
	{
		return array('router-test', 'another-segment');
	}
}

class Test3Router implements JComponentRouterInterface
{
	public function preprocess($query)
	{
		return $query;
	}

	public function parse(&$segments)
	{
		return array();
	}

	public function build(&$query)
	{
		unset($query['Itemid']);

		return array();
	}
}
