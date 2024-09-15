<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_installer
 *
 * @copyright   (C) 2011 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Installer\Administrator\View\Database;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Pagination\Pagination;
use Joomla\Component\Installer\Administrator\Model\DatabaseModel;
use Joomla\Component\Installer\Administrator\View\Installer\HtmlView as InstallerViewDefault;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Extension Manager Database View
 *
 * @since  1.6
 */
class HtmlView extends InstallerViewDefault
{
    /**
     * List of change sets
     *
     * @var    array
     * @since  4.0.0
     */
    protected $changeSet = [];

    /**
     * The number of errors found
     *
     * @var    integer
     * @since  4.0.0
     */
    protected $errorCount = 0;

    /**
     * List pagination.
     *
     * @var    Pagination
     * @since  4.0.0
     */
    protected $pagination;

    /**
     * The filter form
     *
     * @var    Form
     * @since  4.0.0
     */
    public $filterForm;

    /**
     * A list of form filters
     *
     * @var    array
     * @since  4.0.0
     */
    public $activeFilters = [];

    /**
     * Display the view.
     *
     * @param   string  $tpl  Template
     *
     * @return  void
     *
     * @throws  \Exception
     *
     * @since   1.6
     */
    public function display($tpl = null)
    {
        // Get the application
        $app = Factory::getApplication();

        // Get data from the model.
        /** @var DatabaseModel $model */
        $model = $this->getModel();

        try {
            $this->changeSet = $model->getItems();
        } catch (\Exception $exception) {
            $app->enqueueMessage($exception->getMessage(), 'error');
        }

        $this->errorCount    = $model->getErrorCount();
        $this->pagination    = $model->getPagination();
        $this->filterForm    = $model->getFilterForm();
        $this->activeFilters = $model->getActiveFilters();

        if ($this->changeSet) {
            ($this->errorCount === 0)
            ? $app->enqueueMessage(Text::_('COM_INSTALLER_MSG_DATABASE_CORE_OK'), 'info')
            : $app->enqueueMessage(Text::_('COM_INSTALLER_MSG_DATABASE_CORE_ERRORS'), 'warning');
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
    protected function addToolbar()
    {
        $toolbar = $this->getDocument()->getToolbar();

        $toolbar->standardButton('fix', 'COM_INSTALLER_TOOLBAR_DATABASE_FIX', 'database.fix')
            ->listCheck(true)
            ->icon('icon-refresh');
        $toolbar->divider();

        parent::addToolbar();

        $toolbar->help('Information:_Database');
    }
}
