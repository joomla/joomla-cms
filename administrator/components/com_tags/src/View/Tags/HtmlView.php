<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_tags
 *
 * @copyright   (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Tags\Administrator\View\Tags;

use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\Button\DropdownButton;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\Component\Tags\Administrator\Model\TagsModel;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Tags view class for the Tags package.
 *
 * @since  3.1
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
     * @var   \Joomla\Registry\Registry
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
     *
     * @since  4.0.0
     */
    public $activeFilters;

    /**
     * Is this view an Empty State
     *
     * @var  boolean
     *
     * @since 4.0.0
     */
    private $isEmptyState = false;

    /**
     * The ordering list for the tags
     *
     * @var    array
     * @since  4.4.0
     */
    protected $ordering = [];

    /**
     * Execute and display a template script.
     *
     * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
     *
     * @return  void
     */
    public function display($tpl = null)
    {
        /** @var TagsModel $model */
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

        // Preprocess the list of items to find ordering divisions.
        foreach ($this->items as &$item) {
            $this->ordering[$item->parent_id][] = $item->id;
        }

        // We don't need toolbar in the modal window.
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
     * Add the page title and toolbar.
     *
     * @return void
     *
     * @since   3.1
     */
    protected function addToolbar()
    {
        $canDo   = ContentHelper::getActions('com_tags');
        $user    = $this->getCurrentUser();
        $toolbar = $this->getDocument()->getToolbar();

        ToolbarHelper::title(Text::_('COM_TAGS_MANAGER_TAGS'), 'tags');

        if ($canDo->get('core.create')) {
            $toolbar->addNew('tag.add');
        }

        if (!$this->isEmptyState && ($canDo->get('core.edit.state') || $user->authorise('core.admin'))) {
            /** @var DropdownButton $dropdown */
            $dropdown = $toolbar->dropdownButton('status-group', 'JTOOLBAR_CHANGE_STATUS')
                ->toggleSplit(false)
                ->icon('icon-ellipsis-h')
                ->buttonClass('btn btn-action')
                ->listCheck(true);

            $childBar = $dropdown->getChildToolbar();

            if ($canDo->get('core.edit.state')) {
                $childBar->publish('tags.publish')->listCheck(true);
                $childBar->unpublish('tags.unpublish')->listCheck(true);
                $childBar->archive('tags.archive')->listCheck(true);
            }

            if ($user->authorise('core.admin')) {
                $childBar->checkin('tags.checkin')->listCheck(true);
            }

            if ($canDo->get('core.edit.state') && $this->state->get('filter.published') != -2) {
                $childBar->trash('tags.trash')->listCheck(true);
            }

            // Add a batch button
            if ($canDo->get('core.create') && $canDo->get('core.edit') && $canDo->get('core.edit.state')) {
                $childBar->popupButton('batch', 'JTOOLBAR_BATCH')
                    ->popupType('inline')
                    ->textHeader(Text::_('COM_TAGS_BATCH_OPTIONS'))
                    ->url('#joomla-dialog-batch')
                    ->modalWidth('800px')
                    ->modalHeight('fit-content')
                    ->listCheck(true);
            }
        }

        if ($this->getCurrentUser()->authorise('core.admin')) {
            $toolbar->standardButton('refresh')
                ->text('JTOOLBAR_REBUILD')
                ->task('tags.rebuild');
        }

        if (!$this->isEmptyState && $this->state->get('filter.published') == -2 && $canDo->get('core.delete')) {
            $toolbar->delete('tags.delete', 'JTOOLBAR_DELETE_FROM_TRASH')
                ->message('JGLOBAL_CONFIRM_DELETE')
                ->listCheck(true);
        }

        if ($canDo->get('core.admin') || $canDo->get('core.options')) {
            $toolbar->preferences('com_tags');
        }

        $toolbar->help('Tags');
    }
}
