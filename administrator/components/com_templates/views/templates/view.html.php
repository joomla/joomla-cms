<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	Templates
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

/**
 * HTML View class for the Templates component
 *
 * @static
 * @package		Joomla.Administrator
 * @subpackage	Templates
 * @since 1.0
 */
class TemplatesViewTemplates extends JView
{
	protected $rows;
	protected $pagination;
	protected $client;

	public function display($tpl = null)
	{
		// Get data from the model
		$rows		= & $this->get('Data');
		$total		= & $this->get('Total');
		$pagination = & $this->get('Pagination');
		$client		= & $this->get('Client');

		$task = JRequest::getCmd('task');

		if ($client->id == 1) {
			JSubMenuHelper::addEntry(JText::_('Site'), 'index.php?option=com_templates&client=0');
			JSubMenuHelper::addEntry(JText::_('Administrator'), 'index.php?option=com_templates&client=1', true);
		} elseif ($client->id == 0 && !$task) {
			JSubMenuHelper::addEntry(JText::_('Site'), 'index.php?option=com_templates&client=0', true);
			JSubMenuHelper::addEntry(JText::_('Administrator'), 'index.php?option=com_templates&client=1');
		} else {
			JSubMenuHelper::addEntry(JText::_('Site'), 'index.php?option=com_templates&client=0');
			JSubMenuHelper::addEntry(JText::_('Administrator'), 'index.php?option=com_templates&client=1');
		}

		// Set toolbar items for the page
		JToolBarHelper::title(JText::_('Template Manager'), 'thememanager');

		JToolBarHelper::editListX('edit', 'Edit');
		//JToolBarHelper::addNew();
		JToolBarHelper::help('screen.templates');

		//$select[] 			= JHtml::_('select.option', '0', JText::_('Site'));
		//$select[] 			= JHtml::_('select.option', '1', JText::_('Administrator'));
		//$lists['client'] 	= JHtml::_('select.genericlist',  $select, 'client', 'class="inputbox" size="1" onchange="document.adminForm.submit();"', 'value', 'text', $client->id);

		$this->assignRef('rows',		$rows);
		$this->assignRef('pagination',	$pagination);
		$this->assignRef('client',		$client);

		parent::display($tpl);
	}
}
