<?php
/**
 * Akeeba Engine
 *
 * @package   akeebaengine
 * @copyright Copyright (c)2006-2021 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Psr\Log;

defined('AKEEBAENGINE') || die();

/**
 * Basic Implementation of LoggerAwareInterface.
 */
trait LoggerAwareTrait
{
	/** @var LoggerInterface */
	protected $logger;

	/**
	 * Sets a logger.
	 *
	 * @param   LoggerInterface  $logger
	 */
	public function setLogger(LoggerInterface $logger)
	{
		$this->logger = $logger;
	}
}
