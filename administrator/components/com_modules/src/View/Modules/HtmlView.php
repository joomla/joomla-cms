<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_modules
 *
 * @copyright   (C) 2008 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Modules\Administrator\View\Modules;

use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\Button\DropdownButton;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * View class for a list of modules.
 *
 * @since  1.6
 */
class HtmlView extends BaseHtmlView
{
    /**
     * An array of items
     *
     * @var  array
     */
    protected $items;

    /**
     * The pagination object
     *
     * @var  \Joomla\CMS\Pagination\Pagination
     */
    protected $pagination;

    /**
     * The model state
     *
     * @var  \Joomla\CMS\Object\CMSObject
     */
    protected $state;

    /**
     * Form object for search filters
     *
     * @var    \Joomla\CMS\Form\Form
     *
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
     * Is this view an Empty State
     *
     * @var  boolean
     * @since 4.0.0
     */
    private $isEmptyState = false;

    /**
     * Display the view
     *
     * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
     *
     * @return  void
     *
     * @since   1.6
     */
    public function display($tpl = null)
    {
        $this->items         = $this->get('Items');
        $this->pagination    = $this->get('Pagination');
        $this->state         = $this->get('State');
        $this->total         = $this->get('Total');
        $this->filterForm    = $this->get('FilterForm');
        $this->activeFilters = $this->get('ActiveFilters');
        $this->clientId      = $this->state->get('client_id');

        if (!count($this->items) && $this->isEmptyState = $this->get('IsEmptyState')) {
            $this->setLayout('emptystate');
        }

        /**
         * The code below make sure the remembered position will be available from filter dropdown even if there are no
         * modules available for this position. This will make the UI less confusing for users in case there is only one
         * module in the selected position and user:
         * 1. Edit the module, change it to new position, save it and come back to Modules Management Screen
         * 2. Or move that module to new position using Batch action
         */
        if (count($this->items) === 0 && $this->state->get('filter.position')) {
            $selectedPosition = $this->state->get('filter.position');
            $positionField    = $this->filterForm->getField('position', 'filter');

            $positionExists = false;

            foreach ($positionField->getOptions() as $option) {
                if ($option->value === $selectedPosition) {
                    $positionExists = true;
                    break;
                }
            }

            if ($positionExists === false) {
                $positionField->addOption($selectedPosition, ['value' => $selectedPosition]);
            }
        }

        // Check for errors.
        if (count($errors = $this->get('Errors'))) {
            throw new GenericDataException(implode("\n", $errors), 500);
        }

        // We do not need the Language filter when modules are not filtered
        if ($this->clientId == 1 && !ModuleHelper::isAdminMultilang()) {
            unset($this->activeFilters['language']);
            $this->filterForm->removeField('language', 'filter');
        }

        // We don't need the toolbar in the modal window.
        if ($this->getLayout() !== 'modal') {
            $this->addToolbar();

            // We do not need to filter by language when multilingual is disabled
            if (!Multilanguage::isEnabled()) {
                unset($this->activeFilters['language']);
                $this->filterForm->removeField('language', 'filter');
            }
        } else {
            // If in modal layout.
            // Client id selector should not exist.
            $this->filterForm->removeField('client_id', '');

            // If in the frontend state and language should not activate the search tools.
            if (Factory::getApplication()->isClient('site')) {
                unset($this->activeFilters['state']);
                unset($this->activeFilters['language']);
            }
        }

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
        $state = $this->get('State');
        $canDo = ContentHelper::getActions('com_modules');
        $user  = $this->getCurrentUser();

        // Get the toolbar object instance
        $toolbar = Toolbar::getInstance();

        if ($state->get('client_id') == 1) {
            ToolbarHelper::title(Text::_('COM_MODULES_MANAGER_MODULES_ADMIN'), 'cube module');
        } else {
            ToolbarHelper::title(Text::_('COM_MODULES_MANAGER_MODULES_SITE'), 'cube module');
        }

        if ($canDo->get('core.create')) {
            $toolbar->standardButton('new', 'JTOOLBAR_NEW')
                ->onclick("location.href='index.php?option=com_modules&amp;view=select&amp;client_id=" . $this->state->get('client_id', 0) . "'");
        }

        if (!$this->isEmptyState && ($canDo->get('core.edit.state') || $this->getCurrentUser()->authorise('core.admin'))) {
            /** @var DropdownButton $dropdown */
            $dropdown = $toolbar->dropdownButton('status-group', 'JTOOLBAR_CHANGE_STATUS')
                ->toggleSplit(false)
                ->icon('icon-ellipsis-h')
                ->buttonClass('btn btn-action')
                ->listCheck(true);

            $childBar = $dropdown->getChildToolbar();

            if ($canDo->get('core.edit.state')) {
                $childBar->publish('modules.publish')->listCheck(true);

                $childBar->unpublish('modules.unpublish')->listCheck(true);
            }

            if ($this->getCurrentUser()->authorise('core.admin')) {
                $childBar->checkin('modules.checkin')->listCheck(true);
            }

            if ($canDo->get('core.edit.state') && $this->state->get('filter.published') != -2) {
                $childBar->trash('modules.trash')->listCheck(true);
            }

            // Add a batch button
            if (
                $user->authorise('core.create', 'com_modules') && $user->authorise('core.edit', 'com_modules')
                && $user->authorise('core.edit.state', 'com_modules')
            ) {
                $childBar->popupButton('batch', 'JTOOLBAR_BATCH')
                    ->selector('collapseModal')
                    ->listCheck(true);
            }

            if ($canDo->get('core.create')) {
                $childBar->standardButton('copy', 'JTOOLBAR_DUPLICATE', 'modules.duplicate')
                    ->listCheck(true);
            }
        }

        if (!$this->isEmptyState && ($state->get('filter.state') == -2 && $canDo->get('core.delete'))) {
            $toolbar->delete('modules.delete', 'JTOOLBAR_EMPTY_TRASH')
                ->message('JGLOBAL_CONFIRM_DELETE')
                ->listCheck(true);
        }

        if ($canDo->get('core.admin')) {
            $toolbar->preferences('com_modules');
        }

        $toolbar->help('Modules');
    }
}
