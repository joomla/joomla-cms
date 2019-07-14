<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Webservices.Fields
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Router\ApiRouter;

/**
 * Web Services adapter for com_fields.
 *
 * @since  4.0.0
 */
class PlgWebservicesFields extends CMSPlugin
{
	/**
	 * Load the language file on instantiation.
	 *
	 * @var    boolean
	 * @since  4.0.0
	 */
	protected $autoloadLanguage = true;

	/**
	 * Registers com_fields's API's routes in the application
	 *
	 * @param   ApiRouter  &$router  The API Routing object
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	public function onBeforeApiRoute(&$router)
	{
		$this->createContentRoutes($router);
		$this->createUserRoutes($router);
		$this->createContactRoutes($router);
	}

	/**
	 * Create content routes
	 *
	 * @param   ApiRouter  &$router  The API Routing object
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	private function createContentRoutes(&$router)
	{
		$router->createCRUDRoutes(
			'v1/fields/content/articles',
			'fields',
			['component' => 'com_fields', 'context' => 'com_content.article']
		);

		$router->createCRUDRoutes(
			'v1/fields/content/categories',
			'fields',
			['component' => 'com_fields', 'context' => 'com_content.categories']
		);

		$router->createCRUDRoutes(
			'v1/fields/groups/content/articles',
			'groups',
			['component' => 'com_fields', 'context' => 'com_content.article']
		);

		$router->createCRUDRoutes(
			'v1/fields/groups/content/categories',
			'groups',
			['component' => 'com_fields', 'context' => 'com_content.categories']
		);
	}

	/**
	 * Create user routes
	 *
	 * @param   ApiRouter  &$router  The API Routing object
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	private function createUserRoutes(&$router)
	{
		$router->createCRUDRoutes(
			'v1/fields/user/users',
			'fields',
			['component' => 'com_fields', 'context' => 'com_users.user']
		);

		$router->createCRUDRoutes(
			'v1/fields/groups/user/users',
			'groups',
			['component' => 'com_fields', 'context' => 'com_users.user']
		);
	}

	/**
	 * Create contact routes
	 *
	 * @param   ApiRouter  &$router  The API Routing object
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	private function createContactRoutes(&$router)
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
}
