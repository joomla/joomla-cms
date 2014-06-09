<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;

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
	 * @var    array
	 * @since  3.2
	 */
	protected $includePaths = array();

	/**
	 * Data to display
	 *
	 * @var    array
	 * @since  3.3
	 */
	protected $data = array();

	/**
	 * Have includePaths been already prefixed?
	 *
	 * @var    boolean
	 * @since  3.3
	 */
	protected $isPrefixed = false;

	/**
	 * Timers for stats/debug
	 *
	 * @var    array
	 * @since  3.3
	 */
	protected $timers = array();

	/**
	 * Cache to avoid duplicated file searches
	 *
	 * @var    array
	 * @since  3.3
	 */
	protected static $cache = array();

	/**
	 * Statistics
	 *
	 * @var    array
	 * @since  3.3
	 */
	protected static $stats = array(
		'fileSearches'        => 0,
		'fileSearchesCached'  => 0,
		'fileSearchesSkipped' => 0,
		'times'               => array(
			'fileSearching'   => 0,
			'fileRendering'   => 0
		),
		'layoutsRendered'     => array()
	);

	/**
	 * Method to instantiate the file-based layout.
	 *
	 * @param   string  $layoutId  Dot separated path to the layout file, relative to base path
	 * @param   string  $basePath  Base path to use when loading layout files
	 * @param   mixed   $options   Optional custom options to load. JRegistry or array format [@since 3.2]
	 *
	 * @since   3.0
	 */
	public function __construct($layoutId, $basePath = null, $options = null)
	{
		// Initialise / Load options
		$this->setOptions($options);

		// Main properties
		$this->setLayout($layoutId);
		$this->basePath = $basePath;

		// Init Enviroment
		$this->setComponent($this->options->get('component', 'auto'));
		$this->setClient($this->options->get('client', 'auto'));

		$suffixes = $this->options->get('suffixes', 'none');
		$this->setSuffixes($suffixes);

		$prefixes = $this->options->get('prefixes', 'none');
		$this->setPrefixes($prefixes);
	}

	/**
	 * Method to assign a data property
	 *
	 * @param   string  $property  Property to set
	 * @param   mixed   $value     Value to assign to the property
	 *
	 * @return  JLayoutFile        Return self instance for chaining
	 *
	 * @since   3.3
	 */
	public function assign($property, $value)
	{
		$this->data[(string) $property] = $value;

		return $this;
	}

	/**
	 * Method to render the layout.
	 *
	 * @param   object  $displayData  Object / array which properties are used inside the layout file to build displayed output
	 *
	 * @return  string  The necessary HTML to display the layout
	 *
	 * @since   3.0
	 */
	public function render($displayData)
	{
		$layoutOutput = '';

		// Check possible overrides, and build the full path to layout file
		if ($path = $this->getPath())
		{
			$this->startTimer('fileRendering');
			$this->startTimer($path);

			// If there exists such a layout file, include it and collect its output
			if (file_exists($path))
			{
				// Initialise counter for this layout
				if (!array_key_exists($path, static::$stats['layoutsRendered']))
				{
					static::$stats['layoutsRendered'][$path] = 0;
				}

				ob_start();
				include $path;
				$layoutOutput = ob_get_contents();
				ob_end_clean();

				$this->stopTimer($path);
				++static::$stats['layoutsRendered'][$path];
			}

			$this->stopTimer('fileRendering');
		}

		// Append the debung
		if ($this->options->get('debug', false))
		{
			$layoutOutput = $this->renderDebugMessages() . $layoutOutput;
		}

		return $layoutOutput;
	}

	/**
	 * Method to use a proper data structure (always array).
	 * This method will render also the properties assigned with assign()
	 *
	 * @param   array  $data  Optional array with data to render.
	 *
	 * @return  string        Rendered layout
	 *
	 * @since   3.3
	 */
	public function renderData($data = array())
	{
		$data = array_merge((array) $data, $this->data);

		return $this->render($data);
	}

	/**
	 * Get the list of include paths
	 *
	 * @return  array
	 *
	 * @since   3.3
	 */
	public function getIncludePaths()
	{
		return $this->includePaths;
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

		++static::$stats['fileSearches'];

		$this->startTimer('fileSearching');

		if (is_null($this->fullPath) && !empty($this->layoutId))
		{
			$prefixes = $this->options->get('prefixes', array());
			$suffixes = $this->options->get('suffixes', array());

			$hash = md5(
				json_encode(
					array(
						'paths' => $this->includePaths,
						'prefixes' => $prefixes,
						'suffixes' => $suffixes
					)
				)
			);

			if (!empty(static::$cache[$this->layoutId][$hash]))
			{
				++static::$stats['fileSearchesCached'];

				$this->stopTimer('fileSearching');

				$this->addDebugMessage('<strong>Cached path:</strong> ' . static::$cache[$this->layoutId][$hash]);

				return static::$cache[$this->layoutId][$hash];
			}

			$this->addDebugMessage('<strong>Layout:</strong> ' . $this->layoutId);

			if (empty($this->includePaths))
			{
				$this->loadDefaultIncludePaths();
			}

			// Refresh prefixes
			if ($prefixes && !$this->isPrefixed)
			{
				$this->addDebugMessage('<strong>Prefixes:</strong> ' . print_r($prefixes, true));
				$this->prefixIncludePaths();
			}

			$this->addDebugMessage('<strong>Include Paths:</strong> ' . print_r($this->includePaths, true));

			// Search for suffixed versions. Example: tags.j31.php
			if (!empty($suffixes))
			{
				$this->addDebugMessage('<strong>Suffixes:</strong> ' . print_r($suffixes, true));

				foreach ($suffixes as $suffix)
				{
					$rawPath  = str_replace('.', '/', $this->layoutId) . '.' . $suffix . '.php';
					$this->addDebugMessage('<strong>Searching layout for:</strong> ' . $rawPath);

					if ($this->fullPath = JPath::find($this->includePaths, $rawPath))
					{
						$this->addDebugMessage('<strong>Found layout:</strong> ' . $this->fullPath);

						$this->stopTimer('fileSearching');

						return static::$cache[$this->layoutId][$hash] = $this->fullPath;
					}
				}
			}

			// Standard version
			$rawPath  = str_replace('.', '/', $this->layoutId) . '.php';
			$this->addDebugMessage('<strong>Searching layout for:</strong> ' . $rawPath);

			$this->fullPath = JPath::find($this->includePaths, $rawPath);

			if ($this->fullPath = JPath::find($this->includePaths, $rawPath))
			{
				$this->addDebugMessage('<strong>Found layout:</strong> ' . $this->fullPath);
			}
			else
			{
				$this->addDebugMessage('<strong>Unable to find layout: </strong> ' . $this->layoutId);
			}

			$this->stopTimer('fileSearching');

			return static::$cache[$this->layoutId][$hash] = $this->fullPath;
		}

		++static::$stats['fileSearchesSkipped'];

		return $this->fullPath;
	}

	/**
	 * Get use statistics
	 *
	 * @return  array
	 *
	 * @since   3.3
	 */
	public static function getStats()
	{
		return static::$stats;
	}

	/**
	 * Add one path to include in layout search. Proxy of addIncludePaths()
	 *
	 * @param   string  $path  The path to search for layouts
	 *
	 * @return  JLayoutFile    Self instance for chaining
	 *
	 * @since   3.2
	 */
	public function addIncludePath($path)
	{
		$this->addIncludePaths($path);

		return $this;
	}

	/**
	 * Add one or more paths to include in layout search
	 *
	 * @param   string  $paths  The path or array of paths to search for layouts
	 *
	 * @return  JLayoutFile     Self instance for chaining
	 *
	 * @since   3.2
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

		return $this;
	}

	/**
	 * Get the list of default includePaths
	 *
	 * @return  array
	 *
	 * @since   3.3
	 */
	public function getDefaultIncludePaths()
	{
		$defaultPaths = array();

		// (1 - highest priority) Received a custom high priority path ?
		if (!is_null($this->basePath))
		{
			$defaultPaths[] = rtrim($this->basePath, DIRECTORY_SEPARATOR);
		}

		// Component layouts & overrides if exist
		$component = $this->options->get('component', null);

		if (!empty($component))
		{
			// (2) Component template overrides path
			$defaultPaths[] = JPATH_THEMES . '/' . JFactory::getApplication()->getTemplate() . '/html/layouts/' . $component;

			// (3) Component path
			if ($this->options->get('client') == 0)
			{
				$defaultPaths[] = JPATH_SITE . '/components/' . $component . '/layouts';
			}
			else
			{
				$defaultPaths[] = JPATH_ADMINISTRATOR . '/components/' . $component . '/layouts';
			}
		}

		// (4) Standard Joomla! layouts overriden
		$defaultPaths[] = JPATH_THEMES . '/' . JFactory::getApplication()->getTemplate() . '/html/layouts';

		// (5 - lower priority) Frontend base layouts
		$defaultPaths[] = JPATH_ROOT . '/layouts';

		return $defaultPaths;
	}

	/**
	 * Load the default includePaths
	 *
	 * @return  JLayoutFile  Self instance for chaining
	 *
	 * @since   3.3
	 */
	public function loadDefaultIncludePaths()
	{
		$this->includePaths = $this->getDefaultIncludePaths();

		return $this;
	}

	/**
	 * Refresh the list of include paths
	 *
	 * @return  void
	 *
	 * @since   3.3
	 */
	protected function prefixIncludePaths()
	{
		$prefixedPaths = array();

		foreach ($this->includePaths as $includePath)
		{
			$prefixedPaths = array_merge($prefixedPaths, $this->prefixPath($includePath));
		}

		$this->includePaths = array_merge($prefixedPaths, $this->includePaths);

		$this->isPrefixed = true;
	}

	/**
	 * Method to prefix a path
	 *
	 * @param   string  $path  Path to prefix
	 *
	 * @return  array          Prefixed routes
	 *
	 * @since   3.3
	 */
	protected function prefixPath($path)
	{
		$paths = array();
		$prefixes = (array) $this->options->get('prefixes', array());

		if ($path && $prefixes && !in_array('none', $prefixes))
		{
			foreach ($prefixes as $prefix)
			{
				$paths[] = $path . '/' . $prefix;
			}
		}

		return $paths;
	}

	/**
	 * Refresh the list of include paths
	 *
	 * @return  JLayoutFile  Self instance for chaining
	 *
	 * @since   3.2
	 */
	public function refreshIncludePaths()
	{
		$this->resetIncludePaths();

		$this->includePaths = $this->getDefaultIncludePaths();

		return $this;
	}

	/**
	 * Remove one path from the layout search
	 *
	 * @param   string  $path  The path to remove from the layout search
	 *
	 * @return  JLayoutFile    Self instance for chaining
	 *
	 * @since   3.2
	 */
	public function removeIncludePath($path)
	{
		$this->removeIncludePaths($path);

		return $this;
	}

	/**
	 * Remove one or more paths to exclude in layout search
	 *
	 * @param   string  $paths  The path or array of paths to remove for the layout search
	 *
	 * @return  JLayoutFile     Self instance for chaining
	 *
	 * @since   3.2
	 */
	public function removeIncludePaths($paths)
	{
		if (!empty($paths))
		{
			$paths = (array) $paths;

			$this->includePaths = array_diff($this->includePaths, $paths);
		}

		return $this;
	}

	/**
	 * Render the list of debug messages
	 *
	 * @return  string  Output text/HTML code
	 *
	 * @since   3.3
	 */
	public function renderDebugMessages()
	{
		if ($this->options->get('debug', false))
		{
			return "<pre>" . parent::renderDebugMessages() . "</pre>";
		}

		return;
	}

	/**
	 * Empties the include paths list
	 *
	 * @return  JLayoutFile  Self instance for chaining
	 *
	 * @since   3.3
	 */
	public function resetIncludePaths()
	{
		$this->includePaths = array();

		// Reset options to re-calculate new path
		$this->resetDebugMessages();
		$this->isPrefixed = false;
		$this->fullPath = null;

		return $this;
	}

	/**
	 * Method to change the component where search for layouts
	 *
	 * @param   string  $option  URL Option of the component. Example: com_content
	 *
	 * @return  JLayoutFile      Self instance for chaining
	 *
	 * @since   3.2
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

		$this->refreshIncludePaths();

		return $this;
	}

	/**
	 * Function to initialise the application client
	 *
	 * @param   mixed  $client  Frontend: 'site' or 0 | Backend: 'admin' or 1
	 *
	 * @return  JLayoutFile     Self instance for chaining
	 *
	 * @since   3.2
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

		$this->refreshIncludePaths();

		return $this;
	}

	/**
	 * Method to set the include paths
	 *
	 * @param   mixed  $paths  Single path or array of paths
	 *
	 * @return  JLayoutFile    Self instance for chaining
	 *
	 * @since   3.3
	 */
	public function setIncludePaths($paths)
	{
		$this->includePaths = (array) $paths;

		// Refresh prefixes for the new include paths
		$this->isPrefixed = false;

		return $this;
	}

	/**
	 * Use prefixes to search layouts in different main folders
	 *
	 * @param   mixed  $prefixes  String with a single prefix or array or prefixes
	 *
	 * @return  JLayoutFile       Self instance for chaining
	 *
	 * @since   3.3
	 */
	public function setPrefixes($prefixes)
	{
		if (!empty($prefixes))
		{
			$this->options->set('prefixes', (array) $prefixes);

			// Refresh prefixes on render
			$this->resetDebugMessages();
			$this->isPrefixed = false;
			$this->fullPath = null;
		}

		return $this;
	}

	/**
	 * [setSuffixes description]
	 *
	 * @param   mixed  $suffixes  String with a single suffix or 'auto' | 'none' or array of suffixes
	 *
	 * @return  JLayoutFile       Self instance for chaining
	 *
	 * @since   3.3
	 */
	public function setSuffixes($suffixes)
	{
		if (!empty($suffixes))
		{
			if (is_array($suffixes))
			{
				$this->options->set('suffixes', $suffixes);
			}
			else
			{
				switch ($suffixes)
				{
					case 'autoversion':
						$cmsVersion = new JVersion;

						// Example j311
						$fullVersion = 'j' . str_replace('.', '', $cmsVersion->getShortVersion());

						// Create suffixes like array('j311', 'j31', 'j3')
						$suffixes = array(
							$fullVersion,
							substr($fullVersion, 0, 3),
							substr($fullVersion, 0, 2),
						);

						$this->options->set('suffixes', array_unique($suffixes));

						break;
					case 'autolanguage':
						$lang = JFactory::getLanguage();
						$langTag = $lang->getTag();
						$langParts = explode('-', $langTag);
						$suffixes = array($langTag, $langParts[0]);
						$suffixes[] = $lang->isRTL() ? 'rtl' : 'ltr';

						// Example: array('es-ES', 'es', 'ltr')
						$this->options->set('suffixes', $suffixes);
						break;
					case 'none':
						$this->options->set('suffixes', array());
						break;
					default:
						$this->options->set('suffixes', array($suffixes));
						break;
				}
			}

			// Force path recalculation
			$this->resetDebugMessages();
			$this->fullPath = null;
		}

		return $this;
	}

	/**
	 * Change the layout
	 *
	 * @param   string  $layoutId  Layout to render
	 *
	 * @return  JLayoutFile        Self instance for chaining
	 *
	 * @since   3.2
	 */
	public function setLayout($layoutId)
	{
		$this->layoutId = $layoutId;
		$this->fullPath = null;

		return $this;
	}

	/**
	 * Change the debug mode
	 *
	 * @param   boolean  $debug  Enable / Disable debug
	 *
	 * @return  JLayoutFile      Self instance for chaining
	 *
	 * @since   3.2
	 */
	public function setDebug($debug)
	{
		$this->options->set('debug', (boolean) $debug);

		return $this;
	}

	/**
	 * Start a timer to track time spent on different tasks
	 *
	 * @param   string  $type  Name of the timer
	 *
	 * @return  void
	 *
	 * @since   3.3
	 */
	private function startTimer($type)
	{
		if (isset($this->timers['times'][$type]))
		{
			$this->stopTimer($type);
		}

		return $this->timers['times'][$type] = microtime(true);
	}

	/**
	 * Stop an active timer
	 *
	 * @param   string  $type  [description]
	 *
	 * @return  float          Time elapsed with the timer active
	 *
	 * @since   3.3
	 */
	protected function stopTimer($type)
	{
		$timeElapsed = 0;

		// Initialise stats tracker if needed
		if (!isset(static::$stats['times'][$type]))
		{
			static::$stats['times'][$type] = 0;
		}

		if (!empty($this->timers['times'][$type]))
		{
			static::$stats['times'][$type] += microtime(true) - $this->timers['times'][$type];
			unset($this->timers['times'][$type]);
		}

		return $timeElapsed;
	}

	/**
	 * Render a layout with the same include paths & options
	 *
	 * @param   object  $layoutId     Object which properties are used inside the layout file to build displayed output
	 * @param   mixed   $displayData  Data to be rendered
	 *
	 * @return  string  The necessary HTML to display the layout
	 *
	 * @since   3.2
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

	/**
	 * Validate that the active component is valid
	 *
	 * @param   string  $option  URL Option of the component. Example: com_content
	 *
	 * @return  boolean
	 *
	 * @since   3.2
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
}
