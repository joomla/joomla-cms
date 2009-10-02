<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	com_menus
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

jimport('joomla.application.component.view');

/**
 * The HTML Menus Menu Items View.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_menus
 * @version		1.6
 */
class MenusViewItems extends JView
{
	protected $state;
	protected $items;
	protected $pagination;
	protected $f_levels;

	/**
	 * Display the view
	 */
	public function display($tpl = null)
	{
		$state		= $this->get('State');
		$items		= $this->get('Items');
		$pagination	= $this->get('Pagination');

		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}

		$this->assignRef('state',		$state);
		$this->assignRef('items',		$items);
		$this->assignRef('pagination',	$pagination);

		// Preprocess the list of items to find ordering divisions.
		foreach ($items as $i => &$item)
		{
			// TODO: Complete the ordering stuff with nested sets
			$item->order_up = true;
			$item->order_dn = true;
		}

		// Levels filter.
		$options	= array();
		$options[]	= JHtml::_('select.option', '1');
		$options[]	= JHtml::_('select.option', '2');
		$options[]	= JHtml::_('select.option', '3');
		$options[]	= JHtml::_('select.option', '4');
		$this->assign('f_levels', $options);

		parent::display($tpl);
		$this->_setToolbar();
	}

	/**
	 * Build the default toolbar.
	 *
	 * @return	void
	 */
	protected function _setToolbar()
	{
		JToolBarHelper::title(JText::_('Menus_View_Items_Title'), 'menu.png');
		JToolBarHelper::custom('item.add', 'new.png', 'new_f2.png', 'New', false);
		JToolBarHelper::custom('item.edit', 'edit.png', 'edit_f2.png', 'Edit', true);

		JToolBarHelper::divider();

		JToolBarHelper::custom('items.publish', 'publish.png', 'publish_f2.png', 'Publish', true);
		JToolBarHelper::custom('items.unpublish', 'unpublish.png', 'unpublish_f2.png', 'Unpublish', true);
		if ($this->state->get('filter.published') == -2) {
			JToolBarHelper::deleteList('', 'items.delete');
		}
		else {
			JToolBarHelper::trash('items.trash');
		}
		JToolBarHelper::divider();


		JToolBarHelper::help('screen.menus.items');
	}
}
