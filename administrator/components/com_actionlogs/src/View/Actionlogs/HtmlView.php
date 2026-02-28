<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_actionlogs
 *
 * @copyright   (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Actionlogs\Administrator\View\Actionlogs;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\ListView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\Component\Actionlogs\Administrator\Helper\ActionlogsHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * View class for a list of logs.
 *
 * @since  3.9.0
 */
class HtmlView extends ListView
{
    /**
     * Setting if the IP column should be shown
     *
     * @var    boolean
     * @since  3.9.0
     */
    protected $showIpColumn = false;

    /**
     * Setting if the date should be displayed relative to the current date.
     *
     * @var    boolean
     * @since  4.1.0
     */
    protected $dateRelative = false;

    /**
     * Constructor
     *
     * @param   array  $config  An optional associative array of configuration settings.
     *
     * @since 6.0.0
     */
    public function __construct(array $config)
    {
        if (empty($config['option'])) {
            $config['option'] = 'com_actionlogs';
        }

        $config['toolbar_title'] = 'COM_ACTIONLOGS_MANAGER_USERLOGS';
        $config['toolbar_icon']  = 'list-2 actionlog';

        parent::__construct($config);
    }

    /**
     * Prepare view data
     *
     * @return  void
     *
     * @since 6.0.0
     */
    protected function initializeView()
    {
        parent::initializeView();

        $params              = ComponentHelper::getParams('com_actionlogs');
        $this->showIpColumn  = (bool) $params->get('ip_logging', 0);
        $this->dateRelative  = (bool) $params->get('date_relative', 1);

        // Load all actionlog plugins language files
        ActionlogsHelper::loadActionLogPluginsLanguage();
    }

    /**
     * Add the page title and toolbar.
     *
     * @return  void
     *
     * @since   3.9.0
     */
    protected function addToolbar()
    {
        ToolbarHelper::title(Text::_('COM_ACTIONLOGS_MANAGER_USERLOGS'), 'icon-list-2');

        $toolbar = $this->getDocument()->getToolbar();

        $toolbar->standardButton('download', 'COM_ACTIONLOGS_EXPORT_CSV', 'actionlogs.exportSelectedLogs')
            ->icon('icon-download')
            ->listCheck(true);

        $toolbar->standardButton('download', 'COM_ACTIONLOGS_EXPORT_ALL_CSV', 'actionlogs.exportLogs')
            ->icon('icon-download')
            ->listCheck(false);

        $toolbar->delete('actionlogs.delete')
            ->message('JGLOBAL_CONFIRM_DELETE');

        $toolbar->confirmButton('delete', 'COM_ACTIONLOGS_TOOLBAR_PURGE', 'actionlogs.purge')
            ->message('COM_ACTIONLOGS_PURGE_CONFIRM')
            ->listCheck(false);

        $toolbar->preferences('com_actionlogs');
        $toolbar->help('User_Actions_Log');
    }
}
