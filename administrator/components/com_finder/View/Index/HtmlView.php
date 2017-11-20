<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_finder
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Joomla\Component\Finder\Administrator\View\Index;

defined('_JEXEC') or die;

use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\Component\Finder\Administrator\Helper\FinderHelperLanguage;
use Joomla\Component\Finder\Administrator\Helper\FinderHelper;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;

/**
 * Index view class for Finder.
 *
 * @since  2.5
 */
class HtmlView extends BaseHtmlView
{
	/**
	 * An array of items
	 *
	 * @var  array
	 *
	 * @since  3.6.1
	 */
	protected $items;

	/**
	 * The pagination object
	 *
	 * @var  \Joomla\CMS\Pagination\Pagination
	 *
	 * @since  3.6.1
	 */
	protected $pagination;

	/**
	 * The state of core Smart Search plugins
	 *
	 * @var  array
	 *
	 * @since  3.6.1
	 */
	protected $pluginState;

	/**
	 * The HTML markup for the sidebar
	 *
	 * @var  string
	 *
	 * @since  3.6.1
	 */
	protected $sidebar;

	/**
	 * The model state
	 *
	 * @var  mixed
	 *
	 * @since  3.6.1
	 */
	protected $state;

	/**
	 * The total number of items
	 *
	 * @var  integer
	 *
	 * @since  3.6.1
	 */
	protected $total;

	/**
	 * Form object for search filters
	 *
	 * @var    \JForm
	 * @since  4.0.0
	 */
	public $filterForm;

	/**
	 * The active search filters
	 *
	 * @var    array
	 * @since  4.0.0
	 */
	public $activeFilters;

	/**
	 * Method to display the view.
	 *
	 * @param   string  $tpl  A template file to load. [optional]
	 *
	 * @return  mixed  A string if successful, otherwise a \JError object.
	 *
	 * @since   2.5
	 */
	public function display($tpl = null)
	{
		// Load plugin language files.
		FinderHelperLanguage::loadPluginLanguage();

		$this->items         = $this->get('Items');
		$this->total         = $this->get('Total');
		$this->pagination    = $this->get('Pagination');
		$this->state         = $this->get('State');
		$this->pluginState   = $this->get('pluginState');
		$this->filterForm    = $this->get('FilterForm');
		$this->activeFilters = $this->get('ActiveFilters');

		FinderHelper::addSubmenu('index');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new \JViewGenericdataexception(implode("\n", $errors), 500);
		}

		if (!$this->pluginState['plg_content_finder']->enabled)
		{
			$link = \JRoute::_('index.php?option=com_plugins&task=plugin.edit&extension_id=' . FinderHelper::getFinderPluginId());
			\JFactory::getApplication()->enqueueMessage(\JText::sprintf('COM_FINDER_INDEX_PLUGIN_CONTENT_NOT_ENABLED', $link), 'warning');
		}
		elseif ($this->get('TotalIndexed') === 0)
		{
			\JFactory::getApplication()->enqueueMessage(\JText::_('COM_FINDER_INDEX_NO_DATA') . '  ' . \JText::_('COM_FINDER_INDEX_TIP'), 'notice');
		}

		\JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');

		// Configure the toolbar.
		$this->addToolbar();
		$this->sidebar = \JHtmlSidebar::render();

		return parent::display($tpl);
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
		$canDo = ContentHelper::getActions('com_finder');

		ToolbarHelper::title(\JText::_('COM_FINDER_INDEX_TOOLBAR_TITLE'), 'zoom-in finder');

		$toolbar = Toolbar::getInstance('toolbar');
		$toolbar->appendButton(
			'Popup', 'archive', 'COM_FINDER_INDEX', 'index.php?option=com_finder&view=indexer&tmpl=component', 500, 210, 0, 0,
			'window.parent.location.reload()', 'COM_FINDER_HEADING_INDEXER'
		);

		if ($canDo->get('core.edit.state'))
		{
			ToolbarHelper::publishList('index.publish');
			ToolbarHelper::unpublishList('index.unpublish');
		}

		$toolbar->appendButton('Popup', 'bars', 'COM_FINDER_STATISTICS', 'index.php?option=com_finder&view=statistics&tmpl=component', 550, 350);

		if ($canDo->get('core.delete'))
		{
			ToolbarHelper::deleteList('', 'index.delete');
		}

		if ($canDo->get('core.edit.state'))
		{
			ToolbarHelper::trash('index.purge', 'COM_FINDER_INDEX_TOOLBAR_PURGE', false);
		}

		if ($canDo->get('core.admin') || $canDo->get('core.options'))
		{
			ToolbarHelper::preferences('com_finder');
		}

		ToolbarHelper::help('JHELP_COMPONENTS_FINDER_MANAGE_INDEXED_CONTENT');
	}
}
