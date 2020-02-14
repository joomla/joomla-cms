<?php
/**
 * @package SP Page Builder
 * @author JoomShaper http://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2017 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/
//no direct accees
defined ('_JEXEC') or die ('restricted aceess');

class SppagebuilderAddonImage_content extends SppagebuilderAddons{

	public function render() {
    
		$class = (isset($this->addon->settings->class) && $this->addon->settings->class) ? $this->addon->settings->class : '';
		$title = (isset($this->addon->settings->title) && $this->addon->settings->title) ? $this->addon->settings->title : '';
		$heading_selector = (isset($this->addon->settings->heading_selector) && $this->addon->settings->heading_selector) ? $this->addon->settings->heading_selector : 'h3';

		//Options
		$image = (isset($this->addon->settings->image) && $this->addon->settings->image) ? $this->addon->settings->image : '';
		$image_width = (isset($this->addon->settings->image_width) && $this->addon->settings->image_width) ? $this->addon->settings->image_width : '';
		$image_alignment = (isset($this->addon->settings->image_alignment) && $this->addon->settings->image_alignment) ? $this->addon->settings->image_alignment : '';
		$text = (isset($this->addon->settings->text) && $this->addon->settings->text) ? $this->addon->settings->text : '';
		$button_text = (isset($this->addon->settings->button_text) && $this->addon->settings->button_text) ? $this->addon->settings->button_text : '';
		$button_url = (isset($this->addon->settings->button_url) && $this->addon->settings->button_url) ? $this->addon->settings->button_url : '';
		$button_classes = (isset($this->addon->settings->button_size) && $this->addon->settings->button_size) ? ' sppb-btn-' . $this->addon->settings->button_size : '';
		$button_classes .= (isset($this->addon->settings->button_type) && $this->addon->settings->button_type) ? ' sppb-btn-' . $this->addon->settings->button_type : '';
		$button_classes .= (isset($this->addon->settings->button_shape) && $this->addon->settings->button_shape) ? ' sppb-btn-' . $this->addon->settings->button_shape: ' sppb-btn-rounded';
		$button_classes .= (isset($this->addon->settings->button_appearance) && $this->addon->settings->button_appearance) ? ' sppb-btn-' . $this->addon->settings->button_appearance : '';
		$button_classes .= (isset($this->addon->settings->button_block) && $this->addon->settings->button_block) ? ' ' . $this->addon->settings->button_block : '';
		$button_icon = (isset($this->addon->settings->button_icon) && $this->addon->settings->button_icon) ? $this->addon->settings->button_icon : '';
		$button_icon_position = (isset($this->addon->settings->button_icon_position) && $this->addon->settings->button_icon_position) ? $this->addon->settings->button_icon_position: 'left';
		$button_position = (isset($this->addon->settings->button_position) && $this->addon->settings->button_position) ? $this->addon->settings->button_position : '';
		$button_attribs = (isset($this->addon->settings->button_target) && $this->addon->settings->button_target) ? ' target="' . $this->addon->settings->button_target . '"' : '';
		$button_attribs .= (isset($this->addon->settings->button_url) && $this->addon->settings->button_url) ? ' href="' . $this->addon->settings->button_url . '"' : '';

		if($button_icon_position == 'left') {
			$button_text = ($button_icon) ? '<i class="fa ' . $button_icon . '"></i> ' . $button_text : $button_text;
		} else {
			$button_text = ($button_icon) ? $button_text . ' <i class="fa ' . $button_icon . '"></i>' : $button_text;
		}
		$button_output = '';
		if ($button_text) {
			$button_output = '<a' . $button_attribs . ' id="btn-'. $this->addon->id .'" class="sppb-btn' . $button_classes . '">' . $button_text . '</a>';
		}

		if($image_alignment=='left') {
			$content_class = ' sppb-col-sm-offset-6';
		} else {
			$content_class = '';
		}

		if($image && $text) {

			$output  = '<div class="sppb-addon sppb-addon-image-content aligment-'. $image_alignment .' clearfix ' . $class . '">';

			//Image
			$output .= '<div style="background-image: url(' . JURI::base(true) . '/' . $image . ');" class="sppb-image-holder">';
			$output .= '</div>';

			//Content
			$output .= '<div class="sppb-container">';
			$output .= '<div class="sppb-row">';

			$output .= '<div class="sppb-col-sm-6'. $content_class .'">';
			$output .= '<div class="sppb-content-holder">';
			$output .= ($title) ? '<'.$heading_selector.' class="sppb-image-content-title">' . $title . '</'.$heading_selector.'>' : '';
			$output .= ($text) ? '<p class="sppb-image-content-text">' . $text . '</p>' : '';

			$output .= $button_output;

			$output .= '</div>';
			$output .= '</div>';
			$output .= '</div>';
			$output .= '</div>';

			$output .= '</div>';

			return $output;
		}

		return;
	}

	public function css() {
		$addon_id = '#sppb-addon-' . $this->addon->id;
		$layout_path = JPATH_ROOT . '/components/com_sppagebuilder/layouts';
		$css_path = new JLayoutFile('addon.css.button', $layout_path);
		return $css_path->render(array('addon_id' => $addon_id, 'options' => $this->addon->settings, 'id' => 'btn-' . $this->addon->id));
	}

	public static function getTemplate() {
		$output = '
		<#
			var content_class = "";
			if(data.image_alignment == "left") {
				content_class = " sppb-col-sm-offset-6";
			}

			var button_text = data.button_text;
			if(data.button_icon_position == "left" && data.button_icon) {
				button_text = \'<i class="fa \' + data.button_icon + \'"></i> \' + data.button_text;
			} else if(data.button_icon){
				button_text = data.button_text + \' <i class="fa \' + data.button_icon + \'"></i>\';
			}

			var button_classes = "";

			if(data.button_size){
				button_classes = button_classes + " sppb-btn-" + data.button_size;
			}

			if(data.button_type){
				button_classes = button_classes + " sppb-btn-" + data.button_type;
			}

			if(data.button_shape){
				button_classes = button_classes + " sppb-btn-" + data.button_shape;
			} else {
				button_classes = button_classes + " sppb-btn-rounded";
			}

			if(data.button_appearance){
				button_classes = button_classes + " sppb-btn-" + data.button_appearance;
			}

			if(data.button_block){
				button_classes = button_classes + " " + data.button_block;
			}

			var button_fontstyle = data.button_fontstyle || "";

			var padding = "";
			var padding_sm = "";
			var padding_xs = "";
			if(data.content_padding){
				if(_.isObject(data.content_padding)){
					if(data.content_padding.md.trim() !== ""){
						padding = data.content_padding.md.split(" ").map(item => {
							if(_.isEmpty(item)){
								return "0";
							}
							return item;
						}).join(" ")
					}

					if(data.content_padding.sm.trim() !== ""){
						padding_sm = data.content_padding.sm.split(" ").map(item => {
							if(_.isEmpty(item)){
								return "0";
							}
							return item;
						}).join(" ")
					}

					if(data.content_padding.xs.trim() !== ""){
						padding_xs = data.content_padding.xs.split(" ").map(item => {
							if(_.isEmpty(item)){
								return "0";
							}
							return item;
						}).join(" ")
					}
				}

			}
		#>
		<style type="text/css">
			#sppb-addon-{{ data.id }} .sppb-image-holder{
				background-image: url({{ data.image }});
			}
			#sppb-addon-{{ data.id }} .sppb-addon-image-content .sppb-content-holder{
				padding: {{ padding }};
			}
			#sppb-addon-{{ data.id }} #btn-{{ data.id }}.sppb-btn-{{ data.button_type }}{
				letter-spacing: {{ data.button_letterspace }};
				<# if(typeof data.button_margin_top !== "undefined" && typeof data.button_margin_top.md !== "undefined"){ #>
					margin-top: {{ data.button_margin_top.md }}px;
				<# } #>
				<# if(_.isArray(button_fontstyle)) { #>
					<# if(button_fontstyle.indexOf("underline") !== -1){ #>
						text-decoration: underline;
					<# } #>
					<# if(button_fontstyle.indexOf("uppercase") !== -1){ #>
						text-transform: uppercase;
					<# } #>
					<# if(button_fontstyle.indexOf("italic") !== -1){ #>
						font-style: italic;
					<# } #>
					<# if(button_fontstyle.indexOf("lighter") !== -1){ #>
						font-weight: lighter;
					<# } else if(button_fontstyle.indexOf("normal") !== -1){#>
						font-weight: normal;
					<# } else if(button_fontstyle.indexOf("bold") !== -1){#>
						font-weight: bold;
					<# } else if(button_fontstyle.indexOf("bolder") !== -1){#>
						font-weight: bolder;
					<# } #>
				<# } #>
			}

			<# if(data.button_type == "custom"){ #>
				#sppb-addon-{{ data.id }} #btn-{{ data.id }}.sppb-btn-custom{
					color: {{ data.button_color }};
					<# if(data.button_appearance == "outline"){ #>
						border-color: {{ data.button_background_color }}
					<# } else if(data.button_appearance == "3d"){ #>
						border-bottom-color: {{ data.button_background_color_hover }};
						background-color: {{ data.button_background_color }};
					<# } else { #>
						background-color: {{ data.button_background_color }};
					<# } #>
				}

				#sppb-addon-{{ data.id }} #btn-{{ data.id }}.sppb-btn-custom:hover{
					color: {{ data.button_color_hover }};
					background-color: {{ data.button_background_color_hover }};
					<# if(data.appearance == "outline"){ #>
						border-color: {{ data.button_background_color_hover }}
					<# } #>
				}
			<# } #>

			@media (min-width: 768px) and (max-width: 991px) {
				#sppb-addon-{{ data.id }} .sppb-addon-image-content .sppb-content-holder{
					padding: {{ padding_sm }};
				}
				#sppb-addon-{{ data.id }} #btn-{{ data.id }}.sppb-btn-{{ data.button_type }}{
					<# if(typeof data.button_margin_top !== "undefined" && typeof data.button_margin_top.sm !== "undefined"){ #>
						margin-top: {{ data.button_margin_top.sm }}px;
					<# } #>
				}
			}
			@media (max-width: 767px) {
				#sppb-addon-{{ data.id }} .sppb-addon-image-content .sppb-content-holder{
					padding: {{ padding_xs }};
				}
				#sppb-addon-{{ data.id }} #btn-{{ data.id }}.sppb-btn-{{ data.button_type }}{
					<# if(typeof data.button_margin_top !== "undefined" && typeof data.button_margin_top.xs !== "undefined"){ #>
						margin-top: {{ data.button_margin_top.xs }}px;
					<# } #>
				}
			}
		</style>
		<div class="sppb-addon sppb-addon-image-content aligment-{{ data.image_alignment }} clearfix {{ data.class }}">
			<div class="sppb-image-holder"></div>
			<div class="sppb-container">
				<div class="sppb-row">
					<# if(data.image_alignment == "left") { #>
						<div class="sppb-col-sm-6"></div>
					<# } #>
					<div class="sppb-col-sm-6 {{ content_class }}">
						<div class="sppb-content-holder">
							<# if( !_.isEmpty( data.title ) ){ #><{{ data.heading_selector }} class="sppb-image-content-title sppb-addon-title">{{ data.title }}</{{ data.heading_selector }}><# } #>
							<# if(data.text){ #><p class="sppb-image-content-text">{{{ data.text }}}</p><# } #>
							<# if(button_text){ #><a href=\'{{ data.button_url }}\' target="{{ data.button_target }}" id="btn-{{ data.id }}" class="sppb-btn {{ button_classes }}">{{{ button_text }}}</a><# } #>
						</div>
					</div>
				</div>
			</div>
		</div>
		';

		return $output;
	}

}
