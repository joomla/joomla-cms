<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Editors.codemirror
 *
 * @copyright   (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\Editors\CodeMirror\Extension;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Event\Event;
use stdClass;

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
        $options   = new stdClass();
//        $keyMapUrl = '';

        // Is field readonly?
        if (!empty($params['readonly'])) {
            $options->readOnly = 'nocursor';
        }

        // Only add "px" to width and height if they are not given as a percentage.
        $options->width  = is_numeric($width) ? $width . 'px' : $width;
        $options->height = is_numeric($height) ? $height . 'px' : $height;

        $options->lineNumbers        = (bool) $this->params->get('lineNumbers', 1);
        $options->foldGutter         = (bool) $this->params->get('codeFolding', 1);
        //$options->markerGutter     = (bool) $this->params->get('markerGutter', $this->params->get('marker-gutter', 1));
        $options->lineWrapping       = (bool) $this->params->get('lineWrapping', 1);
        $options->activeLine         = (bool) $this->params->get('activeLine', 1);
        $options->highlightSelection = (bool) $this->params->get('selectionMatches', 0);

        // Do we highlight selection matches?
//        if ($this->params->get('selectionMatches', 1)) {
//            $options->highlightSelectionMatches = [
//                    'showToken'         => true,
//                    'annotateScrollbar' => true,
//                ];
//        }
//        if ($options->lineNumbers) {
//            $options->gutters[] = 'CodeMirror-linenumbers';
//        }
//        if ($options->foldGutter) {
//            $options->gutters[] = 'CodeMirror-foldgutter';
//        }
//        if ($options->markerGutter) {
//            $options->gutters[] = 'CodeMirror-markergutter';
//        }

        // Load the syntax mode.
        $options->mode = !empty($params['syntax']) ? $params['syntax'] : $this->params->get('syntax', 'html');
        //$options->mode = $this->modeAlias[$syntax] ?? $syntax;
/*
        // Load the theme if specified.
        if ($theme = $this->params->get('theme')) {
            $options->theme = $theme;

            $this->getApplication()->getDocument()->getWebAssetManager()
                ->registerAndUseStyle('codemirror.theme', $this->basePath . 'theme/' . $theme . '.css');
        }
*/
/*
        // Special options for tagged modes (xml/html).
        if (in_array($options->mode, ['xml', 'html', 'php'])) {
            // Autogenerate closing tags (html/xml only).
            $options->autoCloseTags = (bool) $this->params->get('autoCloseTags', 1);

            // Highlight the matching tag when the cursor is in a tag (html/xml only).
            $options->matchTags = (bool) $this->params->get('matchTags', 1);
        }
*/
        // Special options for non-tagged modes.
        if (!in_array($options->mode, ['xml', 'html'])) {
            // Autogenerate closing brackets.
            $options->autoCloseBrackets = (bool) $this->params->get('autoCloseBrackets', 1);

            // Highlight the matching bracket.
            //$options->matchBrackets = (bool) $this->params->get('matchBrackets', 1);
        }

//        $options->scrollbarStyle = $this->params->get('scrollbarStyle', 'native');

        // KeyMap settings.
        $options->keyMap = $this->params->get('keyMap', '');

        // Support for older settings.
//        if ($options->keyMap === false) {
//            $options->keyMap = $this->params->get('vimKeyBinding', 0) ? 'vim' : 'default';
//        }

//        if ($options->keyMap !== 'default') {
//            $keyMapUrl = HTMLHelper::_('script', $this->basePath . 'keymap/' . $options->keyMap . '.min.js', ['relative' => false, 'pathOnly' => true]);
//            $keyMapUrl .= '?' . $this->getApplication()->getDocument()->getMediaVersion();
//        }
//
//        $options->keyMapUrl = $keyMapUrl;

        $displayData = (object) [
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

    /**
     * Gets font info from the json data file
     *
     * @param   string  $font  A key from the $fonts array.
     *
     * @return  object
     */
    protected function getFontInfo($font)
    {
        static $fonts;

        if (!$fonts) {
            $fonts = json_decode(file_get_contents(JPATH_PLUGINS . '/editors/codemirror/fonts.json'), true);
        }

        return isset($fonts[$font]) ? (object) $fonts[$font] : null;
    }
}
