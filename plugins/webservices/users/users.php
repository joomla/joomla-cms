<?php
/**
 * @package     Joomla.Users
 * @subpackage  Webservices.Users
 *
 * @copyright   (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Router\ApiRouter;

/**
 * Web Services adapter for com_users.
 *
 * @since  4.0.0
 */
class PlgWebservicesUsers extends CMSPlugin
{
	/**
	 * Load the language file on instantiation.
	 *
	 * @var    boolean
	 * @since  4.0.0
	 */
	protected $autoloadLanguage = true;

	/**
	 * Registers com_users's API's routes in the application
	 *
	 * @param   ApiRouter  &$router  The API Routing object
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	public function onBeforeApiRoute(&$router)
	{
		$router->createCRUDRoutes(
			'v1/users',
			'users',
			['component' => 'com_users']
		);

		$this->createFieldsRoutes($router);
	}

	/**
	 * Create fields routes
	 *
	 * @param   ApiRouter  &$router  The API Routing object
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	private function createFieldsRoutes(&$router)
	{
		$router->createCRUDRoutes(
			'v1/fields/users',
			'fields',
			['component' => 'com_fields', 'context' => 'com_users.user']
		);

		$router->createCRUDRoutes(
			'v1/fields/groups/users',
			'groups',
			['component' => 'com_fields', 'context' => 'com_users.user']
		);
	}
}
