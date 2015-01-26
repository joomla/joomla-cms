<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_banners
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * View class for a list of tracks.
 *
 * @since  1.6
 */
class BannersViewTracks extends JViewLegacy
{
	protected $items;

	protected $pagination;

	protected $state;

	/**
	 * Display the view
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  void
	 */
	public function display($tpl = null)
	{
		$this->items      = $this->get('Items');
		$this->pagination = $this->get('Pagination');
		$this->state      = $this->get('State');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			JError::raiseError(500, implode("\n", $errors));

			return false;
		}

		BannersHelper::addSubmenu('tracks');

		$this->addToolbar();

		require_once JPATH_COMPONENT . '/models/fields/bannerclient.php';

		$this->sidebar = JHtmlSidebar::render();

		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	protected function addToolbar()
	{
		require_once JPATH_COMPONENT . '/helpers/banners.php';

		$canDo = JHelperContent::getActions('com_banners', 'category', $this->state->get('filter.category_id'));

		JToolbarHelper::title(JText::_('COM_BANNERS_MANAGER_TRACKS'), 'bookmark banners-tracks');

		$bar = JToolBar::getInstance('toolbar');
		$bar->appendButton('Popup', 'download', 'JTOOLBAR_EXPORT', 'index.php?option=com_banners&amp;view=download&amp;tmpl=component', 600, 300);

		if ($canDo->get('core.delete'))
		{
			$bar->appendButton('Confirm', 'COM_BANNERS_DELETE_MSG', 'delete', 'COM_BANNERS_TRACKS_DELETE', 'tracks.delete', false);
			JToolbarHelper::divider();
		}

		if ($canDo->get('core.admin'))
		{
			JToolbarHelper::preferences('com_banners');
			JToolbarHelper::divider();
		}

		JToolbarHelper::help('JHELP_COMPONENTS_BANNERS_TRACKS');

		JHtmlSidebar::setAction('index.php?option=com_banners&view=tracks');

		JHtmlSidebar::addFilter(
			JText::_('COM_BANNERS_SELECT_CLIENT'),
			'filter_client_id',
			JHtml::_('select.options', BannersHelper::getClientOptions(), 'value', 'text', $this->state->get('filter.client_id'))
		);

		JHtmlSidebar::addFilter(
			JText::_('JOPTION_SELECT_CATEGORY'),
			'filter_category_id',
			JHtml::_('select.options', JHtml::_('category.options', 'com_banners'), 'value', 'text', $this->state->get('filter.category_id'))
		);

		JHtmlSidebar::addFilter(
			JText::_('COM_BANNERS_SELECT_TYPE'),
			'filter_type',
			JHtml::_(
				'select.options',
				array(JHtml::_('select.option', 1, JText::_('COM_BANNERS_IMPRESSION')), JHtml::_('select.option', 2, JText::_('COM_BANNERS_CLICK'))),
				'value',
				'text',
				$this->state->get('filter.type')
			)
		);
	}

	/**
	 * Returns an array of fields the table can be sorted by
	 *
	 * @return  array  Array containing the field name to sort by as the key and display text as value
	 *
	 * @since   3.0
	 */
	protected function getSortFields()
	{
		return array(
			'b.name' => JText::_('COM_BANNERS_HEADING_NAME'),
			'cl.name' => JText::_('COM_BANNERS_HEADING_CLIENT'),
			'track_type' => JText::_('COM_BANNERS_HEADING_TYPE'),
			'count' => JText::_('COM_BANNERS_HEADING_COUNT'),
			'track_date' => JText::_('JDATE')
		);
	}
}
