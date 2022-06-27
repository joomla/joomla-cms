<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_installer
 *
 * @copyright   (C) 2008 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Installer\Administrator\View\Discover;

use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\Component\Installer\Administrator\View\Installer\HtmlView as InstallerViewDefault;

/**
 * Extension Manager Discover View
 *
 * @since  1.6
 */
class HtmlView extends InstallerViewDefault
{
    /**
     * Is this view an Empty State
     *
     * @var  boolean
     * @since 4.0.0
     */
    private $isEmptyState = false;

    /**
     * Display the view.
     *
     * @param   string  $tpl  Template
     *
     * @return  void
     *
     * @since   1.6
     */
    public function display($tpl = null)
    {
        // Run discover from the model.
        if (!$this->getModel()->checkExtensions()) {
            $this->getModel()->discover();
        }

        // Get data from the model.
        $this->items         = $this->get('Items');
        $this->pagination    = $this->get('Pagination');
        $this->filterForm    = $this->get('FilterForm');
        $this->activeFilters = $this->get('ActiveFilters');

        if (!count($this->items) && $this->isEmptyState = $this->get('IsEmptyState')) {
            $this->setLayout('emptystate');
        }

        // Check for errors.
        if (count($errors = $this->get('Errors'))) {
            throw new GenericDataException(implode("\n", $errors), 500);
        }

        parent::display($tpl);
    }

    /**
     * Add the page title and toolbar.
     *
     * @return  void
     *
     * @since   3.1
     */
    protected function addToolbar()
    {
        /*
         * Set toolbar items for the page.
         */
        if (!$this->isEmptyState) {
            ToolbarHelper::custom('discover.install', 'upload', '', 'JTOOLBAR_INSTALL', true);
        }

        ToolbarHelper::custom('discover.refresh', 'refresh', '', 'COM_INSTALLER_TOOLBAR_DISCOVER', false);
        ToolbarHelper::divider();

        parent::addToolbar();

        ToolbarHelper::help('Extensions:_Discover');
    }
}
