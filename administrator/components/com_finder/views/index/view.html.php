<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_finder
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

JLoader::register('FinderHelperLanguage', JPATH_ADMINISTRATOR . '/components/com_finder/helpers/language.php');

/**
 * Index view class for Finder.
 *
 * @since  2.5
 */
class FinderViewIndex extends JViewLegacy
{
	/**
	 * Method to display the view.
	 *
	 * @param   string  $tpl  A template file to load. [optional]
	 *
	 * @return  mixed  A string if successful, otherwise a JError object.
	 *
	 * @since   2.5
	 */
	public function display($tpl = null)
	{
		// Load plug-in language files.
		FinderHelperLanguage::loadPluginLanguage();

		$this->items		= $this->get('Items');
		$this->total		= $this->get('Total');
		$this->pagination	= $this->get('Pagination');
		$this->state		= $this->get('State');
		$this->pluginState  = $this->get('pluginState');

		FinderHelper::addSubmenu('index');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			JError::raiseError(500, implode("\n", $errors));

			return false;
		}

		JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');

		// Configure the toolbar.
		$this->addToolbar();
		$this->sidebar = JHtmlSidebar::render();
		parent::display($tpl);
	}

	/**
	 * Method to configure the toolbar for this view.
	 *
	 * @return  void
	 *
	 * @since   2.5
	 */
	protected function addToolbar()
	{
		$canDo	= JHelperContent::getActions('com_finder');

		JToolbarHelper::title(JText::_('COM_FINDER_INDEX_TOOLBAR_TITLE'), 'zoom-in finder');

		$toolbar = JToolbar::getInstance('toolbar');
		$toolbar->appendButton(
			'Popup', 'archive', 'COM_FINDER_INDEX', 'index.php?option=com_finder&view=indexer&tmpl=component', 500, 210, 0, 0,
			'window.parent.location.reload()', 'COM_FINDER_HEADING_INDEXER'
		);

		if ($canDo->get('core.edit.state'))
		{
			JToolbarHelper::publishList('index.publish');
			JToolbarHelper::unpublishList('index.unpublish');
		}

		if ($canDo->get('core.admin'))
		{
			JToolbarHelper::preferences('com_finder');
		}

		$toolbar->appendButton('Popup', 'bars', 'COM_FINDER_STATISTICS', 'index.php?option=com_finder&view=statistics&tmpl=component', 550, 350);

		if ($canDo->get('core.delete'))
		{
			JToolbarHelper::deleteList('', 'index.delete');
		}

		if ($canDo->get('core.edit.state'))
		{
			JToolbarHelper::trash('index.purge', 'COM_FINDER_INDEX_TOOLBAR_PURGE', false);
		}

		JToolbarHelper::help('JHELP_COMPONENTS_FINDER_MANAGE_INDEXED_CONTENT');

		JHtmlSidebar::setAction('index.php?option=com_finder&view=index');

		JHtmlSidebar::addFilter(
			JText::_('COM_FINDER_INDEX_FILTER_BY_STATE'),
			'filter_state',
			JHtml::_('select.options', JHtml::_('finder.statelist'), 'value', 'text', $this->state->get('filter.state'))
		);

		JHtmlSidebar::addFilter(
			JText::_('COM_FINDER_INDEX_TYPE_FILTER'),
			'filter_type',
			JHtml::_('select.options', JHtml::_('finder.typeslist'), 'value', 'text', $this->state->get('filter.type'))
		);
	}
}
