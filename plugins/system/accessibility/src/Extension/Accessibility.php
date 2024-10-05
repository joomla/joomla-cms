<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  System.accessibility
 *
 * @copyright   (C) 2020 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\System\Accessibility\Extension;

use Joomla\CMS\Event\Application\BeforeCompileHeadEvent;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Event\SubscriberInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * System plugin to add additional accessibility features to the administrator interface.
 *
 * @since  4.0.0
 */
final class Accessibility extends CMSPlugin implements SubscriberInterface
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
        return [
            'onBeforeCompileHead' => 'onBeforeCompileHead',
        ];
    }

    /**
     * Add the javascript for the accessibility menu
     *
     * @param  BeforeCompileHeadEvent $event  The event object
     *
     * @return  void
     *
     * @since   4.0.0
     */
    public function onBeforeCompileHead(BeforeCompileHeadEvent $event): void
    {
        $section = $this->params->get('section', 'administrator');
        $app     = $event->getApplication();

        if ($section !== 'both' && $app->isClient($section) !== true) {
            return;
        }

        // Get the document object.
        $document = $event->getDocument();

        if ($document->getType() !== 'html') {
            return;
        }

        // Are we in a modal?
        if ($app->getInput()->get('tmpl', '', 'cmd') === 'component') {
            return;
        }

        // Load language file.
        $this->loadLanguage();

        // Determine if it is an LTR or RTL language
        $direction = $app->getLanguage()->isRtl() ? 'right' : 'left';

        // Detect the current active language
        $lang = $app->getLanguage()->getTag();

        /**
        * Add strings for translations in Javascript.
        * Reference  https://ranbuch.github.io/accessibility/
        */
        $document->addScriptOptions(
            'accessibility-options',
            [
                'labels' => [
                    'menuTitle'           => $this->getApplication()->getLanguage()->_('PLG_SYSTEM_ACCESSIBILITY_MENU_TITLE'),
                    'increaseText'        => $this->getApplication()->getLanguage()->_('PLG_SYSTEM_ACCESSIBILITY_INCREASE_TEXT'),
                    'decreaseText'        => $this->getApplication()->getLanguage()->_('PLG_SYSTEM_ACCESSIBILITY_DECREASE_TEXT'),
                    'increaseTextSpacing' => $this->getApplication()->getLanguage()->_('PLG_SYSTEM_ACCESSIBILITY_INCREASE_SPACING'),
                    'decreaseTextSpacing' => $this->getApplication()->getLanguage()->_('PLG_SYSTEM_ACCESSIBILITY_DECREASE_SPACING'),
                    'invertColors'        => $this->getApplication()->getLanguage()->_('PLG_SYSTEM_ACCESSIBILITY_INVERT_COLORS'),
                    'grayHues'            => $this->getApplication()->getLanguage()->_('PLG_SYSTEM_ACCESSIBILITY_GREY'),
                    'underlineLinks'      => $this->getApplication()->getLanguage()->_('PLG_SYSTEM_ACCESSIBILITY_UNDERLINE'),
                    'bigCursor'           => $this->getApplication()->getLanguage()->_('PLG_SYSTEM_ACCESSIBILITY_CURSOR'),
                    'readingGuide'        => $this->getApplication()->getLanguage()->_('PLG_SYSTEM_ACCESSIBILITY_READING'),
                    'textToSpeech'        => $this->getApplication()->getLanguage()->_('PLG_SYSTEM_ACCESSIBILITY_TTS'),
                    'speechToText'        => $this->getApplication()->getLanguage()->_('PLG_SYSTEM_ACCESSIBILITY_STT'),
                    'resetTitle'          => $this->getApplication()->getLanguage()->_('PLG_SYSTEM_ACCESSIBILITY_RESET'),
                    'closeTitle'          => $this->getApplication()->getLanguage()->_('PLG_SYSTEM_ACCESSIBILITY_CLOSE'),
                ],
                'icon' => [
                    'position' => [
                        $direction => [
                            'size'  => '0',
                            'units' => 'px',
                        ],
                    ],
                    'useEmojis' => $this->params->get('useEmojis', 'true') === 'true',
                ],
                'hotkeys' => [
                    'enabled'    => true,
                    'helpTitles' => true,
                ],
                'textToSpeechLang' => [$lang],
                'speechToTextLang' => [$lang],
            ]
        );

        $document->getWebAssetManager()
            ->useScript('accessibility')
            ->addInlineScript(
                'window.addEventListener("load", function() {'
                . 'new Accessibility(Joomla.getOptions("accessibility-options") || {});'
                . '});',
                ['name' => 'inline.plg.system.accessibility'],
                ['type' => 'module'],
                ['accessibility']
            );
    }
}
