<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_users
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Routing class from com_users
 *
 * @since  3.2
 */
class UsersRouter extends JComponentRouterView
{
	/**
	 * Users Component router constructor
	 *
	 * @param   JApplicationCms  $app   The application object
	 * @param   JMenu            $menu  The menu object to work with
	 */
	public function __construct($app = null, $menu = null)
	{
		$this->registerView(new JComponentRouterViewconfiguration('login'));
		$profile = new JComponentRouterViewconfiguration('profile');
		$profile->addLayout('edit');
		$this->registerView($profile);
		$this->registerView(new JComponentRouterViewconfiguration('registration'));
		$this->registerView(new JComponentRouterViewconfiguration('remind'));
		$this->registerView(new JComponentRouterViewconfiguration('reset'));

		parent::__construct($app, $menu);

		$this->attachRule(new JComponentRouterRulesMenu($this));

		$params = JComponentHelper::getParams('com_users');

		if ($params->get('sef_advanced', 0))
		{
			$this->attachRule(new JComponentRouterRulesStandard($this));
			$this->attachRule(new JComponentRouterRulesNomenu($this));
		}
		else
		{
			JLoader::register('UsersRouterRulesLegacy', __DIR__ . '/helpers/legacyrouter.php');
			$this->attachRule(new UsersRouterRulesLegacy($this));
		}
	}
}

/**
 * Users router functions
 *
 * These functions are proxys for the new router interface
 * for old SEF extensions.
 *
 * @param   array  &$query  REQUEST query
 *
 * @return  array  Segments of the SEF url
 *
 * @deprecated  4.0  Use Class based routers instead
 */
function usersBuildRoute(&$query)
{
	$app = JFactory::getApplication();
	$router = new UsersRouter($app, $app->getMenu());

	return $router->build($query);
}

/**
 * Convert SEF URL segments into query variables
 *
 * @param   array  $segments  Segments in the current URL
 *
 * @return  array  Query variables
 *
 * @deprecated  4.0  Use Class based routers instead
 */
function usersParseRoute($segments)
{
	$app = JFactory::getApplication();
	$router = new UsersRouter($app, $app->getMenu());

	return $router->parse($segments);
}
