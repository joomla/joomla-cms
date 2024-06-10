<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Editors-xtd.menu
 *
 * @copyright   (C) 2016 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\EditorsXtd\Menu\Extension;

use Joomla\CMS\Editor\Button\Button;
use Joomla\CMS\Event\Editor\EditorButtonsSetupEvent;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Session\Session;
use Joomla\Event\SubscriberInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Editor menu button
 *
 * @since  3.7.0
 */
final class Menu extends CMSPlugin implements SubscriberInterface
{
    /**
     * Returns an array of events this subscriber will listen to.
     *
     * @return array
     *
     * @since   __DEPLOY_VERSION__
     */
    public static function getSubscribedEvents(): array
    {
        return ['onEditorButtonsSetup' => 'onEditorButtonsSetup'];
    }

    /**
     * @param  EditorButtonsSetupEvent $event
     * @return void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function onEditorButtonsSetup(EditorButtonsSetupEvent $event): void
    {
        $subject  = $event->getButtonsRegistry();
        $disabled = $event->getDisabledButtons();

        if (\in_array($this->_name, $disabled)) {
            return;
        }

        $button = $this->onDisplay($event->getEditorId());

        if ($button) {
            $subject->add($button);
        }
    }

    /**
     * Display the button
     *
     * @param   string  $name  The name of the button to add
     *
     * @return  Button|void  The button options as Button object
     *
     * @since  3.7.0
     *
     * @deprecated  5.0 Use onEditorButtonsSetup event
     */
    public function onDisplay($name)
    {
        $user  = $this->getApplication()->getIdentity();

        if (
            $user->authorise('core.create', 'com_menus')
            || $user->authorise('core.edit', 'com_menus')
        ) {
            $this->loadLanguage();

            $link = 'index.php?option=com_menus&view=items&layout=modal&tmpl=component&'
            . Session::getFormToken() . '=1&editor=' . $name;

            $button = new Button(
                $this->_name,
                [
                    'action'  => 'modal',
                    'link'    => $link,
                    'text'    => Text::_('PLG_EDITORS-XTD_MENU_BUTTON_MENU'),
                    'icon'    => 'list',
                    'iconSVG' => '<svg viewBox="0 0 512 512"  width="24" height="24"><path d="M80 368H16a16 16 0 0 0-16 16v64a16 16 0 0 0 16 16h64a16 1'
                        . '6 0 0 0 16-16v-64a16 16 0 0 0-16-16zm0-320H16A16 16 0 0 0 0 64v64a16 16 0 0 0 16 16h64a16 16 0 0 0 16-16V64a16 16 '
                        . '0 0 0-16-16zm0 160H16a16 16 0 0 0-16 16v64a16 16 0 0 0 16 16h64a16 16 0 0 0 16-16v-64a16 16 0 0 0-16-16zm416 176H1'
                        . '76a16 16 0 0 0-16 16v32a16 16 0 0 0 16 16h320a16 16 0 0 0 16-16v-32a16 16 0 0 0-16-16zm0-320H176a16 16 0 0 0-16 16'
                        . 'v32a16 16 0 0 0 16 16h320a16 16 0 0 0 16-16V80a16 16 0 0 0-16-16zm0 160H176a16 16 0 0 0-16 16v32a16 16 0 0 0 16 16'
                        . 'h320a16 16 0 0 0 16-16v-32a16 16 0 0 0-16-16z"></path></svg>',
                    // This is whole Plugin name, it is needed for keeping backward compatibility
                    'name' => $this->_type . '_' . $this->_name,
                ]
            );

            return $button;
        }
    }
}
