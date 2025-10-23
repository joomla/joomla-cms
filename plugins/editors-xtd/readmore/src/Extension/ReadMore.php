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
     * @since   5.0.0
     */
    public static function getSubscribedEvents(): array
    {
        return ['onEditorButtonsSetup' => 'onEditorButtonsSetup'];
    }

    /**
     * @param  EditorButtonsSetupEvent $event
     * @return void
     *
     * @since   5.0.0
     */
    public function onEditorButtonsSetup(EditorButtonsSetupEvent $event): void
    {
        $subject  = $event->getButtonsRegistry();
        $disabled = $event->getDisabledButtons();

        if (\in_array($this->_name, $disabled)) {
            return;
        }

        $button = $this->onDisplay($event->getEditorId());
        $subject->add($button);
    }

    /**
     * Readmore button
     *
     * @param   string  $name  The name of the button to add
     *
     * @return  Button  The button options as Button object
     *
     * @since   1.5
     *
     * @deprecated  5.0 Use onEditorButtonsSetup event
     */
    public function onDisplay($name)
    {
        /** @var \Joomla\CMS\WebAsset\WebAssetManager $wa */
        $wa = $this->getApplication()->getDocument()->getWebAssetManager();

        // Register the asset "editor-button.<button name>", will be loaded by the button layout
        if (!$wa->assetExists('script', 'editor-button.' . $this->_name)) {
            $wa->registerScript(
                'editor-button.' . $this->_name,
                'com_content/admin-article-readmore.min.js',
                [],
                ['type' => 'module'],
                ['editors', 'joomla.dialog']
            );
        }

        $this->loadLanguage();

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
