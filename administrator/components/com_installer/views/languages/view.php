<?php
/**
 * @version		$Id$
 * @package		Joomla
 * @subpackage	Menus
 * @copyright	Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
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
 * Extension Manager Languages View
 *
 * @package		Joomla
 * @subpackage	Installer
 * @since		1.5
 */
include_once(dirname(__FILE__).DS.'..'.DS.'default'.DS.'view.php');

class InstallerViewLanguages extends InstallerViewDefault
{
	protected $items;
	protected $pagination;
	protected $lists;
	protected $item;
	function display($tpl=null)
	{
		/*
		 * Set toolbar items for the page
		 */
		JToolBarHelper::deleteList( JText::_('UNINSTALL LANGUAGE'), 'remove', 'Uninstall' );
		JToolBarHelper::help( 'screen.installer2' );

		// Get data from the model
		$state		= &$this->get('State');
		$items		= &$this->get('Items');
		$pagination	= &$this->get('Pagination');

		$lists = new stdClass();
		$select[] = JHtml::_('select.option', '-1', JText::_('All'));
		$select[] = JHtml::_('select.option', '0', JText::_('Site Languages'));
		$select[] = JHtml::_('select.option', '1', JText::_('Admin Languages'));
		$lists->client = JHtml::_('select.genericlist',  $select, 'client', 'class="inputbox" size="1" onchange="document.adminForm.submit();"', 'value', 'text', $state->get('filter.client'));

		$this->assignRef('items',		$items);
		$this->assignRef('pagination',	$pagination);
		$this->assignRef('lists',		$lists);

		parent::display($tpl);
	}

	function loadItem($index=0)
	{
		$item =& $this->items[$index];
		$item->index	= $index;

		if ($item->published) {
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
