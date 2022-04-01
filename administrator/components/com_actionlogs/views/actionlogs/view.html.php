<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_actionlogs
 *
 * @copyright   (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;

JLoader::register('ActionlogsHelper', JPATH_ADMINISTRATOR . '/components/com_actionlogs/helpers/actionlogs.php');

/**
 * View class for a list of logs.
 *
 * @since  3.9.0
 */
class ActionlogsViewActionlogs extends JViewLegacy
{
	/**
	 * An array of items.
	 *
	 * @var    array
	 * @since  3.9.0
	 */
	protected $items;

	/**
	 * The model state
	 *
	 * @var    array
	 * @since  3.9.0
	 */
	protected $state;

	/**
	 * The pagination object
	 *
	 * @var    JPagination
	 * @since  3.9.0
	 */
	protected $pagination;

	/**
	 * The active search filters
	 *
	 * @var    array
	 * @since  3.9.0
	 */
	public $activeFilters;

	/**
	 * Method to display the view.
	 *
	 * @param   string  $tpl  A template file to load. [optional]
	 *
	 * @return  mixed  A string if successful, otherwise an Error object.
	 *
	 * @since   3.9.0
	 */
	public function display($tpl = null)
	{
		$params = ComponentHelper::getParams('com_actionlogs');

		$this->items         = $this->get('Items');
		$this->state         = $this->get('State');
		$this->filterForm    = $this->get('FilterForm');
		$this->activeFilters = $this->get('ActiveFilters');
		$this->pagination    = $this->get('Pagination');
		$this->showIpColumn  = (bool) $params->get('ip_logging', 0);

		if (count($errors = $this->get('Errors')))
		{
			JError::raiseError(500, implode("\n", $errors));

			return false;
		}

		$this->addToolBar();

		// Load all actionlog plugins language files
		ActionlogsHelper::loadActionLogPluginsLanguage();

		return parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return  void
	 *
	 * @since   3.9.0
	 */
	protected function addToolbar()
	{
		ToolbarHelper::title(Text::_('COM_ACTIONLOGS_MANAGER_USERLOGS'), 'list-2');

		ToolbarHelper::deleteList('JGLOBAL_CONFIRM_DELETE', 'actionlogs.delete');
		$bar = Toolbar::getInstance('toolbar');
		$bar->appendButton('Confirm', 'COM_ACTIONLOGS_PURGE_CONFIRM', 'delete', 'COM_ACTIONLOGS_TOOLBAR_PURGE', 'actionlogs.purge', false);
		ToolbarHelper::preferences('com_actionlogs');
		ToolbarHelper::help('JHELP_COMPONENTS_ACTIONLOGS');
		ToolBarHelper::custom('actionlogs.exportSelectedLogs', 'download', '', 'COM_ACTIONLOGS_EXPORT_CSV', true);
		ToolBarHelper::custom('actionlogs.exportLogs', 'download', '', 'COM_ACTIONLOGS_EXPORT_ALL_CSV', false);
	}
}
