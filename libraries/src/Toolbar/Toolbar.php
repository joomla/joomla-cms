<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2006 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Toolbar;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Toolbar\Button\BasicButton;
use Joomla\CMS\Toolbar\Button\ConfirmButton;
use Joomla\CMS\Toolbar\Button\CustomButton;
use Joomla\CMS\Toolbar\Button\DropdownButton;
use Joomla\CMS\Toolbar\Button\HelpButton;
use Joomla\CMS\Toolbar\Button\InlinehelpButton;
use Joomla\CMS\Toolbar\Button\LinkButton;
use Joomla\CMS\Toolbar\Button\PopupButton;
use Joomla\CMS\Toolbar\Button\SeparatorButton;
use Joomla\CMS\Toolbar\Button\StandardButton;

// phpcs:disable PSR1.Files.SideEffects
\defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * ToolBar handler
 *
 * @method  StandardButton  standardButton(string $name = '', string $text = '', string $task = '')
 * @method  SeparatorButton  separatorButton(string $name = '', string $text = '', string $task = '')
 * @method  PopupButton  popupButton(string $name = '', string $text = '', string $task = '')
 * @method  LinkButton  linkButton(string $name = '', string $text = '', string $task = '')
 * @method  HelpButton  helpButton(string $name = '', string $text = '', string $task = '')
 * @method  InlinehelpButton  inlinehelpButton(string $name = '', string $text = '', string $task = '')
 * @method  CustomButton  customButton(string $name = '', string $text = '', string $task = '')
 * @method  ConfirmButton  confirmButton(string $name = '', string $text = '', string $task = '')
 * @method  BasicButton  basicButton(string $name = '', string $text = '', string $task = '')
 * @method  DropdownButton  dropdownButton(string $name = '', string $text = '', string $task = '')
 *
 * @since  1.5
 */
class Toolbar
{
    use CoreButtonsTrait;

    /**
     * Toolbar name
     *
     * @var    string
     * @since  1.5
     */
    protected $_name = '';

    /**
     * Toolbar array
     *
     * @var    array
     * @since  1.5
     */
    protected $_bar = [];

    /**
     * Directories, where button types can be stored.
     *
     * @var    array
     * @since  1.5
     */
    protected $_buttonPath = [];

    /**
     * Stores the singleton instances of various toolbar.
     *
     * @var    Toolbar[]
     * @since  2.5
     */
    protected static $instances = [];

    /**
     * Factory for creating Toolbar API objects
     *
     * @var    ToolbarFactoryInterface
     * @since  4.0.0
     */
    protected $factory;

    /**
     * Constructor
     *
     * @param   string                   $name     The toolbar name.
     * @param   ToolbarFactoryInterface  $factory  The toolbar factory.
     *
     * @since   1.5
     */
    public function __construct($name = 'toolbar', ToolbarFactoryInterface $factory = null)
    {
        $this->_name = $name;

        // At 5.0, require the factory to be injected
        if (!$factory) {
            @trigger_error(
                sprintf(
                    'As of Joomla! 5.0, a %1$s must be provided to a %2$s object when creating it.',
                    ToolbarFactoryInterface::class,
                    \get_class($this)
                ),
                E_USER_DEPRECATED
            );

            $factory = new ContainerAwareToolbarFactory();
            $factory->setContainer(Factory::getContainer());
        }

        $this->setFactory($factory);

        // Set base path to find buttons.
        $this->_buttonPath[] = __DIR__ . '/Button';
    }

    /**
     * Returns the global Toolbar object, only creating it if it doesn't already exist.
     *
     * @param   string  $name  The name of the toolbar.
     *
     * @return  Toolbar  The Toolbar object.
     *
     * @since       1.5
     * @deprecated  5.0 Use the ToolbarFactoryInterface instead
     *
     * @throws \Joomla\DI\Exception\KeyNotFoundException
     */
    public static function getInstance($name = 'toolbar')
    {
        if (empty(self::$instances[$name])) {
            self::$instances[$name] = Factory::getContainer()->get(ToolbarFactoryInterface::class)->createToolbar($name);
        }

        return self::$instances[$name];
    }

