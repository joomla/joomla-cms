<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_privacy
 *
 * @copyright   (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Privacy\Administrator\View\Capabilities;

use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\Toolbar\ToolbarHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Capabilities view class
 *
 * @since  3.9.0
 */
class HtmlView extends BaseHtmlView
{
    /**
     * The reported extension capabilities
     *
     * @var    array
     * @since  3.9.0
     */
    protected $capabilities;

    /**
     * The state information
     *
     * @var    CMSObject
     * @since  3.9.0
     */
    protected $state;

    /**
     * Execute and display a template script.
     *
     * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
     *
     * @return  void
     *
     * @see     BaseHtmlView::loadTemplate()
     * @since   3.9.0
     * @throws  \Exception
     */
    public function display($tpl = null)
    {
        // Initialise variables
        $this->capabilities = $this->get('Capabilities');
        $this->state        = $this->get('State');

        // Check for errors.
        if (count($errors = $this->get('Errors'))) {
            throw new Genericdataexception(implode("\n", $errors), 500);
        }

        $this->addToolbar();

        parent::display($tpl);
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
        ToolbarHelper::title(Text::_('COM_PRIVACY_VIEW_CAPABILITIES'), 'lock');

        ToolbarHelper::preferences('com_privacy');

        ToolbarHelper::help('Privacy:_Extension_Capabilities');
    }
}
