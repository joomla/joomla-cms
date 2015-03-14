<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_config
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

abstract class ConfigModelConfig extends JModelRecord
{
	public function __construct($config)
	{
		//we don't use list filters in this model.
		$config['ignore_request'] = true;
		parent::__construct($config);
	}

	public function getList($key = null, $class = 'JRegistry', $appendFilters = true)
	{
		return $this->getConfigurableComponent(parent::getList($key, $class, $appendFilters));
	}

	protected function getListQuery(JDatabaseQuery $query = null, $appendFilters = true)
	{
		$dbo = $this->getDbo();

		$query = parent::getListQuery($query, $appendFilters);
		$query->where('a.type = '.$dbo->quote('component'));
		$query->where('a.enabled = 1');
		$query->where('a.element != '. $dbo->quote('com_config'));

		return $query;
	}

	/**
	 * Overridden to use the extensions table
	 * @return string
	 */
	public function getTableName($config = array())
	{
		return '#__extensions';
	}

	/**
	 * Returns a list of components that have configuration options.
	 *
	 * @param  array  $list  Component name
	 *
	 * @return  array
	 */
	protected function getConfigurableComponent($list = array())
	{
		$configurable = array();
		foreach($list AS $component)
		{
			$hasConfigFile = is_file(JPATH_ADMINISTRATOR . '/components/' . $component->element . '/config.xml');
			if($hasConfigFile)
			{
				$configurable[] = $component;
			}
		}
		return $configurable;
	}

	/**
	 * Method to remove non-numeric values from the rules array
	 * This should really be moved over to the JAccess class
	 *
	 * @param $rules
	 *
	 * @return array clean rules array
	 */
	protected function cleanRules($rules)
	{
		$cleanRules = array();
		foreach($rules AS $action => $rule)
		{
			$cleanRules[$action] = array();
			foreach($rule AS $group => $setting)
			{
				if(is_numeric($setting))
				{
					$cleanRules[$action][$group] = $setting;
				}
			}
		}
		return $cleanRules;
	}
}