<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_scheduler
 *
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Scheduler\Administrator\View\Select;

use Joomla\CMS\Application\AdministratorApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\Component\Scheduler\Administrator\Task\TaskOption;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * The MVC View for the routine selection page (SelectView).
 * This view lets the user choose from a list of plugin defined task routines.
 *
 * @since  4.1.0
 */
class HtmlView extends BaseHtmlView
{
    /**
     * @var  AdministratorApplication
     * @since  4.1.0
     */
    protected $app;

    /**
     * The model state
     *
     * @var  CMSObject
     * @since  4.1.0
     */
    protected $state;

    /**
     * An array of items
     *
     * @var  TaskOption[]
     * @since  4.1.0
     */
    protected $items;

    /**
     * HtmlView constructor.
     *
     * @param   array  $config  A named configuration array for object construction.
     *                          name: the name (optional) of the view (defaults to the view class name suffix).
     *                          charset: the character set to use for display
     *                          escape: the name (optional) of the function to use for escaping strings
     *                          base_path: the parent path (optional) of the `views` directory (defaults to the component
     *                          folder) template_plath: the path (optional) of the layout directory (defaults to
     *                          base_path + /views/ + view name helper_path: the path (optional) of the helper files
     *                          (defaults to base_path + /helpers/) layout: the layout (optional) to use to display the
     *                          view
     *
     * @since  4.1.0
     * @throws  \Exception
     */
    public function __construct($config = [])
    {
        $this->app = Factory::getApplication();

        parent::__construct($config);
    }

    /**
     * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
     *
     * @return  void
     *
     * @since  4.1.0
     * @throws \Exception
     */
    public function display($tpl = null): void
    {
        $this->state     = $this->get('State');
        $this->items     = $this->get('Items');

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
     * @return void
     *
     * @since  4.1.0
     */
    protected function addToolbar(): void
    {
        /*
        * Get the global Toolbar instance
        * @todo : Replace usage with ToolbarFactoryInterface. but how?
        *       Probably some changes in the core, since mod_menu calls and renders the getInstance() toolbar
        */
        $toolbar = Toolbar::getInstance();

        // Add page title
        ToolbarHelper::title(Text::_('COM_SCHEDULER_MANAGER_TASKS'), 'clock');

        $toolbar->linkButton('cancel')
            ->url('index.php?option=com_scheduler')
            ->buttonClass('btn btn-danger')
            ->icon('icon-times')
            ->text(Text::_('JCANCEL'));
    }
}
