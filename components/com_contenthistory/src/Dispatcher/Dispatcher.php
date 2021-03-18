<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_contenthistory
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Contenthistory\Site\Dispatcher;

\defined('_JEXEC') or die;

use Joomla\CMS\Access\Exception\NotAllowed;
use Joomla\CMS\Dispatcher\ComponentDispatcher;
use Joomla\CMS\MVC\Controller\BaseController;

/**
 * ComponentDispatcher class for com_contenthistory
 *
 * @since  4.0.0
 */
class Dispatcher extends ComponentDispatcher
{
	/**
	 * Load the language
	 *
	 * @since   4.0.0
	 *
	 * @return  void
	 */
	protected function loadLanguage()
	{
		// Load common and local language files.
		$this->app->getLanguage()->load($this->option, JPATH_ADMINISTRATOR) ||
		$this->app->getLanguage()->load($this->option, JPATH_SITE);
	}

	/**
	 * Method to check component access permission
	 *
	 * @since   4.0.0
	 *
	 * @return  void
	 *
	 * @throws  \Exception|Notallowed
	 */
	protected function checkAccess()
	{
		// Check the user has permission to access this component if in the backend
		if ($this->app->getIdentity()->guest)
		{
			throw new Notallowed($this->app->getLanguage()->_('JERROR_ALERTNOAUTHOR'), 403);
		}
	}

	/**
	 * Get a controller from the component
	 *
	 * @param   string  $name    Controller name
	 * @param   string  $client  Optional client (like Administrator, Site etc.)
	 * @param   array   $config  Optional controller config
	 *
	 * @return  BaseController
	 *
	 * @since   4.0.0
	 */
	public function getController(string $name, string $client = '', array $config = array()): BaseController
	{
		$config['base_path'] = JPATH_COMPONENT_ADMINISTRATOR;
		$client = 'Administrator';

		return parent::getController($name, $client, $config);
	}
}
