<?php

/**
 * @package    JED
 *
 * @copyright  (C) 2022 Open Source Matters, Inc.  <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Jed\Component\Jed\Administrator\View\Copyjed3data;

// No direct access
// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use Exception;
use Jed\Component\Jed\Administrator\Helper\JedHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\Registry\Registry;
use SimpleXMLElement;

/**
 * View class for a single Copyjed3data.
 *
 * @since  4.0.0
 */
class HtmlView extends BaseHtmlView
{
    /**
     * The item object
     *
     * @var    object
     * @since  4.0.0
     */
    protected mixed $item;


    /**
     * The model state
     *
     * @var    CMSObject
     * @since  4.0.0
     */
    protected CMSObject $state;

    /**
     * Component Parameters
     *
     * @var    Registry
     * @since  4.0.0
     */
    protected mixed $params = null;

    /**
     * Migration SQL
     *
     * @var    SimpleXMLElement
     * @since  4.0.0
     */
    protected SimpleXMLElement $migrate_xml;

    /**
     * Action Task
     *
     * @var    string
     * @since  4.0.0
     */
    protected string $task;

    /**
     * Add the page title and toolbar.
     *
     * @since  4.0.0
     * @throws Exception
     */
    private function addToolbar(): void
    {
        ToolBarHelper::title(Text::_('COM_JED_TITLE_COPY_JED3_DATA'));

        $user = Factory::getApplication()->getIdentity();

        if (
            $user->authorise('core.admin', 'com_jed')
            || $user->authorise(
                'core.options',
                'com_jed'
            )
        ) {
            $bar = Toolbar::getInstance();

            JedHelper::addConfigToolbar($bar);


            ToolbarHelper::preferences('com_jed');
        }
    }

    /**
     * Execute and display a template script.
     *
     * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
     *
     * @return  void
     *
     * @since  4.0.0
     * @throws  Exception
     */
    public function display($tpl = null): void
    {
        $this->state       = $this->get('State');
        $this->item        = $this->get('Item');
        $this->params      = ComponentHelper::getParams('com_jed');
        $this->migrate_xml = $this->getMigrateXML();
        $app               = Factory::getApplication();
        $input             = $app->input->getInputForRequestMethod();
        $this->task        = $input->get('task', '');

        $this->addToolbar();


        parent::display($tpl);
    }

    /**
     * Loads a list of SQL to migrate from JED3 to JED 4
     * @return SimpleXMLElement
     *
     * @since 4.0.0
     */
    public function getMigrateXML(): SimpleXMLElement
    {
        $completeurl = JPATH_ADMINISTRATOR . '/components/com_jed/sql/migrate_jed3.xml';

        return simplexml_load_file($completeurl);
    }
}
