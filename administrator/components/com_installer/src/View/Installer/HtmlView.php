<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_installer
 *
 * @copyright   (C) 2007 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Installer\Administrator\View\Installer;

use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Extension Manager Default View
 *
 * @since  1.5
 */
class HtmlView extends BaseHtmlView
{
    /**
     * The model state
     *
     * @var    \Joomla\CMS\Object\CMSObject
     *
     * @since  4.0.0
     */
    public $state;

    /**
     * True if there are extension messages to be displayed
     *
     * @var    boolean
     *
     * @since  4.0.0
     */
    public $showMessage;

    /**
     * Constructor.
     *
     * @param   array  $config  Configuration array
     *
     * @since   1.5
     */
    public function __construct($config = null)
    {
        parent::__construct($config);

        $this->_addPath('template', $this->_basePath . '/tmpl/installer');
        $this->_addPath('template', JPATH_THEMES . '/' . Factory::getApplication()->getTemplate() . '/html/com_installer/installer');
    }

    /**
     * Display the view.
     *
     * @param   string  $tpl  Template
     *
     * @return  void
     *
     * @since   1.5
     */
    public function display($tpl = null)
    {
        // Get data from the model.
        $state = $this->get('State');

        // Are there messages to display?
        $showMessage = false;

        if (is_object($state)) {
            $message1    = $state->get('message');
            $message2    = $state->get('extension_message');
            $showMessage = ($message1 || $message2);
        }

        $this->showMessage = $showMessage;
        $this->state       = &$state;

        $this->addToolbar();
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
        $canDo = ContentHelper::getActions('com_installer');
        ToolbarHelper::title(Text::_('COM_INSTALLER_HEADER_' . strtoupper($this->getName())), 'puzzle-piece install');

        if ($canDo->get('core.admin') || $canDo->get('core.options')) {
            ToolbarHelper::preferences('com_installer');
            ToolbarHelper::divider();
        }
    }
}
