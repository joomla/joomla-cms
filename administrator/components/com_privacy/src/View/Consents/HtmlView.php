<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_privacy
 *
 * @copyright   (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Privacy\Administrator\View\Consents;

use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\Pagination\Pagination;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\Component\Privacy\Administrator\Model\ConsentsModel;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Consents view class
 *
 * @since  3.9.0
 */
class HtmlView extends BaseHtmlView
{
    /**
     * The active search tools filters
     *
     * @var    array
     * @since  3.9.0
     * @note   Must be public to be accessed from the search tools layout
     */
    public $activeFilters;

    /**
     * Form instance containing the search tools filter form
     *
     * @var    Form
     * @since  3.9.0
     * @note   Must be public to be accessed from the search tools layout
     */
    public $filterForm;

    /**
     * The items to display
     *
     * @var    array
     * @since  3.9.0
     */
    protected $items;

    /**
     * The pagination object
     *
     * @var    Pagination
     * @since  3.9.0
     */
    protected $pagination;

    /**
     * The state information
     *
     * @var    CMSObject
     * @since  3.9.0
     */
    protected $state;

    /**
     * Is this view an Empty State
     *
     * @var  boolean
     * @since 4.0.0
     */
    private $isEmptyState = false;

    /**
     * Execute and display a template script.
     *
     * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
     *
     * @return  void
     *
     * @see     BaseHtmlView::loadTemplate()
     * @since   3.9.0
     * @throws  \Exception
     */
    public function display($tpl = null)
    {
        /** @var ConsentsModel $model */
        $model               = $this->getModel();
        $this->items         = $model->getItems();
        $this->pagination    = $model->getPagination();
        $this->state         = $model->getState();
        $this->filterForm    = $model->getFilterForm();
        $this->activeFilters = $model->getActiveFilters();

        if (!count($this->items) && $this->isEmptyState = $this->get('IsEmptyState')) {
            $this->setLayout('emptystate');
        }

        // Check for errors.
        if (count($errors = $this->get('Errors'))) {
            throw new Genericdataexception(implode("\n", $errors), 500);
        }

        $this->addToolbar();

        parent::display($tpl);
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
        ToolbarHelper::title(Text::_('COM_PRIVACY_VIEW_CONSENTS'), 'lock');

        $bar = Toolbar::getInstance('toolbar');

        // Add a button to invalidate a consent
        if (!$this->isEmptyState) {
            $bar->appendButton(
                'Confirm',
                'COM_PRIVACY_CONSENTS_TOOLBAR_INVALIDATE_CONFIRM_MSG',
                'trash',
                'COM_PRIVACY_CONSENTS_TOOLBAR_INVALIDATE',
                'consents.invalidate',
                true
            );
        }

        // If the filter is restricted to a specific subject, show the "Invalidate all" button
        if ($this->state->get('filter.subject') != '') {
            $bar->appendButton(
                'Confirm',
                'COM_PRIVACY_CONSENTS_TOOLBAR_INVALIDATE_ALL_CONFIRM_MSG',
                'cancel',
                'COM_PRIVACY_CONSENTS_TOOLBAR_INVALIDATE_ALL',
                'consents.invalidateAll',
                false
            );
        }

        ToolbarHelper::preferences('com_privacy');

        ToolbarHelper::help('Privacy:_Consents');
    }
}
