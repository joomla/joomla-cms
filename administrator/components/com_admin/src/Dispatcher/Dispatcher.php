<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_admin
 *
 * @copyright   (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Admin\Administrator\Dispatcher;

\defined('_JEXEC') or die;

use Joomla\CMS\Dispatcher\ComponentDispatcher;

/**
 * ComponentDispatcher class for com_admin
 *
 * @since  4.0.0
 */
class Dispatcher extends ComponentDispatcher
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
