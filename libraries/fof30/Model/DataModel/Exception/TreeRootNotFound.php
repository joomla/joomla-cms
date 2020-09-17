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
use RuntimeException;

class TreeRootNotFound extends RuntimeException
{
	public function __construct($tableName, $lft, $code = 500, Exception $previous = null)
	{
		$message = Text::sprintf('LIB_FOF_MODEL_ERR_TREE_ROOTNOTFOUND', $tableName, $lft);

		parent::__construct($message, $code, $previous);
	}

}
