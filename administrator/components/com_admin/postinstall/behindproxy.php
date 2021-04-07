<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_admin
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Notifies users of the new Behind Load Balancer option in Global Config, if we detect they might be behind a proxy
 *
 * @return  boolean
 *
 * @since   __DEPLOY_VERSION__
 */
function admin_postinstall_behindproxy_condition()
{
	if (in_array('HTTP_X_FORWARDED_FOR', $_SERVER) && !empty($_SERVER['HTTP_X_FORWARDED_FOR']))
	{
		return true;
	}

	if (in_array('HTTP_CLIENT_IP', $_SERVER) && !empty($_SERVER['HTTP_CLIENT_IP']))
	{
		return true;
	}

	return false;
}
