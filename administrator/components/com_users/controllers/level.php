<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_users
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\Utilities\ArrayHelper;

/**
 * User view level controller class.
 *
 * @since  1.6
 */
class UsersControllerLevel extends JControllerForm
{
	/**
	 * @var     string  The prefix to use with controller messages.
	 * @since   1.6
	 */
	protected $text_prefix = 'COM_USERS_LEVEL';

	/**
	 * Method to check if you can save a new or existing record.
	 *
	 * Overrides JControllerForm::allowSave to check the core.admin permission.
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
		return (JFactory::getUser()->authorise('core.admin', $this->option) && parent::allowSave($data, $key));
	}

	/**
	 * Overrides JControllerForm::allowEdit
	 *
	 * Checks that non-Super Admins are not editing Super Admins.
	 *
	 * @param   array   $data  An array of input data.
	 * @param   string  $key   The name of the key for the primary key.
	 *
	 * @return  boolean
	 *
	 * @since   3.8.8
	 */
	protected function allowEdit($data = array(), $key = 'id')
	{
		// Get user instance
		$user = JFactory::getUser();

		// Check for if Super Admin can edit
		$db = JFactory::getDbo();
		$query = $db->getQuery(true)
			->select('*')
			->from($db->quoteName('#__viewlevels'))
			->where($db->quoteName('id') . ' = ' . (int) $data['id']);
		$db->setQuery($query);

		$viewlevel = $db->loadAssoc();

		// Decode level groups
		$groups = json_decode($viewlevel['rules']);

		// If this group is super admin and this user is not super admin, canEdit is false
		if (!$user->authorise('core.admin') && JAccess::checkGroup($groups[0], 'core.admin'))
		{
			$this->setError(JText::sprintf('JLIB_APPLICATION_ERROR_EDIT_NOT_PERMITTED'));

			return false;
		}

		return parent::allowEdit($data, $key);
	}

	/**
	 * Removes an item.
	 *
	 * Overrides JControllerAdmin::delete to check the core.admin permission.
	 *
	 * @return  boolean  Returns true on success, false on failure.
	 *
	 * @since   1.6
	 */
	public function delete()
	{
		// Check for request forgeries.
		$this->checkToken();

		$ids = $this->input->get('cid', array(), 'array');

		if (!JFactory::getUser()->authorise('core.admin', $this->option))
		{
			JError::raiseError(500, JText::_('JERROR_ALERTNOAUTHOR'));
			jexit();
		}
		elseif (empty($ids))
		{
			JError::raiseWarning(500, JText::_('COM_USERS_NO_LEVELS_SELECTED'));
		}
		else
		{
			// Get the model.
			$model = $this->getModel();

			$ids = ArrayHelper::toInteger($ids);

			// Remove the items.
			if (!$model->delete($ids))
			{
				JError::raiseWarning(500, $model->getError());
			}
			else
			{
				$this->setMessage(JText::plural('COM_USERS_N_LEVELS_DELETED', count($ids)));
			}
		}

		$this->setRedirect('index.php?option=com_users&view=levels');
	}
}
