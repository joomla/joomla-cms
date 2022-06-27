<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Editors-xtd.menu
 *
 * @copyright   (C) 2016 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt

 * @phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace
 */

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Session\Session;

/**
 * Editor menu button
 *
 * @since  3.7.0
 */
class PlgButtonMenu extends CMSPlugin
{
    /**
     * Load the language file on instantiation.
     *
     * @var    boolean
     * @since  3.7.0
     */
    protected $autoloadLanguage = true;

    /**
     * Display the button
     *
     * @param   string  $name  The name of the button to add
     *
     * @since  3.7.0
     * @return CMSObject
     */
    public function onDisplay($name)
    {
        /*
         * Use the built-in element view to select the menu item.
         * Currently uses blank class.
         */
        $user  = Factory::getUser();

        if (
            $user->authorise('core.create', 'com_menus')
            || $user->authorise('core.edit', 'com_menus')
        ) {
            $link = 'index.php?option=com_menus&amp;view=items&amp;layout=modal&amp;tmpl=component&amp;'
            . Session::getFormToken() . '=1&amp;editor=' . $name;

            $button = new CMSObject();
            $button->modal   = true;
            $button->link    = $link;
            $button->text    = Text::_('PLG_EDITORS-XTD_MENU_BUTTON_MENU');
            $button->name    = $this->_type . '_' . $this->_name;
            $button->icon    = 'list';
            $button->iconSVG = '<svg viewBox="0 0 512 512"  width="24" height="24"><path d="M80 368H16a16 16 0 0 0-16 16v64a16 16 0 0 0 16 16h64a16 1'
                            . '6 0 0 0 16-16v-64a16 16 0 0 0-16-16zm0-320H16A16 16 0 0 0 0 64v64a16 16 0 0 0 16 16h64a16 16 0 0 0 16-16V64a16 16 '
                            . '0 0 0-16-16zm0 160H16a16 16 0 0 0-16 16v64a16 16 0 0 0 16 16h64a16 16 0 0 0 16-16v-64a16 16 0 0 0-16-16zm416 176H1'
                            . '76a16 16 0 0 0-16 16v32a16 16 0 0 0 16 16h320a16 16 0 0 0 16-16v-32a16 16 0 0 0-16-16zm0-320H176a16 16 0 0 0-16 16'
                            . 'v32a16 16 0 0 0 16 16h320a16 16 0 0 0 16-16V80a16 16 0 0 0-16-16zm0 160H176a16 16 0 0 0-16 16v32a16 16 0 0 0 16 16'
                            . 'h320a16 16 0 0 0 16-16v-32a16 16 0 0 0-16-16z"></path></svg>';

            $button->options = [
            'height'      => '300px',
            'width'       => '800px',
            'bodyHeight'  => '70',
            'modalWidth'  => '80',
            ];

            return $button;
        }
    }
}
