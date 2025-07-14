<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2005 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Editor;

use Joomla\CMS\Editor\Button\ButtonsRegistry;
use Joomla\CMS\Factory;
use Joomla\CMS\Filter\InputFilter;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Event\AbstractEvent;
use Joomla\Event\DispatcherAwareInterface;
use Joomla\Event\DispatcherAwareTrait;
use Joomla\Event\DispatcherInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Editor class to handle WYSIWYG editors
 *
 * @since  1.5
 */
class Editor implements DispatcherAwareInterface
{
    use DispatcherAwareTrait;

    /**
     * Editor Plugin object
     *
     * @var    object
     * @since  1.5
     *
     * @deprecated  Should use Provider instance
     */
    protected $_editor = null;

    /**
     * Captcha Provider instance
     *
     * @var    EditorProviderInterface
     * @since  5.0.0
     */
    private $provider;

    /**
     * Editor Plugin name
     *
     * @var    string
     * @since  1.5
     */
    protected $_name = null;

    /**
     * Object asset
     *
     * @var    string
     * @since  1.6
     */
    protected $asset = null;

    /**
     * Object author
     *
     * @var    string
     * @since  1.6
     */
    protected $author = null;

    /**
     * Editor instances container.
     *
     * @var    Editor[]
     * @since  2.5
     */
    protected static $instances = [];

    /**
     * Constructor
     *
     * @param   string                $editor      The editor name
     * @param   ?DispatcherInterface  $dispatcher  The event dispatcher we're going to use
     * @param   ?EditorsRegistry      $registry    The editors registry
     *
     * @since  1.5
     */
    public function __construct(string $editor = 'none', ?DispatcherInterface $dispatcher = null, ?EditorsRegistry $registry = null)
    {
        $this->_name = $editor;

        /** @var  EditorsRegistry  $registry */
        $registry ??= Factory::getContainer()->get(EditorsRegistry::class);

        if ($registry->has($editor)) {
            $this->provider = $registry->get($editor);
        } else {
            // Fallback to legacy editor logic
            @trigger_error(
                '6.0 Discovering an editor "' . $this->_name . '" outside of EditorsRegistry is deprecated.',
                \E_USER_DEPRECATED
            );

            // Set the dispatcher
            if (!\is_object($dispatcher)) {
                $dispatcher = Factory::getContainer()->get('dispatcher');
            }

            $this->setDispatcher($dispatcher);

            // Register the getButtons event
            $this->getDispatcher()->addListener(
                'getButtons',
                function (AbstractEvent $event) {
                    @trigger_error(
                        '6.0 Use Button "getButtons" event is deprecated,  buttons should be set up onEditorButtonsSetup event.',
                        \E_USER_DEPRECATED
                    );

                    $event['result'] = (array)$this->getButtons(
                        $event->getArgument('editor', null),
                        $event->getArgument('buttons', null)
                    );
                }
            );
        }
    }

    /**
     * Returns the global Editor object, only creating it
     * if it doesn't already exist.
     *
     * @param   string  $editor  The editor to use.
     *
     * @return  Editor The Editor object.
     *
     * @since   1.5
     */
    public static function getInstance($editor = 'none')
    {
        $signature = $editor;

        if (empty(self::$instances[$signature])) {
            self::$instances[$signature] = new static($editor);
        }

        return self::$instances[$signature];
    }

    /**
     * Initialise the editor
     *
     * @return  void
     *
     * @since   1.5
     *
     * @deprecated  6.0 Without replacement
     */
    public function initialise()
    {
        if ($this->provider) {
            return;
        }

        // Check if editor is already loaded
        if ($this->_editor === null) {
            return;
        }

        @trigger_error('6.0 Method onInit() for Editor instance is deprecated, without replacement.', \E_USER_DEPRECATED);

        if (method_exists($this->_editor, 'onInit')) {
            \call_user_func([$this->_editor, 'onInit']);
        }
    }

