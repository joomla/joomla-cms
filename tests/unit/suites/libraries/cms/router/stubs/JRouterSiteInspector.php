<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Router
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Inspector for JRouterSite
 *
 * @package     Joomla.UnitTest
 * @subpackage  Router
 * @since       3.4
 */
class JRouterSiteInspector extends JRouterSite
{
	public function getApp()
	{
		return $this->app;
	}

	public function setApp($app)
	{
		$this->app = $app;
	}

	public function getMenu()
	{
		return $this->menu;
	}

	public function setMenu($menu)
	{
		$this->menu = $menu;
	}

	/**
	 * Runs the protected parseRawRoute() method
	 *
	 * @param   JUri   $uri  JUri object of the URL
 	 *
	 * @return  array  Array of URL vars
	 *
	 * @since   3.4
	 */
	public function runParseRawRoute(&$uri)
	{
		return $this->parseRawRoute($uri);
	}

	/**
	 * Runs the protected parseSefRoute() method
	 *
	 * @param   JUri   $uri  JUri object of the URL
 	 *
	 * @return  array  Array of URL vars
	 *
	 * @since   3.4
	 */
	public function runParseSefRoute(&$uri)
	{
		return $this->parseSefRoute($uri);
	}

	/**
	 * Runs the protected buildRawRoute() method
	 *
	 * @param   JUri   $uri  JUri object of the URL
 	 *
	 * @return  array  Array of URL vars
	 *
	 * @since   3.4
	 */
	public function runBuildRawRoute(&$uri)
	{
		return $this->buildRawRoute($uri);
	}

	/**
	 * Runs the protected buildSefRoute() method
	 *
	 * @param   JUri   $uri  JUri object of the URL
 	 *
	 * @return  array  Array of URL vars
	 *
	 * @since   3.4
	 */
	public function runBuildSefRoute(&$uri)
	{
		return $this->buildSefRoute($uri);
	}

	/**
	 * Runs the protected processParseRules() method
	 *
	 * @param   JUri   $uri  JUri object of the URL
 	 *
	 * @return  array  Array of URL vars
	 *
	 * @since   3.4
	 */
	public function runProcessParseRules(&$uri)
	{
		return $this->processParseRules($uri);
	}

	/**
	 * Runs the protected processBuildRules() method
	 *
	 * @param   JUri   $uri  JUri object of the URL
 	 *
	 * @return  array  Array of URL vars
	 *
	 * @since   3.4
	 */
	public function runProcessBuildRules(&$uri)
	{
		return $this->processBuildRules($uri);
	}

	/**
	 * Runs the protected createURI() method
	 *
	 * @param   mixed   $url  URL to process
 	 *
	 * @return  JUri  JUri object of the URL
	 *
	 * @since   3.4
	 */
	public function runCreateURI($url)
	{
		return $this->createURI($url);
	}
}

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
