<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

jimport( 'joomla.application.component.controller' );

/**
 * The Menu Item Controller
 *
 * @package		Joomla.Administrator
 * @subpackage	com_menus
 * @since		1.6
 */
class MenusControllerItems extends JController
{
	/**
	 * Constructor.
	 *
	 * @param	array An optional associative array of configuration settings.
	 * @see		JController
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);

		// Register proxy tasks.
		$this->registerTask('unpublish',	'publish');
		$this->registerTask('trash',		'publish');
		$this->registerTask('orderup',		'ordering');
		$this->registerTask('orderdown',	'ordering');
	}

	/**
	 * Display the view
	 */
	public function display()
	{
	}

	/**
	 * Proxy for getModel
	 */
	function &getModel($name = 'Item', $prefix = 'MenusModel')
	{
		return parent::getModel($name, $prefix, array('ignore_request' => true));
	}

	/**
	 * Removes an item
	 */
	public function delete()
	{
		// Check for request forgeries
		JRequest::checkToken() or jexit(JText::_('JInvalid_Token'));

		// Get items to remove from the request.
		$pks	= JRequest::getVar('cid', array(), 'post', 'array');
		$n		= count($pks);

		if (empty($pks)) {
			JError::raiseWarning(500, JText::_('JError_No_items_selected'));
		}
		else
		{
			// Get the model.
			$model = $this->getModel();

			// Remove the items.
			if ($model->delete($pks)) {
				$this->setMessage(JText::sprintf('JSuccess_N_items_deleted', $n));
			}
			else {
				$this->setMessage($model->getError());
			}
		}

		$this->setRedirect('index.php?option=com_menus&view=items');
	}

	/**
	 * Method to change the published state of selected rows.
	 *
	 * @return	void
	 */
	public function publish()
	{
		// Check for request forgeries
		JRequest::checkToken() or jexit(JText::_('JInvalid_Token'));

		// Get items to publish from the request.
		$pks	= JRequest::getVar('cid', array(), '', 'array');
		$values	= array('publish' => 1, 'unpublish' => 0, 'trash' => -2);
		$task	= $this->getTask();
		$value	= JArrayHelper::getValue($values, $task, 0, 'int');

		if (empty($pks)) {
			JError::raiseWarning(500, JText::_('JError_No_items_selected'));
		}
		else
		{
			// Get the model.
			$model	= $this->getModel();

			// Publish the items.
			if ($model->publish($pks, $value)) {
				$this->setMessage($value ? JText::_('JSuccess_N_items_published') : JText::_('JSuccess_N_items_unpublished'));
			}
			else {
				$this->setMessage($model->getError());
			}
		}

		$this->setRedirect('index.php?option=com_menus&view=items');
	}

	/**
	 * Method to reorder selected rows.
	 *
	 * @return	bool	False on failure or error, true on success.
	 */
	public function ordering()
	{
		JRequest::checkToken() or jexit(JText::_('JInvalid_Token'));

		// Initialise variables.
		$pks	= JRequest::getVar('cid', null, 'post', 'array');
		$model	= &$this->getModel();

		// Attempt to move the row.
		$return = $model->ordering(array_pop($pks), $this->getTask() == 'orderup' ? -1 : 1);

		if ($return === false) {
			// Reorder failed.
			$message = JText::sprintf('JError_Reorder_failed', $model->getError());
			$this->setRedirect('index.php?option=com_menus&view=items', $message, 'error');
			return false;
		}
		else {
			// Reorder succeeded.
			$message = JText::_('JSuccess_Item_reordered');
			$this->setRedirect('index.php?option=com_menus&view=items', $message);
			return true;
		}
	}
}