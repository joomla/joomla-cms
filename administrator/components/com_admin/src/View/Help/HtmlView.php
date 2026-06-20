<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_admin
 *
 * @copyright   (C) 2008 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Admin\Administrator\View\Help;

use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\Component\Admin\Administrator\Model\HelpModel;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * HTML View class for the Admin component
 *
 * @since  1.6
 */
class HtmlView extends BaseHtmlView
{
    /**
     * The search string
     *
     * @var    string
     * @since  1.6
     */
    protected $helpSearch = null;

    /**
     * The page to be viewed
     *
     * @var    string
     * @since  1.6
     */
    protected $page = null;

    /**
     * The level of each submenu
     *
     * @var    integer
     */
    protected $toclevel = 0;

    /**
     * Execute and display a template script.
     *
     * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
     *
     * @return  void
     *
     * @since   1.6
     *
     * @throws  \Exception
     */
    public function display($tpl = null): void
    {
        /** @var HelpModel $model */
        $model                    = $this->getModel();
        $this->page               = $model->getPage();

        $this->addToolbar();

        parent::display($tpl);
    }

    /**
     * Setup the Toolbar
     *
     * @return  void
     *
     * @since   1.6
     */
    protected function addToolbar(): void
    {
        ToolbarHelper::title(Text::_('COM_ADMIN_HELP'), 'support help_header');
    }

    /**
     * Method to render a given level of a menu using provided layout file
     *
     * @param   string      $layoutFile  The layout file to be used to render
     * @param   array       $menu        The menu to render the children of
     *
     * @return  void
     *
     * @since   6.2.0
     */
    public function renderSubmenu($layoutFile, $menu)
    {
        if (is_file($layoutFile)) {
            require $layoutFile;
        }
    }
}
