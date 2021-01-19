<?php
/**
 * @package   FOF
 * @copyright Copyright (c)2010-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 2, or later
 */

namespace FOF30\Controller\Exception;

defined('_JEXEC') || die;

/**
 * Exception thrown when we can't find a suitable method to handle the requested task
 */
class TaskNotFound extends \InvalidArgumentException
{
}
