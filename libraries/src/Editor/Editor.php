<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2005 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Editor;

use Joomla\CMS\Factory;
use Joomla\CMS\Filter\InputFilter;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Event\AbstractEvent;
use Joomla\Event\DispatcherAwareInterface;
use Joomla\Event\DispatcherAwareTrait;
use Joomla\Event\DispatcherInterface;
use Joomla\Registry\Registry;

// phpcs:disable PSR1.Files.SideEffects
\defined('JPATH_PLATFORM') or die;
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
     */
    protected $_editor = null;

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
     * @param   string               $editor      The editor name
     * @param   DispatcherInterface  $dispatcher  The event dispatcher we're going to use
     */
    public function __construct($editor = 'none', DispatcherInterface $dispatcher = null)
    {
        $this->_name = $editor;

        // Set the dispatcher
        if (!\is_object($dispatcher)) {
            $dispatcher = Factory::getContainer()->get('dispatcher');
        }

        $this->setDispatcher($dispatcher);

        // Register the getButtons event
        $this->getDispatcher()->addListener(
            'getButtons',
            function (AbstractEvent $event) {
                $event['result'] = (array) $this->getButtons(
                    $event->getArgument('editor', null),
                    $event->getArgument('buttons', null)
                );
            }
        );
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
        $signature = serialize($editor);

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
     */
    public function initialise()
    {
        // Check if editor is already loaded
        if ($this->_editor === null) {
            return;
        }

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
        $this->asset = $asset;
        $this->author = $author;
        $this->_loadEditor($params);

        // Check whether editor is already loaded
        if ($this->_editor === null) {
            Factory::getApplication()->enqueueMessage(Text::_('JLIB_NO_EDITOR_PLUGIN_PUBLISHED'), 'danger');

            return;
        }

        // Backwards compatibility. Width and height should be passed without a semicolon from now on.
        // If editor plugins need a unit like "px" for CSS styling, they need to take care of that
        $width = str_replace(';', '', $width);
        $height = str_replace(';', '', $height);

        $args['name'] = $name;
        $args['content'] = $html;
        $args['width'] = $width;
        $args['height'] = $height;
        $args['col'] = $col;
        $args['row'] = $row;
        $args['buttons'] = $buttons;
        $args['id'] = $id ?: $name;
        $args['asset'] = $asset;
        $args['author'] = $author;
        $args['params'] = $params;

        return \call_user_func_array([$this->_editor, 'onDisplay'], $args);
    }

    /**
     * Get the editor extended buttons (usually from plugins)
     *
     * @param   string  $editor   The name of the editor.
     * @param   mixed   $buttons  Can be boolean or array, if boolean defines if the buttons are
     *                            displayed, if array defines a list of buttons not to show.
     *
     * @return  array
     *
     * @since   1.5
     */
    public function getButtons($editor, $buttons = true)
    {
        $result = [];

        if (\is_bool($buttons) && !$buttons) {
            return $result;
        }

        // Get plugins
        $plugins = PluginHelper::getPlugin('editors-xtd');

        foreach ($plugins as $plugin) {
            if (\is_array($buttons) && \in_array($plugin->name, $buttons)) {
                continue;
            }

            PluginHelper::importPlugin('editors-xtd', $plugin->name, false);
            $className = 'PlgEditorsXtd' . $plugin->name;

            if (!class_exists($className)) {
                $className = 'PlgButton' . $plugin->name;
            }

            if (class_exists($className)) {
                $dispatcher = $this->getDispatcher();
                $plugin = new $className($dispatcher, (array) $plugin);
            }

            // Try to authenticate
            if (!method_exists($plugin, 'onDisplay')) {
                continue;
            }

            $button = $plugin->onDisplay($editor, $this->asset, $this->author);

            if (empty($button)) {
                continue;
            }

            if (\is_array($button)) {
                $result = array_merge($result, $button);
                continue;
            }

            $button->editor = $editor;

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
     */
    protected function _loadEditor($config = [])
    {
        // Check whether editor is already loaded
        if ($this->_editor !== null) {
            return false;
        }

        // Build the path to the needed editor plugin
        $name = InputFilter::getInstance()->clean($this->_name, 'cmd');
        $path = JPATH_PLUGINS . '/editors/' . $name . '/' . $name . '.php';

        if (!is_file($path)) {
            Log::add(Text::_('JLIB_HTML_EDITOR_CANNOT_LOAD'), Log::WARNING, 'jerror');

            return false;
        }

        // Require plugin file
        require_once $path;

        // Get the plugin
        $plugin = PluginHelper::getPlugin('editors', $this->_name);

        // If no plugin is published we get an empty array and there not so much to do with it
        if (empty($plugin)) {
            return false;
        }

        $params = new Registry($plugin->params);
        $params->loadArray($config);
        $plugin->params = $params;

        // Build editor plugin classname
        $name = 'PlgEditor' . $this->_name;

        $dispatcher = $this->getDispatcher();

        if ($this->_editor = new $name($dispatcher, (array) $plugin)) {
            // Load plugin parameters
            $this->initialise();
            PluginHelper::importPlugin('editors-xtd');
        }
    }
}
