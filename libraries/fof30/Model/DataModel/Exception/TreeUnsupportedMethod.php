<?php
/**
 * @package   FOF
 * @copyright Copyright (c)2010-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 2, or later
 */

namespace FOF30\Model\DataModel\Exception;

defined('_JEXEC') || die;

use Exception;
use Joomla\CMS\Language\Text;
use LogicException;

class TreeUnsupportedMethod extends LogicException
{
	public function __construct($method = '', $code = 500, Exception $previous = null)
	{
		$message = Text::sprintf('LIB_FOF_MODEL_ERR_TREE_UNSUPPORTEDMETHOD', $method);

		parent::__construct($message, $code, $previous);
	}

}
