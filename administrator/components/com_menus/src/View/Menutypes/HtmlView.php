<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_menus
 *
 * @copyright   (C) 2011 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Menus\Administrator\View\Menutypes;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * The HTML Menus Menu Item Types View.
 *
 * @since  1.6
 */
class HtmlView extends BaseHtmlView
{
    /**
     * The menu type id
     *
     * @var    integer
     * @since  4.0.0
     */
    protected $recordId;

    /**
     * Array of menu types
     *
     * @var    CMSObject[]
     *
     * @since  3.7.0
     */
    protected $types;

    /**
     * Display the view
     *
     * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
     *
     * @return  void
     *
     * @since   1.6
     */
    public function display($tpl = null)
    {
        $app            = Factory::getApplication();
        $this->recordId = $app->getInput()->getInt('recordId');

        $types = $this->get('TypeOptions');

        $this->addCustomTypes($types);

        $sortedTypes = [];

        foreach ($types as $name => $list) {
            $tmp = [];

            foreach ($list as $item) {
                $tmp[Text::_($item->title)] = $item;
            }

            uksort($tmp, 'strcasecmp');
            $sortedTypes[Text::_($name)] = $tmp;
        }

        uksort($sortedTypes, 'strcasecmp');

        $this->types = $sortedTypes;

        $this->addToolbar();

        parent::display($tpl);
    }

    /**
     * Add the page title and toolbar.
     *
     * @return  void
     *
     * @since   3.0
     */
    protected function addToolbar()
    {
        // Add page title
        ToolbarHelper::title(Text::_('COM_MENUS'), 'list menumgr');

        $toolbar = Toolbar::getInstance();

        // Cancel
        $title = Text::_('JTOOLBAR_CANCEL');
        $dhtml = "<button onClick=\"location.href='index.php?option=com_menus&view=items'\" class=\"btn\">
					<span class=\"icon-times\" title=\"$title\"></span>
					$title</button>";
        $toolbar->customButton('new')
            ->html($dhtml);
    }

    /**
     * Method to add system link types to the link types array
     *
     * @param   array  $types  The list of link types
     *
     * @return  void
     *
     * @since   3.7.0
     */
    protected function addCustomTypes(&$types)
    {
        if (empty($types)) {
            $types = [];
        }

        // Adding System Links
        $list           = [];
        $o              = new CMSObject();
        $o->title       = 'COM_MENUS_TYPE_EXTERNAL_URL';
        $o->type        = 'url';
        $o->description = 'COM_MENUS_TYPE_EXTERNAL_URL_DESC';
        $o->request     = null;
        $list[]         = $o;

        $o              = new CMSObject();
        $o->title       = 'COM_MENUS_TYPE_ALIAS';
        $o->type        = 'alias';
        $o->description = 'COM_MENUS_TYPE_ALIAS_DESC';
        $o->request     = null;
        $list[]         = $o;

        $o              = new CMSObject();
        $o->title       = 'COM_MENUS_TYPE_SEPARATOR';
        $o->type        = 'separator';
        $o->description = 'COM_MENUS_TYPE_SEPARATOR_DESC';
        $o->request     = null;
        $list[]         = $o;

        $o              = new CMSObject();
        $o->title       = 'COM_MENUS_TYPE_HEADING';
        $o->type        = 'heading';
        $o->description = 'COM_MENUS_TYPE_HEADING_DESC';
        $o->request     = null;
        $list[]         = $o;

        if ($this->get('state')->get('client_id') == 1) {
            $o              = new CMSObject();
            $o->title       = 'COM_MENUS_TYPE_CONTAINER';
            $o->type        = 'container';
            $o->description = 'COM_MENUS_TYPE_CONTAINER_DESC';
            $o->request     = null;
            $list[]         = $o;
        }

        $types['COM_MENUS_TYPE_SYSTEM'] = $list;
    }
}
