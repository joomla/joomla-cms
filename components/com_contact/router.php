<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_contact
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Routing class from com_contact
 *
 * @since  3.3
 */
class ContactRouter extends JComponentRouterAdvanced
{
	function __construct($app = null, $menu = null)
	{
		$this->registerView('categories', 'categories');
		$this->registerView('category', 'category', 'id', 'categories', '', true, array('default', 'blog'));
		$this->registerView('contact', 'contact', 'id', 'category', 'catid');
		$this->registerView('featured', 'featured');

		parent::__construct($app, $menu);

		$this->attachRule(new JComponentRouterRulesMenu($this));
		require_once JPATH_SITE . '/components/com_contact/helpers/legacyrouter.php';
		$this->attachRule(new ContactRouterRulesLegacy($this));
	}
}

/**
 * Contact router functions
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
function ContactBuildRoute(&$query)
{
	$app = JFactory::getApplication();
	$router = new ContactRouter($app, $app->getMenu());

	return $router->build($query);
}

/**
 * Contact router functions
 *
 * These functions are proxys for the new router interface
 * for old SEF extensions.
 *
 * @param   array  &$segments  The segments of the URL to parse.
 *
 * @return  array  The URL attributes to be used by the application.
 *
 * @deprecated  4.0  Use Class based routers instead
 */
function ContactParseRoute($segments)
{
	$app = JFactory::getApplication();
	$router = new ContactRouter($app, $app->getMenu());

	return $router->parse($segments);
}
