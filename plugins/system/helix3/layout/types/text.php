<?php
/**
* @package Helix3 Framework
* @author JoomShaper http://www.joomshaper.com
* @copyright Copyright (c) 2010 - 2015 JoomShaper
* @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or Later
*/  

//no direct accees
defined ('_JEXEC') or die ('resticted aceess');

class SpTypeText{

	static function getInput($key, $attr)
	{

		if (!isset($attr['std'])) {
			$attr['std'] = '';
		}

		if (!isset($attr['placeholder'])) {
			$attr['placeholder'] = '';
		}

		$output  = '<div class="form-group">';
		$output .= '<label>'.$attr['title'].'</label>';
		$output	.= '<input class="form-control addon-input addon-'.$key.'" type="text" data-attrname="'.$key.'" value="'.$attr['std'].'" placeholder="'.$attr['placeholder'].'" />';
		
		if( ( isset($attr['desc']) ) && ( isset($attr['desc']) != '' ) )
		{
			$output .= '<p class="help-block">' . $attr['desc'] . '</p>';
		}
		
		$output .= '</div>';

		return $output;
	}

}