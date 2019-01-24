<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_admin
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * User profile controller class.
 *
 * @since  1.6
 */
class AdminControllerProfile extends JControllerForm
{
	/**
	 * Method to check if you can edit a record.
	 *
	 * Extended classes can override this if necessary.
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
		return isset($data['id']) && $data['id'] == JFactory::getUser()->id;
	}

	/**
	 * Overrides parent save method to check the submitted passwords match.
	 *
	 * @param   string  $key     The name of the primary key of the URL variable.
	 * @param   string  $urlVar  The name of the URL variable if different from the primary key (sometimes required to avoid router collisions).
	 *
	 * @return  boolean  True if successful, false otherwise.
	 *
	 * @since   3.2
	 */
	public function save($key = null, $urlVar = null)
	{
		$this->setRedirect(JRoute::_('index.php?option=com_admin&view=profile&layout=edit&id=' . JFactory::getUser()->id, false));

		$return = parent::save();

		if ($this->getTask() != 'apply')
		{
			// Redirect to the main page.
			$this->setRedirect(JRoute::_('index.php', false));
		}

		return $return;
	}

	/**
	 * Method to cancel an edit.
	 *
	 * @param   string  $key  The name of the primary key of the URL variable.
	 *
	 * @return  boolean  True if access level checks pass, false otherwise.
	 *
	 * @since   1.6
	 */
	public function cancel($key = null)
	{
		$return = parent::cancel($key);

		// Redirect to the main page.
		$this->setRedirect(JRoute::_('index.php', false));

		return $return;
	}
}