    /**
     * Set the factory instance
     *
     * @param   ToolbarFactoryInterface  $factory  The factory instance
     *
     * @return  $this
     *
     * @since   4.0.0
     */
    public function setFactory(ToolbarFactoryInterface $factory): self
    {
        $this->factory = $factory;

        return $this;
    }

    /**
     * Append a button to toolbar.
     *
     * @param   ToolbarButton  $button  The button instance.
     * @param   array          $args    The more arguments.
     *
     * @return  ToolbarButton|boolean  Return button instance to help chaining configure. If using legacy arguments
     *                                 returns true
     *
     * @since   1.5
     */
    public function appendButton($button, ...$args)
    {
        if ($button instanceof ToolbarButton) {
            $button->setParent($this);

            $this->_bar[] = $button;

            return $button;
        }

        // B/C
        array_unshift($args, $button);
        $this->_bar[] = $args;

        @trigger_error(
            sprintf(
                '%s::appendButton() should only accept %s instance in Joomla 5.0.',
                static::class,
                ToolbarButton::class
            ),
            E_USER_DEPRECATED
        );

        return true;
    }

    /**
     * Get the list of toolbar links.
     *
     * @return  array
     *
     * @since   1.6
     */
    public function getItems()
    {
        return $this->_bar;
    }

    /**
     * Set the button list.
     *
     * @param   ToolbarButton[]  $items  The button list array.
     *
     * @return  static
     *
     * @since   4.0.0
     */
    public function setItems(array $items): self
    {
        $this->_bar = $items;

        return $this;
    }

    /**
     * Get the name of the toolbar.
     *
     * @return  string
     *
     * @since   1.6
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * Prepend a button to toolbar.
     *
     * @param   ToolbarButton  $button  The button instance.
     * @param   array          $args    The more arguments.
     *
     * @return  ToolbarButton|boolean  Return button instance to help chaining configure. If using legacy arguments
     *                                 returns true
     *
     * @since   1.5
     */
    public function prependButton($button, ...$args)
    {
        if ($button instanceof ToolbarButton) {
            $button->setParent($this);

            array_unshift($this->_bar, $button);

            return $button;
        }

        // B/C
        array_unshift($args, $button);
        array_unshift($this->_bar, $args);

        @trigger_error(
            sprintf(
                '%s::prependButton() should only accept %s instance in Joomla 5.0.',
                static::class,
                ToolbarButton::class
            ),
            E_USER_DEPRECATED
        );

        return true;
    }

    /**
     * Render a toolbar.
     *
     * @param   array  $options  The options of toolbar.
     *
     * @return  string  HTML for the toolbar.
     *
     * @throws \Exception
     * @since   1.5
     */
    public function render(array $options = [])
    {
        $html = [];

        $isChild = !empty($options['is_child']);

        // Start toolbar div.
        if (!$isChild) {
            $layout = new FileLayout('joomla.toolbar.containeropen');

            $html[] = $layout->render(['id' => $this->_name]);
        }

        $len = count($this->_bar);

        // Render each button in the toolbar.
        foreach ($this->_bar as $i => $button) {
            if ($button instanceof ToolbarButton) {
                // Child dropdown only support new syntax
                $button->setOption('is_child', $isChild);
                $button->setOption('is_first_child', $i === 0);
                $button->setOption('is_last_child', $i === $len - 1);
                $html[] = $button->render();
            } else {
                // B/C
                $html[] = $this->renderButton($button);
            }
        }

        // End toolbar div.
        if (!$isChild) {
            $layout = new FileLayout('joomla.toolbar.containerclose');

            $html[] = $layout->render([]);
        }

        return implode('', $html);
    }

