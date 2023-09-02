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
use Joomla\Event\Event;

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
     * @param   string  $name      the id of the editor field
     * @param   string  $excluded  the buttons that should be hidden
     *
     * @return array|void
     *
     * @since 4.1.0
     */
    private function tinyButtons($name, $excluded)
    {
        // Get the available buttons
        $buttonsEvent = new Event(
            'getButtons',
            [
                'editor'  => $name,
                'buttons' => $excluded,
            ]
        );

        $buttonsResult = $this->getDispatcher()->dispatch('getButtons', $buttonsEvent);
        $buttons       = $buttonsResult['result'];

        /** @var \Joomla\CMS\WebAsset\WebAssetManager $wa */
        $wa = $this->getApplication()->getDocument()->getWebAssetManager();

        if (is_array($buttons) || (is_bool($buttons) && $buttons)) {
            Text::script('PLG_TINY_CORE_BUTTONS');

            // Init the arrays for the buttons
            $btnsNames = [];

            // Build the script
            foreach ($buttons as $button) {
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
                $legacyModal = $button->get('modal') && !empty($options['confirmCallback']);

                if (!$action && $button->get('modal') && !$legacyModal) {
                    $action = 'modal';

                    // Backward compatibility check, for older options
                    if (!empty($options['modalWidth'])) {
                        $options['width'] = $options['modalWidth'] . 'vw';
                    }
                    if (!empty($options['bodyHeight'])) {
                        $options['height'] = $options['bodyHeight'] . 'vh';
                    }
                }

                // Prepare default values for modal
                if ($action === 'modal') {
                    $this->getApplication()->getDocument()
                        ->getWebAssetManager()->useScript('joomla.dialog');

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
                    $coreButton['id']      = $name . '_' . $button->name;

                    $button->id = $name . '_' . $button->name . '_modal';

                    echo LayoutHelper::render('joomla.editors.buttons.modal', $button);
                }

                // The array with the toolbar buttons
                $btnsNames[] = $coreButton;
            }

            sort($btnsNames);

            return ['names' => $btnsNames];
        }
    }
}
