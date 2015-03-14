<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_config
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

class ConfigTableComponent extends JTableCms
{
	public function __construct($config = array())
	{
		$config['table']['name'] = '#__extensions';
		$config['table']['key']  = 'extension_id';
		parent::__construct($config);
		$this->stateField = 'enabled';
	}

	public function check()
	{
		return true;
	}

	public function bind($array, $ignore = array())
	{
		// I'm not entirely sure what the control is for, but when in doubt keep it.
		if (isset($array['control']) && is_array($array['control']))
		{
			$registry = new Registry;
			$registry->loadArray($array['control']);
			$array['control'] = (string) $registry;
		}

		// Ignore as much as possible
		// Yes, I know its ugly =^P
		// Ignore that fact too please.
		$ignore = array('type','folder','client_id',
						'enabled','access','protected','manifest_cache',
						'custom_data','system_data','checked_out', 'checked_out_time',
						'ordering','state'
					);

		return parent::bind($array, $ignore);
	}
}