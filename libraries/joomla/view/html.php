<?php
/**
 * @package     Joomla.Platform
 * @subpackage  View
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

jimport('joomla.filesystem.path');

/**
 * Joomla Platform HTML View Class
 *
 * @since       3.0.0
 * @deprecated  5.0 Use the default MVC library
 */
abstract class JViewHtml extends JViewBase
{
	/**
	 * The view layout.
	 *
	 * @var    string
	 * @since  3.0.0
	 */
	protected $layout = 'default';

	/**
	 * The paths queue.
	 *
	 * @var    SplPriorityQueue
	 * @since  3.0.0
	 */
	protected $paths;

	/**
	 * Method to instantiate the view.
	 *
	 * @param   JModel            $model  The model object.
	 * @param   SplPriorityQueue  $paths  The paths queue.
	 *
	 * @since   3.0.0
	 */
	public function __construct(JModel $model, SplPriorityQueue $paths = null)
	{
		parent::__construct($model);

		// Setup dependencies.
		$this->paths = isset($paths) ? $paths : $this->loadPaths();
	}

	/**
	 * Magic toString method that is a proxy for the render method.
	 *
	 * @return  string
	 *
	 * @since   3.0.0
	 */
	public function __toString()
	{
		return $this->render();
	}

	/**
	 * Method to escape output.
	 *
	 * @param   string  $output  The output to escape.
	 *
	 * @return  string  The escaped output.
	 *
	 * @note the ENT_COMPAT flag will be replaced by ENT_QUOTES in Joomla 4.0 to also escape single quotes
	 *
	 * @see     JView::escape()
	 * @since   3.0.0
	 */
	public function escape($output)
	{
		// Escape the output.
		return htmlspecialchars($output, ENT_COMPAT, 'UTF-8');
	}

	/**
	 * Method to get the view layout.
	 *
	 * @return  string  The layout name.
	 *
	 * @since   3.0.0
	 */
	public function getLayout()
	{
		return $this->layout;
	}

	/**
	 * Method to get the layout path.
	 *
	 * @param   string  $layout  The layout name.
	 *
	 * @return  mixed  The layout file name if found, false otherwise.
	 *
	 * @since   3.0.0
	 */
	public function getPath($layout)
	{
		// Get the layout file name.
		$file = JPath::clean($layout . '.php');

		// Find the layout file path.
		$path = JPath::find(clone $this->paths, $file);

		return $path;
	}

	/**
	 * Method to get the view paths.
	 *
	 * @return  SplPriorityQueue  The paths queue.
	 *
	 * @since   3.0.0
	 */
	public function getPaths()
	{
		return $this->paths;
	}

	/**
	 * Method to render the view.
	 *
	 * @return  string  The rendered view.
	 *
	 * @since   3.0.0
	 * @throws  RuntimeException
	 */
	public function render()
	{
		// Get the layout path.
		$path = $this->getPath($this->getLayout());

		// Check if the layout path was found.
		if (!$path)
		{
			throw new RuntimeException('Layout Path Not Found');
		}

		// Start an output buffer.
		ob_start();

		// Load the layout.
		include $path;

		// Get the layout contents.
		$output = ob_get_clean();

		return $output;
	}

	/**
	 * Method to set the view layout.
	 *
	 * @param   string  $layout  The layout name.
	 *
	 * @return  JViewHtml  Method supports chaining.
	 *
	 * @since   3.0.0
	 */
	public function setLayout($layout)
	{
		$this->layout = $layout;

		return $this;
	}

	/**
	 * Method to set the view paths.
	 *
	 * @param   SplPriorityQueue  $paths  The paths queue.
	 *
	 * @return  JViewHtml  Method supports chaining.
	 *
	 * @since   3.0.0
	 */
	public function setPaths(SplPriorityQueue $paths)
	{
		$this->paths = $paths;

		return $this;
	}

	/**
	 * Method to load the paths queue.
	 *
	 * @return  SplPriorityQueue  The paths queue.
	 *
	 * @since   3.0.0
	 */
	protected function loadPaths()
	{
		return new SplPriorityQueue;
	}
}
