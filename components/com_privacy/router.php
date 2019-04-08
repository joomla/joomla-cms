<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_privacy
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Routing class from com_privacy
 *
 * @since  3.9.0
 */
class PrivacyRouter extends JComponentRouterView
{
	/**
	 * Privacy Component router constructor
	 *
	 * @param   JApplicationCms  $app   The application object
	 * @param   JMenu            $menu  The menu object to work with
	 *
	 * @since   3.9.0
	 */
	public function __construct($app = null, $menu = null)
	{
		$this->registerView(new JComponentRouterViewconfiguration('confirm'));
		$this->registerView(new JComponentRouterViewconfiguration('request'));
		$this->registerView(new JComponentRouterViewconfiguration('remind'));

		parent::__construct($app, $menu);

		$this->attachRule(new JComponentRouterRulesMenu($this));
		$this->attachRule(new JComponentRouterRulesStandard($this));
		$this->attachRule(new JComponentRouterRulesNomenu($this));
	}
}

/**
 * Privacy router functions
 *
 * These functions are proxys for the new router interface
 * for old SEF extensions.
 *
 * @param   array  &$query  REQUEST query
 *
 * @return  array  Segments of the SEF url
 *
 * @since   3.9.0
 * @deprecated  4.0  Use Class based routers instead
 */
function privacyBuildRoute(&$query)
{
	$app = JFactory::getApplication();
	$router = new PrivacyRouter($app, $app->getMenu());

	return $router->build($query);
}

/**
 * Convert SEF URL segments into query variables
 *
 * @param   array  $segments  Segments in the current URL
 *
 * @return  array  Query variables
 *
 * @since   3.9.0
 * @deprecated  4.0  Use Class based routers instead
 */
function privacyParseRoute($segments)
{
	$app = JFactory::getApplication();
	$router = new PrivacyRouter($app, $app->getMenu());

	return $router->parse($segments);
}
