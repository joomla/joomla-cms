<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_menus
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Menus\Site\Dispatcher;

defined('_JEXEC') or die;

use Joomla\CMS\Access\Exception\NotAllowed;
use Joomla\CMS\MVC\Controller\BaseController;

/**
 * Dispatcher class for com_menus
 *
 * @since  4.0.0
 */
class Dispatcher extends \Joomla\CMS\Dispatcher\Dispatcher
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
		$this->app->getLanguage()->load('joomla', JPATH_ADMINISTRATOR);
		$this->app->getLanguage()->load('com_menus', JPATH_ADMINISTRATOR);
	}

	/**
	 * Dispatch a controller task. Redirecting the user if appropriate.
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	public function checkAccess()
	{
		parent::checkAccess();

		if ($this->input->get('view') === 'items'
			&& $this->input->get('layout') === 'modal'
			&& !$this->app->getIdentity()->authorise('core.create', 'com_menus'))
		{
			throw new NotAllowed;
		}
	}

	/**
	 * Get a controller from the component
	 *
	 * @param   string  $name    Controller name
	 * @param   string  $client  Optional client (like Administrator, Site etc.)
	 * @param   array   $config  Optional controller config
	 *
	 * @return  \Joomla\CMS\MVC\Controller\BaseController
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
