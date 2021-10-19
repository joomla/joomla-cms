<?php
/**
 * @package         Joomla.Administrator
 * @subpackage      com_users
 *
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license         GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Users\Administrator\Dispatcher;

\defined('_JEXEC') or die;

use Joomla\CMS\Dispatcher\ComponentDispatcher;

/**
 * ComponentDispatcher class for com_users
 *
 * @since  4.0.0
 */
class Dispatcher extends ComponentDispatcher
{
	/**
	 * Override checkAccess to allow users edit profile without having to have core.manager permission
	 *
	 * @return  void
	 *
	 * @since  4.0.0
	 */
	protected function checkAccess()
	{
		$task         = $this->input->getCmd('task');
		$view         = $this->input->getCmd('view');
		$layout       = $this->input->getCmd('layout');
		$allowedTasks = ['user.edit', 'user.apply', 'user.save', 'user.cancel'];

		// Allow users to edit their own account
		if (in_array($task, $allowedTasks, true) || ($view === 'user' && $layout === 'edit'))
		{
			$user = $this->app->getIdentity();
			$id   = $this->input->getInt('id');

			if ((int) $user->id === $id)
			{
				return;
			}
		}

		parent::checkAccess();
	}
}
