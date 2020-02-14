<?php
/**
 * @package SP Page Builder
 * @author JoomShaper http://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2019 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/
//no direct accees
defined ('_JEXEC') or die ('Restricted access');

class SppagebuilderAddonDivider extends SppagebuilderAddons {

	public function render() {
		$settings = $this->addon->settings;
		$class = (isset($settings ->class) && $settings ->class) ? $settings ->class : '';
		$divider_type = (isset($settings ->divider_type) && $settings ->divider_type) ? $settings ->divider_type : '';
		$divider_position = (isset($settings ->divider_position) && $settings ->divider_position) ? 'divider-position' : '';
		//output start
		$output = '';
		$output .='<div class="sppb-addon-divider-wrap '.$divider_position.'">';
		$output .='<div class="sppb-divider sppb-divider-' . $divider_type . ' ' . $class . '"></div>';
		$output .='</div>';
		return $output;
	}

	public function css() {
		$addon_id = '#sppb-addon-' . $this->addon->id;
		$settings = $this->addon->settings;

		$divider_type		= (isset($settings->divider_type) && $settings->divider_type) ? $settings->divider_type : '';
		$margin_top 	 	= (isset($settings->margin_top) && gettype($settings->margin_top)=='string') ? $settings->margin_top : 30;
		$margin_top_sm 	 	= (isset($settings->margin_top_sm) && gettype($settings->margin_top_sm)=='string') ? $settings->margin_top_sm : 30;
		$margin_top_xs 	 	= (isset($settings->margin_top_xs) && gettype($settings->margin_top_xs)=='string') ? $settings->margin_top_xs : 30;
		$margin_bottom 	 	= (isset($settings->margin_bottom) && gettype($settings->margin_bottom)=='string') ? $settings->margin_bottom : 30;
		$margin_bottom_sm 	 	= (isset($settings->margin_bottom_sm) && gettype($settings->margin_bottom_sm)=='string') ? $settings->margin_bottom_sm : 30;
		$margin_bottom_xs 	 	= (isset($settings->margin_bottom_xs) && gettype($settings->margin_bottom_xs)=='string') ? $settings->margin_bottom_xs : 30;
		$border_color 	 	= (isset($settings->border_color) && $settings->border_color) ? $settings->border_color : '#eeeeee';
		$border_style 	 	= (isset($settings->border_style) && $settings->border_style) ? $settings->border_style : 'solid';
		$border_width 	 	= (isset($settings->border_width) && $settings->border_width) ? $settings->border_width : 1;
		$divider_height 	= (isset($settings->divider_height) && $settings->divider_height) ? $settings->divider_height : 10;
		$divider_image 		= (isset($settings->divider_image) && $settings->divider_image) ? $settings->divider_image : '';
		$background_repeat 	= (isset($settings->background_repeat) && $settings->background_repeat) ? $settings->background_repeat : 'no-repeat';
		$border_radius 	= (isset($settings->border_radius) && $settings->border_radius) ? $settings->border_radius : '';
		$divider_vertical 	= (isset($settings->divider_vertical) && $settings->divider_vertical) ? $settings->divider_vertical : '';

		$container_div_width = (isset($settings->container_div_width) && $settings->container_div_width) ? $settings->container_div_width : '';
		$container_div_width_sm = (isset($settings->container_div_width_sm) && $settings->container_div_width_sm) ? $settings->container_div_width_sm : '';
		$container_div_width_xs = (isset($settings->container_div_width_xs) && $settings->container_div_width_xs) ? $settings->container_div_width_xs : '';
		//Divider height in vertical mod
		$divider_height_vertical = (isset($settings->divider_height_vertical) && $settings->divider_height_vertical) ? $settings->divider_height_vertical : '';
		$divider_height_vertical_sm = (isset($settings->divider_height_vertical_sm) && $settings->divider_height_vertical_sm) ? $settings->divider_height_vertical_sm : '';
		$divider_height_vertical_xs = (isset($settings->divider_height_vertical_xs) && $settings->divider_height_vertical_xs) ? $settings->divider_height_vertical_xs : '';

		$css = '';

		$style = '';
		$style_sm = '';
		$style_xs = '';

		$style .= ($margin_top != '') ? 'margin-top:' . (int) $margin_top  . 'px;' : '';
		$style_sm .= ($margin_top_sm != '') ? 'margin-top:' . (int) $margin_top_sm  . 'px;' : '';
		$style_xs .= ($margin_top_xs != '') ? 'margin-top:' . (int) $margin_top_xs  . 'px;' : '';
		$style .= ($margin_bottom != '') ? 'margin-bottom:' . (int) $margin_bottom  . 'px;' : '';
		$style_sm .= ($margin_bottom_sm != '') ? 'margin-bottom:' . (int) $margin_bottom_sm  . 'px;' : '';
		$style_xs .= ($margin_bottom_xs != '') ? 'margin-bottom:' . (int) $margin_bottom_xs  . 'px;' : '';
		$style .= ($container_div_width != '') ? 'width:' . (int) $container_div_width  . 'px;' : '';
		$style_sm .= ($container_div_width_sm != '') ? 'width:' . (int) $container_div_width_sm  . 'px;' : '';
		$style_xs .= ($container_div_width_xs != '') ? 'width:' . (int) $container_div_width_xs  . 'px;' : '';

		$inner_style = '';
		if($divider_type == 'border') {
			if(!$divider_vertical){
				$inner_style .= $border_width ? 'border-bottom-width:' . (int) $border_width  . 'px;' : '';
				$inner_style .= ($border_style) ? 'border-bottom-style:' . $border_style  . ';' : '';
				$inner_style .= ($border_color) ? 'border-bottom-color:' . $border_color  . ';' : '';
				$inner_style .= ($border_radius) ? 'border-radius:' . $border_radius  . 'px;' : '';
			} else {
				$inner_style .= $divider_height_vertical ? 'height:' . $divider_height_vertical  . 'px;' : '';
				$inner_style .= $border_width ? 'width:' . (int) $border_width  . 'px;' : '';
				$inner_style .= $border_width ? 'border-left-width:' . (int) $border_width  . 'px;' : '';
				$inner_style .= ($border_style) ? 'border-left-style:' . $border_style  . ';' : '';
				$inner_style .= ($border_color) ? 'border-left-color:' . $border_color  . ';' : '';
				$inner_style .= ($border_radius) ? 'border-radius:' . $border_radius  . 'px;' : '';
			}
		} else {
			$inner_style .= ($divider_height) ? 'height:' . (int) $divider_height  . 'px;' : '';

			if(strpos($divider_image, 'http://') !== false || strpos($divider_image, 'https://') !== false){
				$inner_style .= ($divider_image) ? 'background-image: url(' . $divider_image  . ');background-repeat:' . $background_repeat . ';background-position:50% 50%;' : '';
			} else {
				$inner_style .= ($divider_image) ? 'background-image: url(' . JURI::base() . '/' . $divider_image  . ');background-repeat:' . $background_repeat . ';background-position:50% 50%;' : '';
			}
		}

		if($style) {
			$css .= $addon_id . ' .sppb-divider {';
			$css .= $style;
			$css .= $inner_style;
			$css .= '}';
		}

		if(isset($settings->divider_position) && $settings->divider_position){
			$css .= '#sppb-addon-' . $this->addon->id . ' .divider-position {';
				if($settings->divider_position == 'left'){
					$css .= 'text-align: left;';
				} elseif( $settings->divider_position == 'right' ){
					$css .= 'text-align: right;';
				} elseif( $settings->divider_position == 'center' ){
					$css .= 'text-align: center;';
				}
			$css .= '}';
		}

		$css .= '@media (min-width: 768px) and (max-width: 991px) {';
			if($style_sm) {
				$css .= $addon_id . ' .sppb-divider {';
					$css .= $style_sm;
					if($divider_vertical && $divider_height_vertical_sm){
						$css .= $divider_height_vertical_sm ? 'height:' . (int) $divider_height_vertical_sm  . 'px;' : '';
					}
				$css .= '}';
			}
			if(isset($settings->divider_position_sm) && $settings->divider_position_sm){
				$css .= '#sppb-addon-' . $this->addon->id . ' .divider-position {';
					if($settings->divider_position_sm == 'left'){
						$css .= 'text-align: left;';
					} elseif( $settings->divider_position_sm == 'right' ){
						$css .= 'text-align: right;';
					} elseif( $settings->divider_position_sm == 'center' ){
						$css .= 'text-align: center;';
					}
				$css .= '}';
			}
		$css .= '}';
		
		$css .= '@media (max-width: 767px) {';
			if($style_xs) {
				$css .= $addon_id . ' .sppb-divider {';
					$css .= $style_xs;
					if($divider_vertical && $divider_height_vertical_xs){
						$css .= $divider_height_vertical_xs ? 'height:' . $divider_height_vertical_xs  . 'px;' : '';
					}
				$css .= '}';
			}
			if(isset($settings->divider_position_xs) && $settings->divider_position_xs){
				$css .= '#sppb-addon-' . $this->addon->id . ' .divider-position {';
					if($settings->divider_position_xs == 'left'){
						$css .= 'text-align: left;';
					} elseif( $settings->divider_position_xs == 'right' ){
						$css .= 'text-align: right;';
					} elseif( $settings->divider_position_xs == 'center' ){
						$css .= 'text-align: center;';
					}
				$css .= '}';
			}
		$css .= '}';

		return $css;
	}

	public static function getTemplate(){
		$output = '
		<style type="text/css">
			#sppb-addon-{{ data.id }} .sppb-divider {
				<# if(_.isObject(data.margin_top)){ #>
					margin-top: {{ data.margin_top.md }}px;
				<# } else { #>
					margin-top: {{ data.margin_top }}px;
				<# } #>

				<# if(_.isObject(data.margin_bottom)){ #>
					margin-bottom: {{ data.margin_bottom.md }}px;
				<# } else { #>
					margin-bottom: {{ data.margin_bottom }}px;
				<# } #>
				<# if(_.isObject(data.container_div_width)){ #>
					width: {{ data.container_div_width.md }}px;
				<# } else { #>
					width: {{ data.container_div_width }}px;
				<# } #>

				<#
				if(data.divider_type == "border"){ 
					if(!data.divider_vertical){
				#>
					border-bottom-width: {{ data.border_width }}px;
					border-bottom-style: {{ data.border_style }};
					border-bottom-color: {{ data.border_color }};
					border-radius: {{ data.border_radius }}px;
				<# } else {
					if(_.isObject(data.divider_height_vertical)){
				#>
					height:{{data.divider_height_vertical.md}}px;
				<# } #>
					width:{{data.border_width}}px;
					border-left-width: {{ data.border_width }}px;
					border-left-style: {{ data.border_style }};
					border-left-color: {{ data.border_color }};
					border-radius:{{data.border_radius}}px;
				<# }
				} else { 
				#>
					height: {{ data.divider_height }}px;
					<# if(data.divider_image.indexOf("http://") == -1 && data.divider_image.indexOf("https://") == -1){ #>background-image: url({{ pagebuilder_base + data.divider_image }});
					<# } else { #>
						background-image: url({{ data.divider_image }});
					<# } #>
					background-repeat: {{ data.background_repeat }};
					background-position:50% 50%;
				<# } #>
			}

			<# if(_.isObject(data.divider_position) && data.divider_position.md){ #>
				#sppb-addon-{{ data.id }} .divider-position {
					<# if(data.divider_position.md == "left"){ #>
						text-align: left;
					<# } else if(data.divider_position.md == "right" ){ #>
						text-align: right;
					<# } else if(data.divider_position.md == "center" ){ #>
						text-align: center;
					<# } #>
				}
			<# } #>

			@media (min-width: 768px) and (max-width: 991px) {
				#sppb-addon-{{ data.id }} .sppb-divider {
					<# if(_.isObject(data.margin_top)){ #>
						margin-top: {{ data.margin_top.sm }}px;
					<# } #>

					<# if(_.isObject(data.margin_bottom)){ #>
						margin-bottom: {{ data.margin_bottom.sm }}px;
					<# } #>
					<# if(_.isObject(data.container_div_width)){ #>
						width: {{ data.container_div_width.sm }}px;
					<# }
					if(data.divider_vertical && _.isObject(data.divider_height_vertical)){
					#>
						height:{{data.divider_height_vertical.sm}}px;
					<# } #>
				}
				<# if(_.isObject(data.divider_position) && data.divider_position.sm){ #>
					#sppb-addon-{{ data.id }} .divider-position {
						<# if(data.divider_position.sm == "left"){ #>
							text-align: left;
						<# } else if(data.divider_position.sm == "right" ){ #>
							text-align: right;
						<# } else if(data.divider_position.sm == "center" ){ #>
							text-align: center;
						<# } #>
					}
				<# } #>
			}
			@media (max-width: 767px) {
				#sppb-addon-{{ data.id }} .sppb-divider {
					<# if(_.isObject(data.margin_top)){ #>
						margin-top: {{ data.margin_top.xs }}px;
					<# } #>

					<# if(_.isObject(data.margin_bottom)){ #>
						margin-bottom: {{ data.margin_bottom.xs }}px;
					<# } #>
					<# if(_.isObject(data.container_div_width)){ #>
						width: {{ data.container_div_width.xs }}px;
					<# }
					if(data.divider_vertical && _.isObject(data.divider_height_vertical)){
					#>
						height:{{data.divider_height_vertical.xs}}px;
					<# } #>
				}
				<# if(_.isObject(data.divider_position) && data.divider_position.xs){ #>
					#sppb-addon-{{ data.id }} .divider-position {
						<# if(data.divider_position.xs == "left"){ #>
							text-align: left;
						<# } else if(data.divider_position.xs == "right" ){ #>
							text-align: right;
						<# } else if(data.divider_position.xs == "center" ){ #>
							text-align: center;
						<# } #>
					}
				<# } #>
			}
		</style>
		<#
		let dividerPosition = (!_.isEmpty(data.divider_type) && data.divider_type) ? "divider-position" : "";
		#>
		<div class="sppb-addon-divider-wrap {{dividerPosition}}">
			<div class="sppb-divider sppb-divider-{{ data.divider_type }} {{ data.class }}"></div>
		</div>
		';

		return $output;
	}

}
