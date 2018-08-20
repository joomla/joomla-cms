<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_admin
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Admin\Administrator\Dispatcher;

defined('_JEXEC') or die;

/**
 * Dispatcher class for com_admin
 *
 * @since  4.0.0
 */
class Dispatcher extends \Joomla\CMS\Dispatcher\Dispatcher
{
	/**
	 * com_admin does not require check permission, so we override checkAccess method and have it empty
	 *
	 * @return  void
	 */
	protected function checkAccess()
	{
	}
}
