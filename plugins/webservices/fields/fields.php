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
		$router->createCRUDRoutes(
			'v1/fields/articles',
			'fields',
			['component' => 'com_fields', 'context' => 'com_content.article']
		);

		$router->createCRUDRoutes(
			'v1/fields/categories',
			'fields',
			['component' => 'com_fields', 'context' => 'com_content.categories']
		);

		$router->createCRUDRoutes(
			'v1/fields/groups/articles',
			'groups',
			['component' => 'com_fields', 'context' => 'com_content.article']
		);

		$router->createCRUDRoutes(
			'v1/fields/groups/categories',
			'groups',
			['component' => 'com_fields', 'context' => 'com_content.categories']
		);
	}
}
