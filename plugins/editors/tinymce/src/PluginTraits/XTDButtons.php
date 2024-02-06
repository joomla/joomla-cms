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

        if (is_array($buttons) || (is_bool($buttons) && $buttons)) {
            Text::script('PLG_TINY_CORE_BUTTONS');

            // Init the arrays for the buttons
            $btnsNames = [];

            // Build the script
            foreach ($buttons as $i => $button) {
                $button->id = $name . '_' . $button->name . '_modal';

                echo LayoutHelper::render('joomla.editors.buttons.modal', $button);

                if ($button->get('name')) {
                    $coreButton            = [];
                    $coreButton['name']    = $button->get('text');
                    $coreButton['href']    = $button->get('link') !== '#' ? Uri::base() . $button->get('link') : null;
                    $coreButton['id']      = $name . '_' . $button->name;
                    $coreButton['icon']    = $button->get('icon');
                    $coreButton['click']   = $button->get('onclick') ?: null;
                    $coreButton['iconSVG'] = $button->get('iconSVG');

                    // The array with the toolbar buttons
                    $btnsNames[] = $coreButton;
                }
            }

            sort($btnsNames);

            return ['names' => $btnsNames];
        }
    }
}
