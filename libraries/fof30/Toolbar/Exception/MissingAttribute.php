<?php
/**
 * @package   FOF
 * @copyright Copyright (c)2010-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 2, or later
 */

namespace FOF30\Toolbar\Exception;

defined('_JEXEC') || die;

use Exception;
use InvalidArgumentException;
use Joomla\CMS\Language\Text;

class MissingAttribute extends InvalidArgumentException
{
	public function __construct($missingArgument, $buttonType, $code = 500, Exception $previous = null)
	{
		$message = Text::sprintf('LIB_FOF_TOOLBAR_ERR_MISSINGARGUMENT', $missingArgument, $buttonType);

		parent::__construct($message, $code, $previous);
	}
}
