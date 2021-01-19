<?php
/**
 * @package   FOF
 * @copyright Copyright (c)2010-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 2, or later
 */

namespace FOF30\Controller\Exception;

defined('_JEXEC') || die;

use Exception;
use Joomla\CMS\Language\Text;

/**
 * Exception thrown when the provided Model is locked for writing by another user
 */
class LockedRecord extends \RuntimeException
{
	public function __construct($message = "", $code = 403, Exception $previous = null)
	{
		if (empty($message))
		{
			$message = Text::_('LIB_FOF_CONTROLLER_ERR_LOCKED');
		}

		parent::__construct($message, $code, $previous);
	}
}
