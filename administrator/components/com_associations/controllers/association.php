<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_associations
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JLoader::register('AssociationsHelper', JPATH_ADMINISTRATOR . '/components/com_associations/helpers/associations.php');

/**
 * Association edit controller class.
 *
 * @since  __DEPLOY_VERSION__
 */
class AssociationsControllerAssociation extends JControllerForm
{
	/**
	 * Method to edit an existing record.
	 *
	 * @param   string  $key     The name of the primary key of the URL variable.
	 * @param   string  $urlVar  The name of the URL variable if different from the primary key
	 *                           (sometimes required to avoid router collisions).
	 *
	 * @return  boolean  True if access level check and checkout passes, false otherwise.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function edit($key = null, $urlVar = null)
	{
		list($extensionName, $typeName) = explode('.', $this->input->get('itemtype'));

		$id = $this->input->get('id', 0);

		// Check if reference item can be edited.
		if (!AssociationsHelper::allowEdit($extensionName, $typeName, $id))
		{
			JFactory::getApplication()->enqueueMessage(JText::_('JLIB_APPLICATION_ERROR_EDIT_NOT_PERMITTED'), 'error');
			$this->setRedirect(JRoute::_('index.php?option=com_associations&view=associations', false));

			return false;
		}

		return parent::display();
	}

	/**
	 * Method for canceling the edit action
	 *
	 * @param   string  $key  The name of the primary key of the URL variable.
	 *
	 * @return  void.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function cancel($key = null)
	{
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		list($extensionName, $typeName) = explode('.', $this->input->get('itemtype'));

		// Only check in, if component item type allows to check out.
		if (AssociationsHelper::typeSupportsCheckout($extensionName, $typeName))
		{
			$ids      = array();
			$targetId = $this->input->get('target-id', '', 'string');

			if ($targetId !== '')
			{
				$ids = array_unique(explode(',', $targetId));
			}

			$ids[] = $this->input->get('id', 0);

			foreach ($ids as $key => $id)
			{
				AssociationsHelper::getItem($extensionName, $typeName, $id)->checkin();
			}
		}

		$this->setRedirect(JRoute::_('index.php?option=com_associations&view=associations', false));
	}
}
