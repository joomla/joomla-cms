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
		// Check for valid name
		if (trim($this->name) == '' || trim($this->element) == '')
		{
			throw new InvalidArgumentException(JText::_('JLIB_DATABASE_ERROR_MUSTCONTAIN_A_TITLE_EXTENSION'));
		}

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

		return parent::bind($array, $ignore);
	}
}