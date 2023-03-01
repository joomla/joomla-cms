<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_scheduler
 *
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Scheduler\Administrator\View\Tasks;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\Pagination\Pagination;
use Joomla\CMS\Toolbar\Button\DropdownButton;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * MVC View for the Tasks list page.
 *
 * @since  4.1.0
 */
class HtmlView extends BaseHtmlView
{
    /**
     * Array of task items.
     *
     * @var    array
     * @since  4.1.0
     */
    protected $items;

    /**
     * The pagination object.
     *
     * @var    Pagination
     * @since  4.1.0
     * @todo   Test pagination.
     */
    protected $pagination;

    /**
     * The model state.
     *
     * @var    CMSObject
     * @since  4.1.0
     */
    protected $state;

    /**
     * A Form object for search filters.
     *
     * @var    Form
     * @since  4.1.0
     */
    public $filterForm;

    /**
     * The active search filters.
     *
     * @var    array
     * @since  4.1.0
     */
    public $activeFilters;

    /**
     * Is this view in an empty state?
     *
     * @var    boolean
     * @since  4.1.0
     */
    private $isEmptyState = false;

    /**
     * @inheritDoc
     *
     * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
     *
     * @return  void
     *
     * @since   4.1.0
     * @throws  \Exception
     */
    public function display($tpl = null): void
    {
        $this->items         = $this->get('Items');
        $this->pagination    = $this->get('Pagination');
        $this->state         = $this->get('State');
        $this->filterForm    = $this->get('FilterForm');
        $this->activeFilters = $this->get('ActiveFilters');

        if (!\count($this->items) && $this->isEmptyState = $this->get('IsEmptyState')) {
            $this->setLayout('empty_state');
        }

        // Check for errors.
        if (\count($errors = $this->get('Errors'))) {
            throw new GenericDataException(implode("\n", $errors), 500);
        }

        $this->addToolbar();

        parent::display($tpl);
    }

    /**
     * Add the page title and toolbar.
     *
     * @return  void
     *
     * @since  4.1.0
     * @throws  \Exception
     */
    protected function addToolbar(): void
    {
        $canDo = ContentHelper::getActions('com_scheduler');
        $user  = Factory::getApplication()->getIdentity();

        /*
        * Get the toolbar object instance
        * !! @todo : Replace usage with ToolbarFactoryInterface
        */
        $toolbar = Toolbar::getInstance();

        ToolbarHelper::title(Text::_('COM_SCHEDULER_MANAGER_TASKS'), 'clock');

        if ($canDo->get('core.create')) {
            $toolbar->linkButton('new', 'JTOOLBAR_NEW')
                ->url('index.php?option=com_scheduler&view=select&layout=default')
                ->buttonClass('btn btn-success')
                ->icon('icon-new');
        }

        if (!$this->isEmptyState && ($canDo->get('core.edit.state') || $user->authorise('core.admin'))) {
            /** @var  DropdownButton $dropdown */
            $dropdown = $toolbar->dropdownButton('status-group')
                ->toggleSplit(false)
                ->text('JTOOLBAR_CHANGE_STATUS')
                ->icon('icon-ellipsis-h')
                ->buttonClass('btn btn-action')
                ->listCheck(true);

            $childBar = $dropdown->getChildToolbar();

            // Add the batch Enable, Disable and Trash buttons if privileged
            if ($canDo->get('core.edit.state')) {
                $childBar->publish('tasks.publish', 'JTOOLBAR_ENABLE')->listCheck(true);
                $childBar->unpublish('tasks.unpublish', 'JTOOLBAR_DISABLE')->listCheck(true);

                if ($canDo->get('core.admin')) {
                    $childBar->checkin('tasks.checkin')->listCheck(true);
                }

                $childBar->checkin('tasks.unlock', 'COM_SCHEDULER_TOOLBAR_UNLOCK')->listCheck(true)->icon('icon-unlock');

                // We don't want the batch Trash button if displayed entries are all trashed
                if ($this->state->get('filter.state') != -2) {
                    $childBar->trash('tasks.trash')->listCheck(true);
                }
            }
        }

        // Add "Empty Trash" button if filtering by trashed.
        if ($this->state->get('filter.state') == -2 && $canDo->get('core.delete')) {
            $toolbar->delete('tasks.delete')
                ->message('JGLOBAL_CONFIRM_DELETE')
                ->text('JTOOLBAR_EMPTY_TRASH')
                ->listCheck(true);
        }

        // Link to component preferences if user has admin privileges
        if ($canDo->get('core.admin') || $canDo->get('core.options')) {
            $toolbar->preferences('com_scheduler');
        }

        $toolbar->help('Scheduled_Tasks');
    }
}
