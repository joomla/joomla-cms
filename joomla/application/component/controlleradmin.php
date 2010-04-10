<?php
/**
 * @version		$Id: controller.php 15936 2010-04-08 06:32:41Z infograf768 $
 * @package		Joomla.Framework
 * @subpackage	Application
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('JPATH_BASE') or die;

jimport('joomla.application.component.controller');

/**
 * Base class for a Joomla Administrator Controller
 *
 * Controller (controllers are where you put all the actual code) Provides basic
 * functionality, such as rendering views (aka displaying templates).
 *
 * @abstract
 * @package		Joomla.Framework
 * @subpackage	Application
 * @since		1.6
 */
class JControllerAdmin extends JController
{
	protected $_url = null;
	
	protected $_msgprefix = null;
	
	protected $_context = null;
	
	/**
	 * Constructor.
	 *
	 * @param	array An optional associative array of configuration settings.
	 * @see		JController
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);

		$this->_msgprefix = strtoupper(str_replace('.', '_', $this->_context));
	}
	
	/**
	 * Set the return URL for controller tasks
	 *  
	 * @param $url string URL to return to
	 * @return void
	 */
	function setURL($url)
	{
		$this->_url = $url;
	}
	
	/**
	 * Removes an item
	 */
	function delete()
	{
		// Check for request forgeries
		JRequest::checkToken() or die(JText::_('JINVALID_TOKEN'));

		// Get items to remove from the request.
		$cid	= JRequest::getVar('cid', array(), '', 'array');

		if (!is_array($cid) || count($cid) < 1) {
			JError::raiseWarning(500, JText::_($this->_msgprefix.'_NO_ITEM_SELECTED'));
		}
		else {
			// Get the model.
			$model = $this->getModel();

			// Make sure the item ids are integers
			jimport('joomla.utilities.arrayhelper');
			JArrayHelper::toInteger($cid);

			// Remove the items.
			if ($model->delete($pks)) {
				$this->setMessage(JText::sprintf((count($pks) == 1) ? $this->_msgprefix.'_ITEM_DELETED' : $this->_msgprefix.'_N_ITEMS_DELETED', count($pks)));
			}
			else {
				$this->setMessage($model->getError());
			}
		}

		$this->setRedirect($this->_url);
	}
	
	/**
	 * Method to publish a list of taxa
	 *
	 * @since	1.0
	 */
	function publish()
	{
		// Check for request forgeries
		JRequest::checkToken() or die(JText::_('JINVALID_TOKEN'));

		// Get items to publish from the request.
		$cid	= JRequest::getVar('cid', array(), '', 'array');
		$data	= array('publish' => 1, 'unpublish' => 0, 'archive'=>-1, 'trash' => -2, 'report'=>-3);
		$task 	= $this->getTask();
		$value	= JArrayHelper::getValue($data, $task, 0, 'int');

		if (!is_array($cid) || count($cid) < 1) {
			JError::raiseWarning(500, JText::_($this->_msgprefix.'_NO_ITEM_SELECTED'));
		}
		else {
			// Get the model.
			$model	= $this->getModel();

			// Make sure the item ids are integers
			JArrayHelper::toInteger($cid);

			// Publish the items.
			if (!$model->publish($cid, $value)) {
				JError::raiseWarning(500, $model->getError());
			} else {
				if ($value == 1) {
					$text = $this->_msgprefix.'_ITEM_PUBLISHED';
					$ntext = $this->_msgprefix.'_N_ITEMS_PUBLISHED';
				}
				else if ($value == 0) {
					$text = $this->_msgprefix.'_ITEM_UNPUBLISHED';
					$ntext = $this->_msgprefix.'_N_ITEMS_UNPUBLISHED';
				}
				else if ($value == -1) {
					$text = $this->_msgprefix.'_ITEM_ARCHIVED';
					$ntext = $this->_msgprefix.'_N_ITEMS_ARCHIVED';
				}
				else {
					$text = $this->_msgprefix.'_ITEM_TRASHED';
					$ntext = $this->_msgprefix.'_N_ITEMS_TRASHED';
				}
				$this->setMessage(JText::sprintf((count($cid) == 1) ? $text : $ntext, count($ids)));
			}
		}

		$this->setRedirect($this->_url);
	}
	
	/**
	 * Changes the order of one or more records.
	 */
	public function reorder()
	{
		// Check for request forgeries.
		JRequest::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		// Initialise variables.
		$user	= JFactory::getUser();
		$ids	= JRequest::getVar('cid', null, 'post', 'array');
		$inc	= ($this->getTask() == 'orderup') ? -1 : +1;

		$model = $this->getModel();
		$return = $model->reorder($ids, $inc);
		if ($return === false) {
			// Reorder failed.
			$message = JText::sprintf('JError_Reorder_failed', $model->getError());
			$this->setRedirect($this->_url, $message, 'error');
			return false;
		}
		else {
			// Reorder succeeded.
			$message = JText::_('JSuccess_Item_reordered');
			$this->setRedirect($this->_url, $message);
			return true;
		}
	}
	
	/**
	 * Method to save the submitted ordering values for records.
	 *
	 * @return	void
	 */
	public function saveorder()
	{
		// Check for request forgeries.
		JRequest::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		// Get the input
		$pks	= JRequest::getVar('cid',	null,	'post',	'array');
		$order	= JRequest::getVar('order',	null,	'post',	'array');

		// Sanitize the input
		JArrayHelper::toInteger($pks);
		JArrayHelper::toInteger($order);

		// Get the model
		$model = &$this->getModel();

		// Save the ordering
		$model->saveorder($pks, $order);

		$this->setMessage(JText::_('JSUCCESS_ORDERING_SAVED'));
		$this->setRedirect($this->_url);
	}
}