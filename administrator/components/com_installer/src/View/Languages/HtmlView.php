<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_installer
 *
 * @copyright   (C) 2012 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Installer\Administrator\View\Languages;

use Joomla\CMS\Access\Exception\NotAllowed;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Language\LanguageHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\Component\Installer\Administrator\View\Installer\HtmlView as InstallerViewDefault;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Extension Manager Language Install View
 *
 * @since  2.5.7
 */
class HtmlView extends InstallerViewDefault
{
    /**
     * @var object item list
     */
    protected $items;

    /**
     * @var object pagination information
     */
    protected $pagination;

    /**
     * Display the view.
     *
     * @param   string  $tpl  template to display
     *
     * @return  void
     */
    public function display($tpl = null)
    {
        if (!$this->getCurrentUser()->authorise('core.admin')) {
            throw new NotAllowed(Text::_('JERROR_ALERTNOAUTHOR'), 403);
        }

        // Get data from the model.
        $this->items         = $this->get('Items');
        $this->pagination    = $this->get('Pagination');
        $this->filterForm    = $this->get('FilterForm');
        $this->activeFilters = $this->get('ActiveFilters');
        $this->installedLang = LanguageHelper::getInstalledLanguages();

        // Check for errors.
        if (count($errors = $this->get('Errors'))) {
            throw new GenericDataException(implode("\n", $errors), 500);
        }

        parent::display($tpl);
    }

    /**
     * Add the page title and toolbar.
     *
     * @return void
     */
    protected function addToolbar()
    {
        $canDo   = ContentHelper::getActions('com_installer');
        $toolbar = Toolbar::getInstance();

        ToolbarHelper::title(Text::_('COM_INSTALLER_HEADER_' . $this->getName()), 'puzzle-piece install');

        if ($canDo->get('core.admin')) {
            parent::addToolbar();

            $toolbar->help('Extensions:_Languages');
        }
    }
}
