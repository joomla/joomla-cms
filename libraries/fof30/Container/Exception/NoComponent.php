<?php
/**
 * @package   FOF
 * @copyright Copyright (c)2010-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 2, or later
 */

namespace FOF30\Container\Exception;

defined('_JEXEC') || die;

use Exception;

class NoComponent extends \Exception
{
	public function __construct($message = "", $code = 0, Exception $previous = null)
	{
		if (empty($message))
		{
			$message = 'No component specified building the Container object';
		}

		if (empty($code))
		{
			$code = 500;
		}

		parent::__construct($message, $code, $previous);
	}
}
