<?php

/**
 * @package     JED
 *
 * @subpackage  Tickets
 *
 * @copyright   (C) 2022 Open Source Matters, Inc.  <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Jed\Component\Jed\Administrator\View\Messagetemplates;

// No direct access
// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use Exception;
use Jed\Component\Jed\Administrator\Helper\JedHelper;
use Joomla\CMS\Form\Form;
use Joomla\CMS\HTML\Helpers\Sidebar;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\Pagination\Pagination;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\Component\Content\Administrator\Extension\ContentComponent;
use Joomla\Registry\Registry;

/**
 * View class for a list of Message Templates.
 *
 * @since  4.0.0
 */
class HtmlView extends BaseHtmlView
{
    /**
     * The form filter
     *
     * @var    Form|null
     * @since  4.0.0
     */
    public ?Form $filterForm;

    /**
     * The active filters
     *
     * @var    array
     * @var    array
     * @since  4.0.0
     */
    public array $activeFilters = [];
    /**
     * List of items
     *
     * @var    array
     * @since  4.0.0
     */
    protected array $items = [];
    /**
     * The pagination object
     *
     * @var    Pagination
     * @since  4.0.0
     */
    protected Pagination $pagination;

    /**
     * The model state
     *
     * @var    Registry
     * @since  4.0.0
     */
    /**
     * The model state
     *
     * @var  object
     *
     * @since 4.0.0
     */
    protected CMSObject $state;

    /**
     * Add the page title and toolbar.
     *
     * @return void
     *
     * @since  4.0.0
     * @throws Exception
     */
    protected function addToolbar()
    {
        $canDo = JedHelper::getActions();

        ToolbarHelper::title(Text::_('COM_JED_TITLE_MESSAGETEMPLATES'), "generic");

        $toolbar = Toolbar::getInstance();


        if ($canDo->get('core.create')) {
            $toolbar->addNew('messagetemplate.add');
        }


        if ($canDo->get('core.edit.state')) {
            $dropdown = $toolbar->dropdownButton('status-group')
                ->text('JTOOLBAR_CHANGE_STATUS')
                ->toggleSplit(false)
                ->icon('fas fa-ellipsis-h')
                ->buttonClass('btn btn-action')
                ->listCheck(true);

            $childBar = $dropdown->getChildToolbar();

            if (isset($this->items[0]->state)) {
                $childBar->publish('messagetemplates.publish')->listCheck(true);
                $childBar->unpublish('messagetemplates.unpublish')->listCheck(true);
                $childBar->archive('messagetemplates.archive')->listCheck(true);
            } elseif (isset($this->items[0])) {
                // If this component does not use state then show a direct delete button as we can not trash
                $toolbar->delete('messagetemplates.delete')
                    ->text('JTOOLBAR_EMPTY_TRASH')
                    ->message('JGLOBAL_CONFIRM_DELETE')
                    ->listCheck(true);
            }

            if (isset($this->items[0]->checked_out)) {
                $childBar->checkin('messagetemplates.checkin')->listCheck(true);
            }

            if (isset($this->items[0]->state)) {
                $childBar->trash('messagetemplates.trash')->listCheck(true);
            }
        }


        // Show trash and delete for components that uses the state field
        if (isset($this->items[0]->state)) {
            if ($this->state->get('filter.state') == ContentComponent::CONDITION_TRASHED && $canDo->get('core.delete')) {
                $toolbar->delete('messagetemplates.delete')
                    ->text('JTOOLBAR_EMPTY_TRASH')
                    ->message('JGLOBAL_CONFIRM_DELETE')
                    ->listCheck(true);
            }
        }
        JedHelper::addConfigToolbar($toolbar);
        if ($canDo->get('core.admin')) {
            $toolbar->preferences('com_jed');
        }

        // Set sidebar action
        Sidebar::setAction('index.php?option=com_jed&view=messagetemplates');
    }

    /**
     * Execute and display a template script.
     *
     * @param   string  $tpl  Template name
     *
     * @return void
     *
     * @since 4.0.0
     * @throws Exception
     *
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
            throw new Exception(implode("\n", $errors));
        }

        $this->addToolbar();

        parent::display($tpl);
    }

    /**
     * Check if state is set
     *
     * @param   mixed  $state  State
     *
     * @return bool
     *
     * @since 4.0.0
     */
    public function getState($state): bool
    {
        return $this->state->{$state} ?? false;
    }
}
