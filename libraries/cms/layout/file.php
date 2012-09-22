<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

/**
 * Base class for rendering a display layout
 * loaded from from a layout file
 *
 * @package     Joomla.Libraries
 * @subpackage  Layout
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
	 * Method to instantiate the file-based layout.
	 *
	 * @param   string  $layoutId  Dot separated path to the layout file, relative to base path
	 * @param   string  $basePath  Base path to use when loading layout files
	 *
	 * @since   3.0
	 */
	public function __construct($layoutId, $basePath = null)
	{
		$this->layoutId = $layoutId;
		$this->basePath = is_null($basePath) ? JPATH_ROOT . '/layouts' : rtrim($basePath, DIRECTORY_SEPARATOR);
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
		static $fullPath = null;

		if (is_null($fullPath) && !empty($this->layoutId))
		{
			$rawPath  = str_replace('.', '/', $this->layoutId) . '.php';
			$fileName = basename($rawPath);
			$filePath = dirname($rawPath);

			$possiblePaths = array(
				JPATH_THEMES . '/' . JFactory::getApplication()->getTemplate() . '/html/layouts/' . $filePath, $this->basePath . '/' . $filePath
			);

			$fullPath = JPath::find($possiblePaths, $fileName);
		}

		return $fullPath;
	}
}
