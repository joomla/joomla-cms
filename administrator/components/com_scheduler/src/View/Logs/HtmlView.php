<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_scheduler
 *
 * @copyright   (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Scheduler\Administrator\View\Logs;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Pagination\Pagination;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\Registry\Registry;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * View class for a list of logs.
 *
 * @since  __DEPLOY_VERSION__
 */
class HtmlView extends BaseHtmlView
{
    /**
     * The search tools form
     *
     * @var    Form
     * @since  __DEPLOY_VERSION__
     */
    public $filterForm;

    /**
     * The active search filters
     *
     * @var    array
     * @since  __DEPLOY_VERSION__
     */
    public $activeFilters = [];

    /**
     * An array of items
     *
     * @var    array
     * @since  __DEPLOY_VERSION__
     */
    protected $items = [];

    /**
     * The pagination object
     *
     * @var    Pagination
     * @since  __DEPLOY_VERSION__
     */
    protected $pagination;

    /**
     * The model state
     *
     * @var    Registry
     * @since  __DEPLOY_VERSION__
     */
    protected $state;

    /**
     * Method to display the view.
     *
     * @param   string  $tpl  A template file to load. [optional]
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     * @throws  \Exception
     */
    public function display($tpl = null): void
    {
        /** @var LogsModel $model */
        $model               = $this->getModel();
        $this->items         = $model->getItems();
        $this->pagination    = $model->getPagination();
        $this->state         = $model->getState();
        $this->filterForm    = $model->getFilterForm();
        $this->activeFilters = $model->getActiveFilters();

        // Check for errors.
        if (\count($errors = $this->get('Errors'))) {
            throw new GenericDataException(implode("\n", $errors), 500);
        }

        $this->addToolbar();
        parent::display($tpl);
    }

    /**
     * Add the page title and toolbar.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function addToolbar(): void
    {
        $canDo   = ContentHelper::getActions('com_scheduler');
        $user    = Factory::getApplication()->getIdentity();
        $toolbar = Toolbar::getInstance();

        ToolbarHelper::title(Text::_('COM_SCHEDULER_FIELDSET_EXEC_HIST'), 'list');

        $arrow   = Factory::getApplication()->getLanguage()->isRtl() ? 'arrow-right' : 'arrow-left';
        $toolbar->link('JTOOLBAR_BACK', 'index.php?option=com_scheduler')
            ->icon('icon-' . $arrow);

        if ($canDo->get('core.delete') && \count($this->items)) {
            $toolbar->delete('logs.delete')
                ->message('JGLOBAL_CONFIRM_DELETE')
                ->listCheck(true);

            $toolbar->confirmButton('trash', 'COM_SCHEDULER_TOOLBAR_PURGE', 'logs.purge')
                ->message('COM_SCHEDULER_TOOLBAR_PURGE_CONFIRM')
                ->listCheck(false);
        }

        // Link to component preferences if user has admin privileges
        if ($canDo->get('core.admin') || $canDo->get('core.options')) {
            $toolbar->preferences('com_scheduler');
        }

        $toolbar->help('Scheduled_Tasks');
    }
}
