<?php
/**
 * @package SP Page Builder
 * @author JoomShaper http://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2019 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/
//no direct accees
defined ('_JEXEC') or die ('Restricted access');

class SppagebuilderAddonImage extends SppagebuilderAddons{

	public function render() {
		$settings = $this->addon->settings;
		$class = (isset($settings->class) && $settings->class) ? $settings->class : '';
		$title = (isset($settings->title) && $settings->title) ? $settings->title : '';
		$title_position = (isset($settings->title_position) && $settings->title_position) ? $settings->title_position : 'top';
		$heading_selector = (isset($settings->heading_selector) && $settings->heading_selector) ? $settings->heading_selector : 'h3';

		//Options
		$image = (isset($settings->image) && $settings->image) ? $settings->image : '';
		$alt_text = (isset($settings->alt_text) && $settings->alt_text) ? $settings->alt_text : '';
		$position = (isset($settings->position) && $settings->position) ? $settings->position : '';
		$link = (isset($settings->link) && $settings->link) ? $settings->link : '';
		$target = (isset($settings->target) && $settings->target) ? 'rel="noopener noreferrer" target="' . $settings->target . '"' : '';
		$open_lightbox = (isset($settings->open_lightbox) && $settings->open_lightbox) ? $settings->open_lightbox : 0;
		$image_overlay = (isset($settings->overlay_color) && $settings->overlay_color) ? 1 : 0;

		$output = '';

		if($image) {
			$output  .= '<div class="sppb-addon sppb-addon-single-image ' . $position . ' ' . $class . '">';
			$output .= ($title && $title_position != 'bottom') ? '<'.$heading_selector.' class="sppb-addon-title">' . $title . '</'.$heading_selector.'>' : '';
			$output .= '<div class="sppb-addon-content">';
			$output .= '<div class="sppb-addon-single-image-container">';

			if (empty($alt_text)) {
				if (!empty($title)) {
					$alt_text = $title;
				} else {
					$alt_text = basename($image);
				}
			}

			if($image_overlay && $open_lightbox) {
				$output .= '<div class="sppb-addon-image-overlay">';
				$output .= '</div>';
			}

			if($open_lightbox) {
				$output .= '<a class="sppb-magnific-popup sppb-addon-image-overlay-icon" data-popup_type="image" data-mainclass="mfp-no-margins mfp-with-zoom" href="' . $image . '">+</a>';
			}

			if(!$open_lightbox) {
				$output .= ($link) ? '<a ' . $target . ' href="' . $link . '">' : '';
			}

			$output  .= '<img class="sppb-img-responsive" src="' . $image . '" alt="'. $alt_text .'" title="'.$title.'">';

			if(!$open_lightbox) {
				$output .= ($link) ? '</a>' : '';
			}

			$output  .= '</div>';
			$output .= ($title && $title_position == 'bottom') ? '<'.$heading_selector.' class="sppb-addon-title">' . $title . '</'.$heading_selector.'>' : '';
			$output  .= '</div>';
			$output  .= '</div>';
		}

		return $output;
	}

	public function scripts() {
		return array(JURI::base(true) . '/components/com_sppagebuilder/assets/js/jquery.magnific-popup.min.js');
	}

	public function stylesheets() {
		return array(JURI::base(true) . '/components/com_sppagebuilder/assets/css/magnific-popup.css');
	}

	public function css() {
		$addon_id = '#sppb-addon-' . $this->addon->id;
		$settings = $this->addon->settings;
		$open_lightbox = (isset($settings->open_lightbox) && $settings->open_lightbox) ? $settings->open_lightbox : 0;
		$style = (isset($settings->overlay_color) && $settings->overlay_color) ? 'background-color: ' . $settings->overlay_color . ';' : '';
		$style_img = (isset($settings->border_radius) && $settings->border_radius) ? 'border-radius: ' . $settings->border_radius . 'px;' : '';
		$title_padding = (isset($settings->title_padding) && trim($settings->title_padding)) ? $settings->title_padding : '';
		$title_padding_sm = (isset($settings->title_padding_sm) && trim($settings->title_padding_sm)) ? $settings->title_padding_sm : '';
		$title_padding_xs = (isset($settings->title_padding_xs) && trim($settings->title_padding_xs)) ? $settings->title_padding_xs : '';

		$css = '';
		if($open_lightbox && $style) {
			$css .= $addon_id . ' .sppb-addon-image-overlay{';
				$css .= $style;
				$css .= $style_img;
			$css .= '}';
		}

		$css .= $addon_id . ' img{' . $style_img . '}';
		if($title_padding){
			$css .= $addon_id . ' .sppb-addon-title{padding: ' . $title_padding . '}';
		}
		if($title_padding_sm){
			$css .= '@media (min-width: 768px) and (max-width: 991px) {' .$addon_id. ' .sppb-addon-title{padding: ' .$title_padding_sm . '}}';
		}
		if($title_padding_xs){
			$css .= '@media (max-width: 767px) {' .$addon_id. ' .sppb-addon-title{padding: ' . $title_padding_xs . '}}';
		}

		return $css;

	}

	public static function getTemplate() {
		$output = '
		<#
			var image_overlay = 0;
			if(!_.isEmpty(data.overlay_color)){
				image_overlay = 1;
			}
			var open_lightbox = parseInt(data.open_lightbox);
			var title_font_style = data.title_fontstyle || "";

			var alt_text = data.alt_text;

			if(_.isEmpty(alt_text)){
				if(!_.isEmpty(data.title)){
					alt_text = data.title;
				}
			}
		#>
		<style type="text/css">
			<# if(open_lightbox && data.overlay_color){ #>
				#sppb-addon-{{ data.id }} .sppb-addon-image-overlay{
					background-color: {{ data.overlay_color }};
					border-radius: {{ data.border_radius }}px;
				}
			<# } #>

			#sppb-addon-{{ data.id }} img{
				border-radius: {{ data.border_radius }}px;
			}
			#sppb-addon-{{ data.id }} .sppb-addon-title{
				<# if(_.isObject(data.title_padding)) { #>
					padding:{{data.title_padding.md}};
				<# } else { #>
					padding:{{data.title_padding}};
				<# } #>
			}
			@media (min-width: 768px) and (max-width: 991px) {
				<# if(_.isObject(data.title_padding)) { #>
					#sppb-addon-{{ data.id }} .sppb-addon-title{
						padding: {{data.title_padding.sm}};
					}
				<# } #>
			}
			@media (max-width: 767px) {
				<# if(_.isObject(data.title_padding)) { #>
					#sppb-addon-{{ data.id }} .sppb-addon-title{
						padding: {{data.title_padding.xs}};
					}
				<# } #>
			}
		</style>
		<# if(data.image){ #>
			<div class="sppb-addon sppb-addon-single-image {{ data.position }} {{ data.class }}">
				<# if( !_.isEmpty( data.title ) && data.title_position != "bottom" ){ #><{{ data.heading_selector }} class="sppb-addon-title sp-inline-editable-element" data-id={{data.id}} data-fieldName="title" contenteditable="true">{{ data.title }}</{{ data.heading_selector }}><# } #>
				<div class="sppb-addon-content">
					<div class="sppb-addon-single-image-container">
						<# if(image_overlay && open_lightbox) { #>
							<div class="sppb-addon-image-overlay"></div>
						<# } #>
						<# if(open_lightbox) { #>
							<a class="sppb-magnific-popup sppb-addon-image-overlay-icon" data-popup_type="image" data-mainclass="mfp-no-margins mfp-with-zoom" href=\'{{ data.image }}\'>+</a>
						<# } #>
			
						<# if(!open_lightbox) { #>
							<a target="{{ data.target }}" href=\'{{ data.link }}\'>
						<# } #>

						<# if(data.image.indexOf("http://") == -1 && data.image.indexOf("https://") == -1){ #>
							<img class="sppb-img-responsive" src=\'{{ pagebuilder_base + data.image }}\' alt="{{ alt_text }}" title="{{ data.title }}">
						<# } else { #>
							<img class="sppb-img-responsive" src=\'{{ data.image }}\' alt="{{ alt_text }}" title="{{ data.title }}">
						<# } #>

						<# if(!open_lightbox) { #>
							</a>
						<# } #>

					</div>
					<# if( !_.isEmpty( data.title ) && data.title_position == "bottom" ){ #><{{ data.heading_selector }} class="sppb-addon-title">{{ data.title }}</{{ data.heading_selector }}><# } #>
				</div>
			</div>
		<# } #>
		';

		return $output;
	}

}
