<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Editors.codemirror
 *
 * @copyright   (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\Editors\CodeMirror\Extension;

use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Uri\Uri;
use Joomla\Event\Event;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * CodeMirror Editor Plugin.
 *
 * @since  1.6
 */
final class Codemirror extends CMSPlugin
{
    /**
     * Display the editor area.
     *
     * @param   string   $name     The control name.
     * @param   string   $content  The contents of the text area.
     * @param   string   $width    The width of the text area (px or %).
     * @param   string   $height   The height of the text area (px or %).
     * @param   int      $col      The number of columns for the textarea.
     * @param   int      $row      The number of rows for the textarea.
     * @param   boolean  $buttons  True and the editor buttons will be displayed.
     * @param   string   $id       An optional ID for the textarea (note: since 1.6). If not supplied the name is used.
     * @param   string   $asset    Not used.
     * @param   object   $author   Not used.
     * @param   array    $params   Associative array of editor parameters.
     *
     * @return  string  HTML
     */
    public function onDisplay(
        $name,
        $content,
        $width,
        $height,
        $col,
        $row,
        $buttons = true,
        $id = null,
        $asset = null,
        $author = null,
        $params = []
    ) {
        $this->loadLanguage();

        $id = empty($id) ? $name : $id;

        // Must pass the field id to the buttons in this editor.
        $buttons = $this->displayButtons($id, $buttons, $asset, $author);

        // Options for the CodeMirror constructor.
        $options = new \stdClass();

        // Is field readonly?
        if (!empty($params['readonly'])) {
            $options->readOnly = true;
        }

        // Only add "px" to width and height if they are not given as a percentage.
        $options->width  = is_numeric($width) ? $width . 'px' : $width;
        $options->height = is_numeric($height) ? $height . 'px' : $height;

        $options->lineNumbers        = (bool) $this->params->get('lineNumbers', 1);
        $options->foldGutter         = (bool) $this->params->get('codeFolding', 1);
        $options->lineWrapping       = (bool) $this->params->get('lineWrapping', 1);
        $options->activeLine         = (bool) $this->params->get('activeLine', 1);
        $options->highlightSelection = (bool) $this->params->get('selectionMatches', 1);

        // Load the syntax mode.
        $modeAlias = [
            'scss' => 'css',
            'sass' => 'css',
            'less' => 'css',
        ];
        $options->mode = !empty($params['syntax']) ? $params['syntax'] : $this->params->get('syntax', 'html');
        $options->mode = $modeAlias[$options->mode] ?? $options->mode;

        // Special options for non-tagged modes.
        if (!in_array($options->mode, ['xml', 'html'])) {
            // Autogenerate closing brackets.
            $options->autoCloseBrackets = (bool) $this->params->get('autoCloseBrackets', 1);
        }

        // KeyMap settings.
        $options->keyMap = $this->params->get('keyMap', '');

        // Check for custom extensions
        $customExtensions          = $this->params->get('customExtensions', []);
        $options->customExtensions = [];

        if ($customExtensions) {
            foreach ($customExtensions as $item) {
                $methods = array_filter(array_map('trim', explode(',', $item->methods ?? '')));

                if (empty($item->module) || !$methods) {
                    continue;
                }

                // Prepend root path if we have a file
                $module = str_ends_with($item->module, '.js') ? Uri::root(true) . '/' . $item->module : $item->module;

                $options->customExtensions[] = [$module, $methods];
            }
        }

        $displayData = [
            'options' => $options,
            'params'  => $this->params,
            'name'    => $name,
            'id'      => $id,
            'cols'    => $col,
            'rows'    => $row,
            'content' => $content,
            'buttons' => $buttons,
        ];

        return LayoutHelper::render('editors.codemirror.codemirror', $displayData, JPATH_PLUGINS . '/editors/codemirror/layouts');
    }

    /**
     * Displays the editor buttons.
     *
     * @param   string  $name     Button name.
     * @param   mixed   $buttons  [array with button objects | boolean true to display buttons]
     * @param   mixed   $asset    Unused.
     * @param   mixed   $author   Unused.
     *
     * @return  string|void
     */
    protected function displayButtons($name, $buttons, $asset, $author)
    {
        if (is_array($buttons) || (is_bool($buttons) && $buttons)) {
            $buttonsEvent = new Event(
                'getButtons',
                [
                    'editor'  => $name,
                    'buttons' => $buttons,
                ]
            );

            $buttonsResult = $this->getDispatcher()->dispatch('getButtons', $buttonsEvent);
            $buttons       = $buttonsResult['result'];

            return LayoutHelper::render('joomla.editors.buttons', $buttons);
        }
    }
}
