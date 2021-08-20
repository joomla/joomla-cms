<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_fields
 *
 * @copyright   (C) 2016 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Fields\Administrator\View\Groups;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\Path;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\Component\Fields\Administrator\Helper\FieldsHelper;

/**
 * Groups View
 *
 * @since  3.7.0
 */
class HtmlView extends BaseHtmlView
{
	/**
	 * @var  \JForm
	 *
	 * @since  3.7.0
	 */
	public $filterForm;

	/**
	 * @var  array
	 *
	 * @since  3.7.0
	 */
	public $activeFilters;

	/**
	 * @var  array
	 *
	 * @since  3.7.0
	 */
	protected $items;

	/**
	 * @var  \JPagination
	 *
	 * @since  3.7.0
	 */
	protected $pagination;

	/**
	 * @var  \JObject
	 *
	 * @since  3.7.0
	 */
	protected $state;

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  void
	 *
	 * @see     HtmlView::loadTemplate()
	 * @since   3.7.0
	 */
	public function display($tpl = null)
	{
		$this->state         = $this->get('State');
		$this->items         = $this->get('Items');
		$this->pagination    = $this->get('Pagination');
		$this->filterForm    = $this->get('FilterForm');
		$this->activeFilters = $this->get('ActiveFilters');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new GenericDataException(implode("\n", $errors), 500);
		}

		// Display a warning if the fields system plugin is disabled
		if (!PluginHelper::isEnabled('system', 'fields'))
		{
			$link = Route::_('index.php?option=com_plugins&task=plugin.edit&extension_id=' . FieldsHelper::getFieldsPluginId());
			Factory::getApplication()->enqueueMessage(Text::sprintf('COM_FIELDS_SYSTEM_PLUGIN_NOT_ENABLED', $link), 'warning');
		}

		$this->addToolbar();

		// We do not need to filter by language when multilingual is disabled
		if (!Multilanguage::isEnabled())
		{
			unset($this->activeFilters['language']);
			$this->filterForm->removeField('language', 'filter');
		}

		parent::display($tpl);
	}

	/**
	 * Adds the toolbar.
	 *
	 * @return  void
	 *
	 * @since   3.7.0
	 */
	protected function addToolbar()
	{
		$groupId   = $this->state->get('filter.group_id');
		$component = '';
		$parts     = FieldsHelper::extract($this->state->get('filter.context'));

		if ($parts)
		{
			$component = $parts[0];
		}

		$canDo     = ContentHelper::getActions($component, 'fieldgroup', $groupId);

		// Get the toolbar object instance
		$toolbar = Toolbar::getInstance('toolbar');

		// Avoid nonsense situation.
		if ($component == 'com_fields')
		{
			return;
		}

		// Load component language file
		$lang = Factory::getLanguage();
		$lang->load($component, JPATH_ADMINISTRATOR)
		|| $lang->load($component, Path::clean(JPATH_ADMINISTRATOR . '/components/' . $component));

		$title = Text::sprintf('COM_FIELDS_VIEW_GROUPS_TITLE', Text::_(strtoupper($component)));

		// Prepare the toolbar.
		ToolbarHelper::title($title, 'puzzle-piece fields ' . substr($component, 4) . '-groups');

		if ($canDo->get('core.create'))
		{
			$toolbar->addNew('group.add');
		}

		if ($canDo->get('core.edit.state') || Factory::getUser()->authorise('core.admin'))
		{
			$dropdown = $toolbar->dropdownButton('status-group')
				->text('JTOOLBAR_CHANGE_STATUS')
				->toggleSplit(false)
				->icon('icon-ellipsis-h')
				->buttonClass('btn btn-action')
				->listCheck(true);

			$childBar = $dropdown->getChildToolbar();

			if ($canDo->get('core.edit.state'))
			{
				$childBar->publish('groups.publish')->listCheck(true);

				$childBar->unpublish('groups.unpublish')->listCheck(true);

				$childBar->archive('groups.archive')->listCheck(true);
			}

			if (Factory::getUser()->authorise('core.admin'))
			{
				$childBar->checkin('groups.checkin')->listCheck(true);
			}

			if ($canDo->get('core.edit.state') && !$this->state->get('filter.state') == -2)
			{
				$childBar->trash('groups.trash')->listCheck(true);
			}

			// Add a batch button
			if ($canDo->get('core.create') && $canDo->get('core.edit') && $canDo->get('core.edit.state'))
			{
				$childBar->popupButton('batch')
					->text('JTOOLBAR_BATCH')
					->selector('collapseModal')
					->listCheck(true);
			}
		}

		if ($this->state->get('filter.state') == -2 && $canDo->get('core.delete', $component))
		{
			$toolbar->delete('groups.delete')
				->text('JTOOLBAR_EMPTY_TRASH')
				->message('JGLOBAL_CONFIRM_DELETE')
				->listCheck(true);
		}

		if ($canDo->get('core.admin') || $canDo->get('core.options'))
		{
			$toolbar->preferences($component);
		}

		$toolbar->help('JHELP_COMPONENTS_FIELDS_FIELD_GROUPS');
	}
}
