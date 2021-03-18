<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_cpanel
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Cpanel\Administrator\Dispatcher;

\defined('_JEXEC') or die;

use Joomla\CMS\Access\Exception\Notallowed;
use Joomla\CMS\Dispatcher\ComponentDispatcher;

/**
 * ComponentDispatcher class for com_cpanel
 *
 * @since  4.0.0
 */
class Dispatcher extends ComponentDispatcher
{
	/**
	 * Method to check component access permission
	 *
	 * @since   4.0.0
	 *
	 * @return  void
	 *
	 * @throws  \Exception|Notallowed
	 */
	protected function checkAccess()
	{
	}
}
