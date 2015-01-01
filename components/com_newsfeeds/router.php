<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_newsfeeds
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Routing class from com_newsfeeds
 *
 * @since  3.3
 */
class NewsfeedsRouter extends JComponentRouterAdvanced
{
	function __construct($app = null, $menu = null)
	{
		$categories = new JComponentRouterViewconfiguration('categories');
		$categories->setKey('id');
		$this->registerView($categories);
		$category = new JComponentRouterViewconfiguration('category');
		$category->setKey('id')->setParent($categories, 'id')->setNestable();
		$this->registerView($category);
		$newsfeed = new JComponentRouterViewconfiguration('newsfeed');
		$newsfeed->setKey('id')->setParent($category, 'catid');
		$this->registerView($newsfeed);

		parent::__construct($app, $menu);

		$this->attachRule(new JComponentRouterRulesMenu($this));
		require_once JPATH_SITE . '/components/com_newsfeeds/helpers/legacyrouter.php';
		$this->attachRule(new NewsfeedsRouterRulesLegacy($this));
	}
}

/**
 * newsfeedsBuildRoute
 *
 * These functions are proxys for the new router interface
 * for old SEF extensions.
 *
 * @param   array  &$query  The segments of the URL to parse.
 *
 * @return array
 *
 * @deprecated  4.0  Use Class based routers instead
 */
function newsfeedsBuildRoute(&$query)
{
	$app = JFactory::getApplication();
	$router = new NewsfeedsRouter($app, $app->getMenu());

	return $router->build($query);
}

/**
 * newsfeedsParseRoute
 *
 * @param   array  $segments  The segments of the URL to parse.
 *
 * @return array
 *
 * @deprecated  4.0  Use Class based routers instead
 */
function newsfeedsParseRoute($segments)
{
	$app = JFactory::getApplication();
	$router = new NewsfeedsRouter($app, $app->getMenu());

	return $router->parse($segments);
}
