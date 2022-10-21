<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Webservices.Media
 *
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Router\ApiRouter;
use Joomla\Router\Route;

/**
 * Web Services adapter for com_media.
 *
 * @since  4.1.0
 */
class PlgWebservicesMedia extends CMSPlugin
{
	/**
	 * Load the language file on instantiation.
	 *
	 * @var    boolean
	 * @since  4.1.0
	 */
	protected $autoloadLanguage = true;

	/**
	 * Registers com_media's API's routes in the application.
	 *
	 * @param   ApiRouter  &$router  The API Routing object
	 *
	 * @return  void
	 *
	 * @since   4.1.0
	 */
	public function onBeforeApiRoute(&$router): void
	{
		$this->createAdapterReadRoutes(
			$router,
			'v1/media/adapters',
			'adapters',
			['component' => 'com_media']
		);
		$this->createMediaCRUDRoutes(
			$router,
			'v1/media/files',
			'media',
			['component' => 'com_media']
		);
	}

	/**
	 * Creates adapter read routes.
	 *
	 * @param   ApiRouter  &$router     The API Routing object
	 * @param   string     $baseName    The base name of the component.
	 * @param   string     $controller  The name of the controller that contains CRUD functions.
	 * @param   array      $defaults    An array of default values that are used when the URL is matched.
	 * @param   bool       $publicGets  Allow the public to make GET requests.
	 *
	 * @return  void
	 *
	 * @since   4.1.0
	 */
	private function createAdapterReadRoutes(&$router, $baseName, $controller, $defaults = [], $publicGets = false): void
	{
		$getDefaults = array_merge(['public' => $publicGets], $defaults);

		$routes = [
			new Route(['GET'], $baseName, $controller . '.displayList', [], $getDefaults),
			new Route(['GET'], $baseName . '/:id', $controller . '.displayItem', [], $getDefaults),
		];

		$router->addRoutes($routes);
	}

	/**
	 * Creates media CRUD routes.
	 *
	 * @param   ApiRouter  &$router     The API Routing object
	 * @param   string     $baseName    The base name of the component.
	 * @param   string     $controller  The name of the controller that contains CRUD functions.
	 * @param   array      $defaults    An array of default values that are used when the URL is matched.
	 * @param   bool       $publicGets  Allow the public to make GET requests.
	 *
	 * @return  void
	 *
	 * @since   4.1.0
	 */
	private function createMediaCRUDRoutes(&$router, $baseName, $controller, $defaults = [], $publicGets = false): void
	{
		$getDefaults = array_merge(['public' => $publicGets], $defaults);

		$routes = [
			new Route(['GET'], $baseName, $controller . '.displayList', [], $getDefaults),
			// When the path ends with a backslash, then list the items
			new Route(['GET'], $baseName . '/:path/', $controller . '.displayList', ['path' => '.*\/'], $getDefaults),
			new Route(['GET'], $baseName . '/:path', $controller . '.displayItem', ['path' => '.*'], $getDefaults),
			new Route(['POST'], $baseName, $controller . '.add', [], $defaults),
			new Route(['PATCH'], $baseName . '/:path', $controller . '.edit', ['path' => '.*'], $defaults),
			new Route(['DELETE'], $baseName . '/:path', $controller . '.delete', ['path' => '.*'], $defaults),
		];

		$router->addRoutes($routes);
	}
}
