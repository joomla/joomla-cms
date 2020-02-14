<?php
/**
 * @package Helix Ultimate Framework
 * @author JoomShaper https://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2018 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or Later
*/

defined ('_JEXEC') or die ();

class HelixUltimateFieldText{

	static function getInput($key, $attr)
	{
		$attributes = (isset($attr['placeholder']) && $attr['placeholder']) ? 'placeholder="'. $attr['placeholder'] .'"' : '';

		$output  = '<div class="control-group">';
		$output .= '<label>'.$attr['title'].'</label>';
		$output	.= '<input class="helix-ultimate-input addon-'.$key.'" type="text" data-attrname="'.$key.'" value="" '. $attributes .' />';

		if( ( isset($attr['desc']) ) && ( isset($attr['desc']) != '' ) )
		{
			$output .= '<p class="control-help">' . $attr['desc'] . '</p>';
		}

		$output .= '</div>';

		return $output;
	}

}
