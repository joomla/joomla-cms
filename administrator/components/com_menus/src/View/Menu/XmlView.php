<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_menus
 *
 * @copyright   (C) 2005 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Menus\Administrator\View\Menu;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Menu\AdministratorMenuItem;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Router\Route;
use Joomla\Component\Menus\Administrator\Helper\MenusHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * The HTML Menus Menu Item View.
 *
 * @since  3.8.0
 */
class XmlView extends BaseHtmlView
{
    /**
     * @var  \stdClass[]
     *
     * @since  3.8.0
     */
    protected $items;

    /**
     * @var    \Joomla\CMS\Object\CMSObject
     *
     * @since  3.8.0
     */
    protected $state;

    /**
     * Display the view
     *
     * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
     *
     * @return  void
     *
     * @since   3.8.0
     */
    public function display($tpl = null)
    {
        $app      = Factory::getApplication();
        $menutype = $app->getInput()->getCmd('menutype');

        if ($menutype) {
            $root = MenusHelper::getMenuItems($menutype, true);
        }

        if (!$root->hasChildren()) {
            Log::add(Text::_('COM_MENUS_SELECT_MENU_FIRST_EXPORT'), Log::WARNING, 'jerror');

            $app->redirect(Route::_('index.php?option=com_menus&view=menus', false));

            return;
        }

        $this->items = $root->getChildren();

        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8" ?><menu ' .
            'xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" ' .
            'xmlns="urn:joomla.org"	xsi:schemaLocation="urn:joomla.org menu.xsd"' .
            '></menu>');

        foreach ($this->items as $item) {
            $this->addXmlChild($xml, $item);
        }

        if (headers_sent($file, $line)) {
            Log::add("Headers already sent at $file:$line.", Log::ERROR, 'jerror');

            return;
        }

        header('content-type: application/xml');
        header('content-disposition: attachment; filename="' . $menutype . '.xml"');
        header("Cache-Control: no-cache, must-revalidate");
        header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");

        $dom                     = new \DOMDocument();
        $dom->preserveWhiteSpace = true;
        $dom->formatOutput       = true;
        $dom->loadXML($xml->asXML());

        echo $dom->saveXML();

        $app->close();
    }

    /**
     * Add a child node to the xml
     *
     * @param   \SimpleXMLElement      $xml   The current XML node which would become the parent to the new node
     * @param   AdministratorMenuItem  $item  The menuitem object to create the child XML node from
     *
     * @return  void
     *
     * @since   3.8.0
     */
    protected function addXmlChild($xml, $item)
    {
        $node = $xml->addChild('menuitem');

        if ($item->title) {
            $node['title'] = htmlentities($item->title, ENT_XML1);
        }

        $node['type'] = $item->type;

        if ($item->element) {
            $node['element'] = $item->element;
        }

        if ($item->link) {
            $node['link'] = $item->link;
        }

        if (isset($item->class) && trim($item->class)) {
            $node['class'] = htmlentities(trim($item->class), ENT_XML1);
        }

        if ($item->access) {
            $node['access'] = $item->access;
        }

        if ($item->browserNav) {
            $node['target'] = '_blank';
        }

        if ($item->getParams()->get('ajax-badge')) {
            $node['ajax-badge'] = $item->getParams()->get('ajax-badge');
        }

        if ($item->icon) {
            $node['icon'] = $item->icon;
        }

        if ($item->getParams()->get('menu-quicktask')) {
            $node['quicktask']       = $item->getParams()->get('menu-quicktask');

            if ($item->getParams()->get('menu-quicktask-title')) {
                $node['quicktask-title'] = $item->getParams()->get('menu-quicktask-title');
            }

            if ($item->getParams()->get('menu-quicktask-icon')) {
                $node['quicktask-icon'] = $item->getParams()->get('menu-quicktask-icon');
            }

            if ($item->getParams()->get('menu-quicktask-permission')) {
                $node['quicktask-permission'] = $item->getParams()->get('menu-quicktask-permission');
            }
        }

        if ($item->getParams()->get('dashboard')) {
            $node['dashboard'] = $item->getParams()->get('dashboard');
        }

        if ($item->getParams() && $hideitems = $item->getParams()->get('hideitems')) {
            $item->getParams()->set('hideitems', $this->getModel('Menu')->getExtensionElementsForMenuItems($hideitems));

            $node->addChild('params', htmlentities((string) $item->getParams(), ENT_XML1));
        }

        if ($item->hasChildren()) {
            foreach ($item->getChildren() as $sub) {
                $this->addXmlChild($node, $sub);
            }
        }
    }
}
