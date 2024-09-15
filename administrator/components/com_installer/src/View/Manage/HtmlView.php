<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_installer
 *
 * @copyright   (C) 2008 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Installer\Administrator\View\Manage;

use Joomla\CMS\Form\Form;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\Pagination\Pagination;
use Joomla\Component\Installer\Administrator\View\Installer\HtmlView as InstallerViewDefault;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Extension Manager Manage View
 *
 * @since  1.6
 */
class HtmlView extends InstallerViewDefault
{
    /**
     * List of updatesites
     *
     * @var    \stdClass[]
     */
    protected $items;

    /**
     * Pagination object
     *
     * @var    Pagination
     */
    protected $pagination;

    /**
     * Form object
     *
     * @var    Form
     */
    protected $form;

    /**
     * Form object for search filters
     *
     * @var  \Joomla\CMS\Form\Form
     */
    public $filterForm;

    /**
     * The active search filters
     *
     * @var  array
     */
    public $activeFilters;

    /**
     * Display the view.
     *
     * @param   string  $tpl  Template
     *
     * @return  mixed|void
     *
     * @since   1.6
     */
    public function display($tpl = null)
    {
        // Get data from the model.
        $this->items         = $this->get('Items');
        $this->pagination    = $this->get('Pagination');
        $this->filterForm    = $this->get('FilterForm');
        $this->activeFilters = $this->get('ActiveFilters');

        // Check for errors.
        if (\count($errors = $this->get('Errors'))) {
            throw new GenericDataException(implode("\n", $errors), 500);
        }

        // Display the view.
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
        $canDo   = ContentHelper::getActions('com_installer');

        $dropdown = $toolbar->dropdownButton('status-group')
            ->text('JTOOLBAR_CHANGE_STATUS')
            ->toggleSplit(false)
            ->icon('icon-ellipsis-h')
            ->buttonClass('btn btn-action')
            ->listCheck(true);

        $childBar = $dropdown->getChildToolbar();

        if ($canDo->get('core.edit.state')) {
            $childBar->publish('manage.publish', 'JTOOLBAR_ENABLE')
                ->listCheck(true);
            $childBar->unpublish('manage.unpublish', 'JTOOLBAR_DISABLE')
                ->listCheck(true);
        }

        if ($canDo->get('core.delete')) {
            $childBar->delete('manage.remove', 'JTOOLBAR_UNINSTALL')
                ->message('COM_INSTALLER_CONFIRM_UNINSTALL')
                ->listCheck(true);
        }

        $childBar->standardButton('refresh', 'JTOOLBAR_REFRESH_CACHE', 'manage.refresh')
            ->listCheck(true);

        if ($canDo->get('core.manage')) {
            $toolbar->linkButton('upload', 'COM_INSTALLER_TOOLBAR_INSTALL_EXTENSIONS')
                ->url('index.php?option=com_installer&view=install');
            $toolbar->linkButton('refresh', 'COM_INSTALLER_TOOLBAR_FIND_UPDATES')
                ->url('index.php?option=com_installer&view=update');
            $toolbar->divider();
        }

        parent::addToolbar();
        $toolbar->help('Extensions:_Manage');
    }
}
