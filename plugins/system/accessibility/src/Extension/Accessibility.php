<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  System.accessibility
 *
 * @copyright   (C) 2020 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\System\Accessibility\Extension;

use Joomla\CMS\Plugin\CMSPlugin;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * System plugin to add additional accessibility features to the administrator interface.
 *
 * @since  4.0.0
 */
final class Accessibility extends CMSPlugin
{
    /**
     * Add the javascript for the accessibility menu
     *
     * @return  void
     *
     * @since   4.0.0
     */
    public function onBeforeCompileHead()
    {
        $section = $this->params->get('section', 'administrator');

        if ($section !== 'both' && $this->getApplication()->isClient($section) !== true) {
            return;
        }

        // Get the document object.
        $document = $this->getApplication()->getDocument();

        if ($document->getType() !== 'html') {
            return;
        }

        // Are we in a modal?
        if ($this->getApplication()->getInput()->get('tmpl', '', 'cmd') === 'component') {
            return;
        }

        // Load language file.
        $this->loadLanguage();

        // Determine if it is an LTR or RTL language
        $direction = $this->getApplication()->getLanguage()->isRtl() ? 'right' : 'left';

        // Detect the current active language
        $lang = $this->getApplication()->getLanguage()->getTag();

        /**
        * Add strings for translations in Javascript.
        * Reference  https://ranbuch.github.io/accessibility/
        */
        $document->addScriptOptions(
            'accessibility-options',
            [
                'labels' => [
                    'bigCursor'           => $this->getApplication()->getLanguage()->_('PLG_SYSTEM_ACCESSIBILITY_CURSOR'),
                    'closeTitle'          => $this->getApplication()->getLanguage()->_('PLG_SYSTEM_ACCESSIBILITY_CLOSE'),
                    'decreaseLineHeight'  => $this->getApplication()->getLanguage()->_('PLG_SYSTEM_ACCESSIBILITY_DECREASE_LINE_HEIGHT'),
                    'decreaseText'        => $this->getApplication()->getLanguage()->_('PLG_SYSTEM_ACCESSIBILITY_DECREASE_TEXT'),
                    'decreaseTextSpacing' => $this->getApplication()->getLanguage()->_('PLG_SYSTEM_ACCESSIBILITY_DECREASE_SPACING'),
                    'disableAnimations'   => $this->getApplication()->getLanguage()->_('PLG_SYSTEM_ACCESSIBILITY_DISABLE_ANIMATIONS'),
                    'grayHues'            => $this->getApplication()->getLanguage()->_('PLG_SYSTEM_ACCESSIBILITY_GREY'),
                    'increaseLineHeight'  => $this->getApplication()->getLanguage()->_('PLG_SYSTEM_ACCESSIBILITY_INCREASE_LINE_HEIGHT'),
                    'increaseText'        => $this->getApplication()->getLanguage()->_('PLG_SYSTEM_ACCESSIBILITY_INCREASE_TEXT'),
                    'increaseTextSpacing' => $this->getApplication()->getLanguage()->_('PLG_SYSTEM_ACCESSIBILITY_INCREASE_SPACING'),
                    'invertColors'        => $this->getApplication()->getLanguage()->_('PLG_SYSTEM_ACCESSIBILITY_INVERT_COLORS'),
                    'menuTitle'           => $this->getApplication()->getLanguage()->_('PLG_SYSTEM_ACCESSIBILITY_MENU_TITLE'),
                    'readingGuide'        => $this->getApplication()->getLanguage()->_('PLG_SYSTEM_ACCESSIBILITY_READING'),
                    'resetTitle'          => $this->getApplication()->getLanguage()->_('PLG_SYSTEM_ACCESSIBILITY_RESET'),
                    'speechToText'        => $this->getApplication()->getLanguage()->_('PLG_SYSTEM_ACCESSIBILITY_STT'),
                    'textToSpeech'        => $this->getApplication()->getLanguage()->_('PLG_SYSTEM_ACCESSIBILITY_TTS'),
                    'underlineLinks'      => $this->getApplication()->getLanguage()->_('PLG_SYSTEM_ACCESSIBILITY_UNDERLINE'),
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
