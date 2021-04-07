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
	$headers = array(
		// Most common.
		'x-forwarded-for',
		// Joomla detects this as well.
		'client-ip',
	);

	foreach ($_SERVER as $k => $v)
	{
		// Headers are case-insensitive so ensure comparing like for like, while still not trusting the user provided value
		if (in_array(strtolower($k), $headers) && !empty($_SERVER[$k]))
		{
			return true;
		}
	}

	return false;
}
