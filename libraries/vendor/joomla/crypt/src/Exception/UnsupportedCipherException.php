<?php
/**
 * Part of the Joomla Framework Crypt Package
 *
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Crypt\Exception;

/**
 * Exception representing an error encrypting data
 *
 * @since  __DEPLOY_VERSION__
 */
class UnsupportedCipherException extends \LogicException implements CryptExceptionInterface
{
	/**
	 * UnsupportedCipherException constructor.
	 *
	 * @param   string  $class  The class name of the unsupported cipher.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function __construct(string $class)
	{
		parent::__construct("The '$class' cipher is not supported in this environment.");
	}
}
