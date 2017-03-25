<?php
/**
 * @package     FrameworkOnFramework
 * @subpackage  utils
 * @copyright   Copyright (C) 2010-2016 Nicholas K. Dionysopoulos / Akeeba Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('FOF_INCLUDED') or die;

/**
 * A utility class to help you fetch component parameters without going through JComponentHelper
 */
class FOFUtilsConfigHelper
{
	/**
	 * Caches the component parameters without going through JComponentHelper. This is necessary since JComponentHelper
	 * cannot be reset or updated once you update parameters in the database.
	 *
	 * @var array
	 */
	private static $componentParams = array();

	/**
	 * Loads the component's configuration parameters so they can be accessed by getComponentConfigurationValue
	 *
	 * @param   string  $component  The component for loading the parameters
	 * @param   bool    $force      Should I force-reload the configuration information?
	 */
	public final static function loadComponentConfig($component, $force = false)
	{
		if (isset(self::$componentParams[$component]) && !is_null(self::$componentParams[$component]) && !$force)
		{
			return;
		}

		$db = FOFPlatform::getInstance()->getDbo();

		$sql = $db->getQuery(true)
				  ->select($db->qn('params'))
				  ->from($db->qn('#__extensions'))
				  ->where($db->qn('type') . ' = ' . $db->q('component'))
				  ->where($db->qn('element') . " = " . $db->q($component));
		$db->setQuery($sql);
		$config_ini = $db->loadResult();

		// OK, Joomla! 1.6 stores values JSON-encoded so, what do I do? Right!
		$config_ini = trim($config_ini);

		if ((substr($config_ini, 0, 1) == '{') && substr($config_ini, -1) == '}')
		{
			$config_ini = json_decode($config_ini, true);
		}
		else
		{
			$config_ini = FOFUtilsIniParser::parse_ini_file($config_ini, false, true);
		}

		if (is_null($config_ini) || empty($config_ini))
		{
			$config_ini = array();
		}

		self::$componentParams[$component] = $config_ini;
	}

	/**
	 * Retrieves the value of a component configuration parameter without going through JComponentHelper
	 *
	 * @param   string  $component  The component for loading the parameter value
	 * @param   string  $key        The key to retrieve
	 * @param   mixed   $default    The default value to use in case the key is missing
	 *
	 * @return  mixed
	 */
	public final static function getComponentConfigurationValue($component, $key, $default = null)
	{
		self::loadComponentConfig($component, false);

		if (array_key_exists($key, self::$componentParams[$component]))
		{
			return self::$componentParams[$component][$key];
		}
		else
		{
			return $default;
		}
	}
} 