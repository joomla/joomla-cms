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

                // echo LayoutHelper::render('joomla.editors.buttons.modal', $button);
                if ($button->get('name')) {
                    $options = is_array($button->get('options')) ? $button->get('options') : array();
                    $id = null !== $button->get('id') ? str_replace(' ', '', $button->get('id')) : $button->get('editor') . '_' . strtolower($button->get('name')) . '_modal';
                    $confirmCallback = isset($options['confirmCallback']) ? str_replace($name, '{{editor}}', $options['confirmCallback']) : null;
                    $confirm = isset($options['confirmText']) && $confirmCallback
                        ? '<button type="button" class="btn btn-success" data-bs-dismiss="modal" onclick="' . $confirmCallback . '">' . $options['confirmText'] . ' </button>'
                        : '';

                    // The array with the toolbar buttons
                    $btnsNames[] = [
                        'name'         => $button->get('text'),
                        'href'         => $button->get('link') !== '#' ? Uri::base() . str_replace('&amp;', '&', $button->get('link')) : null,
                        'id'           => $name . '_' . $button->name,
                        'icon'         => $button->get('icon'),
                        'click'        => $button->get('onclick') ? str_replace($name, '{{editor}}', $button->get('onclick')) : null,
                        'iconSVG'      => $button->get('iconSVG'),
                        'header'       => '<h3 class="modal-title">' . ($button->get('title') ? $button->get('title') : $button->get('text')) . '</h3><button type="button" class="btn-close novalidate" data-bs-dismiss="modal" aria-label="' . Text::_('JCLOSE') . '"></button>',
                        'footer'       => $confirm . '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">' . Text::_('JLIB_HTML_BEHAVIOR_CLOSE') . '</button>',
                        'modalOptions' => [
                            'height'     => array_key_exists('height', $options) ? $options['height'] : '400px',
                            'width'      => array_key_exists('width', $options) ? $options['width'] : '800px',
                            'bodyHeight' => array_key_exists('bodyHeight', $options) ? $options['bodyHeight'] : '70',
                            'modalWidth' => array_key_exists('modalWidth', $options) ? $options['modalWidth'] : '80',
                        ],
                    ];
                }
            }

            sort($btnsNames);

            return ['names'  => $btnsNames];
        }
    }
}
