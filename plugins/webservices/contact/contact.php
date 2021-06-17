<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Webservices.Contact
 *
 * @copyright   (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Router\ApiRouter;
use Joomla\Router\Route;

/**
 * Web Services adapter for com_contact.
 *
 * @since  4.0.0
 */
class PlgWebservicesContact extends CMSPlugin
{
	/**
	 * Load the language file on instantiation.
	 *
	 * @var    boolean
	 * @since  4.0.0
	 */
	protected $autoloadLanguage = true;

	/**
	 * Registers com_contact's API's routes in the application
	 *
	 * @param   ApiRouter  &$router  The API Routing object
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	public function onBeforeApiRoute(&$router)
	{
		$route = new Route(
			['POST'],
			'v1/contacts/form/:id',
			'contact.submitForm',
			['id' => '(\d+)'],
			['component' => 'com_contact']
		);

		$router->addRoute($route);

		$router->createCRUDRoutes(
			'v1/contacts',
			'contact',
			['component' => 'com_contact']
		);

		$router->createCRUDRoutes(
			'v1/contacts/categories',
			'categories',
			['component' => 'com_categories', 'extension' => 'com_contact']
		);

		$this->createFieldsRoutes($router);

		$this->createContentHistoryRoutes($router);
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
			'v1/fields/contacts/contact',
			'fields',
			['component' => 'com_fields', 'context' => 'com_contact.contact']
		);

		$router->createCRUDRoutes(
			'v1/fields/contacts/mail',
			'fields',
			['component' => 'com_fields', 'context' => 'com_contact.mail']
		);

		$router->createCRUDRoutes(
			'v1/fields/contacts/categories',
			'fields',
			['component' => 'com_fields', 'context' => 'com_contact.categories']
		);

		$router->createCRUDRoutes(
			'v1/fields/groups/contacts/contact',
			'groups',
			['component' => 'com_fields', 'context' => 'com_contact.contact']
		);

		$router->createCRUDRoutes(
			'v1/fields/groups/contacts/mail',
			'groups',
			['component' => 'com_fields', 'context' => 'com_contact.mail']
		);

		$router->createCRUDRoutes(
			'v1/fields/groups/contacts/categories',
			'groups',
			['component' => 'com_fields', 'context' => 'com_contact.categories']
		);
	}

	/**
	 * Create contenthistory routes
	 *
	 * @param   ApiRouter  &$router  The API Routing object
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	private function createContentHistoryRoutes(&$router)
	{
		$defaults    = [
			'component'  => 'com_contenthistory',
			'type_alias' => 'com_contact.contact',
			'type_id'    => 2,
		];
		$getDefaults = array_merge(['public' => false], $defaults);

		$routes = [
			new Route(['GET'], 'v1/contacts/:id/contenthistory', 'history.displayList', ['id' => '(\d+)'], $getDefaults),
			new Route(['PATCH'], 'v1/contacts/:id/contenthistory/keep', 'history.keep', ['id' => '(\d+)'], $defaults),
			new Route(['DELETE'], 'v1/contacts/:id/contenthistory', 'history.delete', ['id' => '(\d+)'], $defaults),
		];

		$router->addRoutes($routes);
	}
}
