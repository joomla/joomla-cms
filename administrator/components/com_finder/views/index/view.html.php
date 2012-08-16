<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_finder
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

JLoader::register('FinderHelperLanguage', JPATH_ADMINISTRATOR . '/components/com_finder/helpers/language.php');

/**
 * Index view class for Finder.
 *
 * @package     Joomla.Administrator
 * @subpackage  com_finder
 * @since       2.5
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

		// Initialise variables
		$this->items		= $this->get('Items');
		$this->total		= $this->get('Total');
		$this->pagination	= $this->get('Pagination');
		$this->state		= $this->get('State');
		$this->pluginState  = $this->get('pluginState');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}

		JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');

		// Configure the toolbar.
		$this->addToolbar();

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
		$canDo	= FinderHelper::getActions();

		JToolbarHelper::title(JText::_('COM_FINDER_INDEX_TOOLBAR_TITLE'), 'finder');
		$toolbar = JToolbar::getInstance('toolbar');

		$toolbar->appendButton('Popup', 'archive', 'COM_FINDER_INDEX', 'index.php?option=com_finder&view=indexer&tmpl=component', 500, 210);
		JToolbarHelper::divider();

		if ($canDo->get('core.edit.state'))
		{
			JToolbarHelper::publishList('index.publish');
			JToolbarHelper::unpublishList('index.unpublish');
			JToolbarHelper::divider();
		}
		if ($canDo->get('core.delete'))
		{
			JToolbarHelper::deleteList('', 'index.delete');
			JToolbarHelper::divider();
		}
		if ($canDo->get('core.edit.state'))
		{
			JToolbarHelper::trash('index.purge', 'COM_FINDER_INDEX_TOOLBAR_PURGE', false);
			JToolbarHelper::divider();
		}

		if ($canDo->get('core.admin'))
		{
			JToolbarHelper::preferences('com_finder');
		}
		JToolbarHelper::divider();
		$toolbar->appendButton('Popup', 'stats', 'COM_FINDER_STATISTICS', 'index.php?option=com_finder&view=statistics&tmpl=component', 550, 350);
		JToolbarHelper::divider();
		JToolbarHelper::help('JHELP_COMPONENTS_FINDER_MANAGE_INDEXED_CONTENT');
	}
}
