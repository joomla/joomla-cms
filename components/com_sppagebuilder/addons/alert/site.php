<?php
/**
 * @package SP Page Builder
 * @author JoomShaper http://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2018 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/
//no direct accees
defined ('_JEXEC') or die ('Restricted access');

class SppagebuilderAddonAlert extends SppagebuilderAddons{

	public function render() {
		$settings = $this->addon->settings;
		
		$class = (isset($settings->class) && $settings->class) ? $settings->class : '';
		$type = (isset($settings->alrt_type) && $settings->alrt_type) ? ' sppb-alert-' . $settings->alrt_type : '';
		$title = (isset($settings->title) && $settings->title) ? $settings->title : '';
		$heading_selector = (isset($settings->heading_selector) && $settings->heading_selector) ? $settings->heading_selector : '';
		$close = (isset($settings->close) && $settings->close) ? $settings->close : 0;
		$text = (isset($settings->text) && $settings->text) ? $settings->text : '';

		if($text) {
			
			$output  = '<div class="sppb-addon sppb-addon-alert ' . $class .'">';
			$output .= (!empty($title)) ? '<' . $heading_selector . ' class="sppb-addon-title">' . $title .'</' . $heading_selector . '>' : '';
			$output .= '<div class="sppb-addon-content">';
			$output .= '<div class="sppb-alert' . $type . ' sppb-fade in">';
			$output .= ( $close ) ? '<button type="button" class="sppb-close" data-dismiss="sppb-alert" aria-label="alert dismiss"><span aria-hidden="true">&times;</span></button>' : '';
			$output .= $text;
			$output .= '</div>';
			$output .= '</div>';
			$output .= '</div>';

			return $output;
		}

		return;
	}

	 public static function getTemplate()
	{
		$output = '
		<div class="sppb-addon sppb-addon-alert {{ data.class }}">
			<# if( !_.isEmpty( data.title ) ){ #><{{ data.heading_selector }} class="sppb-addon-title sp-inline-editable-element" data-id={{data.id}} data-fieldName="title" contenteditable="true">{{{ data.title }}}</{{ data.heading_selector }}><# } #>
			<div class="sppb-addon-content">
				<div class="sppb-alert sppb-alert-{{ data.alrt_type }} sppb-fade in">
					<# if( data.close ){ #>
						<button type="button" class="sppb-close"><span aria-hidden="true">&times;</span></button>
					<# } #>
					<div id="addon-text-{{data.id}}" class="sp-editable-content" data-id={{data.id}} data-fieldName="text">{{{ data.text }}}</div>
				</div>
			</div>
		</div>';
		return $output;
	}
}
