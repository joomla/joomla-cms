<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_users
 *
 * @copyright   (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Users\Administrator\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\Access\Access;
use Joomla\CMS\MVC\Controller\FormController;

/**
 * User view level controller class.
 *
 * @since  1.6
 */
class GroupController extends FormController
{
	/**
	 * @var	    string  The prefix to use with controller messages.
	 * @since   1.6
	 */
	protected $text_prefix = 'COM_USERS_GROUP';

	/**
	 * Method to check if you can save a new or existing record.
	 *
	 * Overrides Joomla\CMS\MVC\Controller\FormController::allowSave to check the core.admin permission.
	 *
	 * @param   array   $data  An array of input data.
	 * @param   string  $key   The name of the key for the primary key.
	 *
	 * @return  boolean
	 *
	 * @since   1.6
	 */
	protected function allowSave($data, $key = 'id')
	{
		return ($this->app->getIdentity()->authorise('core.admin', $this->option) && parent::allowSave($data, $key));
	}

	/**
	 * Overrides Joomla\CMS\MVC\Controller\FormController::allowEdit
	 *
	 * Checks that non-Super Admins are not editing Super Admins.
	 *
	 * @param   array   $data  An array of input data.
	 * @param   string  $key   The name of the key for the primary key.
	 *
	 * @return  boolean
	 *
	 * @since   1.6
	 */
	protected function allowEdit($data = array(), $key = 'id')
	{
		// Check if this group is a Super Admin
		if (Access::checkGroup($data[$key], 'core.admin'))
		{
			// If I'm not a Super Admin, then disallow the edit.
			if (!$this->app->getIdentity()->authorise('core.admin'))
			{
				return false;
			}
		}

		return parent::allowEdit($data, $key);
	}
}
