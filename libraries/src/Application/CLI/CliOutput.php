<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Application\CLI;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Application\CLI\Output\Processor\ProcessorInterface;

/**
 * Base class defining a command line output handler
 *
 * @since       __DEPLOY_VERSION__
 * @deprecated  5.0  Use the `joomla/console` package instead
 */
abstract class CliOutput
{
	/**
	 * Output processing object
	 *
	 * @var    ProcessorInterface
	 * @since  __DEPLOY_VERSION__
	 */
	protected $processor;

	/**
	 * Constructor
	 *
	 * @param   ProcessorInterface  $processor  The output processor.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function __construct(ProcessorInterface $processor = null)
	{
		$this->setProcessor($processor ?: new Output\Processor\ColorProcessor);
	}

	/**
	 * Set a processor
	 *
	 * @param   ProcessorInterface  $processor  The output processor.
	 *
	 * @return  $this
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function setProcessor(ProcessorInterface $processor)
	{
		$this->processor = $processor;

		return $this;
	}

	/**
	 * Get a processor
	 *
	 * @return  ProcessorInterface
	 *
	 * @since   __DEPLOY_VERSION__
	 * @throws  \RuntimeException
	 */
	public function getProcessor()
	{
		if ($this->processor)
		{
			return $this->processor;
		}

		throw new \RuntimeException('A ProcessorInterface object has not been set.');
	}

	/**
	 * Write a string to an output handler.
	 *
	 * @param   string   $text  The text to display.
	 * @param   boolean  $nl    True (default) to append a new line at the end of the output string.
	 *
	 * @return  $this
	 *
	 * @since   __DEPLOY_VERSION__
	 * @codeCoverageIgnore
	 */
	abstract public function out($text = '', $nl = true);
}
