<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_installer
 *
 * @copyright   (C) 2014 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Installer\Administrator\View\Updatesites;

use Joomla\CMS\Form\Form;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\Pagination\Pagination;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\Component\Installer\Administrator\Model\UpdatesitesModel;
use Joomla\Component\Installer\Administrator\View\Installer\HtmlView as InstallerViewDefault;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Extension Manager Update Sites View
 *
 * @since  3.4
 */
class HtmlView extends InstallerViewDefault
{
    /**
     * The search tools form
     *
     * @var    Form
     * @since  3.4
     */
    public $filterForm;

    /**
     * The active search filters
     *
     * @var    array
     * @since  3.4
     */
    public $activeFilters = [];

    /**
     * List of updatesites
     *
     * @var    \stdClass[]
     * @since 3.4
     */
    protected $items;

    /**
     * Pagination object
     *
     * @var    Pagination
     * @since 3.4
     */
    protected $pagination;

    /**
     * Display the view
     *
     * @param   string  $tpl  Template
     *
     * @return  mixed|void
     *
     * @since   3.4
     *
     * @throws  \Exception on errors
     */
    public function display($tpl = null): void
    {
        /** @var UpdatesitesModel $model */
        $model               = $this->getModel();
        $this->items         = $model->getItems();
        $this->pagination    = $model->getPagination();
        $this->filterForm    = $model->getFilterForm();
        $this->activeFilters = $model->getActiveFilters();

        // Check for errors.
        if (count($errors = $model->getErrors())) {
            throw new GenericDataException(implode("\n", $errors), 500);
        }

        // Display the view
        parent::display($tpl);
    }

    /**
     * Add the page title and toolbar.
     *
     * @return  void
     *
     * @since   3.4
     */
    protected function addToolbar(): void
    {
        $canDo = ContentHelper::getActions('com_installer');

        // Get the toolbar object instance
        $toolbar = Toolbar::getInstance('toolbar');

        if ($canDo->get('core.edit.state')) {
            $dropdown = $toolbar->dropdownButton('status-group')
                ->text('JTOOLBAR_CHANGE_STATUS')
                ->toggleSplit(false)
                ->icon('icon-ellipsis-h')
                ->buttonClass('btn btn-action')
                ->listCheck(true);

            $childBar = $dropdown->getChildToolbar();

            $childBar->publish('updatesites.publish', 'JTOOLBAR_ENABLE')->listCheck(true);
            $childBar->unpublish('updatesites.unpublish', 'JTOOLBAR_DISABLE')->listCheck(true);

            if ($canDo->get('core.delete')) {
                $childBar->delete('updatesites.delete')->listCheck(true);
            }

            $childBar->checkin('updatesites.checkin')->listCheck(true);
        }

        if ($canDo->get('core.admin') || $canDo->get('core.options')) {
            ToolbarHelper::custom('updatesites.rebuild', 'refresh', '', 'JTOOLBAR_REBUILD', false);
        }

        parent::addToolbar();

        ToolbarHelper::help('Extensions:_Update_Sites');
    }
}
