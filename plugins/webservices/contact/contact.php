<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Webservices.Contact
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
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
			'v1/contact/form/:id',
			'contact.submitForm',
			['id' => '(\d+)'],
			['component' => 'com_contact']
		);

		$router->addRoute($route);

		$router->createCRUDRoutes(
			'v1/contact',
			'contact',
			['component' => 'com_contact']
		);

		$router->createCRUDRoutes(
			'v1/contact/categories',
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
			'v1/fields/contact/contact',
			'fields',
			['component' => 'com_fields', 'context' => 'com_contact.contact']
		);

		$router->createCRUDRoutes(
			'v1/fields/contact/mail',
			'fields',
			['component' => 'com_fields', 'context' => 'com_contact.mail']
		);

		$router->createCRUDRoutes(
			'v1/fields/contact/categories',
			'fields',
			['component' => 'com_fields', 'context' => 'com_contact.categories']
		);

		$router->createCRUDRoutes(
			'v1/fields/groups/contact/contact',
			'groups',
			['component' => 'com_fields', 'context' => 'com_contact.contact']
		);

		$router->createCRUDRoutes(
			'v1/fields/groups/contact/mail',
			'groups',
			['component' => 'com_fields', 'context' => 'com_contact.mail']
		);

		$router->createCRUDRoutes(
			'v1/fields/groups/contact/categories',
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
			'type_id'    => 2
		];
		$getDefaults = array_merge(['public' => false], $defaults);

		$routes = [
			new Route(['GET'], 'v1/contact/contenthistory/:id', 'history.displayList', ['id' => '(\d+)'], $getDefaults),
			new Route(['PATCH'], 'v1/contact/contenthistory/keep/:id', 'history.keep', ['id' => '(\d+)'], $defaults),
			new Route(['DELETE'], 'v1/contact/contenthistory/:id', 'history.delete', ['id' => '(\d+)'], $defaults),
		];

		$router->addRoutes($routes);
	}
}
