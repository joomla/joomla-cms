<?php

/**
 * @package SP Page Builder
 * @author JoomShaper http://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2019 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
 */
//no direct accees
defined('_JEXEC') or die('Restricted access');

class SppagebuilderAddonButton extends SppagebuilderAddons {

    public function render() {
		$settings = $this->addon->settings;
        $alignment = (isset($settings->alignment) && $settings->alignment) ? $settings->alignment : 'sppb-text-left';
        $class = (isset($settings->class) && $settings->class) ? ' ' . $settings->class : '';
        $class .= (isset($settings->type) && $settings->type) ? ' sppb-btn-' . $settings->type : '';
        $class .= (isset($settings->size) && $settings->size) ? ' sppb-btn-' . $settings->size : '';
        $class .= (isset($settings->block) && $settings->block) ? ' ' . $settings->block : '';
        $class .= (isset($settings->shape) && $settings->shape) ? ' sppb-btn-' . $settings->shape : ' sppb-btn-rounded';
        $class .= (isset($settings->appearance) && $settings->appearance) ? ' sppb-btn-' . $settings->appearance : '';
        $attribs = (isset($settings->target) && $settings->target) ? ' rel="noopener noreferrer" target="' . $settings->target . '"' : '';
        $attribs .= (isset($settings->url) && $settings->url) ? ' href="' . $settings->url . '"' : '';
        $attribs .= ' id="btn-' . $this->addon->id . '"';
        $text = (isset($settings->text) && $settings->text) ? $settings->text : '';
        $icon = (isset($settings->icon) && $settings->icon) ? $settings->icon : '';
        $icon_position = (isset($settings->icon_position) && $settings->icon_position) ? $settings->icon_position : 'left';

        if ($icon_position == 'left') {
            $text = ($icon) ? '<i class="fa ' . $icon . '" aria-hidden="true"></i> ' . $text : $text;
        } else {
            $text = ($icon) ? $text . ' <i class="fa ' . $icon . '" aria-hidden="true"></i>' : $text;
        }

        $output = '<div class="' . $alignment . '">';
        $output .= '<a' . $attribs . ' class="sppb-btn ' . $class . '">' . $text . '</a>';
        $output .= '</div>';

        return $output;
    }

    public function css() {
		$settings = $this->addon->settings;
        $addon_id = '#sppb-addon-' . $this->addon->id;
		$layout_path = JPATH_ROOT . '/components/com_sppagebuilder/layouts';
		$css = '';

        $css_path = new JLayoutFile('addon.css.button', $layout_path);

        $options = new stdClass;
        $options->button_type = (isset($settings->type) && $settings->type) ? $settings->type : '';
        $options->button_appearance = (isset($settings->appearance) && $settings->appearance) ? $settings->appearance : '';
        $options->button_color = (isset($settings->color) && $settings->color) ? $settings->color : '';
        $options->button_border_width = (isset($settings->button_border_width) && $settings->button_border_width) ? $settings->button_border_width : '';
        $options->button_color_hover = (isset($settings->color_hover) && $settings->color_hover) ? $settings->color_hover : '';
        $options->button_background_color = (isset($settings->background_color) && $settings->background_color) ? $settings->background_color : '';
        $options->button_background_color_hover = (isset($settings->background_color_hover) && $settings->background_color_hover) ? $settings->background_color_hover : '';
        $options->button_fontstyle = (isset($settings->fontstyle) && $settings->fontstyle) ? $settings->fontstyle : '';
        $options->button_font_style = (isset($settings->font_style) && $settings->font_style) ? $settings->font_style : '';
        $options->button_padding = (isset($settings->button_padding) && $settings->button_padding) ? $settings->button_padding : '';
        $options->button_padding_sm = (isset($settings->button_padding_sm) && $settings->button_padding_sm) ? $settings->button_padding_sm : '';
        $options->button_padding_xs = (isset($settings->button_padding_xs) && $settings->button_padding_xs) ? $settings->button_padding_xs : '';
		$options->fontsize = (isset($settings->fontsize) && $settings->fontsize) ? $settings->fontsize : '';
		//Button Type Link
		$options->link_button_color = (isset($settings->link_button_color) && $settings->link_button_color) ? $settings->link_button_color : '';
		$options->link_border_color = (isset($settings->link_border_color) && $settings->link_border_color) ? $settings->link_border_color : '';
		$options->link_button_border_width = (isset($settings->link_button_border_width) && $settings->link_button_border_width) ? $settings->link_button_border_width : '';
		$options->link_button_padding_bottom = (isset($settings->link_button_padding_bottom) && gettype($settings->link_button_padding_bottom)=='string') ? $settings->link_button_padding_bottom : '';
		//Link Hover
		$options->link_button_hover_color = (isset($settings->link_button_hover_color) && $settings->link_button_hover_color) ? $settings->link_button_hover_color : '';
		$options->link_button_border_hover_color = (isset($settings->link_button_border_hover_color) && $settings->link_button_border_hover_color) ? $settings->link_button_border_hover_color : '';
		
        $options->fontsize_sm = (isset($settings->fontsize_sm) && $settings->fontsize_sm) ? $settings->fontsize_sm : '';
        $options->fontsize_xs = (isset($settings->fontsize_xs) && $settings->fontsize_xs) ? $settings->fontsize_xs : '';
        $options->button_letterspace = (isset($settings->letterspace) && $settings->letterspace) ? $settings->letterspace : '';
        $options->button_background_gradient = (isset($settings->background_gradient) && $settings->background_gradient) ? $settings->background_gradient : new stdClass();
        $options->button_background_gradient_hover = (isset($settings->background_gradient_hover) && $settings->background_gradient_hover) ? $settings->background_gradient_hover : new stdClass();

		$css .= $css_path->render(array('addon_id' => $addon_id, 'options' => $options, 'id' => 'btn-' . $this->addon->id));

		//Icon style
		$icon_margin = (isset($settings->icon_margin) && trim($settings->icon_margin)) ? 'margin:'.$settings->icon_margin.';' : '';
		$icon_margin_sm = (isset($settings->icon_margin_sm) && trim($settings->icon_margin_sm)) ? 'margin:'.$settings->icon_margin_sm.';' : '';
		$icon_margin_xs = (isset($settings->icon_margin_xs) && trim($settings->icon_margin_xs)) ? 'margin:'.$settings->icon_margin_xs.';' : '';

		if($icon_margin) {
			$css .= $addon_id . ' .sppb-btn i {';
			$css .= $icon_margin;
			$css .= '}';
		}

		if($icon_margin_sm){
			$css .= '@media (min-width: 768px) and (max-width: 991px) {';
				$css .= $addon_id . ' .sppb-btn i {';
				$css .= $icon_margin_sm;
				$css .= '}';
			$css .= '}';
		}
		if($icon_margin_xs){
			$css .= '@media (max-width: 767px) {';
				$css .= $addon_id . ' .sppb-btn i {';
				$css .= $icon_margin_xs;
				$css .= '}';
			$css .= '}';
		}
		
		return $css;
    }

