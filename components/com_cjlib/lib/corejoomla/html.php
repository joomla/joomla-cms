<?php
/**
 * @package     corejoomla.administrator
 * @subpackage  com_cjlib
 *
 * @copyright   Copyright (C) 2009 - 2014 corejoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

class CjHtml
{
	protected static $registry = array();
	
	public static function _($key)
	{
		// function name should be removed
		$args = func_get_args();
		array_shift($args);
		
		// call the function
		$toCall = array('CjHtml', $key);
		
		return static::call($toCall, $args);
	}
	
	protected static function call($function, $args)
	{
		if (!is_callable($function))
		{
			throw new InvalidArgumentException('Function not supported', 500);
		}
	
		// PHP 5.3 workaround
		$temp = array();
	
		foreach ($args as &$arg)
		{
			$temp[] = &$arg;
		}
	
		return call_user_func_array($function, $temp);
	}
	
	private static function jssocials($options = null)
	{
		CjScript::_('jssocials', $options);
		$content = 'jQuery(document).ready(function($){
				$("#cjshare").jsSocials({shares: ["email", "twitter", "facebook", "googleplus", "linkedin", "pinterest"]});
		});';
		JFactory::getDocument()->addScriptDeclaration($content);
		$size = isset($options['size']) ? $options['size'] : 12;
		
		return '<div id="cjshare" style="display: inline-block; font-size: '.$size.'px;"></div>';
	}
}