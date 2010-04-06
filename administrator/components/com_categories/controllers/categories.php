<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

jimport( 'joomla.application.component.controller' );

/**
 * The Menu Item Controller
 *
 * @package		Joomla.Administrator
 * @subpackage	com_categories
 * @since		1.6
 */
class CategoriesControllerCategories extends JController
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
	function &getModel($name = 'Category', $prefix = 'CategoriesModel')
	{
		$model = parent::getModel($name, $prefix, array('ignore_request' => true));
		return $model;
	}

	/**
	 * Removes an item
	 */
	public function delete()
	{
		// Check for request forgeries
		JRequest::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		// Get items to remove from the request.
		$pks	= JRequest::getVar('cid', array(), 'post', 'array');
		$n		= count($pks);
		if (empty($pks)) {
			JError::raiseWarning(500, JText::_('COM_CATEGORIES_NO_CATEGORY_SELECTED'));
		}
		else
		{
			// Get the model.
			$model = $this->getModel('category');
			// Remove the items.
			if ($model->delete($pks)) {
				$this->setMessage(JText::sprintf((count($pks) == 1) ? 'COM_CATEGORIES_CATEGORY_DELETED' : 'COM_CATEGORIES_N_CATEGORIES_DELETED', count($pks)));
			}
			else {
				$this->setMessage($model->getError());
			}
		}

		$this->setRedirect('index.php?option=com_categories&view=categories');
	}

	/**
	 * Method to change the published state of selected rows.
	 *
	 * @return	void
	 */
	public function publish()
	{
		// Check for request forgeries
		JRequest::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		// Get items to publish from the request.
		$pks	= JRequest::getVar('cid', array(), '', 'array');
		$values	= array('publish' => 1, 'unpublish' => 0, 'trash' => -2);
		$task	= $this->getTask();
		$value	= JArrayHelper::getValue($values, $task, 0, 'int');

		if (empty($pks)) {
			JError::raiseWarning(500, JText::_('COM_CATEGORIES_NO_CATEGORY_SELECTED'));
		}
		else
		{
			// Get the model.
			$model	= $this->getModel();

			// Publish the items.
			if ($model->publish($pks, $value)) {
				if ($value == 1) {
					$text = 'COM_CATEGORIES_CATEGORY_PUBLISHED';
					$ntext = 'COM_CATEGORIES_N_CATEGORIES_PUBLISHED';
				}
				else if ($value == 0) {
					$text = 'COM_CATEGORIES_CATEGORY_UNPUBLISHED';
					$ntext = 'COM_CATEGORIES_N_CATEGORIES_UNPUBLISHED';
				}
				else {
					$text = 'COM_CATEGORIES_CATEGORY_TRASHED';
					$ntext = 'COM_CATEGORIES_N_CATEGORIES_TRASHED';
				}
				$this->setMessage(JText::sprintf((count($pks) == 1) ? $text : $ntext, count($pks)));
			}
			else {
				$this->setMessage($model->getError());
			}
		}

		$this->setRedirect('index.php?option=com_categories&view=categories');
	}

	/**
	 * Method to reorder selected rows.
	 *
	 * @return	bool	False on failure or error, true on success.
	 */
	public function ordering()
	{
		JRequest::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		// Initialise variables.
		$pks	= JRequest::getVar('cid', null, 'post', 'array');
		$model	= &$this->getModel();

		// Attempt to move the row.
		$return = $model->ordering(array_pop($pks), $this->getTask() == 'orderup' ? -1 : 1);

		if ($return === false) {
			// Reorder failed.
			$message = JText::sprintf('JError_Reorder_failed', $model->getError());
			$this->setRedirect('index.php?option=com_categories&view=categories', $message, 'error');
			return false;
		}
		else {
			// Reorder succeeded.
			$message = JText::_('JSuccess_Item_reordered');
			$this->setRedirect('index.php?option=com_categories&view=categories', $message);
			return true;
		}
	}

	/**
	 * Rebuild the nested set tree.
	 *
	 * @return	bool	False on failure or error, true on success.
	 */
	public function rebuild()
	{
		JRequest::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$this->setRedirect('index.php?option=com_categories&view=categories');

		// Initialise variables.
		$model = &$this->getModel();

		if ($model->rebuild())
		{
			// Reorder succeeded.
			$this->setMessage(JText::_('CATEGORIES_REBUILD_SUCCESS'));
			return true;
		}
		else {
			// Rebuild failed.
			$this->setMessage(JText::sprintf('CATEGORIES_REBUILD_FAILED'));
			return false;
		}
	}
}