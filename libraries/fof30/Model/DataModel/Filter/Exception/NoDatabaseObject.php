<?php
/**
 * @package   FOF
 * @copyright Copyright (c)2010-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 2, or later
 */

namespace FOF30\Model\DataModel\Filter\Exception;

defined('_JEXEC') || die;

use Exception;
use InvalidArgumentException;
use Joomla\CMS\Language\Text;

class NoDatabaseObject extends InvalidArgumentException
{
	public function __construct($fieldType, $code = 500, Exception $previous = null)
	{
		$message = Text::sprintf('LIB_FOF_MODEL_ERR_FILTER_NODBOBJECT', $fieldType);

		parent::__construct($message, $code, $previous);
	}

}
