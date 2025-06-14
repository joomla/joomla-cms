<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Editors-xtd.module
 *
 * @copyright   (C) 2015 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\EditorsXtd\Module\Extension;

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
 * Editor Module button
 *
 * @since  3.5
 */
final class Module extends CMSPlugin implements SubscriberInterface
{
    /**
     * Returns an array of events this subscriber will listen to.
     *
     * @return array
     *
     * @since   5.2.0
     */
    public static function getSubscribedEvents(): array
    {
        return ['onEditorButtonsSetup' => 'onEditorButtonsSetup'];
    }

    /**
     * @param  EditorButtonsSetupEvent $event
     * @return void
     *
     * @since   5.2.0
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
     * @since   3.5
     *
     * @deprecated  5.0 Use onEditorButtonsSetup event
     */
    public function onDisplay($name)
    {
        $user  = $this->getApplication()->getIdentity();

        if (
            $user->authorise('core.create', 'com_modules')
            || $user->authorise('core.edit', 'com_modules')
            || $user->authorise('core.edit.own', 'com_modules')
        ) {
            $this->loadLanguage();

            $link = 'index.php?option=com_modules&view=modules&layout=modal&tmpl=component&'
                . Session::getFormToken() . '=1&editor=' . $name;

            $button = new Button(
                $this->_name,
                [
                    'action'  => 'modal',
                    'link'    => $link,
                    'text'    => Text::_('PLG_MODULE_BUTTON_MODULE'),
                    'icon'    => 'cube',
                    'iconSVG' => '<svg viewBox="0 0 512 512" width="24" height="24"><path d="M239.1 6.3l-208 78c-18.7 7-31.1 '
                        . '25-31.1 45v225.1c0 18.2 10.3 34.8 26.5 42.9l208 104c13.5 6.8 29.4 6.8 42.9 0l208-104c16.3-8.1 26.5-24.8 '
                        . '26.5-42.9V129.3c0-20-12.4-37.9-31.1-44.9l-208-78C262 2.2 250 2.2 239.1 6.3zM256 68.4l192 72v1.1l-192 '
                        . '78-192-78v-1.1l192-72zm32 356V275.5l160-65v133.9l-160 80z"></path></svg>',
                    // This is whole Plugin name, it is needed for keeping backward compatibility
                    'name' => $this->_type . '_' . $this->_name,
                ]
            );

            return $button;
        }
    }
}
