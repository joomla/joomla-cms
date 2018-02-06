<?php
/**
 * Part of the Joomla Framework Crypt Package
 *
 * @copyright  Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Crypt\Exception;

/**
 * Exception representing an invalid Joomla\Crypt\Key type for a cipher
 *
 * @since  __DEPLOY_VERSION__
 */
class InvalidKeyTypeException extends \InvalidArgumentException
{
	/**
	 * InvalidKeyTypeException constructor.
	 *
	 * @param   string  $expectedKeyType  The expected key type.
	 * @param   string  $actualKeyType    The actual key type.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function __construct($expectedKeyType, $actualKeyType)
	{
		parent::__construct("Invalid key of type: $actualKeyType.  Expected $expectedKeyType.");
	}
}