    /**
     * Render a button.
     *
     * @param   array  &$node  A toolbar node.
     *
     * @return  string
     *
     * @since   1.5
     * @throws  \Exception
     */
    public function renderButton(&$node)
    {
        // Get the button type.
        $type = $node[0];

        $button = $this->loadButtonType($type);

        // Check for error.
        if ($button === false) {
            throw new \UnexpectedValueException(Text::sprintf('JLIB_HTML_BUTTON_NOT_DEFINED', $type));
        }

        $button->setParent($this);

        return $button->render($node);
    }

    /**
     * Loads a button type.
     *
     * @param   string   $type  Button Type
     * @param   boolean  $new   False by default
     *
     * @return  false|ToolbarButton
     *
     * @since   1.5
     */
    public function loadButtonType($type, $new = false)
    {
        // For B/C, catch the exceptions thrown by the factory
        try {
            return $this->factory->createButton($this, $type);
        } catch (\InvalidArgumentException $e) {
            Log::add($e->getMessage(), Log::WARNING, 'jerror');

            return false;
        }
    }

    /**
     * Add a directory where Toolbar should search for button types in LIFO order.
     *
     * You may either pass a string or an array of directories.
     *
     * Toolbar will be searching for an element type in the same order you
     * added them. If the parameter type cannot be found in the custom folders,
     * it will look in libraries/joomla/html/toolbar/button.
     *
     * @param   mixed  $path  Directory or directories to search.
     *
     * @return  void
     *
     * @since       1.5
     * @deprecated  5.0  ToolbarButton classes should be autoloaded
     */
    public function addButtonPath($path)
    {
        @trigger_error(
            sprintf(
                'Registering lookup paths for toolbar buttons is deprecated and will be removed in Joomla 5.0.'
                . ' %1$s objects should be autoloaded or a custom %2$s implementation supporting path lookups provided.',
                ToolbarButton::class,
                ToolbarFactoryInterface::class
            ),
            E_USER_DEPRECATED
        );

        // Loop through the path directories.
        foreach ((array) $path as $dir) {
            // No surrounding spaces allowed!
            $dir = trim($dir);

            // Add trailing separators as needed.
            if (substr($dir, -1) !== DIRECTORY_SEPARATOR) {
                // Directory
                $dir .= DIRECTORY_SEPARATOR;
            }

            // Add to the top of the search dirs.
            array_unshift($this->_buttonPath, $dir);
        }
    }

    /**
     * Get the lookup paths for button objects
     *
     * @return  array
     *
     * @since   4.0.0
     * @deprecated  5.0  ToolbarButton classes should be autoloaded
     */
    public function getButtonPath(): array
    {
        @trigger_error(
            sprintf(
                'Lookup paths for %s objects is deprecated and will be removed in Joomla 5.0.',
                ToolbarButton::class
            ),
            E_USER_DEPRECATED
        );

        return $this->_buttonPath;
    }

    /**
     * Create child toolbar.
     *
     * @param   string  $name  The toolbar name.
     *
     * @return  static
     *
     * @since   4.0.0
     */
    public function createChild($name): self
    {
        return new static($name, $this->factory);
    }

    /**
     * Magic method proxy.
     *
     * @param   string  $name  The method name.
     * @param   array   $args  The method arguments.
     *
     * @return  ToolbarButton
     *
     * @throws  \Exception
     *
     * @since   4.0.0
     */
    public function __call($name, $args)
    {
        if (strtolower(substr($name, -6)) === 'button') {
            $type = substr($name, 0, -6);

            $button = $this->factory->createButton($this, $type);

            $button->name($args[0] ?? '')
                ->text($args[1] ?? '')
                ->task($args[2] ?? '');

            return $this->appendButton($button);
        }

        throw new \BadMethodCallException(
            sprintf(
                'Method %s() not found in class: %s',
                $name,
                static::class
            )
        );
    }
}
