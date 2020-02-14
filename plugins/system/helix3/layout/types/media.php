<?php
/**
* @package Helix3 Framework
* @author JoomShaper http://www.joomshaper.com
* @copyright Copyright (c) 2010 - 2015 JoomShaper
* @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or Later
*/  

//no direct accees
defined ('_JEXEC') or die ('resticted aceess');

class SpTypeMedia
{
	static function getInput($key, $attr)
	{

		if(!isset($attr['std'])){
			$attr['std'] = '';
		}

		if($attr['std']!='') {
			$src = 'src="' . JURI::root() .  $attr['std'] . '"';
		} else {
			$src = '';
		}

		JHtml::_('behavior.modal');
		JHtml::_('jquery.framework');
		
		$output  = '<div class="form-group">';
		$output .= '<label>' . $attr['title'] . '</label>';
		$output .= '<div class="media">';

		$output .= '<div class="media-preview add-on">';
		$output .= '<div class="image-preview">';
		$output .= '<img class="media-preview" ' . $src . ' alt="" height="100px">';
		$output .= '</div>';
		$output .= '</div>';

		$output .= '<input type="hidden" data-attrname="'.$key.'" class="input-media addon-input" value="'.$attr['std'].'">';
		$output .= '<a class="modal sppb-btn sppb-btn-primary" title="Select" rel="{handler: \'iframe\', size: {x: 800, y: 500}}">Select</a>';
		$output .= ' <a class="sppb-btn sppb-btn-danger remove-media" href="#"><i class="icon-remove"></i></a>';
		$output .= '</div>';

		if( ( isset($attr['desc']) ) && ( isset($attr['desc']) != '' ) )
		{
			$output .= '<p class="help-block">' . $attr['desc'] . '</p>';
		}

		$output .= '</div>';

		return $output;

	}
}
