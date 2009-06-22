<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	Modules
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

/**
 * HTML View class for the Modules component
 *
 * @static
 * @package		Joomla.Administrator
 * @subpackage	Modules
 * @since 1.0
 */
class ModulesViewModules extends JView
{
	protected $client;
	protected $filter;
	protected $pagination;
	protected $rows;
	protected $user;

	function display($tpl = null)
	{
		// Set toolbar items for the page
		JToolBarHelper::title(JText::_('Module Manager'), 'module.png');
		JToolBarHelper::publishList();
		JToolBarHelper::unpublishList();
		JToolBarHelper::custom('copy', 'copy.png', 'copy_f2.png', 'Copy', true);
		JToolBarHelper::deleteList();
		JToolBarHelper::editListX();
		JToolBarHelper::addNewX();
		JToolBarHelper::help('screen.modules');

		// Get data from the model
		$rows		= & $this->get('Data');
		$total		= & $this->get('Total');
		$pagination = & $this->get('Pagination');
		$filter		= & $this->get('Filter');
		$client		= & $this->get('Client');

		if ($client->id == 1) {
			JSubMenuHelper::addEntry(JText::_('Site'), 'index.php?option=com_modules&client_id=0');
			JSubMenuHelper::addEntry(JText::_('Administrator'), 'index.php?option=com_modules&client=1', true);
		} else {
			JSubMenuHelper::addEntry(JText::_('Site'), 'index.php?option=com_modules&client_id=0', true);
			JSubMenuHelper::addEntry(JText::_('Administrator'), 'index.php?option=com_modules&client=1');
		}

		$this->assignRef('user',		JFactory::getUser());
		$this->assignRef('rows',		$rows);
		$this->assignRef('pagination',	$pagination);
		$this->assignRef('filter',		$filter);
		$this->assignRef('client',		$client);

		parent::display($tpl);
	}
}
