<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_content
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Routing class of com_content
 *
 * @since  3.3
 */
class ContentRouter extends JComponentRouterAdvanced
{
	function __construct($app = null, $menu = null)
	{
		$this->registerView('categories', 'categories');
		$this->registerView('category', 'category', 'id', 'categories', '', true, array('default', 'blog'));
		$this->registerView('article', 'article', 'id', 'category', 'catid');
		$this->registerView('archive', 'archive');
		$this->registerView('featured', 'featured');
		$this->registerView('form', 'form');

		parent::__construct($app, $menu);

		$this->attachRule(new JComponentRouterRulesMenu($this));
		require_once JPATH_SITE . '/components/com_content/helpers/legacyrouter.php';
		$this->attachRule(new ContentRouterRulesLegacy($this));
	}
}

/**
 * Content router functions
 *
 * These functions are proxys for the new router interface
 * for old SEF extensions.
 *
 * @param   array  &$query  An array of URL arguments
 *
 * @return  array  The URL arguments to use to assemble the subsequent URL.
 *
 * @deprecated  4.0  Use Class based routers instead
 */
function contentBuildRoute(&$query)
{
	$app = JFactory::getApplication();
	$router = new ContentRouter($app, $app->getMenu());

	return $router->build($query);
}

/**
 * Parse the segments of a URL.
 *
 * This function is a proxy for the new router interface
 * for old SEF extensions.
 *
 * @param   array  $segments  The segments of the URL to parse.
 *
 * @return  array  The URL attributes to be used by the application.
 *
 * @since   3.3
 * @deprecated  4.0  Use Class based routers instead
 */
function contentParseRoute($segments)
{
	$app = JFactory::getApplication();
	$router = new ContentRouter($app, $app->getMenu());

	return $router->parse($segments);
}
