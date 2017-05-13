<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_contenthistory
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Access\Exception\Notallowed;
use Joomla\CMS\Controller\Controller;
use Joomla\CMS\Dispatcher\Dispatcher;

/**
 * Dispatcher class for com_contenthistory
 *
 * @since  __DEPLOY_VERSION__
 */
class ContenthistoryDispatcher extends Dispatcher
{
	/**
	 * The extension namespace
	 *
	 * @var    string
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	protected $namespace = 'Joomla\\Component\\Contenthistory';

	/**
	 * Load the language
	 *
	 * @since   __DEPLOY_VERSION__
	 *
	 * @return  void
	 */
	protected function loadLanguage()
	{
		// Load common and local language files.
		$this->app->getLanguage()->load($this->option, JPATH_ADMINISTRATOR, null, false, true) ||
		$this->app->getLanguage()->load($this->option, JPATH_SITE, null, false, true);
	}

	/**
	 * Method to check component access permission
	 *
	 * @since   __DEPLOY_VERSION__
	 *
	 * @return  void
	 */
	protected function checkAccess()
	{
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
	 * @return  Controller
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getController($name, $client = null, $config = array())
	{
		$config['base_path'] = JPATH_COMPONENT_ADMINISTRATOR;
		
		return parent::getController($name, $client, $config);
	}
}
