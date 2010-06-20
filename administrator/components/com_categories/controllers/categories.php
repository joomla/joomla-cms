<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

jimport('joomla.application.component.controlleradmin');

/**
 * The Menu Item Controller
 *
 * @package		Joomla.Administrator
 * @subpackage	com_categories
 * @since		1.6
 */
class CategoriesControllerCategories extends JControllerAdmin
{
	/**
	 * Proxy for getModel
	 * @since	1.6
	 */
	function &getModel($name = 'Category', $prefix = 'CategoriesModel')
	{
		$model = parent::getModel($name, $prefix, array('ignore_request' => true));
		return $model;
	}

	/**
	 * Rebuild the nested set tree.
	 *
	 * @return	bool	False on failure or error, true on success.
	 * @since	1.6
	 */
	public function rebuild()
	{
		JRequest::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$this->setRedirect('index.php?option=com_categories&view=categories');

		// Initialise variables.
		$model =& $this->getModel();

		if ($model->rebuild())
		{
			// Reorder succeeded.
			$this->setMessage(JText::_('COM_CATEGORIES_REBUILD_SUCCESS'));
			return true;
		}
		else
		{
			// Rebuild failed.
			$this->setMessage(JText::_('COM_CATEGORIES_REBUILD_FAILURE'));
			return false;
		}
	}

	public function saveorder()
	{
		JRequest::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$this->setRedirect('index.php?option=com_categories&view=categories');

		// Initialise variables.
		$model =& $this->getModel();

		// Get the arrays from the Request
		$idArray = JRequest::getVar(cid);
		$orderArray = JRequest::getVar(order);
		$originalOrderArray = explode(',', JRequest::getString('original_order_values'));

		// Check that they are arrays and the same size
		if (is_array($idArray) && is_array($orderArray) && is_array($originalOrderArray) 
				&& count($idArray) == count($orderArray) && count($idArray == count($originalOrderArray)))
		{
			// Clean up arrays
			for ($i = 0; $i < count($idArray); $i++)
			{
				$idArray[$i] = (int) $idArray[$i];
				$orderArray[$i] = (int) $orderArray[$i];
				$originalOrderArray[$i] = (int) $originalOrderArray[$i];
			}

			// Make sure something has changed
			if (!($orderArray === $originalOrderArray))
			{

				if ($model->saveorder($idArray, $orderArray))
				{
					// Reorder succeeded.
					$this->setMessage(JText::_('JLIB_APPLICATION_SUCCESS_ITEM_REORDERED'));
					return true;
				}
				else
				{
					// Rebuild failed.
					$this->setMessage(JText::_('JLIB_APPLICATION_ERROR_REORDER_FAILED'));
					return false;
				}
			}

			else
			{
				// Nothing to reorder
				return true;
			}
		}
		else
		{
			$this->setMessage(JText::_('JLIB_APPLICATION_ERROR_REORDER_FAILED'));
			return false;
		}
	}
}
