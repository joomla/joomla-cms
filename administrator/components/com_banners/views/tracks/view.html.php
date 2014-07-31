<?php
/**
 * @copyright	Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * View class for a list of tracks.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_banners
 * @since		1.6
 */
class BannersViewTracks extends JViewLegacy
{
	protected $items;
	protected $pagination;
	protected $state;

	/**
	 * Display the view
	 */
	public function display($tpl = null)
	{
		$this->items		= $this->get('Items');
		$this->pagination	= $this->get('Pagination');
		$this->state		= $this->get('State');

		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}

		$this->addToolbar();
		require_once JPATH_COMPONENT .'/models/fields/bannerclient.php';
		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @since	1.6
	 */
	protected function addToolbar()
	{
		require_once JPATH_COMPONENT.'/helpers/banners.php';

		$canDo	= BannersHelper::getActions($this->state->get('filter.category_id'));

		JToolBarHelper::title(JText::_('COM_BANNERS_MANAGER_TRACKS'), 'banners-tracks.png');

		$bar = JToolBar::getInstance('toolbar');
			$bar->appendButton('Popup', 'export', 'JTOOLBAR_EXPORT', 'index.php?option=com_banners&amp;view=download&amp;tmpl=component', 600, 300);
		$document = JFactory::getDocument();
		$app = JFactory::getApplication();
		if ($canDo->get('core.delete')) {
			$bar->appendButton('Confirm', 'COM_BANNERS_DELETE_MSG', 'delete', 'COM_BANNERS_TRACKS_DELETE', 'tracks.delete', false);
			JToolBarHelper::divider();
		}
		if ($canDo->get('core.admin')) {
			JToolBarHelper::preferences('com_banners');
			JToolBarHelper::divider();
		}
		JToolBarHelper::help('JHELP_COMPONENTS_BANNERS_TRACKS');
	}
}
