<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 * @link		http://www.theartofjoomla.com
 */

// no direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.controllerform');

/**
 * @package		Joomla.Administrator
 * @subpackage	com_content
 */
class ContentControllerArticle extends JControllerForm
{
	/**
	 * Method override to check if you can add a new record.
	 *
	 * @param	array	An array of input data.
	 *
	 * @return 	boolean
	 */
	protected function _allowAdd($data = array())
	{
		// Initialize variables.
		$user		= JFactory::getUser();
		$categoryId	= JArrayHelper::getValue($data, 'catid', JRequest::getInt('filter_category_id'), 'int');
		$allow		= null;

		if ($categoryId)
		{
			// If the category has been passed in the URL check it.
			$allow	= $user->authorise('core.create', 'com_content.category.'.$categoryId);
		}
		if ($allow === null)
		{
			// In the absense of better information, revert to the component permissions.
			return parent::_allowAdd();
		}
		else {
			return $allow;
		}
	}

	/**
	 * Method to check if you can add a new record.
	 *
	 * Extended classes can override this if necessary.
	 *
	 * @param	array	An array of input data.
	 * @param	string	The name of the key for the primary key.
	 *
	 * @return 	boolean
	 */
	protected function _allowEdit($data = array(), $key = 'id')
	{
		// Initialize variables.
		$recordId	= isset($data[$key]) ? $data[$key] : '0';
		$user		= JFactory::getUser();

		return $user->authorise('core.edit', sprintf($this->_asset_name, $recordId));
	}

	/**
	 * Removes an item
	 *
	 * TODO: Move to JControllerForm
	 */
	function delete()
	{
		// Check for request forgeries
		JRequest::checkToken() or jexit(JText::_('JInvalid_Token'));

		// Initialise variables.
		$user	= JFactory::getUser();
		$cid	= JRequest::getVar('cid', array(), '', 'array');

		if (!is_array($cid) || count($cid) < 1) {
			JError::raiseWarning(500, JText::_('Select an item to delete'));
		}
		else {
			// Get the model.
			$model = $this->getModel();

			// Make sure the item ids are integers
			jimport('joomla.utilities.arrayhelper');
			JArrayHelper::toInteger($cid);

			// Remove the items.
			if (!$model->delete($cid)) {
				JError::raiseWarning(500, $model->getError());
			}
		}

		$this->setRedirect('index.php?option=com_content&view=articles');
	}

	/**
	 * Method to publish a list of taxa
	 *
	 * @access	public
	 * @return	void
	 * @since	1.0
	 *
	 * TODO: Move to list controller. Add access control checks.
	 */
	function publish()
	{
		// Check for request forgeries
		JRequest::checkToken() or jexit(JText::_('JInvalid_Token'));

		// Initialise variables.
		$user	= JFactory::getUser();
		$cid	= JRequest::getVar('cid', array(), '', 'array');
		$data	= array('publish' => 1, 'unpublish' => 0, 'trash' => -2);
		$task	= $this->getTask();
		$value	= JArrayHelper::getValue($data, $task, 0, 'int');

		if (!is_array($cid) || count($cid) < 1) {
			JError::raiseWarning(500, JText::_('Select an item to publish'));
		}
		else {
			// Get the model.
			$model	= $this->getModel();

			// Make sure the item ids are integers
			JArrayHelper::toInteger($cid);

			// Publish the items.
			if (!$model->publish($cid, $value)) {
				JError::raiseWarning(500, $model->getError());
			}
		}

		$this->setRedirect('index.php?option=com_content&view=articles');
	}

	/**
	 * Changes the order of an item
	 *
	 * TODO: Move to list controller. Add access control checks.
	 */
	function ordering()
	{
		// Check for request forgeries
		JRequest::checkToken() or jexit(JText::_('JInvalid_Token'));

		// Initialise variables.
		$user	= JFactory::getUser();
		$cid	= JRequest::getVar('cid', null, 'post', 'array');
		$inc	= $this->getTask() == 'orderup' ? -1 : +1;

		$model = & $this->getModel();
		$model->ordering($cid, $inc);

		$this->setRedirect('index.php?option=com_content&view=articles');
	}
}