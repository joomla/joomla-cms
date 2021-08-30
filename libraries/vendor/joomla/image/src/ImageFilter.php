<?php
/**
 * Part of the Joomla Framework Image Package
 *
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Image;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Class to manipulate an image.
 *
 * @since       1.0
 * @deprecated  The joomla/image package is deprecated
 */
abstract class ImageFilter implements LoggerAwareInterface
{
	/**
	 * @var    resource|\GdImage  The image resource handle.
	 * @since  1.0
	 */
	protected $handle;

	/**
	 * @var    LoggerInterface  Logger object
	 * @since  1.0
	 */
	protected $logger;

	/**
	 * Class constructor.
	 *
	 * @param   resource|\GdImage  $handle  The image resource on which to apply the filter.
	 *
	 * @since   1.0
	 * @throws  \InvalidArgumentException
	 * @throws  \RuntimeException
	 */
	public function __construct($handle)
	{
		// Verify that image filter support for PHP is available.
		if (!\function_exists('imagefilter'))
		{
			// @codeCoverageIgnoreStart
			$this->getLogger()->error('The imagefilter function for PHP is not available.');

			throw new \RuntimeException('The imagefilter function for PHP is not available.');

			// @codeCoverageIgnoreEnd
		}

		// Make sure the file handle is valid.
		if (!$this->isValidImage($handle))
		{
			$this->getLogger()->error('The image handle is invalid for the image filter.');

			throw new \InvalidArgumentException('The image handle is invalid for the image filter.');
		}

		$this->handle = $handle;
	}

	/**
	 * Get the logger.
	 *
	 * @return  LoggerInterface
	 *
	 * @since   1.0
	 */
	public function getLogger()
	{
		// If a logger hasn't been set, use NullLogger
		if (! ($this->logger instanceof LoggerInterface))
		{
			$this->logger = new NullLogger;
		}

		return $this->logger;
	}

	/**
	 * Sets a logger instance on the object
	 *
	 * @param   LoggerInterface  $logger  A PSR-3 compliant logger.
	 *
	 * @return  ImageFilter  This object for message chaining.
	 *
	 * @since   1.0
	 */
	public function setLogger(LoggerInterface $logger)
	{
		$this->logger = $logger;

		return $this;
	}

	/**
	 * Method to apply a filter to an image resource.
	 *
	 * @param   array  $options  An array of options for the filter.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	abstract public function execute(array $options = array());

	/**
	 * @param   mixed  $handle  A potential image handle
	 *
	 * @return  boolean
	 */
	private function isValidImage($handle)
	{
		// @todo Remove resource check, once PHP7 support is dropped.
		return (\is_resource($handle) && \get_resource_type($handle) === 'gd')
			   || (\is_object($handle) && $handle instanceof \GDImage);
	}
}
