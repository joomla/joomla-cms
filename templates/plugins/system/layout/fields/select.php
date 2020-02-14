<?php
/**
 * @package Helix Ultimate Framework
 * @author JoomShaper https://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2018 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or Later
*/

defined ('_JEXEC') or die ();

class HelixUltimateFieldSelect{

	static function getInput($key, $attr)
	{

		$output  = '<div class="control-group '.$key.'">';
		$output .= '<label>'.$attr['title'].'</label>';

		$output .= '<select class="helix-ultimate-input input-select" data-attrname="'.$key.'">';
		foreach( $attr['values'] as $key => $value )
		{
			$output .= '<option value="'.$key.'">'.$value.'</option>';
		}
		$output .= '</select>';

		if( ( isset($attr['desc']) ) && ( isset($attr['desc']) != '' ) )
		{
			$output .= '<p class="control-help">' . $attr['desc'] . '</p>';
		}

		$output .= '</div>';

		return $output;
	}

}
