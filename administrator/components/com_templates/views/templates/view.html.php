<?php
/**
* @version		$Id: $
* @package		Joomla
* @subpackage	Templates
* @copyright	Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

jimport( 'joomla.application.component.view');

/**
 * HTML View class for the Templates component
 *
 * @static
 * @package		Joomla
 * @subpackage	Templates
 * @since 1.0
 */
class TemplatesViewTemplates extends JView
{
	protected $rows;
	protected $pagination;
	protected $client;
	function display($tpl = null)
	{
		// Get data from the model
		$rows		= & $this->get( 'Data');
		$total		= & $this->get( 'Total');
		$pagination = & $this->get( 'Pagination' );
		$client		= & $this->get( 'Client');

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
		JToolBarHelper::title( JText::_( 'Template Manager' ), 'thememanager' );

		if ($client->id == '1') {
			JToolBarHelper::makeDefault('publish');
		} else {
			JToolBarHelper::makeDefault();
		}
		JToolBarHelper::editListX( 'edit', 'Edit' );
		//JToolBarHelper::addNew();
		JToolBarHelper::help( 'screen.templates' );

		//$select[] 			= JHTML::_('select.option', '0', JText::_('Site'));
		//$select[] 			= JHTML::_('select.option', '1', JText::_('Administrator'));
		//$lists['client'] 	= JHTML::_('select.genericlist',  $select, 'client', 'class="inputbox" size="1" onchange="document.adminForm.submit();"', 'value', 'text', $client->id);

		$this->assignRef('rows',		$rows);
		$this->assignRef('pagination',	$pagination);
		$this->assignRef('client',		$client);

		parent::display($tpl);
	}
}
