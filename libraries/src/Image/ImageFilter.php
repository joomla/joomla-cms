<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Image;

\defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Log\DelegatingPsrLogger;
use Joomla\CMS\Log\Log;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;

/**
 * Class to manipulate an image.
 *
 * @since  2.5.0
 */
abstract class ImageFilter implements LoggerAwareInterface
{
	use LoggerAwareTrait;

	/**
	 * @var    resource  The image resource handle.
	 * @since  2.5.0
	 */
	protected $handle;

	/**
	 * Class constructor.
	 *
	 * @param   resource  $handle  The image resource on which to apply the filter.
	 *
	 * @since   2.5.0
	 * @throws  \InvalidArgumentException
	 * @throws  \RuntimeException
	 */
	public function __construct($handle)
	{
		// Verify that image filter support for PHP is available.
		if (!function_exists('imagefilter'))
		{
			$this->getLogger()->error('The imagefilter function for PHP is not available.');

			throw new \RuntimeException('The imagefilter function for PHP is not available.');
		}

		// Make sure the file handle is valid.
		if (!is_resource($handle) || (get_resource_type($handle) != 'gd'))
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
	 * @since   2.5.0
	 */
	public function getLogger()
	{
		// If a logger hasn't been set, use DelegatingPsrLogger
		if (!($this->logger instanceof LoggerInterface))
		{
			$this->logger = Log::createDelegatedLogger();
		}

		return $this->logger;
	}

	/**
	 * Method to apply a filter to an image resource.
	 *
	 * @param   array  $options  An array of options for the filter.
	 *
	 * @return  void
	 *
	 * @since   2.5.0
	 */
	abstract public function execute(array $options = []);
}
