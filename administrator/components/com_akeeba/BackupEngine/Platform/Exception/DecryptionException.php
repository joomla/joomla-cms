<?php
/**
 * Akeeba Engine
 *
 * @package   akeebaengine
 * @copyright Copyright (c)2006-2021 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Engine\Platform\Exception;

defined('AKEEBAENGINE') || die();

use Akeeba\Engine\Platform;
use Exception;
use RuntimeException;

/**
 * Thrown when the settings cannot be decrypted, e.g. when the server no longer has encyrption enabled or the key has
 * changed.
 */
class DecryptionException extends RuntimeException
{
	public function __construct($message = null, $code = 500, Exception $previous = null)
	{
		if (empty($message))
		{
			$message = Platform::getInstance()->translate('COM_AKEEBA_CONFIG_ERR_DECRYPTION');
		}

		parent::__construct($message, $code, $previous);
	}

}
