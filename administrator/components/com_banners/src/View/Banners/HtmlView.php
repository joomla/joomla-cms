<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_banners
 *
 * @copyright   (C) 2008 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Banners\Administrator\View\Banners;

use Joomla\CMS\Form\Form;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Pagination\Pagination;
use Joomla\CMS\Toolbar\Button\DropdownButton;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\Component\Banners\Administrator\Model\BannersModel;
use Joomla\Registry\Registry;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * View class for a list of banners.
 *
 * @since  1.6
 */
class HtmlView extends BaseHtmlView
{
    /**
     * The search tools form
     *
     * @var    Form
     * @since  1.6
     */
    public $filterForm;

    /**
     * The active search filters
     *
     * @var    array
     * @since  1.6
     */
    public $activeFilters = [];

    /**
     * Category data
     *
     * @var    array
     * @since  1.6
     */
    protected $categories = [];

    /**
     * An array of items
     *
     * @var    array
     * @since  1.6
     */
    protected $items = [];

    /**
     * The pagination object
     *
     * @var    Pagination
     * @since  1.6
     */
    protected $pagination;

    /**
     * The model state
     *
     * @var    Registry
     * @since  1.6
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
     * Method to display the view.
     *
     * @param   string  $tpl  A template file to load. [optional]
     *
     * @return  void
     *
     * @since   1.6
     * @throws  \Exception
     */
    public function display($tpl = null): void
    {
        /** @var BannersModel $model */
        $model               = $this->getModel();
        $this->categories    = $model->getCategoryOrders();
        $this->items         = $model->getItems();
        $this->pagination    = $model->getPagination();
        $this->state         = $model->getState();
        $this->filterForm    = $model->getFilterForm();
        $this->activeFilters = $model->getActiveFilters();

        $this->isEmptyState = $model->getIsEmptyState();
        if (!\count($this->items) && $this->isEmptyState) {
            $this->setLayout('emptystate');
        }

        // Check for errors.
        // @todo: 6.0 - Update Error handling
        $errors = $model->getErrors();
        if (\count($errors)) {
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
     * @return  void
     *
     * @since   1.6
     */
    protected function addToolbar(): void
    {
        $canDo   = ContentHelper::getActions('com_banners', 'category', $this->state->get('filter.category_id'));
        $user    = $this->getCurrentUser();
        $toolbar = $this->getDocument()->getToolbar();

        ToolbarHelper::title(Text::_('COM_BANNERS_MANAGER_BANNERS'), 'bookmark banners');

        if ($canDo->get('core.create') || \count($user->getAuthorisedCategories('com_banners', 'core.create')) > 0) {
            $toolbar->addNew('banner.add');
        }

        if (!$this->isEmptyState && ($canDo->get('core.edit.state') || ($this->state->get('filter.published') == -2 && $canDo->get('core.delete')))) {
            /** @var  DropdownButton $dropdown */
            $dropdown = $toolbar->dropdownButton('status-group', 'JTOOLBAR_CHANGE_STATUS')
                ->toggleSplit(false)
                ->icon('icon-ellipsis-h')
                ->buttonClass('btn btn-action')
                ->listCheck(true);

            $childBar = $dropdown->getChildToolbar();

            if ($canDo->get('core.edit.state')) {
                if ($this->state->get('filter.published') != 2) {
                    $childBar->publish('banners.publish')->listCheck(true);

                    $childBar->unpublish('banners.unpublish')->listCheck(true);
                }

                if ($this->state->get('filter.published') != -1) {
                    if ($this->state->get('filter.published') != 2) {
                        $childBar->archive('banners.archive')->listCheck(true);
                    } elseif ($this->state->get('filter.published') == 2) {
                        $childBar->publish('publish')->task('banners.publish')->listCheck(true);
                    }
                }

                $childBar->checkin('banners.checkin');

                if ($this->state->get('filter.published') != -2) {
                    $childBar->trash('banners.trash')->listCheck(true);
                }
            }

            if ($this->state->get('filter.published') == -2 && $canDo->get('core.delete')) {
                $toolbar->delete('banners.delete', 'JTOOLBAR_DELETE_FROM_TRASH')
                    ->message('JGLOBAL_CONFIRM_DELETE')
                    ->listCheck(true);
            }

            // Add a batch button
            if (
                $user->authorise('core.create', 'com_banners')
                && $user->authorise('core.edit', 'com_banners')
                && $user->authorise('core.edit.state', 'com_banners')
            ) {
                $childBar->popupButton('batch', 'JTOOLBAR_BATCH')
                    ->popupType('inline')
                    ->textHeader(Text::_('COM_BANNERS_BATCH_OPTIONS'))
                    ->url('#joomla-dialog-batch')
                    ->modalWidth('800px')
                    ->modalHeight('fit-content')
                    ->listCheck(true);
            }
        }

        if ($user->authorise('core.admin', 'com_banners') || $user->authorise('core.options', 'com_banners')) {
            $toolbar->preferences('com_banners');
        }

        $toolbar->help('Banners');
    }
}
