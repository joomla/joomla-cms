<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_config
 *
 * @copyright   (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Config\Site\Dispatcher;

\defined('_JEXEC') or die;

use Joomla\CMS\Access\Exception\NotAllowed;
use Joomla\CMS\Dispatcher\ComponentDispatcher;

/**
 * ComponentDispatcher class for com_config
 *
 * @since  4.0.0
 */
class Dispatcher extends ComponentDispatcher
{
	/**
	 * Method to check component access permission
	 *
	 * @since   4.0.0
	 *
	 * @return  void
	 *
	 * @throws  Exception|NotAllowed
	 */
	protected function checkAccess()
	{
		parent::checkAccess();

		$task = $this->input->getCmd('task', 'display');
		$view = $this->input->get('view');
		$user = $this->app->getIdentity();

		if (substr($task, 0, 8) === 'modules.' || $view === 'modules')
		{
			if (!$user->authorise('module.edit.frontend', 'com_modules.module.' . $this->input->get('id')))
			{
				throw new NotAllowed($this->app->getLanguage()->_('JERROR_ALERTNOAUTHOR'), 403);
			}
		}
		elseif (!$user->authorise('core.admin'))
		{
			throw new NotAllowed($this->app->getLanguage()->_('JERROR_ALERTNOAUTHOR'), 403);
		}
	}
}