    /**
     * Display the editor area.
     *
     * @param   string   $name     The control name.
     * @param   string   $html     The contents of the text area.
     * @param   string   $width    The width of the text area (px or %).
     * @param   string   $height   The height of the text area (px or %).
     * @param   integer  $col      The number of columns for the textarea.
     * @param   integer  $row      The number of rows for the textarea.
     * @param   boolean  $buttons  True and the editor buttons will be displayed.
     * @param   string   $id       An optional ID for the textarea (note: since 1.6). If not supplied the name is used.
     * @param   string   $asset    The object asset
     * @param   object   $author   The author.
     * @param   array    $params   Associative array of editor parameters.
     *
     * @return  string
     *
     * @since   1.5
     */
    public function display($name, $html, $width, $height, $col, $row, $buttons = true, $id = null, $asset = null, $author = null, $params = [])
    {
        if ($this->provider) {
            $params['buttons'] ??= $buttons;
            $params['asset']   ??= $asset;
            $params['author']  ??= $author;
            $content           = $html ?? '';

            return $this->provider->display($name, $content, [
                'width'  => $width,
                'height' => $height,
                'col'    => $col,
                'row'    => $row,
                'id'     => $id,
            ], $params);
        }

        $this->asset  = $asset;
        $this->author = $author;
        $this->_loadEditor($params);

        // Check whether editor is already loaded
        if ($this->_editor === null) {
            Factory::getApplication()->enqueueMessage(Text::_('JLIB_NO_EDITOR_PLUGIN_PUBLISHED'), 'danger');

            return '';
        }

        // Make sure editors api is loaded
        Factory::getApplication()->getDocument()->getWebAssetManager()->useScript('editors');

        // Backwards compatibility. Width and height should be passed without a semicolon from now on.
        // If editor plugins need a unit like "px" for CSS styling, they need to take care of that
        $width  = str_replace(';', '', $width);
        $height = str_replace(';', '', $height);

        $args = [
            'name'    => $name,
            'content' => $html,
            'width'   => $width,
            'height'  => $height,
            'col'     => $col,
            'row'     => $row,
            'buttons' => $buttons,
            'id'      => ($id ?: $name),
            'asset'   => $asset,
            'author'  => $author,
            'params'  => $params,
        ];

        return \call_user_func_array([$this->_editor, 'onDisplay'], $args);
    }

    /**
     * Get the editor extended buttons (usually from plugins)
     *
     * @param   string  $editor   The ID of the editor.
     * @param   mixed   $buttons  Can be boolean or array, if boolean defines if the buttons are
     *                            displayed, if array defines a list of buttons not to show.
     *
     * @return  array
     *
     * @since   1.5
     *
     */
    public function getButtons($editor, $buttons = true)
    {
        if ($this->provider) {
            return $this->provider->getButtons($buttons, ['editorId' => $editor]);
        }

        if ($buttons === false) {
            return [];
        }

        $loadAll = false;

        if ($buttons === true || $buttons === []) {
            $buttons = [];
            $loadAll = true;
        }

        // Retrieve buttons for legacy editor
        $result  = [];
        $btnsReg = new ButtonsRegistry();
        $btnsReg->setDispatcher($this->getDispatcher())->initRegistry([
            'editorType'      => $this->_name,
            'disabledButtons' => $buttons,
            'editorId'        => $editor,
            'asset'           => (int) $this->asset,
            'author'          => (int) $this->author,
        ]);

        // Go through all and leave only allowed buttons
        foreach ($btnsReg->getAll() as $button) {
            $btnName = $button->getButtonName();

            if (!$loadAll && \in_array($btnName, $buttons)) {
                continue;
            }

            $result[] = $button;
        }

        return $result;
    }

    /**
     * Load the editor
     *
     * @param   array  $config  Associative array of editor config parameters
     *
     * @return  mixed
     *
     * @since   1.5
     *
     * @deprecated  6.0 Should use EditorRegistry
     */
    protected function _loadEditor($config = [])
    {
        // Check whether editor is already loaded
        if ($this->_editor !== null) {
            return false;
        }

        @trigger_error('6.0 Editor "' . $this->_name . '" instance should be set up onEditorSetup event.', \E_USER_DEPRECATED);

        // Build the path to the needed editor plugin
        $name = InputFilter::getInstance()->clean($this->_name, 'cmd');

        // Boot the editor plugin
        $this->_editor = Factory::getApplication()->bootPlugin($name, 'editors');

        // Check if the editor can be loaded
        if (!$this->_editor) {
            Log::add(Text::_('JLIB_HTML_EDITOR_CANNOT_LOAD'), Log::WARNING, 'jerror');

            return false;
        }

        $this->_editor->params->loadArray($config);

        $this->initialise();
        PluginHelper::importPlugin('editors-xtd');

        return true;
    }
}
