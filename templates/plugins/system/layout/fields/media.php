<?php
/**
 * @package Helix Ultimate Framework
 * @author JoomShaper https://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2018 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or Later
*/

defined ('_JEXEC') or die ();

class HelixUltimateFieldMedia
{
	static function getInput($key, $attr)
	{

		$output  = '<div class="control-group">';
		$output .= '<label>' . $attr['title'] . '</label>';

		$output .= '<div class="helix-ultimate-image-holder"></div>';

		$output .= '<input type="hidden" class="helix-ultimate-input helix-ultimate-input-media" data-attrname="' . $key . '" data-baseurl="'. \JURI::root() .'" value="">';
		$output .= '<a href="#" class="helix-ultimate-media-picker btn btn-primary btn-sm" data-target="' . $key . '"><span class="fa fa-picture-o"></span> Select Media</a>';
		$output .= '<a href="#" class="helix-ultimate-media-clear btn btn-secondary btn-sm"><span class="fa fa-times"></span> Clear</a>';

		if( ( isset($attr['desc']) ) && ( isset($attr['desc']) != '' ) )
		{
			$output .= '<p class="control-help">' . $attr['desc'] . '</p>';
		}

		$output .= '</div>';

		return $output;

	}
}
