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
     * Affects constructor behavior. If true, language files will be loaded automatically.
     *
     * @var    boolean
     * @since  3.1.4
     */
    protected $autoloadLanguage = true;

    /**
     * Mapping of syntax to CodeMirror modes.
     *
     * @var array
     */
    protected $modeAlias = [];

    /**
     * Base path for editor assets.
     *
     * @var  string
     *
     * @since  4.0.0
     */
    protected $basePath = 'media/vendor/codemirror/';

    /**
     * Base path for editor modes.
     *
     * @var  string
     *
     * @since  4.0.0
     */
    protected $modePath = 'media/vendor/codemirror/mode/%N/%N';

    /**
     * Initialises the Editor.
     *
     * @return  void
     */
    public function onInit()
    {
        static $done = false;

        // Do this only once.
        if ($done) {
            return;
        }

        $done = true;

        // Most likely need this later
        $doc = $this->getApplication()->getDocument();

        // Codemirror shall have its own group of plugins to modify and extend its behavior
        PluginHelper::importPlugin('editors_codemirror');

        // At this point, params can be modified by a plugin before going to the layout renderer.
        $this->getApplication()->triggerEvent('onCodeMirrorBeforeInit', [&$this->params, &$this->basePath, &$this->modePath]);

        $displayData = (object) ['params' => $this->params];
        $font        = $this->params->get('fontFamily', '0');
        $fontInfo    = $this->getFontInfo($font);

        if (isset($fontInfo)) {
            if (isset($fontInfo->url)) {
                $doc->addStyleSheet($fontInfo->url);
            }

            if (isset($fontInfo->css)) {
                $displayData->fontFamily = $fontInfo->css . '!important';
            }
        }

        // We need to do output buffering here because layouts may actually 'echo' things which we do not want.
        ob_start();
        LayoutHelper::render('editors.codemirror.styles', $displayData, JPATH_PLUGINS . '/editors/codemirror/layouts');
        ob_end_clean();

        $this->getApplication()->triggerEvent('onCodeMirrorAfterInit', [&$this->params, &$this->basePath, &$this->modePath]);
    }

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
        // True if a CodeMirror already has autofocus. Prevent multiple autofocuses.
        static $autofocused;

        $id = empty($id) ? $name : $id;

        // Must pass the field id to the buttons in this editor.
        $buttons = $this->displayButtons($id, $buttons, $asset, $author);

        // Only add "px" to width and height if they are not given as a percentage.
        $width .= is_numeric($width) ? 'px' : '';
        $height .= is_numeric($height) ? 'px' : '';

        // Options for the CodeMirror constructor.
        $options   = new stdClass();
        $keyMapUrl = '';

        // Is field readonly?
        if (!empty($params['readonly'])) {
            $options->readOnly = 'nocursor';
        }

        // Should we focus on the editor on load?
        if (!$autofocused) {
            $options->autofocus = isset($params['autofocus']) ? (bool) $params['autofocus'] : false;
            $autofocused        = $options->autofocus;
        }
        // Set autorefresh to true - fixes issue when editor is not loaded in a focused tab
        $options->autoRefresh = true;

        $options->lineWrapping = (bool) $this->params->get('lineWrapping', 1);

        // Add styling to the active line.
        $options->styleActiveLine = (bool) $this->params->get('activeLine', 1);

        // Do we highlight selection matches?
        if ($this->params->get('selectionMatches', 1)) {
            $options->highlightSelectionMatches = [
                    'showToken'         => true,
                    'annotateScrollbar' => true,
                ];
        }

        // Do we use line numbering?
        if ($options->lineNumbers = (bool) $this->params->get('lineNumbers', 1)) {
            $options->gutters[] = 'CodeMirror-linenumbers';
        }

        // Do we use code folding?
        if ($options->foldGutter = (bool) $this->params->get('codeFolding', 1)) {
            $options->gutters[] = 'CodeMirror-foldgutter';
        }

        // Do we use a marker gutter?
        if ($options->markerGutter = (bool) $this->params->get('markerGutter', $this->params->get('marker-gutter', 1))) {
            $options->gutters[] = 'CodeMirror-markergutter';
        }

        // Load the syntax mode.
        $syntax = !empty($params['syntax'])
            ? $params['syntax']
            : $this->params->get('syntax', 'html');
        $options->mode = $this->modeAlias[$syntax] ?? $syntax;

        // Load the theme if specified.
        if ($theme = $this->params->get('theme')) {
            $options->theme = $theme;

            $this->getApplication()->getDocument()->getWebAssetManager()
                ->registerAndUseStyle('codemirror.theme', $this->basePath . 'theme/' . $theme . '.css');
        }

        // Special options for tagged modes (xml/html).
        if (in_array($options->mode, ['xml', 'html', 'php'])) {
            // Autogenerate closing tags (html/xml only).
            $options->autoCloseTags = (bool) $this->params->get('autoCloseTags', 1);

            // Highlight the matching tag when the cursor is in a tag (html/xml only).
            $options->matchTags = (bool) $this->params->get('matchTags', 1);
        }

        // Special options for non-tagged modes.
        if (!in_array($options->mode, ['xml', 'html'])) {
            // Autogenerate closing brackets.
            $options->autoCloseBrackets = (bool) $this->params->get('autoCloseBrackets', 1);

            // Highlight the matching bracket.
            $options->matchBrackets = (bool) $this->params->get('matchBrackets', 1);
        }

        $options->scrollbarStyle = $this->params->get('scrollbarStyle', 'native');

        // KeyMap settings.
        $options->keyMap = $this->params->get('keyMap', false);

        // Support for older settings.
        if ($options->keyMap === false) {
            $options->keyMap = $this->params->get('vimKeyBinding', 0) ? 'vim' : 'default';
        }

        if ($options->keyMap !== 'default') {
            $keyMapUrl = HTMLHelper::_('script', $this->basePath . 'keymap/' . $options->keyMap . '.min.js', ['relative' => false, 'pathOnly' => true]);
            $keyMapUrl .= '?' . $this->getApplication()->getDocument()->getMediaVersion();
        }

        $options->keyMapUrl = $keyMapUrl;

        $displayData = (object) [
            'options'  => $options,
            'params'   => $this->params,
            'name'     => $name,
            'id'       => $id,
            'cols'     => $col,
            'rows'     => $row,
            'content'  => $content,
            'buttons'  => $buttons,
            'basePath' => $this->basePath,
            'modePath' => $this->modePath,
        ];

        // At this point, displayData can be modified by a plugin before going to the layout renderer.
        $results = $this->getApplication()->triggerEvent('onCodeMirrorBeforeDisplay', [&$displayData]);

        $results[] = LayoutHelper::render('editors.codemirror.element', $displayData, JPATH_PLUGINS . '/editors/codemirror/layouts');

        foreach ($this->getApplication()->triggerEvent('onCodeMirrorAfterDisplay', [&$displayData]) as $result) {
            $results[] = $result;
        }

        return implode("\n", $results);
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
