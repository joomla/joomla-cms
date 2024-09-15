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
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\Event\DispatcherInterface;
use Joomla\Registry\Registry;

/**
 * Editor provider class
 *
 * @since   5.0.0
 */
final class CodeMirrorProvider extends AbstractEditorProvider
{
    /**
     * A Registry object holding the parameters for the plugin
     *
     * @var    Registry
     * @since  5.0.0
     */
    protected $params;

    /**
     * The application object
     *
     * @var    CMSApplicationInterface
     *
     * @since  5.0.0
     */
    protected $application;

    /**
     * Class constructor
     *
     * @param   Registry                 $params
     * @param   CMSApplicationInterface  $application
     * @param   DispatcherInterface      $dispatcher
     *
     * @since  5.0.0
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
     * @since   5.0.0
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
     * @since   5.0.0
     */
    public function display(string $name, string $content = '', array $attributes = [], array $params = []): string
    {
        $col     = $attributes['col'] ?? '';
        $row     = $attributes['row'] ?? '';
        $width   = $attributes['width'] ?? '';
        $height  = $attributes['height'] ?? '';
        $id      = $attributes['id'] ?? '';
        $buttons = $params['buttons'] ?? true;
        $asset   = $params['asset'] ?? 0;
        $author  = $params['author'] ?? 0;

        // Must pass the field id to the buttons in this editor.
        $buttonsStr = $this->displayButtons($buttons, ['asset' => $asset, 'author' => $author, 'editorId' => $id]);

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
            'js'   => 'javascript',
        ];
        $options->mode = !empty($params['syntax']) ? $params['syntax'] : $this->params->get('syntax', 'html');
        $options->mode = $modeAlias[$options->mode] ?? $options->mode;

        // Special options for non-tagged modes.
        if (!\in_array($options->mode, ['xml', 'html'])) {
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
            'buttons' => $buttonsStr,
        ];

        return LayoutHelper::render('editors.codemirror.codemirror', $displayData, JPATH_PLUGINS . '/editors/codemirror/layouts');
    }
}
