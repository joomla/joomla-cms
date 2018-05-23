<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_cpanel
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Dispatcher\ComponentDispatcher;

/**
 * Dispatcher class for com_cpanel
 *
 * @since  4.0.0
 */
class CpanelDispatcher extends ComponentDispatcher
{
	/**
	 * The extension namespace
	 *
	 * @var    string
	 *
	 * @since  4.0.0
	 */
	protected $namespace = 'Joomla\\Component\\Cpanel';

	/**
	 * com_cpanel does not require check permission, so we override checkAccess method and have it empty
	 *
	 * @return  void
	 */
	protected function checkAccess()
	{

	}
}
