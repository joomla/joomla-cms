<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_contenthistory
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Contenthistory list controller class.
 *
 * @since  3.2
 */
class ContenthistoryControllerHistory extends JControllerAdmin
{
	/**
	 * Deletes and returns correctly.
	 *
	 * @return	void
	 *
	 * @since	3.2
	 */
	public function delete()
	{
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		// Get items to remove from the request.
		$cid = $this->input->get('cid', array(), 'array');

		if (!is_array($cid) || count($cid) < 1)
		{
			JError::raiseWarning(500, JText::_('COM_CONTENTHISTORY_NO_ITEM_SELECTED'));
		}
		else
		{
			// Get the model.
			$model = $this->getModel();

			// Make sure the item ids are integers
			jimport('joomla.utilities.arrayhelper');
			JArrayHelper::toInteger($cid);

			// Remove the items.
			if ($model->delete($cid))
			{
				$this->setMessage(JText::plural('COM_CONTENTHISTORY_N_ITEMS_DELETED', count($cid)));
			}
			else
			{
				$this->setMessage($model->getError());
			}
		}

		$this->setRedirect(
			JRoute::_(
				'index.php?option=com_contenthistory&view=history&layout=modal&tmpl=component&item_id='
				. $this->input->getInt('item_id') . '&type_id=' . $this->input->getInt('type_id')
				. '&type_alias=' . $this->input->getCmd('type_alias') . '&' . JSession::getFormToken() . '=1', false
			)
		);
	}

	/**
	 * Proxy for getModel.
	 *
	 * @param   string  $name    The name of the model
	 * @param   string  $prefix  The prefix for the model
	 * @param   array   $config  An additional array of parameters
	 *
	 * @return  JModelLegacy  The model
	 *
	 * @since   3.2
	 */
	public function getModel($name = 'History', $prefix = 'ContenthistoryModel', $config = array('ignore_request' => true))
	{
		return parent::getModel($name, $prefix, $config);
	}

	/**
	 * Toggles the keep forever value for one or more history rows. If it was Yes, changes to No. If No, changes to Yes.
	 *
	 * @return	void
	 *
	 * @since	3.2
	 */
	public function keep()
	{
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		// Get items to remove from the request.
		$cid = $this->input->get('cid', array(), 'array');

		if (!is_array($cid) || count($cid) < 1)
		{
			JError::raiseWarning(500, JText::_('COM_CONTENTHISTORY_NO_ITEM_SELECTED'));
		}
		else
		{
			// Get the model.
			$model = $this->getModel();

			// Make sure the item ids are integers
			jimport('joomla.utilities.arrayhelper');
			JArrayHelper::toInteger($cid);

			// Remove the items.
			if ($model->keep($cid))
			{
				$this->setMessage(JText::plural('COM_CONTENTHISTORY_N_ITEMS_KEEP_TOGGLE', count($cid)));
			}
			else
			{
				$this->setMessage($model->getError());
			}
		}

		$this->setRedirect(
			JRoute::_(
				'index.php?option=com_contenthistory&view=history&layout=modal&tmpl=component&item_id='
				. $this->input->getInt('item_id') . '&type_id=' . $this->input->getInt('type_id')
				. '&type_alias=' . $this->input->getCmd('type_alias') . '&' . JSession::getFormToken() . '=1', false
			)
		);
	}
}
