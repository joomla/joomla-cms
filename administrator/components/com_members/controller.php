<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @copyright	Copyright (C) 2008 - 2009 JXtended, LLC. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
 */

defined('_JEXEC') or die;

jimport('joomla.application.component.controller');

/**
 * Base controller class for Members.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_members
 * @since		1.6
 */
class MembersController extends JController
{
	/**
	 * Method to display a view.
	 *
	 * @return	void
	 */
	public function display()
	{
		// Get the document object.
		$document = &JFactory::getDocument();

		// Set the default view name and format from the Request.
		$vName		= JRequest::getWord('view', 'members');
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
		JSubMenuHelper::addEntry(JText::_('Members_Submenu_Members'),	'index.php?option=com_members&view=members',	(in_array($vName, array('members','member'))));
		JSubMenuHelper::addEntry(JText::_('Members_Submenu_Groups'),	'index.php?option=com_members&view=groups',		(in_array($vName, array('groups','group'))));
		JSubMenuHelper::addEntry(JText::_('Members_Submenu_Levels'),	'index.php?option=com_members&view=levels',		(in_array($vName, array('levels','level'))));
	}
}
