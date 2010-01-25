<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

/**
 * View class for a list of banners.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_banners
 * @since		1.6
 */
class BannersViewBanners extends JView
{
	protected $state;
	protected $items;
	protected $pagination;

	/**
	 * Display the view
	 */
	public function display($tpl = null)
	{
		$state		= $this->get('State');
		$items		= $this->get('Items');
		$pagination	= $this->get('Pagination');
		$categories	= $this->get('Categories');
		$params		= JComponentHelper::getParams('com_banners');

		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}

		$this->assignRef('state',		$state);
		$this->assignRef('items',		$items);
		$this->assignRef('pagination',	$pagination);
		$this->assignRef('categories',	$categories);
		$this->assignRef('params',		$params);

		$this->_setToolbar();
		require_once JPATH_COMPONENT .'/models/fields/bannerclient.php';
		parent::display($tpl);
	}

	/**
	 * Setup the Toolbar.
	 */
	protected function _setToolbar()
	{
		require_once JPATH_COMPONENT.DS.'helpers'.DS.'banners.php';

		$state	= $this->get('State');
		$canDo	= BannersHelper::getActions($state->get('filter.category_id'));

		JToolBarHelper::title(JText::_('Banners_Manager_Banners'), 'generic.png');
		if ($canDo->get('core.create')) {
			JToolBarHelper::addNew('banner.add');
		}
		if ($canDo->get('core.edit')) {
			JToolBarHelper::editList('banner.edit');
		}
		JToolBarHelper::divider();
		if ($canDo->get('core.edit.state')) {
			JToolBarHelper::custom('banners.publish', 'publish.png', 'publish_f2.png', 'Publish', true);
			JToolBarHelper::custom('banners.unpublish', 'unpublish.png', 'unpublish_f2.png', 'Unpublish', true);
			JToolBarHelper::divider();
			if ($state->get('filter.published') != -1) {
				JToolBarHelper::archiveList('banners.archive');
			}
		}
		if ($state->get('filter.state') == -2 && $canDo->get('core.delete')) {
			JToolBarHelper::deleteList('', 'banners.delete');
		}
		else if ($canDo->get('core.edit.state')) {
			JToolBarHelper::trash('banners.trash');
		}
		if ($canDo->get('core.admin')) {
			JToolBarHelper::divider();
			JToolBarHelper::preferences('com_banners');
		}
		JToolBarHelper::divider();
		JToolBarHelper::help('screen.banners.banners','JTOOLBAR_HELP');
	}
}
