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
 * Describes a logger-aware instance
 */
interface LoggerAwareInterface
{
	/**
	 * Sets a logger instance on the object
	 *
	 * @param   LoggerInterface  $logger
	 *
	 * @return null
	 */
	public function setLogger(LoggerInterface $logger);
}
