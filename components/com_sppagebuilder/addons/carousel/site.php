<?php
/**
 * @package SP Page Builder
 * @author JoomShaper http://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2019 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/
//no direct accees
defined ('_JEXEC') or die ('Restricted access');

class SppagebuilderAddonCarousel extends SppagebuilderAddons {

	public function render() {
		$settings = $this->addon->settings;
		$class = (isset($settings->class) && $settings->class) ? ' ' . $settings->class : '';

		//Addons option
		$autoplay = (isset($settings->autoplay) && $settings->autoplay) ? 1 : 0;
		$controllers = (isset($settings->controllers) && $settings->controllers) ? $settings->controllers : 0;
		$arrows = (isset($settings->arrows) && $settings->arrows) ? $settings->arrows : 0;
		$alignment = (isset($settings->alignment) && $settings->alignment) ? $settings->alignment : 0;
		$interval = (isset($settings->interval) && $settings->interval) ? ((int) $settings->interval * 1000) : 5000;
		$carousel_autoplay = ($autoplay) ? ' data-sppb-ride="sppb-carousel"':'';
        if($autoplay == 0) {
            $interval = 'false';
        }
		$output  = '<div id="sppb-carousel-'. $this->addon->id .'" data-interval="'.$interval.'" class="sppb-carousel sppb-slide' . $class . '"'. $carousel_autoplay .'>';

		if($controllers) {
			$output .= '<ol class="sppb-carousel-indicators">';
				foreach ($settings->sp_carousel_item as $key1 => $value) {
					$output .= '<li data-sppb-target="#sppb-carousel-'. $this->addon->id .'" '. (($key1 == 0) ? ' class="active"': '' ) .'  data-sppb-slide-to="'. $key1 .'"></li>' . "\n";
				}
			$output .= '</ol>';
		}

		$output .= '<div class="sppb-carousel-inner ' . $alignment . '">';

		if(isset($settings->sp_carousel_item) && count((array) $settings->sp_carousel_item)){
			foreach ($settings->sp_carousel_item as $key => $value) {
				$button_url = (isset($value->button_url) && $value->button_url) ? $value->button_url : '';
	
				$output   .= '<div class="sppb-item sppb-item-'. $this->addon->id . $key . ' ' . ((isset($value->bg) && $value->bg) ? ' sppb-item-has-bg' : '') . (($key == 0) ? ' active' : '') .'">';
				$alt_text = isset($value->title) ? $value->title : '';
				$output  .= (isset($value->bg) && $value->bg) ? '<img src="' . $value->bg . '" alt="'.$alt_text.'">' : '';

				$output  .= '<div class="sppb-carousel-item-inner">';
				$output  .= '<div class="sppb-carousel-caption">';
				$output  .= '<div class="sppb-carousel-text">';
	
				if((isset($value->title) && $value->title) || (isset($value->content) && $value->content) ) {
					$output  .= (isset($value->title) && $value->title) ? '<h2>' . $value->title . '</h2>' : '';
					$output  .= (isset($value->content) && $value->content) ? '<div class="sppb-carousel-content">' . $value->content . '</div>': '';
					if(isset($value->button_text) && $value->button_text) {
						$button_class = (isset($value->button_type) && $value->button_type) ? ' sppb-btn-' . $value->button_type : ' sppb-btn-default';
						$button_class .= (isset($value->button_size) && $value->button_size) ? ' sppb-btn-' . $value->button_size : '';
						$button_class .= (isset($value->button_shape) && $value->button_shape) ? ' sppb-btn-' . $value->button_shape: ' sppb-btn-rounded';
						$button_class .= (isset($value->button_appearance) && $value->button_appearance) ? ' sppb-btn-' . $value->button_appearance : '';
						$button_class .= (isset($value->button_block) && $value->button_block) ? ' ' . $value->button_block : '';
						$button_icon = (isset($value->button_icon) && $value->button_icon) ? $value->button_icon : '';
						$button_icon_position = (isset($value->button_icon_position) && $value->button_icon_position) ? $value->button_icon_position: 'left';
						$button_target = (isset($value->button_target) && $value->button_target) ? $value->button_target : '_self';
	
						if($button_icon_position == 'left') {
							$value->button_text = ($button_icon) ? '<i aria-hidden="true" class="fa ' . $button_icon . '" aria-hidden="true"></i> ' . $value->button_text : $value->button_text;
						} else {
							$value->button_text = ($button_icon) ? $value->button_text . ' <i aria-hidden="true" class="fa ' . $button_icon . '" aria-hidden="true"></i>' : $value->button_text;
						}
	
						$output  .= '<a href="' . $button_url . '" target="' . $button_target . '" '.($button_target === '_blank' ? 'rel="noopener noreferrer"' : '').' id="btn-'. ($this->addon->id + $key) .'" class="sppb-btn'. $button_class .'">' . $value->button_text . '</a>';
					}
				}
	
				$output  .= '</div>';
				$output  .= '</div>';
	
				$output  .= '</div>';
				$output  .= '</div>';
			}
		}


		$output	.= '</div>';

		if($arrows) {
			$output	.= '<a href="#sppb-carousel-'. $this->addon->id .'" class="sppb-carousel-arrow left sppb-carousel-control" data-slide="prev" aria-label="'.JText::_('COM_SPPAGEBUILDER_ARIA_PREVIOUS').'"><i class="fa fa-chevron-left" aria-hidden="true"></i></a>';
			$output	.= '<a href="#sppb-carousel-'. $this->addon->id .'" class="sppb-carousel-arrow right sppb-carousel-control" data-slide="next" aria-label="'.JText::_('COM_SPPAGEBUILDER_ARIA_NEXT').'"><i class="fa fa-chevron-right" aria-hidden="true"></i></a>';
		}

		$output .= '</div>';

		return $output;
	}

	public function css() {
		$addon_id = '#sppb-addon-' . $this->addon->id;
		$layout_path = JPATH_ROOT . '/components/com_sppagebuilder/layouts';
		$css = '';

		// Buttons style
		foreach ($this->addon->settings->sp_carousel_item as $key => $value) {

			if(isset($value->button_text)) {
				$css_path = new JLayoutFile('addon.css.button', $layout_path);
				$css .= $css_path->render(array('addon_id' => $addon_id, 'options' => $value, 'id' => 'btn-' . ($this->addon->id + $key) ));
			}

			$title_css = '';
			$title_css .= (isset($value->title_fontsize) && $value->title_fontsize) ? 'font-size:' . $value->title_fontsize . 'px;' : '';
			$title_css .= (isset($value->title_lineheight) && $value->title_lineheight) ? 'line-height:' . $value->title_lineheight . 'px;' : '';
			$title_css .= (isset($value->title_color) && !empty($value->title_color)) ? 'color:' . $value->title_color . ';' : '';

			if(isset($value->title_font_family) && $value->title_font_family) {
				$font_path = new JLayoutFile('addon.css.fontfamily', $layout_path);
				$font_path->render(array('font'=>$value->title_font_family));
				$title_css .= 'font-family: ' . $value->title_font_family . ';';
			}

			if(isset($value->title_padding) && $value->title_padding) {
				if(trim($value->title_padding) != "") {
					$title_padding_md = '';
					$title_paddings_md = explode(' ', $value->title_padding);
					foreach($title_paddings_md as $padding_md) {
						if(empty(trim($padding_md))) {
							$title_padding_md .= ' 0';
						} else {
							$title_padding_md .= ' '.$padding_md;
						}
					}
					$title_css .= "padding: " . $title_padding_md . ";\n";
				}
			}

			if(isset($value->title_margin) && $value->title_margin){
				if(trim($value->title_margin) != "") {
					$title_margin_md = '';
					$title_margins_md = explode(' ', $value->title_margin);
					foreach($title_margins_md as $margin_md) {
						if(empty(trim($margin_md))) {
							$title_margin_md .= ' 0';
						} else {
							$title_margin_md .= ' '.$margin_md;
						}
					}
					$title_css .= "margin: " . $title_margin_md . ";\n";
				}
			}

			if(!empty($title_css)){
				$css .= $addon_id . ' .sppb-item-' . $this->addon->id . $key . ' .sppb-carousel-caption h2 {';
					$css .= $title_css;
				$css .= '}';
			}

			$content_css = '';
			$content_css .= (isset($value->content_fontsize) && $value->content_fontsize) ? 'font-size:' . $value->content_fontsize . 'px;' : '';
			$content_css .= (isset($value->content_lineheight) && $value->content_lineheight) ? 'line-height:' . $value->content_lineheight . 'px;' : '';
			$content_css .= (isset($value->content_color) && $value->content_color) ? 'color:' . $value->content_color . ';' : '';

			if(isset($value->content_font_family) && $value->content_font_family) {
				$font_path = new JLayoutFile('addon.css.fontfamily', $layout_path);
				$font_path->render(array('font'=>$value->content_font_family));
				$content_css .= 'font-family: ' . $value->content_font_family . ';';
			}

			if(isset($value->content_padding) && $value->content_padding){
				if(trim($value->content_padding) != ""){
					$content_padding_md = '';
					$content_paddings_md = explode(' ', $value->content_padding);
					foreach($content_paddings_md as $padding_md){
						if(empty(trim($padding_md))){
							$content_padding_md .= ' 0';
						} else {
							$content_padding_md .= ' '.$padding_md;
						}

					}
					$content_css .= "padding: " . $content_padding_md . ";\n";
				}
			}

			if(isset($value->content_margin) && $value->content_margin){
				if(trim($value->content_margin) != ""){
					$content_margin_md = '';
					$content_margins_md = explode(' ', $value->content_margin);
					foreach($content_margins_md as $margin_md){
						if(empty(trim($margin_md))){
							$content_margin_md .= ' 0';
						} else {
							$content_margin_md .= ' ' . $margin_md;
						}

					}
					$content_css .= "margin: " . $content_margin_md . ";\n";
				}
			}

			if(!empty($content_css)){
				$css .= $addon_id . ' .sppb-item-' . $this->addon->id . $key . ' .sppb-carousel-caption .sppb-carousel-content{';
					$css .= $content_css;
				$css .= '}';
			}

			$selector_css = new JLayoutFile('addon.css.selector', $layout_path);
			$css .= $selector_css->render(
				array(
					'options'=>$value,
					'addon_id'=>$addon_id,
					'selector'=>'#sppb-item-' . ($this->addon->id . $key)
				)
			);

			// echo $css;
			// die();

			// Tablet CSS
			$tablet_css = '';
			$title_css = '';
			$title_css .= (isset($value->title_fontsize_sm) && $value->title_fontsize_sm) ? 'font-size:' . $value->title_fontsize_sm . 'px;' : '';
			$title_css .= (isset($value->title_lineheight_sm) && $value->title_lineheight_sm) ? 'line-height:' . $value->title_lineheight_sm . 'px;' : '';

			if(isset($value->title_padding_sm) && $value->title_padding_sm){
				if(trim($value->title_padding_sm) != "") {
					$title_padding_sm = '';
					$title_paddings_sm = explode(' ', $value->title_padding_sm);
					foreach($title_paddings_sm as $padding_sm){
						if(empty(trim($padding_sm))){
							$title_padding_sm .= ' 0';
						} else {
							$title_padding_sm .= ' '.$padding_sm;
						}
					}
					$title_css .= "padding: " . $title_padding_sm . ";\n";
				}
			}

			if(isset($value->title_padding_sm) && $value->title_margin_sm){
				if(trim($value->title_margin_sm) != "") {
					$title_margin_sm = '';
					$title_margins_sm = explode(' ', $value->title_margin_sm);
					foreach($title_margins_sm as $margin_sm){
						if(empty(trim($margin_sm))){
							$title_margin_sm .= ' 0';
						} else {
							$title_margin_sm .= ' '.$margin_sm;
						}
					}
					$title_css .= "margin: " . $title_margin_sm . ";\n";
				}
			}

			if(!empty($title_css)){
				$tablet_css .= $addon_id . ' .sppb-item-' . $this->addon->id . $key . ' .sppb-carousel-caption h2 {';
				$tablet_css .= $title_css;
				$tablet_css .= '}';
			}

			$content_css = '';
			$content_css .= (isset($value->content_fontsize_sm) && $value->content_fontsize_sm) ? 'font-size:' . $value->content_fontsize_sm . 'px;' : '';
			$content_css .= (isset($value->content_lineheight_sm) && $value->content_lineheight_sm) ? 'line-height:' . $value->content_lineheight_sm . 'px;' : '';

			if(isset($value->content_padding_sm) && $value->content_padding_sm){
				if(trim($value->content_padding_sm) != ""){
					$content_padding_sm = '';
					$content_paddings_sm = explode(' ', $value->content_padding_sm);
					foreach($content_paddings_sm as $padding_sm){
						if(empty(trim($padding_sm))){
							$content_padding_sm .= ' 0';
						} else {
							$content_padding_sm .= ' '.$padding_sm;
						}
					}
					$content_css .= "padding: " . $content_padding_sm . ";\n";
				}
			}

			if(isset($value->content_margin_sm) && $value->content_margin_sm){
				if(trim($value->content_margin_sm) != ""){
					$content_margin_sm = '';
					$content_margins_sm = explode(' ', $value->content_margin_sm);
					foreach($content_margins_sm as $margin_sm){
						if(empty(trim($margin_sm))){
							$content_margin_sm .= ' 0';
						} else {
							$content_margin_sm .= ' '.$margin_sm;
						}
					}
					$content_css .= "margin: " . $content_margin_sm . ";\n";
				}
			}

			if(!empty($content_css)){
				$tablet_css .= $addon_id . ' .sppb-item-' . $this->addon->id . $key . ' .sppb-carousel-caption .sppb-carousel-content{';
					$tablet_css .= $content_css;
				$tablet_css .= '}';
			}

			if(!empty($tablet_css)){
				$css .= '@media (min-width: 768px) and (max-width: 991px) {';
					$css .= $tablet_css;
				$css .= '}';
			}

			// Mobile CSS
			$mobile_css = '';
			$title_css = '';
			$title_css .= (isset($value->title_fontsize_xs) && $value->title_fontsize_xs) ? 'font-size:' . $value->title_fontsize_xs . 'px;' : '';
			$title_css .= (isset($value->title_lineheight_xs) && $value->title_lineheight_xs) ? 'line-height:' . $value->title_lineheight_xs . 'px;' : '';

			if(isset($value->title_padding_xs) && $value->title_padding_xs){
				if(trim($value->title_padding_xs) != "") {
					$title_padding_xs = '';
					$title_paddings_xs = explode(' ', $value->title_padding_xs);
					foreach($title_paddings_xs as $padding_xs){
						if(empty(trim($padding_xs))) {
							$title_padding_xs .= ' 0';
						} else {
							$title_padding_xs .= ' '.$padding_xs;
						}
					}
					$title_css .= "padding: " . $title_padding_xs . ";\n";
				}
			}

			if(isset($value->title_margin_xs) && $value->title_margin_xs){
				if(trim($value->title_margin_xs) != ""){
					$title_margin_xs = '';
					$title_margins_xs = explode(' ', $value->title_margin_xs);
					foreach($title_margins_xs as $margin_xs){
						if(empty(trim($margin_xs))){
							$title_margin_xs .= ' 0';
						} else {
							$title_margin_xs .= ' '.$margin_xs;
						}
					}
					$title_css .= "margin: " . $title_margin_xs . ";\n";
				}
			}

			if(!empty($title_css)){
				$mobile_css .= $addon_id . ' .sppb-item-' . $this->addon->id . $key . ' .sppb-carousel-caption h2{';
					$mobile_css .= $title_css;
				$mobile_css .= '}';
			}

			$content_css = '';
			$content_css .= (isset($value->content_fontsize_xs) && $value->content_fontsize_xs) ? 'font-size:' . $value->content_fontsize_xs . 'px;' : '';
			$content_css .= (isset($value->content_lineheight_xs) && $value->content_lineheight_xs) ? 'line-height:' . $value->content_lineheight_xs . 'px;' : '';

			if(isset($value->content_padding_xs) && $value->content_padding_xs){
				if(trim($value->content_padding_xs) != ""){
					$content_padding_xs = '';
					$content_paddings_xs = explode(' ', $value->content_padding_xs);
					foreach($content_paddings_xs as $padding_xs){
						if(empty(trim($padding_xs))){
							$content_padding_xs .= ' 0';
						} else {
							$content_padding_xs .= ' '.$padding_xs;
						}
					}
					$content_css .= "padding: " . $content_padding_xs . ";\n";
				}
			}

			if(isset($value->content_margin_xs) && $value->content_margin_xs){
				if(trim($value->content_margin_xs) != ""){
					$content_margin_xs = '';
					$content_margins_xs = explode(' ', $value->content_margin_xs);
					foreach($content_margins_xs as $margin_xs){
						if(empty(trim($margin_xs))){
							$content_margin_xs .= ' 0';
						} else {
							$content_margin_xs .= ' '.$margin_xs;
						}
					}
					$content_css .= "margin: " . $content_margin_xs . ";\n";
				}
			}

			if(!empty($content_css)){
				$mobile_css .= $addon_id . ' .sppb-item-' . $this->addon->id . $key . ' .sppb-carousel-caption .sppb-carousel-content{';
					$mobile_css .= $content_css;
				$mobile_css .= '}';
			}

			if(!empty($mobile_css)){
				$css .= '@media (max-width: 767px) {';
					$css .= $mobile_css;
				$css .= '}';
			}
		}

		$speed = (isset($this->addon->settings->speed) && $this->addon->settings->speed) ? $this->addon->settings->speed : 600;

		$css .= $addon_id.' .sppb-carousel-inner > .sppb-item{-webkit-transition-duration: '.$speed.'ms; transition-duration: '.$speed.'ms;}';

		return $css;
	}

	public static function getTemplate(){
		$output = '
		<#
		var interval = data.interval ? parseInt(data.interval) * 1000 : 5000;
		if(data.autoplay==0){
			interval = "false";
		}
		var autoplay = data.autoplay ? \'data-sppb-ride="sppb-carousel"\' : "";
		#>
		<style type="text/css">
			#sppb-addon-{{ data.id }} .sppb-carousel-inner > .sppb-item{
				-webkit-transition-duration: {{ data.speed }}ms;
				transition-duration: {{ data.speed }}ms;
			}
			<# _.each(data.sp_carousel_item, function (carousel_item, key){ #>
				<# var button_fontstyle = carousel_item.button_fontstyle || ""; #>
				#sppb-addon-{{ data.id }} #btn-{{ data.id + "" + key }}.sppb-btn-{{ carousel_item.type }}{
					letter-spacing: {{ carousel_item.button_letterspace }};
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

				<# if(carousel_item.button_type == "custom"){ #>
					#sppb-addon-{{ data.id }} #btn-{{ data.id + "" + key }}.sppb-btn-custom{
						color: {{ carousel_item.button_color }};
						<# if(carousel_item.button_appearance == "outline"){ #>
							border-color: {{ carousel_item.button_background_color }}
						<# } else if(carousel_item.button_appearance == "3d"){ #>
							border-bottom-color: {{ carousel_item.button_background_color_hover }};
							background-color: {{ carousel_item.button_background_color }};
						<# } else if(carousel_item.button_appearance == "gradient"){ #>
							border: none;
							<# if(typeof carousel_item.button_background_gradient.type !== "undefined" && carousel_item.button_background_gradient.type == "radial"){ #>
								background-image: radial-gradient(at {{ carousel_item.button_background_gradient.radialPos || "center center"}}, {{ carousel_item.button_background_gradient.color }} {{ carousel_item.button_background_gradient.pos || 0 }}%, {{ carousel_item.button_background_gradient.color2 }} {{ carousel_item.button_background_gradient.pos2 || 100 }}%);
							<# } else { #>
								background-image: linear-gradient({{ carousel_item.button_background_gradient.deg || 0}}deg, {{ carousel_item.button_background_gradient.color }} {{ carousel_item.button_background_gradient.pos || 0 }}%, {{ carousel_item.button_background_gradient.color2 }} {{ carousel_item.button_background_gradient.pos2 || 100 }}%);
							<# } #>
						<# } else { #>
							background-color: {{ carousel_item.button_background_color }};
						<# } #>
					}

					#sppb-addon-{{ data.id }} #btn-{{ data.id + "" + key }}.sppb-btn-custom:hover{
						color: {{ carousel_item.button_color_hover }};
						background-color: {{ carousel_item.button_background_color_hover }};
						<# if(carousel_item.button_appearance == "outline"){ #>
							border-color: {{ carousel_item.button_background_color_hover }};
						<# } else if(carousel_item.button_appearance == "gradient"){ #>
							<# if(typeof carousel_item.button_background_gradient_hover.type !== "undefined" && carousel_item.button_background_gradient_hover.type == "radial"){ #>
								background-image: radial-gradient(at {{ carousel_item.button_background_gradient_hover.radialPos || "center center"}}, {{ carousel_item.button_background_gradient_hover.color }} {{ carousel_item.button_background_gradient_hover.pos || 0 }}%, {{ carousel_item.button_background_gradient_hover.color2 }} {{ carousel_item.button_background_gradient_hover.pos2 || 100 }}%);
							<# } else { #>
								background-image: linear-gradient({{ carousel_item.button_background_gradient_hover.deg || 0}}deg, {{ carousel_item.button_background_gradient_hover.color }} {{ carousel_item.button_background_gradient_hover.pos || 0 }}%, {{ carousel_item.button_background_gradient_hover.color2 }} {{ carousel_item.button_background_gradient_hover.pos2 || 100 }}%);
							<# } #>
						<# } #>
					}

				<# } #>
				<#
					var padding = "";
					var padding_sm = "";
					var padding_xs = "";
					if(carousel_item.title_padding){
						if(_.isObject(carousel_item.title_padding)){
							if(carousel_item.title_padding.md.trim() !== ""){
								padding = carousel_item.title_padding.md.split(" ").map(item => {
									if(_.isEmpty(item)){
										return "0";
									}
									return item;
								}).join(" ")
							}

							if(carousel_item.title_padding.sm.trim() !== ""){
								padding_sm = carousel_item.title_padding.sm.split(" ").map(item => {
									if(_.isEmpty(item)){
										return "0";
									}
									return item;
								}).join(" ")
							}

							if(carousel_item.title_padding.xs.trim() !== ""){
								padding_xs = carousel_item.title_padding.xs.split(" ").map(item => {
									if(_.isEmpty(item)){
										return "0";
									}
									return item;
								}).join(" ")
							}
						}

					}

					var margin = "";
					var margin_sm = "";
					var margin_xs = "";
					if(carousel_item.title_margin){
						if(_.isObject(carousel_item.title_margin)){
							if(carousel_item.title_margin.md.trim() !== ""){
								margin = carousel_item.title_margin.md.split(" ").map(item => {
									if(_.isEmpty(item)){
										return "0";
									}
									return item;
								}).join(" ")
							}

							if(carousel_item.title_margin.sm.trim() !== ""){
								margin_sm = carousel_item.title_margin.sm.split(" ").map(item => {
									if(_.isEmpty(item)){
										return "0";
									}
									return item;
								}).join(" ")
							}

							if(carousel_item.title_margin.xs.trim() !== ""){
								margin_xs = carousel_item.title_margin.xs.split(" ").map(item => {
									if(_.isEmpty(item)){
										return "0";
									}
									return item;
								}).join(" ")
							}
						}

					}


					var content_padding = "";
					var content_padding_sm = "";
					var content_padding_xs = "";
					if(carousel_item.content_padding){
						if(_.isObject(carousel_item.content_padding)){
							if(carousel_item.content_padding.md.trim() !== ""){
								content_padding = carousel_item.content_padding.md.split(" ").map(item => {
									if(_.isEmpty(item)){
										return "0";
									}
									return item;
								}).join(" ")
							}

							if(carousel_item.content_padding.sm.trim() !== ""){
								content_padding_sm = carousel_item.content_padding.sm.split(" ").map(item => {
									if(_.isEmpty(item)){
										return "0";
									}
									return item;
								}).join(" ")
							}

							if(carousel_item.content_padding.xs.trim() !== ""){
								content_padding_xs = carousel_item.content_padding.xs.split(" ").map(item => {
									if(_.isEmpty(item)){
										return "0";
									}
									return item;
								}).join(" ")
							}
						}

					}

					var content_margin = "";
					var content_margin_sm = "";
					var content_margin_xs = "";
					if(carousel_item.content_margin){
						if(_.isObject(carousel_item.content_margin)){
							if(carousel_item.content_margin.md.trim() !== ""){
								content_margin = carousel_item.content_margin.md.split(" ").map(item => {
									if(_.isEmpty(item)){
										return "0";
									}
									return item;
								}).join(" ")
							}

							if(carousel_item.content_margin.sm.trim() !== ""){
								content_margin_sm = carousel_item.content_margin.sm.split(" ").map(item => {
									if(_.isEmpty(item)){
										return "0";
									}
									return item;
								}).join(" ")
							}

							if(carousel_item.content_margin.xs.trim() !== ""){
								content_margin_xs = carousel_item.content_margin.xs.split(" ").map(item => {
									if(_.isEmpty(item)){
										return "0";
									}
									return item;
								}).join(" ")
							}
						}

					}
				#>

				#sppb-addon-{{ data.id }} .sppb-item-{{ data.id }}{{ key }} .sppb-carousel-caption h2{
					<# if(_.isObject(carousel_item.title_fontsize)){ #>
						font-size: {{ carousel_item.title_fontsize.md }}px;
					<# } else { #>
						font-size: {{ carousel_item.title_fontsize }}px;
					<# } #>
					<# if(_.isObject(carousel_item.title_lineheight)){ #>
						line-height: {{ carousel_item.title_lineheight.md }}px;
					<# } else { #>
						line-height: {{ carousel_item.title_lineheight }}px;
					<# } #>
					color: {{ carousel_item.title_color }};
					padding: {{ padding }};
					margin: {{ margin }};
				}
				#sppb-addon-{{ data.id }} .sppb-item-{{ data.id }}{{ key }} .sppb-carousel-caption .sppb-carousel-content{
					<# if(_.isObject(carousel_item.content_fontsize)){ #>
						font-size: {{ carousel_item.content_fontsize.md }}px;
					<# } else { #>
						font-size: {{ carousel_item.content_fontsize }}px;
					<# } #>
					<# if(_.isObject(carousel_item.content_lineheight)){ #>
						line-height: {{ carousel_item.content_lineheight.md }}px;
					<# } else { #>
						line-height: {{ carousel_item.content_lineheight }}px;
					<# } #>
					color: {{ carousel_item.content_color }};
					padding: {{ content_padding }};
					margin: {{ content_margin }};
				}
				@media (min-width: 768px) and (max-width: 991px) {
					#sppb-addon-{{ data.id }} .sppb-item-{{ data.id }}{{ key }} .sppb-carousel-caption h2{
						<# if(_.isObject(carousel_item.title_fontsize)){ #>
							font-size: {{ carousel_item.title_fontsize.sm }}px;
						<# } #>
						<# if(_.isObject(carousel_item.title_lineheight)){ #>
							line-height: {{ carousel_item.title_lineheight.sm }}px;
						<# } #>
						padding: {{ padding_sm }};
						margin: {{ margin_sm }};
					}
					#sppb-addon-{{ data.id }} .sppb-item-{{ data.id }}{{ key }} .sppb-carousel-caption .sppb-carousel-content{
						<# if(_.isObject(carousel_item.content_fontsize)){ #>
							font-size: {{ carousel_item.content_fontsize.sm }}px;
						<# } #>
						<# if(_.isObject(carousel_item.content_lineheight)){ #>
							line-height: {{ carousel_item.content_lineheight.sm }}px;
						<# } #>
						padding: {{ content_padding_sm }};
						margin: {{ content_margin_sm }};
					}
				}

				@media (max-width: 767px) {
					#sppb-addon-{{ data.id }} .sppb-item-{{ data.id }}{{ key }} .sppb-carousel-caption h2{
						<# if(_.isObject(carousel_item.title_fontsize)){ #>
							font-size: {{ carousel_item.title_fontsize.xs }}px;
						<# } #>
						<# if(_.isObject(carousel_item.title_lineheight)){ #>
							line-height: {{ carousel_item.title_lineheight.xs }}px;
						<# } #>
						padding: {{ padding_xs }};
						margin: {{ margin_xs }};
					}
					#sppb-addon-{{ data.id }} .sppb-item-{{ data.id }}{{ key }} .sppb-carousel-caption .sppb-carousel-content{
						<# if(_.isObject(carousel_item.content_fontsize)){ #>
							font-size: {{ carousel_item.content_fontsize.xs }}px;
						<# } #>
						<# if(_.isObject(carousel_item.content_lineheight)){ #>
							line-height: {{ carousel_item.content_lineheight.xs }}px;
						<# } #>
						padding: {{ content_padding_xs }};
						margin: {{ content_margin_xs }};
					}
				}
			<# }); #>
		</style>
		<div class="sppb-carousel sppb-slide {{ data.class }}" id="sppb-carousel-{{ data.id }}" data-interval="{{ interval }}" {{{ autoplay }}}>
			<# if(data.controllers){ #>
				<ol class="sppb-carousel-indicators">
				<# _.each(data.sp_carousel_item, function (carousel_item, key){ #>
					<# var active = (key == 0) ? "active" : ""; #>
					<li data-sppb-target="#sppb-carousel-{{ data.id }}"  class="{{ active }}"  data-sppb-slide-to="{{ key }}"></li>
				<# }); #>
				</ol>
			<# } #>
			<div class="sppb-carousel-inner {{ data.alignment }}">
				<# _.each(data.sp_carousel_item, function (carousel_item, key){ #>
					<#
						var classNames = (key == 0) ? "active" : "";
						classNames += (carousel_item.bg) ? " sppb-item-has-bg" : "";
						classNames += " sppb-item-"+data.id+""+key;
					#>
					<div class="sppb-item {{ classNames }}">
						<# if(carousel_item.bg && carousel_item.bg.indexOf("http://") == -1 && carousel_item.bg.indexOf("https://") == -1){ #>
							<img src=\'{{ pagebuilder_base + carousel_item.bg }}\' alt="{{ carousel_item.title }}">
						<# } else if(carousel_item.bg){ #>
							<img src=\'{{ carousel_item.bg }}\' alt="{{ carousel_item.title }}">
						<# } #>
						<div class="sppb-carousel-item-inner">
							<div class="sppb-carousel-caption">
								<div class="sppb-carousel-text">
									<# if(carousel_item.title || carousel_item.content) { #>
										<# if(carousel_item.title) { #>
											<h2 class="sp-editable-content" id="addon-title-{{data.id}}-{{key}}" data-id={{data.id}} data-fieldName="sp_carousel_item-{{key}}-title">{{ carousel_item.title }}</h2>
										<# } #>
										<div class="sppb-carousel-content sp-editable-content" id="addon-content-{{data.id}}-{{key}}" data-id={{data.id}} data-fieldName="sp_carousel_item-{{key}}-content">{{{ carousel_item.content }}}</div>
										<# if(carousel_item.button_text) { #>
											<#
												var btnClass = "";
												btnClass += carousel_item.button_type ? " sppb-btn-"+carousel_item.button_type : " sppb-btn-default" ;
												btnClass += carousel_item.button_size ? " sppb-btn-"+carousel_item.button_size : "" ;
												btnClass += carousel_item.button_shape ? " sppb-btn-"+carousel_item.button_shape : " sppb-btn-rounded" ;
												btnClass += carousel_item.button_appearance ? " sppb-btn-"+carousel_item.button_appearance : "" ;
												btnClass += carousel_item.button_block ? " "+carousel_item.button_block : "" ;
												var button_text = carousel_item.button_text;

												if(carousel_item.button_icon_position == "left"){
													button_text = (carousel_item.button_icon) ? \'<i class="fa  \'+carousel_item.button_icon+\'"></i> \'+carousel_item.button_text : carousel_item.button_text ;
												}else{
													button_text = (carousel_item.button_icon) ? carousel_item.button_text+\' <i class="fa \'+carousel_item.button_icon+\'"></i>\' : carousel_item.button_text ;
												}
											#>
											<a href=\'{{ carousel_item.button_url }}\' target="{{ carousel_item.button_target }}" id="btn-{{ data.id + "" + key}}" class="sppb-btn{{ btnClass }}">{{{ button_text }}}</a>
										<# } #>
									<# } #>
								</div>
							</div>
						</div>
					</div>
				<# }); #>
			</div>
			<# if(data.arrows) { #>
				<a href="#sppb-carousel-{{ data.id }}" class="sppb-carousel-arrow left sppb-carousel-control" data-slide="prev"><i class="fa fa-chevron-left"></i></a>
				<a href="#sppb-carousel-{{ data.id }}" class="sppb-carousel-arrow right sppb-carousel-control" data-slide="next"><i class="fa fa-chevron-right"></i></a>
			<# } #>
		</div>
		';

		return $output;
	}
}