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
 * View class for a list of tracks.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_banners
 * @since		1.6
 */
class BannersViewTracks extends JView
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

		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}

		$this->assignRef('state',		$state);
		$this->assignRef('items',		$items);
		$this->assignRef('pagination',	$pagination);

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

		JToolBarHelper::title(JText::_('Banners_Manager_Tracks'), 'generic.png');

		$bar = &JToolBar::getInstance('toolbar');
		$bar->appendButton('Popup', 'export', 'Banners_Tracks_Export', 'index.php?option=com_banners&view=download&tmpl=component',600,250);

		$document = &JFactory::getDocument();
		$app = &JFactory::getApplication();
		// TODO: must be written in the bluestork template
		$document->addStyleDeclaration('.icon-32-export { background-image: url(templates/'.$app->getTemplate().'/images/toolbar/icon-32-export.png); }');
		if ($canDo->get('core.delete')) {
			$bar->appendButton('Confirm','Banners_Delete_Msg', 'delete', 'Delete', 'tracks.delete',false);
		}
		if ($canDo->get('core.admin')) {
			JToolBarHelper::divider();
			JToolBarHelper::preferences('com_banners');
		}
		JToolBarHelper::divider();
		JToolBarHelper::help('screen.banners.tracks');
	}
}
