<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_installer
 *
 * @copyright   (C) 2006 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Installer\Administrator\View\Install;

use Joomla\CMS\Access\Exception\NotAllowed;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\Component\Installer\Administrator\View\Installer\HtmlView as InstallerViewDefault;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Extension Manager Install View
 *
 * @since  1.5
 */
class HtmlView extends InstallerViewDefault
{
    /**
     * Display the view
     *
     * @param   string  $tpl  Template
     *
     * @return  void
     *
     * @since   1.5
     */
    public function display($tpl = null)
    {
        if (!$this->getCurrentUser()->authorise('core.admin')) {
            throw new NotAllowed(Text::_('JERROR_ALERTNOAUTHOR'), 403);
        }

        $paths        = new \stdClass();
        $paths->first = '';

        $this->paths  = &$paths;

        PluginHelper::importPlugin('installer');

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
        $toolbar = Toolbar::getInstance();

        if (ContentHelper::getActions('com_installer')->get('core.manage')) {
            $toolbar->linkButton('list', 'COM_INSTALLER_TOOLBAR_MANAGE')
                ->url('index.php?option=com_installer&view=manage');
            $toolbar->divider();
        }

        parent::addToolbar();

        $toolbar->help('Extensions:_Install');
    }
}
