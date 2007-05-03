<?php
/**
 * @version		$Id$
 * @package		Joomla
 * @subpackage	Menus
 * @copyright	Copyright (C) 2005 - 2007 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.application.component.view');

/**
 * Extension Manager Modules View
 *
 * @author		Louis Landry <louis.landry@joomla.org>
 * @package		Joomla
 * @subpackage	Installer
 * @since		1.5
 */
class InstallerViewModules extends JView
{
	function display($tpl=null)
	{
		/*
		 * Set toolbar items for the page
		 */
		JToolBarHelper::title( JText::_( 'Extension Manager'), 'install.png' );
		JToolBarHelper::deleteList( '', 'remove', 'Uninstall' );
		JToolBarHelper::help( 'screen.installer2' );

		// Document
		$document = & JFactory::getDocument();
		$document->setTitle(JText::_('Extension Manager').' : '.JText::_('Modules'));

		// Get data from the model
		$state		= &$this->get('State');
		$items		= &$this->get('Items');
		$pagination	= &$this->get('Pagination');

		$lists = new stdClass();
		$select[] = JHTML::_('select.option', '-1', JText::_('All'));
		$select[] = JHTML::_('select.option', '0', JText::_('Site Modules'));
		$select[] = JHTML::_('select.option', '1', JText::_('Admin Modules'));
		$lists->client = JHTML::_('select.genericlist',  $select, 'client', 'class="inputbox" size="1" onchange="document.adminForm.submit();"', 'value', 'text', $state->get('filter.client'));

		$this->assignRef('state',		$state);
		$this->assignRef('items',		$items);
		$this->assignRef('pagination',	$pagination);
		$this->assignRef('lists',		$lists);

		JHTML::_('behavior.tooltip');
		parent::display($tpl);
	}

	function loadItem($index=0)
	{
		$item =& $this->items[$index];
		$item->index	= $index;

		if ($item->iscore) {
			$item->cbd		= 'disabled';
			$item->style	= 'style="color:#999999;"';
		} else {
			$item->cbd		= null;
			$item->style	= null;
		}
		$item->author_info = @$item->authorEmail .'<br />'. @$item->authorUrl;

		$this->assignRef('item', $item);
	}
}
?>