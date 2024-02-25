<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_workflow
 *
 * @copyright   (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Workflow\Administrator\View\Transitions;

use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Toolbar\Button\DropdownButton;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Transitions view class for the Workflow package.
 *
 * @since  4.0.0
 */
class HtmlView extends BaseHtmlView
{
    /**
     * An array of transitions
     *
     * @var    array
     * @since  4.0.0
     */
    protected $transitions;

    /**
     * The model state
     *
     * @var    object
     * @since  4.0.0
     */
    protected $state;

    /**
     * The HTML for displaying sidebar
     *
     * @var    string
     * @since  4.0.0
     */
    protected $sidebar;

    /**
     * The pagination object
     *
     * @var    \Joomla\CMS\Pagination\Pagination
     *
     * @since  4.0.0
     */
    protected $pagination;

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
     * The current workflow
     *
     * @var    object
     * @since  4.0.0
     */
    protected $workflow;

    /**
     * The ID of current workflow
     *
     * @var    integer
     * @since  4.0.0
     */
    protected $workflowID;

    /**
     * The name of current extension
     *
     * @var    string
     * @since  4.0.0
     */
    protected $extension;

    /**
     * The section of the current extension
     *
     * @var    string
     * @since  4.0.0
     */
    protected $section;

    /**
     * Display the view
     *
     * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
     *
     * @return  void
     *
     * @since  4.0.0
     */
    public function display($tpl = null)
    {
        $this->state            = $this->get('State');
        $this->transitions      = $this->get('Items');
        $this->pagination       = $this->get('Pagination');
        $this->filterForm       = $this->get('FilterForm');
        $this->activeFilters    = $this->get('ActiveFilters');
        $this->workflow         = $this->get('Workflow');

        // Check for errors.
        if (count($errors = $this->get('Errors'))) {
            throw new GenericDataException(implode("\n", $errors), 500);
        }

        $this->workflowID    = $this->workflow->id;

        $parts = explode('.', $this->workflow->extension);

        $this->extension = array_shift($parts);

        if (!empty($parts)) {
            $this->section = array_shift($parts);
        }

        $this->addToolbar();

        parent::display($tpl);
    }

    /**
     * Add the page title and toolbar.
     *
     * @return  void
     *
     * @since  4.0.0
     */
    protected function addToolbar()
    {
        $canDo   = ContentHelper::getActions($this->extension, 'workflow', $this->workflowID);
        $user    = $this->getCurrentUser();
        $toolbar = Toolbar::getInstance();

        ToolbarHelper::title(Text::sprintf('COM_WORKFLOW_TRANSITIONS_LIST', Text::_($this->state->get('active_workflow'))), 'address contact');

        $arrow  = $this->getLanguage()->isRtl() ? 'arrow-right' : 'arrow-left';

        $toolbar->link(
            'JTOOLBAR_BACK',
            Route::_('index.php?option=com_workflow&view=workflows&extension=' . $this->escape($this->workflow->extension))
        )
            ->icon('icon-' . $arrow);

        if ($canDo->get('core.create')) {
            $toolbar->addNew('transition.add');
        }

        if ($canDo->get('core.edit.state') || $user->authorise('core.admin')) {
            /** @var DropdownButton $dropdown */
            $dropdown = $toolbar->dropdownButton('status-group', 'JTOOLBAR_CHANGE_STATUS')
                ->toggleSplit(false)
                ->icon('icon-ellipsis-h')
                ->buttonClass('btn btn-action')
                ->listCheck(true);

            $childBar = $dropdown->getChildToolbar();

            $childBar->publish('transitions.publish', 'JTOOLBAR_ENABLE');
            $childBar->unpublish('transitions.unpublish', 'JTOOLBAR_DISABLE');

            if ($canDo->get('core.admin')) {
                $childBar->checkin('transitions.checkin')->listCheck(true);
            }

            if ($this->state->get('filter.published') !== '-2') {
                $childBar->trash('transitions.trash');
            }
        }

        if ($this->state->get('filter.published') === '-2' && $canDo->get('core.delete')) {
            $toolbar->delete('transitions.delete', 'JTOOLBAR_EMPTY_TRASH')
                ->message('JGLOBAL_CONFIRM_DELETE')
                ->listCheck(true);
        }

        $toolbar->help('Transitions_List:_Basic_Workflow');
    }
}
