<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_cache
 *
 * @copyright   (C) 2008 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Cache\Administrator\View\Cache;

use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Pagination\Pagination;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\Component\Cache\Administrator\Model\CacheModel;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * HTML View class for the Cache component
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
     * The cache data
     *
     * @var    array
     * @since  1.6
     */
    protected $data = [];

    /**
     * The pagination object
     *
     * @var    Pagination
     * @since  1.6
     */
    protected $pagination;

    /**
     * Total number of cache groups
     *
     * @var    integer
     * @since  1.6
     */
    protected $total = 0;

    /**
     * The model state
     *
     * @var    \Joomla\Registry\Registry
     * @since  1.6
     */
    protected $state;

    /**
     * Display a view.
     *
     * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
     *
     * @return  void
     *
     * @since   1.6
     *
     * @throws  GenericDataException
     */
    public function display($tpl = null): void
    {
        /** @var CacheModel $model */
        $model               = $this->getModel();
        $this->data          = $model->getData();
        $this->pagination    = $model->getPagination();
        $this->total         = $model->getTotal();
        $this->state         = $model->getState();
        $this->filterForm    = $model->getFilterForm();
        $this->activeFilters = $model->getActiveFilters();

        // Check for errors.
        if (\count($errors = $this->get('Errors'))) {
            throw new GenericDataException(implode("\n", $errors), 500);
        }

        if (!\count($this->data) && ($this->state->get('filter.search') === null || $this->state->get('filter.search') === '')) {
            $this->setLayout('emptystate');
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
    protected function addToolbar(): void
    {
        ToolbarHelper::title(Text::_('COM_CACHE_CLEAR_CACHE'), 'bolt clear');

        // Get the toolbar object instance
        $toolbar = $this->getDocument()->getToolbar();

        if (\count($this->data)) {
            $toolbar->delete('delete')
                ->listCheck(true);

            $toolbar->confirmButton('delete', 'JTOOLBAR_DELETE_ALL', 'deleteAll')
                ->icon('icon-remove')
                ->listCheck(false)
                ->buttonClass('button-remove btn btn-primary');

            $toolbar->confirmButton('delete', 'COM_CACHE_PURGE_EXPIRED', 'purge')
                ->name('delete')
                ->message('COM_CACHE_RESOURCE_INTENSIVE_WARNING');

            $toolbar->divider();
        }

        if ($this->getCurrentUser()->authorise('core.admin', 'com_cache')) {
            $toolbar->preferences('com_cache');
            $toolbar->divider();
        }

        $toolbar->help('Maintenance:_Clear_Cache');
    }
}
