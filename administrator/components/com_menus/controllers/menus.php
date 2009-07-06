<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

jimport( 'joomla.application.component.controller' );

/**
 * The Menu List Controller
 *
 * @package		Joomla.Administrator
 * @subpackage	com_menus
 * @since		1.6
 */
class MenusControllerMenus extends JController
{
	/**
	 * Display the view
	 */
	public function display()
	{
	}

	/**
	 * Proxy for getModel
	 */
	function &getModel($name = 'Menu', $prefix = 'MenusModel')
	{
		return parent::getModel($name, $prefix, array('ignore_request' => true));
	}

	/**
	 * Removes an item
	 */
	public function delete()
	{
		// Check for request forgeries
		JRequest::checkToken() or jExit(JText::_('JInvalid_Token'));

		// Get items to remove from the request.
		$cid	= JRequest::getVar('cid', array(), '', 'array');

		if (!is_array($cid) || count($cid) < 1) {
			JError::raiseWarning(500, JText::_('JError_No_items_selected'));
		}
		else {
			// Get the model.
			$model = $this->getModel();

			// Make sure the item ids are integers
			jimport('joomla.utilities.arrayhelper');
			JArrayHelper::toInteger($cid);

			// Remove the items.
			if (!$model->delete($cid)) {
				$this->setMessage($model->getError());
			}
		}

		$this->setRedirect('index.php?option=com_menus&view=menus');
	}


	/**
	 * Rebuild the menu tree.
	 *
	 * @return	bool	False on failure or error, true on success.
	 */
	public function rebuild()
	{
		JRequest::checkToken() or jExit(JText::_('JInvalid_Token'));

		$this->setRedirect('index.php?option=com_menus&view=menus');

		// Initialize variables.
		$model = &$this->getModel('Item');

		if ($model->rebuild())
		{
			// Reorder succeeded.
			$this->setMessage(JText::_('Menus_Rebuild_success'));
			return true;
		}
		else {
			// Rebuild failed.
			$this->setMessage(JText::sprintf('Menus_Rebuild_failed', $model->getMessage()));
			return false;
		}
	}
}