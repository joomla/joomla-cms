<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\Editors\CodeMirror\Provider;

use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Editor\AbstractEditorProvider;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Event\DispatcherInterface;
use Joomla\Registry\Registry;

/**
 * Editor provider class
 *
 * @since   __DEPLOY_VERSION__
 */
final class CodeMirrorProvider extends AbstractEditorProvider
{
    /**
     * A Registry object holding the parameters for the plugin
     *
     * @var    Registry
     * @since  __DEPLOY_VERSION__
     */
    protected $params;

    /**
     * The application object
     *
     * @var    CMSApplicationInterface
     *
     * @since  __DEPLOY_VERSION__
     */
    protected $application;

    /**
     * A flag whether assets was loaded already
     *
     * @var bool
     *
     * @since  __DEPLOY_VERSION__
     */
    protected $assetsLoaded = false;

    /**
     * Mapping of syntax to CodeMirror modes.
     *
     * @var array
     *
     * @since  4.0.0
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
     * Class constructor
     *
     * @param   Registry                 $params
     * @param   CMSApplicationInterface  $application
     * @param   DispatcherInterface      $dispatcher
     *
     * @since  __DEPLOY_VERSION__
     */
    public function __construct(Registry $params, CMSApplicationInterface $application, DispatcherInterface $dispatcher)
    {
        $this->params      = $params;
        $this->application = $application;

        $this->setDispatcher($dispatcher);
    }

    /**
     * Return Editor name, CMD string.
     *
     * @return string
     * @since   __DEPLOY_VERSION__
     */
    public function getName(): string
    {
        return 'codemirror';
    }

    /**
     * Gets the editor HTML markup
     *
     * @param   string  $name        Input name.
     * @param   string  $content     The content of the field.
     * @param   array   $attributes  Associative array of editor attributes.
     * @param   array   $params      Associative array of editor parameters.
     *
     * @return  string  The HTML markup of the editor
     *
     * @since   __DEPLOY_VERSION__
     */
    public function display(string $name, string $content = '', array $attributes = [], array $params = []): string
    {
        // True if a CodeMirror already has autofocus. Prevent multiple autofocuses.
        static $autofocused;

        $this->loadAssets();

        $col     = $attributes['col'] ?? '';
        $row     = $attributes['row'] ?? '';
        $id      = $attributes['id'] ?? '';
        $buttons = $params['buttons'] ?? true;
        $asset   = $params['asset'] ?? 0;
        $author  = $params['author'] ?? 0;

        // Must pass the field id to the buttons in this editor.
        $buttonsStr = $this->displayButtons($buttons, ['asset' => $asset, 'author' => $author, 'editorId' => $id]);

        // Options for the CodeMirror constructor.
        $options   = new \stdClass();
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

            $this->application->getDocument()->getWebAssetManager()
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
            $keyMapUrl .= '?' . $this->application->getDocument()->getMediaVersion();
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
            'buttons'  => $buttonsStr,
            'basePath' => $this->basePath,
            'modePath' => $this->modePath,
        ];

        // At this point, displayData can be modified by a plugin before going to the layout renderer.
        $results = $this->application->triggerEvent('onCodeMirrorBeforeDisplay', [&$displayData]);

        $results[] = LayoutHelper::render('editors.codemirror.element', $displayData, JPATH_PLUGINS . '/editors/codemirror/layouts');

        foreach ($this->application->triggerEvent('onCodeMirrorAfterDisplay', [&$displayData]) as $result) {
            $results[] = $result;
        }

        return implode("\n", $results);
    }

    /**
     * Load editor assets
     *
     * @return void
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function loadAssets()
    {
        if ($this->assetsLoaded) {
            return;
        }

        $this->assetsLoaded = true;

        // Most likely need this later
        $doc = $this->application->getDocument();

        // Codemirror shall have its own group of plugins to modify and extend its behavior
        PluginHelper::importPlugin('editors_codemirror');

        // At this point, params can be modified by a plugin before going to the layout renderer.
        $this->application->triggerEvent('onCodeMirrorBeforeInit', [$this->params, &$this->basePath, &$this->modePath]);

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

        $this->application->triggerEvent('onCodeMirrorAfterInit', [$this->params, &$this->basePath, &$this->modePath]);
    }

    /**
     * Gets font info from the json data file
     *
     * @param   string  $font  A key from the $fonts array.
     *
     * @return  object|null
     *
     * @since  4.0.0
     */
    protected function getFontInfo(string $font)
    {
        static $fonts;

        if (!$fonts) {
            $fonts = json_decode(file_get_contents(JPATH_PLUGINS . '/editors/codemirror/fonts.json'), true);
        }

        return isset($fonts[$font]) ? (object) $fonts[$font] : null;
    }
}
