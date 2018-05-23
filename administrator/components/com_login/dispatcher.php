<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_login
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Dispatcher\ComponentDispatcher;

/**
 * Dispatcher class for com_login
 *
 * @since  4.0.0
 */
class LoginDispatcher extends ComponentDispatcher
{
	/**
	 * The extension namespace
	 *
	 * @var    string
	 *
	 * @since  4.0.0
	 */
	protected $namespace = 'Joomla\\Component\\Login';

	/**
	 * Dispatch a controller task.
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	public function dispatch()
	{
		// Only accept two values login and logout for `task`
		$task = $this->input->get('task');

		if ($task != 'login' && $task != 'logout')
		{
			$this->input->set('task', '');
		}

		parent::dispatch();
	}

	/**
	 * com_login does not require check permission, so we override checkAccess method and have it empty
	 *
	 * @return  void
	 */
	protected function checkAccess()
	{

	}
}
