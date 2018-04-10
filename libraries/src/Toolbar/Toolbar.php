<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Toolbar;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Layout\FileLayout;

/**
 * ToolBar handler
 *
 * @since  1.5
 */
class Toolbar
{
	/**
	 * Toolbar name
	 *
	 * @var    string
	 */
	protected $_name = array();

	/**
	 * Toolbar array
	 *
	 * @var    array
	 */
	protected $_bar = array();

	/**
	 * Loaded buttons
	 *
	 * @var    array
	 */
	protected $_buttons = array();

	/**
	 * Directories, where button types can be stored.
	 *
	 * @var    array
	 */
	protected $_buttonPath = array();

	/**
	 * Stores the singleton instances of various toolbar.
	 *
	 * @var    Toolbar
	 * @since  2.5
	 */
	protected static $instances = array();

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
		$this->_name   = $name;

		// At 5.0, require the factory to be injected
		if (!$factory)
		{
			\JLog::add(
				sprintf(
					'As of Joomla! 5.0, a %1$s must be provided to a %2$s object when creating it.',
					ToolbarFactoryInterface::class,
					get_class($this)
				),
				\JLog::WARNING,
				'deprecated'
			);

			$factory = new ContainerAwareToolbarFactory;
			$factory->setContainer(\JFactory::getContainer());
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
	 * @since   1.5
	 */
	public static function getInstance($name = 'toolbar')
	{
		if (empty(self::$instances[$name]))
		{
			self::$instances[$name] = \JFactory::getContainer()->get(ToolbarFactoryInterface::class)->createToolbar($name);
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
	 * Set a value
	 *
	 * @return  string  The set value.
	 *
	 * @since   1.5
	 */
	public function appendButton()
	{
		// Push button onto the end of the toolbar array.
		$btn          = func_get_args();
		$this->_bar[] = $btn;

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
	 * Get a value.
	 *
	 * @return  string
	 *
	 * @since   1.5
	 */
	public function prependButton()
	{
		// Insert button into the front of the toolbar array.
		$btn = func_get_args();
		array_unshift($this->_bar, $btn);

		return true;
	}

	/**
	 * Render a toolbar.
	 *
	 * @return  string  HTML for the toolbar.
	 *
	 * @since   1.5
	 */
	public function render()
	{
		$html = array();

		// Start toolbar div.
		$layout = new FileLayout('joomla.toolbar.containeropen');

		$html[] = $layout->render(array('id' => $this->_name));

		// Render each button in the toolbar.
		foreach ($this->_bar as $button)
		{
			$html[] = $this->renderButton($button);
		}

		// End toolbar div.
		$layout = new FileLayout('joomla.toolbar.containerclose');

		$html[] = $layout->render(array());

		return implode('', $html);
	}

	/**
	 * Render a button.
	 *
	 * @param   object  &$node  A toolbar node.
	 *
	 * @return  string
	 *
	 * @since   1.5
	 */
	public function renderButton(&$node)
	{
		// Get the button type.
		$type = $node[0];

		$button = $this->loadButtonType($type);

		// Check for error.
		if ($button === false)
		{
			return \JText::sprintf('JLIB_HTML_BUTTON_NOT_DEFINED', $type);
		}

		return $button->render($node);
	}

	/**
	 * Loads a button type.
	 *
	 * @param   string   $type  Button Type
	 * @param   boolean  $new   False by default
	 *
	 * @return  boolean
	 *
	 * @since   1.5
	 */
	public function loadButtonType($type, $new = false)
	{
		$signature = md5($type);

		if ($new === false && isset($this->_buttons[$signature]))
		{
			return $this->_buttons[$signature];
		}

		if (!class_exists('Joomla\\CMS\\Toolbar\\ToolbarButton'))
		{
			\JLog::add(\JText::_('JLIB_HTML_BUTTON_BASE_CLASS'), \JLog::WARNING, 'jerror');

			return false;
		}

		// For B/C, catch the exceptions thrown by the factory
		try
		{
			$this->_buttons[$signature] = $this->factory->createButton($this, $type);
		}
		catch (\InvalidArgumentException $e)
		{
			\JLog::add($e->getMessage(), \JLog::WARNING, 'jerror');

			return false;
		}

		return $this->_buttons[$signature];
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
		\JLog::add(
			sprintf(
				'Registering lookup paths for toolbar buttons is deprecated and will be removed in Joomla 5.0.'
				. ' %1$s objects should be autoloaded or a custom %2$s implementation supporting path lookups provided.',
				ToolbarButton::class,
				ToolbarFactoryInterface::class
			),
			\JLog::WARNING,
			'deprecated'
		);

		// Loop through the path directories.
		foreach ((array) $path as $dir)
		{
			// No surrounding spaces allowed!
			$dir = trim($dir);

			// Add trailing separators as needed.
			if (substr($dir, -1) !== DIRECTORY_SEPARATOR)
			{
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
		\JLog::add(
			sprintf(
				'Lookup paths for %s objects is deprecated and will be removed in Joomla 5.0.',
				ToolbarButton::class
			),
			\JLog::WARNING,
			'deprecated'
		);

		return $this->_buttonPath;
	}
}
