<?php
/**
* @package Helix3 Framework
* @author JoomShaper http://www.joomshaper.com
* @copyright Copyright (c) 2010 - 2015 JoomShaper
* @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or Later
*/  

//no direct accees
defined ('_JEXEC') or die ('resticted aceess');

class SpTypeCheckbox{

	static function getInput($key, $attr)
	{

		if (isset($attr['value'])) {
			$attr['std'] = $attr['value'];
		}else{
			if (!isset($attr['std'])) {
				$attr['std'] = '0';
			}
		}

		$output   = '<div class="form-group">';
		$output  .= '<div class="checkbox">';
		$output  .= '<label>';
		$output  .= '<input class="addon-input input-'.$key.'" data-attrname="'.$key.'" type="checkbox" '.(($attr['std'] == 1)?'checked':'').'> ' .$attr['title'];
		$output  .= '</label>';
		$output  .= '</div>';

		if( ( isset($attr['desc']) ) && ( isset($attr['desc']) != '' ) )
		{
			$output .= '<p class="help-block">' . $attr['desc'] . '</p>';
		}

		$output  .= '</div>';

		return $output;
	}

}