<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.controller');

/**
 * Base controller class for Menu Manager.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_menus
 * @version		1.6
 */
class MenusController extends JController
{
	/**
	 * Method to display a view.
	 *
	 * @return	void
	 */
	function display()
	{
		// Get the document object.
		$document = &JFactory::getDocument();

		// Set the default view name and format from the Request.
		$vName		= JRequest::getWord('view', 'items');
		$vFormat	= $document->getType();
		$lName		= JRequest::getWord('layout', 'default');

		// Get and render the view.
		if ($view = &$this->getView($vName, $vFormat))
		{
			switch ($vName)
			{
				default:
					$model = &$this->getModel($vName);
					break;
			}

			// Push the model into the view (as default).
			$view->setModel($model, true);
			$view->setLayout($lName);

			// Push document object into the view.
			$view->assignRef('document', $document);

			$view->display();
		}
		else {
			// Error condition.
		}

		// Setup the sub-menu.
		JSubMenuHelper::addEntry(JText::_('Menus_Submenu_Items'),	'index.php?option=com_menus&view=items',	(in_array($vName, array('items','item'))));
		JSubMenuHelper::addEntry(JText::_('Menus_Submenu_Menus'),	'index.php?option=com_menus&view=menus',	(in_array($vName, array('menus','menu'))));
	}
}
