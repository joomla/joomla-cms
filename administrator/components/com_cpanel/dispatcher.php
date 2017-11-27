<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_content
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Dispatcher\Dispatcher;

/**
 * Dispatcher class for com_cpanel
 *
 * @since  4.0.0
 */
class CpanelDispatcher extends Dispatcher
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
