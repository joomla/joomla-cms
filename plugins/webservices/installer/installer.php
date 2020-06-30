<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Webservices.Installer
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Router\ApiRouter;
use Joomla\Router\Route;

/**
 * Web Services adapter for com_installer.
 *
 * @since  4.0.0
 */
class PlgWebservicesInstaller extends CMSPlugin
{
	/**
	 * Load the language file on instantiation.
	 *
	 * @var    boolean
	 * @since  4.0.0
	 */
	protected $autoloadLanguage = true;

	/**
	 * Registers com_installer's API's routes in the application
	 *
	 * @param   ApiRouter  &$router  The API Routing object
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	public function onBeforeApiRoute(&$router)
	{
		$defaults    = ['component' => 'com_installer', 'public' => false];

		$routes = [
			new Route(['GET'], 'v1/installer/manage', 'manage.displayList', [], $defaults)
		];

		$router->addRoutes($routes);
	}
}
