<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Editors-xtd.readmore
 *
 * @copyright   (C) 2006 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\EditorsXtd\ReadMore\Extension;

use Joomla\CMS\Editor\Button\Button;
use Joomla\CMS\Event\Editor\EditorButtonsSetupEvent;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Event\SubscriberInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Editor Readmore button
 *
 * @since  1.5
 */
final class ReadMore extends CMSPlugin implements SubscriberInterface
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
    public function onEditorButtonsSetup(EditorButtonsSetupEvent $event)
    {
        $subject  = $event->getButtonsRegistry();
        $disabled = $event->getDisabledButtons();

        if (\in_array($this->_name, $disabled)) {
            return;
        }

        $this->loadLanguage();

        $button = $this->onDisplay($event->getEditorId());
        $subject->add($button);
    }

    /**
     * Readmore button
     *
     * @param   string  $name  The name of the button to add
     *
     * @return  Button  $button  A two element array of (imageName, textToInsert)
     *
     * @since   1.5
     *
     * @deprecated  6.0 Use onEditorButtonsSetup event
     */
    public function onDisplay($name)
    {
        // Register the asset "editor-button.<button name>", will be loaded by the button layout
        $this->getApplication()->getDocument()->getWebAssetManager()
            ->registerScript(
                'editor-button.' . $this->_name,
                'com_content/admin-article-readmore.min.js',
                [],
                ['type' => 'module'],
                ['editors', 'joomla.dialog']
            );

        Text::script('PLG_READMORE_ALREADY_EXISTS');

        $button = new Button(
            $this->_name,
            [
                'action'  => 'insert-readmore',
                'text'    => Text::_('PLG_READMORE_BUTTON_READMORE'),
                'icon'    => 'arrow-down',
                'iconSVG' => '<svg viewBox="0 0 32 32" width="24" height="24"><path d="M32 12l-6-6-10 10-10-10-6 6 16 16z"></path></svg>',
                // This is whole Plugin name, it is needed for keeping backward compatibility
                'name' => $this->_type . '_' . $this->_name,
            ]
        );
        return $button;
    }
}
