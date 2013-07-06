<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

/**
 * Base class for rendering a display layout
 * loaded from from a layout file
 *
 * @package     Joomla.Libraries
 * @subpackage  Layout
 * @see         http://docs.joomla.org/Sharing_layouts_across_views_or_extensions_with_JLayout
 * @since       3.0
 */
class JLayoutFile extends JLayoutBase
{
	/**
	 * @var    string  Dot separated path to the layout file, relative to base path
	 * @since  3.0
	 */
	protected $layoutId = '';

	/**
	 * @var    string  Base path to use when loading layout files
	 * @since  3.0
	 */
	protected $basePath = null;

	/**
	 * @var    string  Full path to actual layout files, after possible template override check
	 * @since  3.0.3
	 */
	protected $fullPath = null;

	/**
	 * Paths to search for layouts
	 *
	 * @var  array
	 */
	protected $includePaths = array();

	/**
	 * Method to instantiate the file-based layout.
	 *
	 * @param   string  $layoutId  Dot separated path to the layout file, relative to base path
	 * @param   string  $basePath  Base path to use when loading layout files
	 * @param   mixed   $options   Optional custom options to load. JRegistry or array format
	 *
	 * @since   3.0
	 */
	public function __construct($layoutId, $basePath = null, $options = null)
	{
		// Initialise / Load options
		$this->bindOptions($options);

		// Main properties
		$this->setLayout($layoutId);
		$this->basePath = $basePath;

		// Init Enviroment
		$this->setComponent($this->options->get('component', 'auto'));
		$this->setClient($this->options->get('client', 'auto'));
	}

	/**
	 * Method to render the layout.
	 *
	 * @param   object  $displayData  Object which properties are used inside the layout file to build displayed output
	 *
	 * @return  string  The necessary HTML to display the layout
	 *
	 * @since   3.0
	 */
	public function render($displayData)
	{
		if ($this->options->get('debug', false))
		{
			echo $this->debugInfo();
		}

		$layoutOutput = '';

		// Check possible overrides, and build the full path to layout file
		$path = $this->getPath();

		// If there exists such a layout file, include it and collect its output
		if (!empty($path))
		{
			ob_start();
			include $path;
			$layoutOutput = ob_get_contents();
			ob_end_clean();
		}

		return $layoutOutput;
	}

	/**
	 * Method to finds the full real file path, checking possible overrides
	 *
	 * @return  string  The full path to the layout file
	 *
	 * @since   3.0
	 */
	protected function getPath()
	{
		JLoader::import('joomla.filesystem.path');

		if (is_null($this->fullPath) && !empty($this->layoutId))
		{
			// Refresh paths
			$this->refreshIncludePaths();

			$rawPath  = str_replace('.', '/', $this->layoutId) . '.php';

			$this->fullPath = JPath::find($this->includePaths, $rawPath);
		}

		return $this->fullPath;
	}

	/**
	 * Add one path to include in layout search. Proxy of addIncludePaths()
	 *
	 * @param   string  $path  The path to search for layouts
	 *
	 * @return  void
	 */
	public function addIncludePath($path)
	{
		$this->addIncludePaths($path);
	}

	/**
	 * Add one or more paths to include in layout search
	 *
	 * @param   string  $paths  The path or array of paths to search for layouts
	 *
	 * @return  void
	 */
	public function addIncludePaths($paths)
	{
		if (!empty($paths))
		{
			if (is_array($paths))
			{
				$this->includePaths = array_unique(array_merge($paths, $this->includePaths));
			}
			else
			{
				array_unshift($this->includePaths, $paths);
			}
		}
	}

	/**
	 * Dirty function to debug layout/path errors
	 *
	 * @return  void
	 */
	public function debugInfo()
	{
		$rawPath  = str_replace('.', '/', $this->layoutId) . '.php';

		$html = "<pre>";
		$html .= '<strong>Layout:</strong> ' . $this->layoutId . '<br />';
		$html .= '<strong>RAW Layout path:</strong> ' . $rawPath . '<br>';
		$html .= '<strong>includePaths:</strong> ';
		$html .= print_r($this->includePaths, true);
		$html .= '<strong>Checking paths:</strong> <br />';

		if ($this->includePaths)
		{
			foreach ($this->includePaths as $path)
			{
				$file = $path . '/' . $rawPath;

				if (!file_exists($file))
				{
					$html .= 'NOT exists: ' . $file . '<br />';
				}
				else
				{
					$html .= '<strong>EXISTS: ' . $file . '</strong><br />';
					break;
				}
			}
		}

		$html .= "</pre>";

		return $html;
	}

