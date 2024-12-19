<?php

/**
 * @package       Joomla.Administrator
 * @subpackage    com_guidedtours
 *
 * @copyright     (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license       GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Guidedtours\Administrator\View\Tours;

use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * View class for a list of guidedtours.
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
        $this->items         = $this->get('Items');
        $this->pagination    = $this->get('Pagination');
        $this->state         = $this->get('State');
        $this->filterForm    = $this->get('FilterForm');
        $this->activeFilters = $this->get('ActiveFilters');

        if (!\count($this->items) && $this->isEmptyState = $this->get('IsEmptyState')) {
            $this->setLayout('emptystate');
        }

        // Check for errors.
        if (\count($errors = $this->get('Errors'))) {
            throw new GenericDataException(implode("\n", $errors), 500);
        }

        $this->addToolbar();

        // We do not need to filter by language when multilingual is disabled
        if (!Multilanguage::isEnabled()) {
            unset($this->activeFilters['language']);
            $this->filterForm->removeField('language', 'filter');
        }

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

        ToolbarHelper::title(Text::_('COM_GUIDEDTOURS_TOURS_LIST'), 'map-signs');

        $canDo = ContentHelper::getActions('com_guidedtours');
        $user  = $this->getCurrentUser();

        if ($canDo->get('core.create')) {
            $toolbar->addNew('tour.add');
        }

        if (!$this->isEmptyState && $canDo->get('core.edit.state')) {
            $dropdown = $toolbar->dropdownButton('status-group')
                ->text('JTOOLBAR_CHANGE_STATUS')
                ->toggleSplit(false)
                ->icon('icon-ellipsis-h')
                ->buttonClass('btn btn-action')
                ->listCheck(true);

            $childBar = $dropdown->getChildToolbar();

            $childBar->publish('tours.publish')->listCheck(true);

            $childBar->unpublish('tours.unpublish')->listCheck(true);

            $childBar->archive('tours.archive')->listCheck(true);

            $childBar->checkin('tours.checkin')->listCheck(true);

            if ($this->state->get('filter.published') != -2) {
                $childBar->trash('tours.trash')->listCheck(true);
            }
            if ($canDo->get('core.create')) {
                $childBar->standardButton('copy')
                    ->text('JTOOLBAR_DUPLICATE')
                    ->task('tours.duplicate')
                    ->listCheck(true);
            }
        }

        if (!$this->isEmptyState && $this->state->get('filter.published') == -2 && $canDo->get('core.delete')) {
            $toolbar->delete('tours.delete')
                ->text('JTOOLBAR_DELETE_FROM_TRASH')
                ->message('JGLOBAL_CONFIRM_DELETE')
                ->listCheck(true);
        }

        if ($user->authorise('core.admin', 'com_guidedtours') || $user->authorise('core.options', 'com_guidedtours')) {
            $toolbar->preferences('com_guidedtours');
        }

        ToolbarHelper::help('Guided_Tours');
    }
}
