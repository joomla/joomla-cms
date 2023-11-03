<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Editors.tinymce
 *
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\Editors\TinyMCE\PluginTraits;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Uri\Uri;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Resolves the XTD Buttons for the current TinyMCE editor.
 *
 * @since  4.1.0
 */
trait XTDButtons
{
    /**
     * Get the XTD buttons and render them inside tinyMCE
     *
     * @param   mixed  $buttons  the buttons that should be hidden
     * @param   array  $options  Associative array with additional parameters
     *
     * @return array
     *
     * @since 4.1.0
     */
    private function tinyButtons($buttons, array $options = []): array
    {
        // Get buttons from plugins
        $buttonsList = $this->getButtons($buttons, $options);

        if (!$buttonsList) {
            return [];
        }

        /** @var \Joomla\CMS\WebAsset\WebAssetManager $wa */
        $wa       = $this->application->getDocument()->getWebAssetManager();
        $editorId = $options['editorId'] ?? '';

        Text::script('PLG_TINY_CORE_BUTTONS');

        // Build a buttons option for TinyMCE
        $tinyButtons = [];

        foreach ($buttonsList as $button) {
            $title   = $button->get('title') ?: $button->get('text', '');
            $icon    = $button->get('icon');
            $link    = $button->get('link');
            $action  = $button->get('action', '');
            $options = (array) $button->get('options');

            $btnAsset = 'editor-button.' . $button->getButtonName();

            // Enable the button assets if any
            if ($wa->assetExists('style', $btnAsset)) {
                $wa->useStyle($btnAsset);
            }
            if ($wa->assetExists('script', $btnAsset)) {
                $wa->useScript($btnAsset);
            }

            // Correct the link
            if ($link && $link[0] !== '#') {
                $link           = str_contains($link, '&amp;') ? htmlspecialchars_decode($link) : $link;
                $link           = Uri::base(true) . '/' . $link;
                $options['src'] = $options['src'] ?? $link;
            }

            // Set action to "modal" for legacy buttons, when possible
            $legacyModal = $button->get('modal');

            // Prepare default values for modal
            if ($action === 'modal') {
                $wa->useScript('joomla.dialog');
                $legacyModal = false;

                $options['popupType']  = $options['popupType'] ?? 'iframe';
                $options['textHeader'] = $options['textHeader'] ?? $title;
                $options['iconHeader'] = $options['iconHeader'] ?? 'icon-' . $icon;
            }

            $coreButton            = [];
            $coreButton['name']    = $title;
            $coreButton['icon']    = $icon;
            $coreButton['click']   = $button->get('onclick');
            $coreButton['iconSVG'] = $button->get('iconSVG');
            $coreButton['action']  = $action;
            $coreButton['options'] = $options;

            if ($legacyModal) {
                $coreButton['bsModal'] = true;
                $coreButton['id']      = $editorId . '_' . $button->name;

                $button->id = $editorId . '_' . $button->name . '_modal';

                echo LayoutHelper::render('joomla.editors.buttons.modal', $button);
            }

            // The array with the toolbar buttons
            $tinyButtons[] = $coreButton;
        }

        sort($tinyButtons);

        return ['names' => $tinyButtons];
    }
}
