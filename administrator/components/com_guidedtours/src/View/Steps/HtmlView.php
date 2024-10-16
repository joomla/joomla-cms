<?php

/**
 * @package       Joomla.Administrator
 * @subpackage    com_guidedtours
 *
 * @copyright     (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license       GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Guidedtours\Administrator\View\Steps;

use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\Component\Guidedtours\Administrator\Model\StepsModel;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * View class for a list of guidedtour_steps.
 *
 * @since 4.3.0
 */
class HtmlView extends BaseHtmlView
{
    /**
     * An array of items
     *
     * @var array
     */
    protected $items;

    /**
     * The pagination object
     *
     * @var \Joomla\CMS\Pagination\Pagination
     */
    protected $pagination;

    /**
     * The model state
     *
     * @var \Joomla\Registry\Registry
     */
    protected $state;

    /**
     * Form object for search filters
     *
     * @var \Joomla\CMS\Form\Form
     */
    public $filterForm;

    /**
     * The active search filters
     *
     * @var array
     */
    public $activeFilters;

    /**
     * Is this view an Empty State
     *
     * @var   boolean
     *
     * @since 4.3.0
     */
    private $isEmptyState = false;

    /**
     * Display the view.
     *
     * @param   string $tpl The name of the template file to parse; automatically searches through the template paths.
     *
     * @return  void
     */
    public function display($tpl = null)
    {
        /** @var StepsModel $model */
        $model = $this->getModel();

        $this->items         = $model->getItems();
        $this->pagination    = $model->getPagination();
        $this->state         = $model->getState();
        $this->filterForm    = $model->getFilterForm();
        $this->activeFilters = $model->getActiveFilters();

        if (!\count($this->items) && $this->isEmptyState = $model->getIsEmptyState()) {
            $this->setLayout('emptystate');
        }

        // Check for errors.
        if (\count($errors = $model->getErrors())) {
            throw new GenericDataException(implode("\n", $errors), 500);
        }

        if ($this->state->get('filter.tour_id', -1) < 0) {
            // This arises when you are logged out and return to the steps view after logging back in
            // We redirect back to the tour lists view
            $app = Factory::getApplication();
            $app->enqueueMessage(Text::_('COM_GUIDEDTOURS_STEPS_UNKNOWN_TOUR'), 'notice');
            $app->redirect(Route::_('index.php?option=com_guidedtours&view=tours', false), 300);
            return;
        }

        // Unset the tour_id field from activeFilters as we don't filter by tour here.
        unset($this->activeFilters['tour_id']);

        $this->addToolbar();

        parent::display($tpl);
    }

    /**
     * Add the page title and toolbar.
     *
     * @return void
     *
     * @since 4.3.0
     */
    protected function addToolbar()
    {
        // Get the toolbar object instance
        $toolbar = $this->getDocument()->getToolbar();

        $canDo = ContentHelper::getActions('com_guidedtours');
        $app   = Factory::getApplication();
        $user  = $app->getIdentity();

        /** @var \Joomla\Component\Guidedtours\Administrator\Model\TourModel $tourModel */
        $tourModel = $app->bootComponent('com_guidedtours')
            ->getMVCFactory()->createModel('Tour', 'Administrator', ['ignore_request' => true]);

        $tour  = $tourModel->getItem($this->state->get('filter.tour_id', -1));
        $title = !empty($tour->title) ? $tour->title : '';

        ToolbarHelper::title(Text::sprintf('COM_GUIDEDTOURS_STEPS_LIST', Text::_($title)), 'map-signs');
        $arrow  = $this->getLanguage()->isRtl() ? 'arrow-right' : 'arrow-left';

        ToolbarHelper::link(
            Route::_('index.php?option=com_guidedtours&view=tours'),
            'JTOOLBAR_BACK',
            $arrow
        );

        if ($canDo->get('core.create')) {
            $toolbar->addNew('step.add');
        }

        if (!$this->isEmptyState && $canDo->get('core.edit.state')) {
            $dropdown = $toolbar->dropdownButton('status-group')
                ->text('JTOOLBAR_CHANGE_STATUS')
                ->toggleSplit(false)
                ->icon('icon-ellipsis-h')
                ->buttonClass('btn btn-action')
                ->listCheck(true);

            $childBar = $dropdown->getChildToolbar();

            $childBar->publish('steps.publish')->listCheck(true);

            $childBar->unpublish('steps.unpublish')->listCheck(true);

            $childBar->archive('steps.archive')->listCheck(true);

            $childBar->checkin('steps.checkin')->listCheck(true);

            if ($this->state->get('filter.published') != -2) {
                $childBar->trash('steps.trash')->listCheck(true);
            }
        }

        if (!$this->isEmptyState && $this->state->get('filter.published') == -2 && $canDo->get('core.delete')) {
            $toolbar->delete('steps.delete')
                ->text('JTOOLBAR_DELETE_FROM_TRASH')
                ->message('JGLOBAL_CONFIRM_DELETE')
                ->listCheck(true);
        }

        if ($user->authorise('core.admin', 'com_guidedtours') || $user->authorise('core.options', 'com_guidedtours')) {
            $toolbar->preferences('com_guidedtours');
        }

        ToolbarHelper::help('Guided_Tours:_Steps');
    }

    /**
     * Returns an array of fields the table can be sorted by
     *
     * @return array  Array containing the field name to sort by as the key and display text as value
     *
     * @since 4.3.0
     */
    protected function getSortFields()
    {
        return [
            'a.id' => Text::_('JGRID_HEADING_ID'),
        ];
    }
}
