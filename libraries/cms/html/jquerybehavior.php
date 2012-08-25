<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  HTML
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Utility class for jQuery JavaScript behaviors
 *
 * @package     Joomla.Libraries
 * @subpackage  HTML
 * @since       3.0
 */
abstract class JHtmlJquerybehavior
{
	protected static $loaded = array();

	/**
	 * Add tooltip for element
	 * Use element's Title as tooltip content
	 *
	 *
	 * @param 	string 	$selector selector for the tooltip
	 * @param	array 	$params An array of options for the tooltip.
	 * 					Options for the tooltip can be:
	 * 						animation 	boolean 			true 	apply a css fade transition to the tooltip
	 *						placement 	string|function 	'top' 	how to position the tooltip - top | bottom | left | right
	 *						selector 	string 				false 	If a selector is provided, tooltip objects will be delegated to the specified targets.
	 *						title 		string | function 	'' 		default title value if `title` tag isn't present
	 *						trigger 	string 	'hover' 	how 	tooltip is triggered - hover | focus | manual
	 *						delay 		number | object 	0 		delay showing and hiding the tooltip (ms) - does not apply to manual trigger type
	 *																If a number is supplied, delay is applied to both hide/show
	 *																Object structure is: delay: { show: 500, hide: 100 }
	 * @return  void
	 *
	 * @since   3.0
	 */
	public static function tooltip($selector = '[rel=tooltip]', $params = array())
	{
		// Only load once
		if (isset(self::$loaded[__METHOD__]))
		{
			return;
		}

		$opt['animation'] = isset($params['animation']) ? $params['animation'] : true;
		$opt['placement'] = isset($params['placement']) ? $params['placement'] : 'top';
		$opt['selector']  = isset($params['selector']) ? $params['selector'] : false;
		$opt['title']     = isset($params['title']) ? $params['title'] : '';
		$opt['trigger']   = isset($params['trigger']) ? $params['trigger'] : 'hover';
		$opt['delay']     = isset($params['delay']) ? $params['delay'] : 0;

		$options = json_encode($opt);

		JFactory::getDocument()->addScriptDeclaration(
			"
			(function($){
				$(document).ready(function (){
					$('" . $selector . "').tooltip(". $options .")
				});

			})(jQuery);
			"
		);

		self::$loaded[__METHOD__] = true;

		return;
	}

	/**
	 * Add a popover for element
	 * Use element's Title as tooltip content
	 *
	 *
	 * @param 	string 	$selector selector for the tooltip
	 * @param	array 	$params An array of options for the tooltip.
	 * 					Options for the tooltip can be:
	 * 						animation 	boolean 			true 	apply a css fade transition to the tooltip
	 *						placement 	string|function 	'top' 	how to position the tooltip - top | bottom | left | right
	 *						selector 	string 				false 	If a selector is provided, tooltip objects will be delegated to the specified targets.
	 *						title 		string | function 	'' 		default title value if `title` tag isn't present
	 *						trigger 	string 	'hover' 	how 	tooltip is triggered - hover | focus | manual
	 *						delay 		number | object 	0 		delay showing and hiding the tooltip (ms) - does not apply to manual trigger type
	 *																If a number is supplied, delay is applied to both hide/show
	 *																Object structure is: delay: { show: 500, hide: 100 }
	 * @return  void
	 *
	 * @since   3.0
	 */
	public static function popover($selector = '[rel=popover]', $params = array())
	{
		// Only load once
		if (isset(self::$loaded[__METHOD__]))
		{
			return;
		}

		$opt['animation'] = isset($params['animation']) ? $params['animation'] : true;
		$opt['placement'] = isset($params['placement']) ? $params['placement'] : 'top';
		$opt['selector']  = isset($params['selector']) ? $params['selector'] : false;
		$opt['title']     = isset($params['title']) ? $params['title'] : '';
		$opt['trigger']   = isset($params['trigger']) ? $params['trigger'] : 'hover';
		$opt['delay']     = isset($params['delay']) ? $params['delay'] : 0;

		$options = json_encode($opt);

		JFactory::getDocument()->addScriptDeclaration(
			"
			(function($){
				$(document).ready(function (){
					$('" . $selector . "').tooltip(". $options .")
				});

			})(jQuery);
			"
		);

		self::$loaded[__METHOD__] = true;

		return;
	}
}
