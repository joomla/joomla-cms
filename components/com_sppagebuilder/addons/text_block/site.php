<?php
/**
 * @package SP Page Builder
 * @author JoomShaper http://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2019 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/
//no direct accees
defined ('_JEXEC') or die ('Restricted access');

class SppagebuilderAddonText_block extends SppagebuilderAddons {

	public function render() {
		$settings = $this->addon->settings;
		$class = (isset($settings->class) && $settings->class) ? $settings->class : '';
		$title = (isset($settings->title) && $settings->title) ? $settings->title : '';
		$heading_selector = (isset($settings->heading_selector) && $settings->heading_selector) ? $settings->heading_selector : 'h3';

		//Options
		$text = (isset($settings->text) && $settings->text) ? $settings->text : '';
		$alignment = (isset($settings->alignment) && $settings->alignment) ? $settings->alignment : '';
		$dropcap = (isset($settings->dropcap) && $settings->dropcap) ? $settings->dropcap : 0;

		$dropcapCls = '';
		if($dropcap){
			$dropcapCls = ' sppb-dropcap';
		}

		//Output
		$output  = '<div class="sppb-addon sppb-addon-text-block' . $dropcapCls . ' ' . $alignment . ' ' . $class . '">';
		$output .= ($title) ? '<'.$heading_selector.' class="sppb-addon-title">' . $title . '</'.$heading_selector.'>' : '';
		$output .= '<div class="sppb-addon-content">';
		$output .= $text;
		$output .= '</div>';
		$output .= '</div>';

		return $output;

	}

	public function css() {
		$settings = $this->addon->settings;
		$css = '';
		$dropcap_style = '';
		$dropcap_style .= (isset($settings->dropcap_color) && $settings->dropcap_color) ? "color: " . $settings->dropcap_color . ";" : "";
		$dropcap_style .= (isset($settings->dropcap_font_size) && $settings->dropcap_font_size) ? "font-size: " . $settings->dropcap_font_size . "px;" : "";
		$dropcap_style .= (isset($settings->dropcap_font_size) && $settings->dropcap_font_size) ? "line-height: " . $settings->dropcap_font_size . "px;" : "";
		$dropcap_style_sm = (isset($settings->dropcap_font_size_sm) && $settings->dropcap_font_size_sm) ? "font-size: " . $settings->dropcap_font_size_sm . "px;" : "";
		$dropcap_style_sm .= (isset($settings->dropcap_font_size_sm) && $settings->dropcap_font_size_sm) ? "line-height: " . $settings->dropcap_font_size_sm . "px;" : "";
		$dropcap_style_xs = (isset($settings->dropcap_font_size_xs) && $settings->dropcap_font_size_xs) ? "font-size: " . $settings->dropcap_font_size_xs . "px;" : "";
		$dropcap_style_xs .= (isset($settings->dropcap_font_size_xs) && $settings->dropcap_font_size_xs) ? "line-height: " . $settings->dropcap_font_size_xs . "px;" : "";

		$style = '';
		$style_sm = '';
		$style_xs = '';

		$style .= (isset($settings->text_fontsize) && $settings->text_fontsize) ? "font-size: " . $settings->text_fontsize . "px;" : "";
		$style .= (isset($settings->text_fontweight) && $settings->text_fontweight) ? "font-weight: " . $settings->text_fontweight . ";" : "";
		$style_sm .= (isset($settings->text_fontsize_sm) && $settings->text_fontsize_sm) ? "font-size: " . $settings->text_fontsize_sm . "px;" : "";
		$style_xs .= (isset($settings->text_fontsize_xs) && $settings->text_fontsize_xs) ? "font-size: " . $settings->text_fontsize_xs . "px;" : "";

		$style .= (isset($settings->text_lineheight) && $settings->text_lineheight) ? "line-height: " . $settings->text_lineheight . "px;" : "";
		$style_sm .= (isset($settings->text_lineheight_sm) && $settings->text_lineheight_sm) ? "line-height: " . $settings->text_lineheight_sm . "px;" : "";
		$style_xs .= (isset($settings->text_lineheight_xs) && $settings->text_lineheight_xs) ? "line-height: " . $settings->text_lineheight_xs . "px;" : "";

		if(isset($settings->dropcap) && $settings->dropcap && !empty($dropcap_style)){
			$css .= '#sppb-addon-' . $this->addon->id . ' .sppb-dropcap .sppb-addon-content:first-letter{ ' . $dropcap_style . ' }';
		}

		if($style){
			$css .= '#sppb-addon-' . $this->addon->id . '{ ' . $style . ' }';
		}

		$css .= '@media (min-width: 768px) and (max-width: 991px) {';
			if($style_sm){
				$css .= '#sppb-addon-' . $this->addon->id . '{';
					$css .= $style_sm;
				$css .= '}';
			}
			if($dropcap_style_sm){
				$css .= '#sppb-addon-' . $this->addon->id . ' .sppb-dropcap .sppb-addon-content:first-letter {';
					$css .= $dropcap_style_sm;
				$css .= '}';
			}
		$css .='}';

		$css .= '@media (max-width: 767px) {';
			if($style_xs){
				$css .= '#sppb-addon-' . $this->addon->id . '{ ' . $style_xs . ' }';
			}
			if($dropcap_style_xs){
				$css .= '#sppb-addon-' . $this->addon->id . ' .sppb-dropcap .sppb-addon-content:first-letter {';
					$css .= $dropcap_style_xs;
				$css .= '}';
			}
		$css .= '}';

		return $css;
	}

	public static function getTemplate()
	{
		$output = '
		<#
			var dropcap = "";

			if(data.dropcap){
				dropcap = "sppb-dropcap";
			}

			if(!data.heading_selector){
				data.heading_selector = "h3";
			}
		#>
		<style type="text/css">
			#sppb-addon-{{ data.id }}{
				<# if(_.isObject(data.text_fontsize)){ #>
					font-size: {{ data.text_fontsize.md }}px;
				<# } else { #>
					font-size: {{ data.text_fontsize }}px;
				<# } #>

				<# if(_.isObject(data.text_lineheight)){ #>
					line-height: {{ data.text_lineheight.md }}px;
				<# } else { #>
					line-height: {{ data.text_lineheight }}px;
				<# } #>
				font-weight:{{data.text_fontweight}};
			}
			#sppb-addon-{{ data.id }} .sppb-dropcap .sppb-addon-content:first-letter {
				color: {{ data.dropcap_color }};
				<# if(_.isObject(data.dropcap_font_size)){ #>
					font-size: {{data.dropcap_font_size.md}}px;
					line-height: {{data.dropcap_font_size.md}}px;
				<# } else { #>
					font-size: {{data.dropcap_font_size}}px;
					line-height: {{data.dropcap_font_size}}px;
				<# } #>
			}

			@media (min-width: 768px) and (max-width: 991px) {
				#sppb-addon-{{ data.id }}{
					<# if(_.isObject(data.text_fontsize)){ #>
						font-size: {{ data.text_fontsize.sm }}px;
					<# } #>

					<# if(_.isObject(data.text_lineheight)){ #>
						line-height: {{ data.text_lineheight.sm }}px;
					<# } #>
				}
				#sppb-addon-{{ data.id }} .sppb-dropcap .sppb-addon-content:first-letter {
					<# if(_.isObject(data.dropcap_font_size)){ #>
						font-size: {{data.dropcap_font_size.sm}}px;
						line-height: {{data.dropcap_font_size.sm}}px;
					<# } #>
				}
			}
			@media (max-width: 767px) {
				#sppb-addon-{{ data.id }}{
					<# if(_.isObject(data.text_fontsize)){ #>
						font-size: {{ data.text_fontsize.xs }}px;
					<# } #>

					<# if(_.isObject(data.text_lineheight)){ #>
						line-height: {{ data.text_lineheight.xs }}px;
					<# } #>
				}
				#sppb-addon-{{ data.id }} .sppb-dropcap .sppb-addon-content:first-letter {
					<# if(_.isObject(data.dropcap_font_size)){ #>
						font-size: {{data.dropcap_font_size.xs}}px;
						line-height: {{data.dropcap_font_size.xs}}px;
					<# } #>
				}
			}
		</style>
		<div class="sppb-addon sppb-addon-text-block {{ dropcap }} {{ data.alignment }} {{ data.class }}">
			<#
			let heading_selector = data.heading_selector || "h3";
			if( !_.isEmpty( data.title ) ){ #><{{ heading_selector }} class="sppb-addon-title sp-inline-editable-element" data-id={{data.id}} data-fieldName="title" contenteditable="true">{{{ data.title }}}</{{ heading_selector }}><# } #>
			<div id="addon-text-{{data.id}}" class="sppb-addon-content sp-editable-content" data-id={{data.id}} data-fieldName="text">{{{ data.text }}}</div>
		</div>';
		return $output;
	}
}
