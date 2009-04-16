<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	Menus
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License, see LICENSE.php
  */

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.application.component.view');

/**
 * Extension Manager Plugins View
 *
 * @package		Joomla.Administrator
 * @subpackage	Installer
 * @since		1.5
 */
include_once(dirname(__FILE__).DS.'..'.DS.'default'.DS.'view.php');

class InstallerViewPlugins extends InstallerViewDefault
{
	protected $item;
	protected $items;
	protected $pagination;
	protected $fields;
	function display($tpl=null)
	{
		/*
		 * Set toolbar items for the page
		 */
		JToolBarHelper::deleteList( '', 'remove', 'Uninstall' );
		JToolBarHelper::help( 'screen.installer2' );

		// Get data from the model
		$state		= &$this->get('State');
		$items		= &$this->get('Items');
		$pagination	= &$this->get('Pagination');
		$groups		= &$this->get('Groups');

		$fields = new stdClass();
		$fields->groups = JHtml::_('select.genericlist', $groups, 'group', 
			array(
				'list.attr' => 'class="inputbox" size="1" onchange="document.adminForm.submit();"',
				'list.select' => $state->get('filter.group')
			)
		);

		$this->assignRef('items',		$items);
		$this->assignRef('pagination',	$pagination);
		$this->assignRef('fields',		$fields);

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
