<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_fields
 *
 * @copyright   (C) 2016 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Fields\Administrator\View\Fields;

use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\Path;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Toolbar\Button\DropdownButton;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\Component\Fields\Administrator\Helper\FieldsHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Fields View
 *
 * @since  3.7.0
 */
class HtmlView extends BaseHtmlView
{
    /**
     * @var    \Joomla\CMS\Form\Form
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
     * @var    \Joomla\CMS\Pagination\Pagination
     *
     * @since  3.7.0
     */
    protected $pagination;

    /**
     * @var    \Joomla\CMS\Object\CMSObject
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
     * @see     \Joomla\CMS\MVC\View\HtmlView::loadTemplate()
     *
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
        if (count($errors = $this->get('Errors'))) {
            throw new GenericDataException(implode("\n", $errors), 500);
        }

        // Display a warning if the fields system plugin is disabled
        if (!PluginHelper::isEnabled('system', 'fields')) {
            $link = Route::_('index.php?option=com_plugins&task=plugin.edit&extension_id=' . FieldsHelper::getFieldsPluginId());
            Factory::getApplication()->enqueueMessage(Text::sprintf('COM_FIELDS_SYSTEM_PLUGIN_NOT_ENABLED', $link), 'warning');
        }

        // Only add toolbar when not in modal window.
        if ($this->getLayout() !== 'modal') {
            $this->addToolbar();

            // We do not need to filter by language when multilingual is disabled
            if (!Multilanguage::isEnabled()) {
                unset($this->activeFilters['language']);
                $this->filterForm->removeField('language', 'filter');
            }
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
        $fieldId   = $this->state->get('filter.field_id');
        $component = $this->state->get('filter.component');
        $section   = $this->state->get('filter.section');
        $canDo     = ContentHelper::getActions($component, 'field', $fieldId);
        $toolbar   = Toolbar::getInstance();

        // Avoid nonsense situation.
        if ($component == 'com_fields') {
            return;
        }

        // Load extension language file
        $lang = Factory::getLanguage();
        $lang->load($component, JPATH_ADMINISTRATOR)
        || $lang->load($component, Path::clean(JPATH_ADMINISTRATOR . '/components/' . $component));

        $title = Text::sprintf('COM_FIELDS_VIEW_FIELDS_TITLE', Text::_(strtoupper($component)));

        // Prepare the toolbar.
        ToolbarHelper::title($title, 'puzzle-piece fields ' . substr($component, 4) . ($section ? "-$section" : '') . '-fields');

        if ($canDo->get('core.create')) {
            $toolbar->addNew('field.add');
        }

        if ($canDo->get('core.edit.state') || $this->getCurrentUser()->authorise('core.admin')) {
            /** @var DropdownButton $dropdown */
            $dropdown = $toolbar->dropdownButton('status-group', 'JTOOLBAR_CHANGE_STATUS')
                ->toggleSplit(false)
                ->icon('icon-ellipsis-h')
                ->buttonClass('btn btn-action')
                ->listCheck(true);

            $childBar = $dropdown->getChildToolbar();

            if ($canDo->get('core.edit.state')) {
                $childBar->publish('fields.publish')->listCheck(true);
                $childBar->unpublish('fields.unpublish')->listCheck(true);
                $childBar->archive('fields.archive')->listCheck(true);
            }

            if ($this->getCurrentUser()->authorise('core.admin')) {
                $childBar->checkin('fields.checkin')->listCheck(true);
            }

            if ($canDo->get('core.edit.state') && !$this->state->get('filter.state') == -2) {
                $childBar->trash('fields.trash')->listCheck(true);
            }

            // Add a batch button
            if ($canDo->get('core.create') && $canDo->get('core.edit') && $canDo->get('core.edit.state')) {
                $childBar->popupButton('batch', 'JTOOLBAR_BATCH')
                    ->selector('collapseModal')
                    ->listCheck(true);
            }
        }

        if ($this->state->get('filter.state') == -2 && $canDo->get('core.delete', $component)) {
            $toolbar->delete('fields.delete', 'JTOOLBAR_EMPTY_TRASH')
                ->message('JGLOBAL_CONFIRM_DELETE')
                ->listCheck(true);
        }

        if ($canDo->get('core.admin') || $canDo->get('core.options')) {
            $toolbar->preferences($component);
        }

        $toolbar->help('Component:_Fields');
    }
}
