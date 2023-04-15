<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_workflow
 *
 * @copyright   (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Workflow\Administrator\View\Workflows;

use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ContentHelper;
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
 * Workflows view class for the Workflow package.
 *
 * @since  4.0.0
 */
class HtmlView extends BaseHtmlView
{
    /**
     * An array of workflows
     *
     * @var    array
     * @since  4.0.0
     */
    protected $workflows;

    /**
     * The model state
     *
     * @var    object
     * @since  4.0.0
     */
    protected $state;

    /**
     * The pagination object
     *
     * @var    \Joomla\CMS\Pagination\Pagination
     * @since  4.0.0
     */
    protected $pagination;

    /**
     * The HTML for displaying sidebar
     *
     * @var    string
     * @since  4.0.0
     */
    protected $sidebar;

    /**
     * Form object for search filters
     *
     * @var    \Joomla\CMS\Form\Form
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
        $this->workflows        = $this->get('Items');
        $this->pagination       = $this->get('Pagination');
        $this->filterForm       = $this->get('FilterForm');
        $this->activeFilters    = $this->get('ActiveFilters');

        // Check for errors.
        if (count($errors = $this->get('Errors'))) {
            throw new GenericDataException(implode("\n", $errors), 500);
        }

        $extension = $this->state->get('filter.extension');

        $parts = explode('.', $extension);

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
        $canDo   = ContentHelper::getActions($this->extension, $this->section);
        $user    = Factory::getApplication()->getIdentity();
        $toolbar = Toolbar::getInstance();

        ToolbarHelper::title(Text::_('COM_WORKFLOW_WORKFLOWS_LIST'), 'file-alt contact');

        if ($canDo->get('core.create')) {
            $toolbar->addNew('workflow.add');
        }

        if ($canDo->get('core.edit.state') || $user->authorise('core.admin')) {
            /** @var DropdownButton $dropdown */
            $dropdown = $toolbar->dropdownButton('status-group', 'JTOOLBAR_CHANGE_STATUS')
                ->toggleSplit(false)
                ->icon('icon-ellipsis-h')
                ->buttonClass('btn btn-action')
                ->listCheck(true);

            $childBar = $dropdown->getChildToolbar();

            $childBar->publish('workflows.publish', 'JTOOLBAR_ENABLE');
            $childBar->unpublish('workflows.unpublish', 'JTOOLBAR_DISABLE');
            $childBar->makeDefault('workflows.setDefault', 'COM_WORKFLOW_TOOLBAR_DEFAULT');

            if ($canDo->get('core.admin')) {
                $childBar->checkin('workflows.checkin')->listCheck(true);
            }

            if ($canDo->get('core.edit.state') && $this->state->get('filter.published') != -2) {
                $childBar->trash('workflows.trash');
            }
        }

        if ($this->state->get('filter.published') === '-2' && $canDo->get('core.delete')) {
            $toolbar->delete('workflows.delete', 'JTOOLBAR_EMPTY_TRASH')
                ->message('JGLOBAL_CONFIRM_DELETE')
                ->listCheck(true);
        }

        if ($canDo->get('core.admin') || $canDo->get('core.options')) {
            $toolbar->preferences($this->extension);
        }

        $toolbar->help('Workflows_List');
    }
}
