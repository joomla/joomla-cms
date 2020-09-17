<?php
/**
 * @package   FOF
 * @copyright Copyright (c)2010-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 2, or later
 */

namespace FOF30\Factory\Exception;

defined('_JEXEC') || die;

use Exception;
use Joomla\CMS\Language\Text;
use RuntimeException;

class DispatcherNotFound extends RuntimeException
{
	public function __construct($dispatcherClass, $code = 500, Exception $previous = null)
	{
		$message = Text::sprintf('LIB_FOF_DISPATCHER_ERR_NOT_FOUND', $dispatcherClass);

		parent::__construct($message, $code, $previous);
	}

}
