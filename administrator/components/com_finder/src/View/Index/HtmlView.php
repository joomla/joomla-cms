<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_finder
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Finder\Administrator\View\Index;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\Component\Finder\Administrator\Helper\FinderHelper;
use Joomla\Component\Finder\Administrator\Helper\LanguageHelper;

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
	 * @return  mixed  A string if successful, otherwise an \Exception object.
	 *
	 * @since   2.5
	 */
	public function display($tpl = null)
	{
		// Load plugin language files.
		LanguageHelper::loadPluginLanguage();

		$this->items         = $this->get('Items');
		$this->total         = $this->get('Total');
		$this->pagination    = $this->get('Pagination');
		$this->state         = $this->get('State');
		$this->pluginState   = $this->get('pluginState');
		$this->filterForm    = $this->get('FilterForm');
		$this->activeFilters = $this->get('ActiveFilters');

		// We do not need to filter by language when multilingual is disabled
		if (!Multilanguage::isEnabled())
		{
			unset($this->activeFilters['language']);
			$this->filterForm->removeField('language', 'filter');
		}

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new GenericDataException(implode("\n", $errors), 500);
		}

		if (!$this->pluginState['plg_content_finder']->enabled)
		{
			if (Factory::getUser()->authorise('core.manage', 'com_plugin'))
			{
				$link = Route::_('index.php?option=com_plugins&task=plugin.edit&extension_id=' . FinderHelper::getFinderPluginId());
				Factory::getApplication()->enqueueMessage(Text::sprintf('COM_FINDER_INDEX_PLUGIN_CONTENT_NOT_ENABLED_LINK', $link), 'warning');
			}
			else
			{
				Factory::getApplication()->enqueueMessage(Text::_('COM_FINDER_INDEX_PLUGIN_CONTENT_NOT_ENABLED'), 'warning');
			}
		}
		elseif ($this->get('TotalIndexed') === 0)
		{
			Factory::getApplication()->enqueueMessage(Text::_('COM_FINDER_INDEX_NO_DATA') . '  ' . Text::_('COM_FINDER_INDEX_TIP'), 'notice');
		}

		// Configure the toolbar.
		$this->addToolbar();

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

		// Get the toolbar object instance
		$toolbar = Toolbar::getInstance('toolbar');

		ToolbarHelper::title(Text::_('COM_FINDER_INDEX_TOOLBAR_TITLE'), 'zoom-in finder');

		$toolbar->appendButton(
			'Popup', 'archive', 'COM_FINDER_INDEX', 'index.php?option=com_finder&view=indexer&tmpl=component', 500, 210, 0, 0,
			'window.parent.location.reload()', Text::_('COM_FINDER_HEADING_INDEXER')
		);

		if ($canDo->get('core.edit.state'))
		{
			$dropdown = $toolbar->dropdownButton('status-group')
				->text('JTOOLBAR_CHANGE_STATUS')
				->toggleSplit(false)
				->icon('fas fa-ellipsis-h')
				->buttonClass('btn btn-action')
				->listCheck(true);

			$childBar = $dropdown->getChildToolbar();

			$childBar->publish('index.publish')->listCheck(true);
			$childBar->unpublish('index.unpublish')->listCheck(true);
		}

		$toolbar->appendButton('Popup', 'bars', 'COM_FINDER_STATISTICS', 'index.php?option=com_finder&view=statistics&tmpl=component', 550, 350, '', '', '', Text::_('COM_FINDER_STATISTICS_TITLE'));

		if ($canDo->get('core.delete'))
		{
			ToolbarHelper::deleteList('', 'index.delete');
			ToolbarHelper::divider();
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
