<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Renderer
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use Joomla\Renderer\RendererInterface;

/**
 * JLayout class for rendering output.
 *
 * @since  3.4
 */
class JRendererJlayout implements RendererInterface
{
	/**
	 * The renderer default configuration parameters.
	 *
	 * @var    array
	 * @since  3.4
	 */
	protected $config = array();

	/**
	 * Public constructor
	 *
	 * @param  array            $config  An array of configuration options
	 *
	 * @since  3.4
	 */
	public function __construct(array $config = array())
	{
		// Insert the config.
		$this->config = $config;
	}

	/**
	 * Render and return compiled data.
	 *
	 * @param   string  $template  The template file name
	 * @param   array   $data      The data to pass to the template
	 *
	 * @return  string  Compiled data
	 *
	 * @since   3.4
	 */
	public function render($template, array $data = array())
	{
		return $this->getLayout($template)->render($data);
	}

	/**
	 * Gets a JLayoutFile object for a given template path.
	 *
	 * @param   string  $template  The template file name
	 *
	 * @return  JLayoutFile  The JLayoutFile object
	 *
	 * @since   3.4
	 */
	protected function getLayout($template)
	{
		$layout = new JLayoutFile($template);

		// If any paths are set in the config we'll replace the default ones
		if (isset($this->config['paths']) && $this->config['paths'] instanceof SplPriorityQueue)
		{
			$layout->setIncludePaths($this->convertQueueToArray($this->config['paths']));
		}

		return $layout;
	}

	/**
	 * Render a layout with the same include paths & options
	 *
	 * @param   SplPriorityQueue  $queue  The SPL Priority Queue Item
	 *
	 * @return  array  An array of paths ordered ordered by priority
	 *
	 * @since   3.3
	 */
	protected function convertQueueToArray(SplPriorityQueue $queue)
	{
		// Initialise the array
		$items = array();

		// If there are no paths registered return empty array
		if (!$queue->isEmpty())
		{
			$queue->top();

			// We start at the top and loop through each element of the Priority Queue
			// and then add this item to the array
			while ($queue->valid())
			{
				$items[] = $queue->current();
				$queue->next(); 
			}
		}

		return $items;
	}
}
