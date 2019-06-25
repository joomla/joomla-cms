<?php
/**
 * @package     Joomla.Privacy
 * @subpackage  Webservices.Privacy
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Router\ApiRouter;
use Joomla\Router\Route;

/**
 * Web Services adapter for com_privacy.
 *
 * @since  4.0.0
 */
class PlgWebservicesPrivacy extends CMSPlugin
{
	/**
	 * Load the language file on instantiation.
	 *
	 * @var    boolean
	 * @since  4.0.0
	 */
	protected $autoloadLanguage = true;

	/**
	 * Registers com_privacy's API's routes in the application
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
			new Route(['GET'], 'v1/privacy/request', 'request' . '.displayList', [], $getDefaults),
			new Route(['GET'], 'v1/privacy/request' . '/:id', 'request' . '.displayItem', ['id' => '(\d+)'], $getDefaults),
			new Route(['POST'], 'v1/privacy/request', 'request' . '.add', [], $defaults)
		);

		$router->addRoutes($routes);
	}
}