	/**
	 * Validate that the active component is valid
	 *
	 * @param   string  $option  URL Option of the component. Example: com_content
	 *
	 * @return  boolean
	 */
	protected function validComponent($option = null)
	{
		// By default we will validate the active component
		$component = ($option !== null) ? $option : $this->options->get('component', null);

		if (!empty($component))
		{
			// Valid option format
			if (substr_count($component, 'com_'))
			{
				// Latest check: component exists and is enabled
				return JComponentHelper::isEnabled($component);
			}
		}

		return false;
	}

	/**
	 * Method to change the component where search for layouts
	 *
	 * @param   string  $option  URL Option of the component. Example: com_content
	 *
	 * @return  mixed            Component option string | null for none
	 */
	public function setComponent($option)
	{
		$component = null;

		switch ((string) $option)
		{
			case 'none':
				$component = null;
				break;

			case 'auto':
				if (defined('JPATH_COMPONENT'))
				{
					$parts = explode('/', JPATH_COMPONENT);
					$component = end($parts);
				}
				break;

			default:
				$component = $option;
				break;
		}

		// Extra checks
		if (!$this->validComponent($component))
		{
			$component = null;
		}

		$this->options->set('component', $component);

		// Refresh include paths
		$this->refreshIncludePaths();
	}

	/**
	 * Function to initialise the application client
	 *
	 * @param   mixed  $client  Frontend: 'site' or 0 | Backend: 'admin' or 1
	 *
	 * @return  void
	 */
	public function setClient($client)
	{
		// Force string conversion to avoid unexpected states
		switch ((string) $client)
		{
			case 'site':
			case '0':
				$client = 0;
				break;

			case 'admin':
			case '1':
				$client = 1;
				break;

			default:
				$client = (int) JFactory::getApplication()->isAdmin();
				break;
		}

		$this->options->set('client', $client);

		// Refresh include paths
		$this->refreshIncludePaths();
	}

	/**
	 * Change the layout
	 *
	 * @param   string  $layoutId  Layout to render
	 *
	 * @return  void
	 */
	public function setLayout($layoutId)
	{
		$this->layoutId = $layoutId;
		$this->fullPath = null;
	}

	/**
	 * Refresh the list of include paths
	 *
	 * @return  void
	 */
	protected function refreshIncludePaths()
	{
		// Reset includePaths
		$this->includePaths = array();

		// (1 - lower priority) Frontend base layouts
		$this->addIncludePaths(JPATH_ROOT . '/layouts');

		// (2) Standard Joomla! layouts overriden
		$this->addIncludePaths(JPATH_THEMES . '/' . JFactory::getApplication()->getTemplate() . '/html/layouts');

		// Component layouts & overrides if exist
		$component = $this->options->get('component', null);

		if (!empty($component))
		{
			// (3) Component path
			if ($this->options->get('client') == 0)
			{
				$this->addIncludePaths(JPATH_SITE . '/components/' . $component . '/layouts');
			}
			else
			{
				$this->addIncludePaths(JPATH_ADMINISTRATOR . '/components/' . $component . '/layouts');
			}

			// (4) Component template overrides path
			$this->addIncludePath(JPATH_THEMES . '/' . JFactory::getApplication()->getTemplate() . '/html/layouts/' . $component);
		}

		// (5 - highest priority) Received a custom high priority path ?
		if (!is_null($this->basePath))
		{
			$this->addIncludePath(rtrim($this->basePath, DIRECTORY_SEPARATOR));
		}
	}

	/**
	 * Change the debug mode
	 *
	 * @param   boolean  $debug  Enable / Disable debug
	 *
	 * @return  void
	 */
	public function setDebug($debug)
	{
		$this->options->set('debug', (boolean) $debug);
	}

	/**
	 * Render a layout with the same include paths & options
	 *
	 * @param   object  $layoutId     Object which properties are used inside the layout file to build displayed output
	 * @param   mixed   $displayData  Data to be rendered
	 *
	 * @return  string  The necessary HTML to display the layout
	 *
	 * @since   3.0
	 */
	public function sublayout($layoutId, $displayData)
	{
		// Sublayouts are searched in a subfolder with the name of the current layout
		if (!empty($this->layoutId))
		{
			$layoutId = $this->layoutId . '.' . $layoutId;
		}

		$sublayout = new static($layoutId, $this->basePath, $this->options);
		$sublayout->includePaths = $this->includePaths;

		return $sublayout->render($displayData);
	}
}
