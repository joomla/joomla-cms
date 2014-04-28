<?php
/**
 * @version     0.0.1
 * @package     Babel-U-Lib
 * @copyright   Copyright (C) 2011. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author     Mathew Lenning - http://mathewlenning.com/
 */
// No direct access
defined('_JEXEC') or die;

function babelu_libJoomlaComponentAutoload($class)
{
	$flags = PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE;
	$directoryArray = preg_split('/(?=[A-Z])/', $class, null, $flags);
	$fileName = $directoryArray[(count($directoryArray) - 1)];
	$componentPrefix = array_shift($directoryArray);
	
	if (strtolower($componentPrefix) != 'j')
	{
		$path_to_file = DIRECTORY_SEPARATOR;
	
		foreach ($directoryArray AS $dir)
		{
			if ($dir != $fileName)
			{		
				$path_to_file .= strtolower($dir).DIRECTORY_SEPARATOR;
			}
		}
	
		$fileName = strtolower($fileName);
	
		// check the admin area first then check the site.
		$primaryIncludePath = JPATH_COMPONENT.$path_to_file.$fileName.'.php';
		
		$app = JFactory::getApplication();
		if ($app->isSite())
		{
			$fallbackIncludePath = JPATH_COMPONENT_ADMINISTRATOR.$path_to_file.$fileName.'.php';
		}
		else
		{
			$fallbackIncludePath = JPATH_COMPONENT_SITE.$path_to_file.$fileName.'.php';
		}
	
		if (file_exists($primaryIncludePath))
		{
			include_once ($primaryIncludePath);
		}
		elseif (file_exists($fallbackIncludePath))
		{
			include_once $fallbackIncludePath;
		}
	}
		
	return true;
}

class Babelu_libjoomlacomponentAutoloader
{
	public static function autoload($class)
	{
		babelu_libJoomlaComponentAutoload($class);
	}
}

spl_autoload_register(array('babelu_libjoomlacomponentautoloader','autoload'));