    public static function getTemplate() {
        $output = '
		<#
			var modern_font_style = false;
			var classList = data.class;
			classList += " sppb-btn-"+data.type;
			classList += " sppb-btn-"+data.size;
			classList += " sppb-btn-"+data.shape;
			if(!_.isEmpty(data.appearance)){
				classList += " sppb-btn-"+data.appearance;
			}

			classList += " "+data.block;

			var button_fontstyle = data.fontstyle || "";
			var button_font_style = data.font_style || "";

			var button_padding = "";
			var button_padding_sm = "";
			var button_padding_xs = "";
			if(data.button_padding){
				if(_.isObject(data.button_padding)){
					if(_.trim(data.button_padding.md) !== ""){
						button_padding = _.split(data.button_padding.md, " ").map(item => {
							if(_.isEmpty(item)){
								return "0";
							}
							return item;
						}).join(" ")
					}

					if(_.trim(data.button_padding.sm) !== ""){
						button_padding_sm = _.split(data.button_padding.sm, " ").map(item => {
							if(_.isEmpty(item)){
								return "0";
							}
							return item;
						}).join(" ")
					}

					if(_.trim(data.button_padding.xs) !== ""){
						button_padding_xs = _.split(data.button_padding.xs, " ").map(item => {
							if(_.isEmpty(item)){
								return "0";
							}
							return item;
						}).join(" ")
					}
				} else {
					if(_.trim(data.button_padding) !== ""){
						button_padding = _.split(data.button_padding, " ").map(item => {
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

			#sppb-addon-{{ data.id }} #btn-{{ data.id }}.sppb-btn-{{ data.type }}{
				letter-spacing: {{ data.letterspace }};

				<# if(_.isObject(button_font_style) && button_font_style.underline) { #>
					text-decoration: underline;
					<# modern_font_style = true #>
				<# } #>

				<# if(_.isObject(button_font_style) && button_font_style.italic) { #>
					font-style: italic;
					<# modern_font_style = true #>
				<# } #>

				<# if(_.isObject(button_font_style) && button_font_style.uppercase) { #>
					text-transform: uppercase;
					<# modern_font_style = true #>
				<# } #>

				<# if(_.isObject(button_font_style) && button_font_style.weight) { #>
					font-weight: {{ button_font_style.weight }};
					<# modern_font_style = true #>
				<# } #>

				<# if(!modern_font_style) { #>
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
				<# } #>
			}

			<# if(data.type == "custom"){ #>
				#sppb-addon-{{ data.id }} #btn-{{ data.id }}.sppb-btn-custom{
						<# if(_.isObject(data.fontsize)){ #>
							font-size: {{data.fontsize.md}}px;
						<# } else { #>
							font-size: {{data.fontsize}}px;
						<# } #>
					color: {{ data.color }};
					padding: {{ button_padding }};
					<# if(data.appearance == "outline"){ #>
						border-color: {{ data.background_color }};
						background-color: transparent;
					<# } else if(data.appearance == "3d"){ #>
						border-bottom-color: {{ data.background_color_hover }};
						background-color: {{ data.background_color }};
					<# } else if(data.appearance == "gradient"){ #>
						border: none;
						<# if(typeof data.background_gradient.type !== "undefined" && data.background_gradient.type == "radial"){ #>
							background-image: radial-gradient(at {{ data.background_gradient.radialPos || "center center"}}, {{ data.background_gradient.color }} {{ data.background_gradient.pos || 0 }}%, {{ data.background_gradient.color2 }} {{ data.background_gradient.pos2 || 100 }}%);
						<# } else { #>
							background-image: linear-gradient({{ data.background_gradient.deg || 0}}deg, {{ data.background_gradient.color }} {{ data.background_gradient.pos || 0 }}%, {{ data.background_gradient.color2 }} {{ data.background_gradient.pos2 || 100 }}%);
						<# } #>
					<# } else { #>
						background-color: {{ data.background_color }};
					<# } #>
				}

				#sppb-addon-{{ data.id }} #btn-{{ data.id }}.sppb-btn-custom:hover{
					color: {{ data.color_hover }};
					background-color: {{ data.background_color_hover }};
					<# if(data.appearance == "outline"){ #>
						border-color: {{ data.background_color_hover }};
					<# } else if(data.appearance == "gradient"){ #>
						<# if(typeof data.background_gradient_hover.type !== "undefined" && data.background_gradient_hover.type == "radial"){ #>
							background-image: radial-gradient(at {{ data.background_gradient_hover.radialPos || "center center"}}, {{ data.background_gradient_hover.color }} {{ data.background_gradient_hover.pos || 0 }}%, {{ data.background_gradient_hover.color2 }} {{ data.background_gradient_hover.pos2 || 100 }}%);
						<# } else { #>
							background-image: linear-gradient({{ data.background_gradient_hover.deg || 0}}deg, {{ data.background_gradient_hover.color }} {{ data.background_gradient_hover.pos || 0 }}%, {{ data.background_gradient_hover.color2 }} {{ data.background_gradient_hover.pos2 || 100 }}%);
						<# } #>
					<# } #>
				}
				@media (min-width: 768px) and (max-width: 991px) {
					#sppb-addon-{{ data.id }} #btn-{{ data.id }}.sppb-btn-custom{
						<# if(_.isObject(data.fontsize)){ #>
							font-size: {{data.fontsize.sm}}px;
						<# } #>
						padding: {{ button_padding_sm }};
					}
				}
				@media (max-width: 767px) {
					#sppb-addon-{{ data.id }} #btn-{{ data.id }}.sppb-btn-custom{
						<# if(_.isObject(data.fontsize)){ #>
							font-size: {{data.fontsize.xs}}px;
						<# } #>
						padding: {{ button_padding_xs }};
					}
				}
			<# } #>
			<# if(data.type == "link"){ #>
				#sppb-addon-{{ data.id }} #btn-{{ data.id }}.sppb-btn-link{
					color: {{data.link_button_color}};
					border-color: {{data.link_border_color}};
					border-width: 0 0 {{data.link_button_border_width}}px 0;
					padding: 0 0 {{data.link_button_padding_bottom}}px 0;
					text-decoration: none;
					border-radius: 0;
				}
				<# if(data.link_button_status == "hover") { #>
					#sppb-addon-{{ data.id }} #btn-{{ data.id }}.sppb-btn-link:hover,
					#sppb-addon-{{ data.id }} #btn-{{ data.id }}.sppb-btn-link:focus{
						color: {{data.link_button_hover_color}};
						border-color: {{data.link_button_border_hover_color}};
					}
				<# } #>
			<# } #>

			#sppb-addon-{{ data.id }} .sppb-btn i{
				<# if(_.isObject(data.icon_margin)){ #>
					margin: {{data.icon_margin.md}};
				<# } else { #>
					margin: {{data.icon_margin}};
				<# } #>
			}
			@media (min-width: 768px) and (max-width: 991px) {
				#sppb-addon-{{ data.id }} .sppb-btn i{
					<# if(_.isObject(data.icon_margin)){ #>
						margin: {{data.icon_margin.sm}};
					<# } #>
				}
			}
			@media (max-width: 767px) {
				#sppb-addon-{{ data.id }} .sppb-btn i{
					<# if(_.isObject(data.icon_margin)){ #>
						margin: {{data.icon_margin.xs}};
					<# } #>
				}
			}
			
		</style>
		<div class="{{ data.alignment }}">
			<a href=\'{{ data.url }}\' id="btn-{{ data.id }}" target="{{ data.target }}" class="sppb-btn {{ classList }}"><# if(data.icon_position == "left" && !_.isEmpty(data.icon)) { #><i class="fa {{ data.icon }}"></i> <# } #>{{ data.text }}<# if(data.icon_position == "right" && !_.isEmpty(data.icon)) { #> <i class="fa {{ data.icon }}"></i><# } #></a>
		</div>';

        return $output;
    }

}
