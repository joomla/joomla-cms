<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_redirect
 *
 * @copyright   (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Redirect\Administrator\View\Links;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Toolbar\Button\DropdownButton;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\Component\Redirect\Administrator\Helper\RedirectHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * View class for a list of redirection links.
 *
 * @since  1.6
 */
class HtmlView extends BaseHtmlView
{
    /**
     * True if "System - Redirect Plugin" is enabled
     *
     * @var  boolean
     */
    protected $enabled;

    /**
     * True if "Collect URLs" is enabled
     *
     * @var  boolean
     */
    protected $collect_urls_enabled;

    /**
     * The id of the redirect plugin in mysql
     *
     * @var    integer
     * @since  3.8.0
     */
    protected $redirectPluginId = 0;

    /**
     * An array of items
     *
     * @var  array
     */
    protected $items;

    /**
     * The pagination object
     *
     * @var    \Joomla\CMS\Pagination\Pagination
     */
    protected $pagination;

    /**
     * The model state
     *
     * @var  \Joomla\Registry\Registry
     */
    protected $state;

    /**
     * The model state
     *
     * @var  \Joomla\Registry\Registry
     */
    protected $params;

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
     * Display the view.
     *
     * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
     *
     * @return  void
     *
     * @throws  GenericDataException
     * @since   1.6
     */
    public function display($tpl = null)
    {
        // Set variables
        $this->items                = $this->get('Items');
        $this->pagination           = $this->get('Pagination');
        $this->state                = $this->get('State');
        $this->filterForm           = $this->get('FilterForm');
        $this->activeFilters        = $this->get('ActiveFilters');
        $this->params               = ComponentHelper::getParams('com_redirect');

        if (!\count($this->items) && $this->isEmptyState = $this->get('IsEmptyState')) {
            $this->setLayout('emptystate');
        }

        // Check for errors.
        if (\count($errors = $this->get('Errors'))) {
            throw new GenericDataException(implode("\n", $errors), 500);
        }

        if (!PluginHelper::isEnabled('system', 'redirect') || !RedirectHelper::collectUrlsEnabled()) {
            $this->redirectPluginId = RedirectHelper::getRedirectPluginId();
        }

        $this->addToolbar();

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
        $state   = $this->get('State');
        $canDo   = ContentHelper::getActions('com_redirect');
        $toolbar = $this->getDocument()->getToolbar();

        ToolbarHelper::title(Text::_('COM_REDIRECT_MANAGER_LINKS'), 'map-signs redirect');

        if ($canDo->get('core.create')) {
            $toolbar->addNew('link.add');
        }

        if (!$this->isEmptyState && ($canDo->get('core.edit.state') || $canDo->get('core.admin'))) {
            /** @var DropdownButton $dropdown */
            $dropdown = $toolbar->dropdownButton('status-group', 'JTOOLBAR_CHANGE_STATUS')
                ->toggleSplit(false)
                ->icon('icon-ellipsis-h')
                ->buttonClass('btn btn-action')
                ->listCheck(true);

            $childBar = $dropdown->getChildToolbar();

            if ($state->get('filter.state') != 2) {
                $childBar->publish('links.publish', 'JTOOLBAR_ENABLE')->listCheck(true);
                $childBar->unpublish('links.unpublish', 'JTOOLBAR_DISABLE')->listCheck(true);
            }

            if ($state->get('filter.state') != -1) {
                if ($state->get('filter.state') != 2) {
                    $childBar->archive('links.archive')->listCheck(true);
                } elseif ($state->get('filter.state') == 2) {
                    $childBar->unarchive('links.unpublish')->listCheck(true);
                }
            }

            if (!$state->get('filter.state') == -2) {
                $childBar->trash('links.trash')->listCheck(true);
            }
        }

        if ($state->get('filter.state') == -2 && $canDo->get('core.delete')) {
            $toolbar->delete('links.delete', 'JTOOLBAR_DELETE_FROM_TRASH')
                ->message('JGLOBAL_CONFIRM_DELETE')
                ->listCheck(true);
        }

        if (!$this->isEmptyState && (!$state->get('filter.state') == -2 && $canDo->get('core.delete'))) {
            $toolbar->confirmButton('delete', 'COM_REDIRECT_TOOLBAR_PURGE', 'links.purge')
                ->message('COM_REDIRECT_CONFIRM_PURGE');
        }

        if ($canDo->get('core.create')) {
            $toolbar->popupButton('batch', 'JTOOLBAR_BULK_IMPORT')
                ->popupType('inline')
                ->textHeader(Text::_('COM_REDIRECT_BATCH_OPTIONS'))
                ->url('#joomla-dialog-batch')
                ->modalWidth('800px')
                ->modalHeight('fit-content')
                ->listCheck(false);
        }

        if ($canDo->get('core.admin') || $canDo->get('core.options')) {
            $toolbar->preferences('com_redirect');
        }

        $toolbar->help('Redirects:_Links');
    }
}
