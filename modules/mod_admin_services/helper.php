<?php
/**
 * @package	Joomla.Site
 * @subpackage	mod_admin_services
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license	GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access
defined('_JEXEC') or die;

class modAdminServicesHelper {

	public static function execute($params){

		$result = '';

		if($params->get('config_visible') == 1)
		{
			$configUrl = JURI::base() . 'index.php?option=com_services&controller=config';
			$result = '<li><a href="' . $configUrl . '"> Configurations </a>
					</li>';
		}

		return $result;
	}
}