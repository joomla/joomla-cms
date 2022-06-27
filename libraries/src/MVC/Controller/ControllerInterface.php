<?php
/**
 * Joomla! Content Management System
 *
 * @copyright   (C) 2005 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\CMS\MVC\Controller;

\defined('JPATH_PLATFORM') or die;

/**
 * Joomla Platform CMS Interface
 *
 * @since  4.0.0
 */
interface ControllerInterface
{
	/**
	 * Execute a controller task.
	 *
	 * @param   string  $task  The task to perform.
	 *
	 * @return  mixed   The value returned by the called method.
	 *
	 * @since   4.0.0
	 * @throws  \InvalidArgumentException
	 * @throws  \RuntimeException
	 */
	public function execute($task);
}
