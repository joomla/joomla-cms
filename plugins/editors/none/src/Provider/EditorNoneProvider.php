<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2024 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\Editors\None\Provider;

use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Editor\AbstractEditorProvider;
use Joomla\Event\DispatcherInterface;
use Joomla\Registry\Registry;

/**
 * Editor provider class
 *
 * @since   5.2.0
 */
final class EditorNoneProvider extends AbstractEditorProvider
{
    /**
     * A Registry object holding the parameters for the plugin
     *
     * @var    Registry
     * @since  5.2.0
     */
    protected $params;

    /**
     * The application object
     *
     * @var    CMSApplicationInterface
     *
     * @since  5.2.0
     */
    protected $application;

    /**
     * Class constructor
     *
     * @param   Registry                 $params
     * @param   CMSApplicationInterface  $application
     * @param   DispatcherInterface      $dispatcher
     *
     * @since  5.2.0
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
     * @since   5.2.0
     */
    public function getName(): string
    {
        return 'none';
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
     * @since   5.2.0
     */
    public function display(string $name, string $content = '', array $attributes = [], array $params = []): string
    {
        $col      = $attributes['col'] ?? '';
        $row      = $attributes['row'] ?? '';
        $width    = $attributes['width'] ?? '';
        $height   = $attributes['height'] ?? '';
        $id       = $attributes['id'] ?? '';
        $buttons  = $params['buttons'] ?? true;
        $asset    = $params['asset'] ?? 0;
        $author   = $params['author'] ?? 0;
        $readonly = !empty($params['readonly']) ? ' readonly disabled' : '';

        if (!$id) {
            $id = $name;
        }

        // Only add "px" to width and height if they are not given as a percentage
        if (is_numeric($width)) {
            $width .= 'px';
        }

        if (is_numeric($height)) {
            $height .= 'px';
        }

        /** @var \Joomla\CMS\WebAsset\WebAssetManager $wa */
        $wa = $this->application->getDocument()->getWebAssetManager();

        // Add assets
        if (!$wa->assetExists('script', 'webcomponent.editor-none')) {
            $wa->registerScript(
                'webcomponent.editor-none',
                'plg_editors_none/joomla-editor-none.min.js',
                [],
                ['type' => 'module'],
                ['editors']
            );
        }

        $wa->useScript('webcomponent.editor-none');

        // Render buttons
        $buttonsStr = $this->displayButtons($buttons, ['asset' => $asset, 'author' => $author, 'editorId' => $id]);

        return '<joomla-editor-none>'
            . '<textarea name="' . $name . '" id="' . $id . '" cols="' . $col . '" rows="' . $row
            . '" style="width: ' . $width . '; height: ' . $height . ';"' . $readonly . '>' . $content . '</textarea>'
            . $buttonsStr
            . '</joomla-editor-none>';
    }
}
