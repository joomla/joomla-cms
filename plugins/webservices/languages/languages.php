<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Webservices.Languages
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Router\ApiRouter;
use Joomla\Router\Route;

/**
 * Web Services adapter for com_languages.
 *
 * @since  4.0.0
 */
class PlgWebservicesLanguages extends CMSPlugin
{
	/**
	 * Load the language file on instantiation.
	 *
	 * @var    boolean
	 * @since  4.0.0
	 */
	protected $autoloadLanguage = true;

	/**
	 * Registers com_languages's API's routes in the application
	 *
	 * @param   ApiRouter  &$router  The API Routing object
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	public function onBeforeApiRoute(&$router)
	{
		$defaults    = array('component' => 'com_privacy');
		$getDefaults = array_merge(array('public' => false), $defaults);

		$routes = array(
			new Route(['GET'], 'v1/languages', 'languages.displayList', [], $getDefaults),
		);

		$router->addRoutes($routes);
	}
}
