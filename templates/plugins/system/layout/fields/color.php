<?php
/**
 * @package Helix Ultimate Framework
 * @author JoomShaper https://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2018 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or Later
*/

defined ('_JEXEC') or die ();

class HelixUltimateFieldColor{

	static function getInput($key, $attr)
	{

		JHtml::_('jquery.framework');
		JHtml::_('script', 'system/html5fallback.js', false, true);

		$output  = '<div class="control-group">';
		$output .= '<label>'.$attr['title'].'</label>';
		$output .= '<input type="text" class="helix-ultimate-input helix-ultimate-input-color" data-attrname="'.$key.'" placeholder="#rrggbb" value="">';

		if( ( isset($attr['desc']) ) && ( isset($attr['desc']) != '' ) )
		{
			$output .= '<p class="control-help">' . $attr['desc'] . '</p>';
		}

		$output .= '</div>';

		return $output;
	}

}
