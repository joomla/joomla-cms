<?php
/**
 * @package SP Page Builder
 * @author JoomShaper http://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2019 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/
//no direct accees
defined ('_JEXEC') or die ('Restricted access');

class SppagebuilderAddonTab extends SppagebuilderAddons {

	public function render() {
        $settings = $this->addon->settings;
		$class = (isset($settings->class) && $settings->class) ? $settings->class : '';
		$style = (isset($settings->style) && $settings->style) ? $settings->style : '';
		$title = (isset($settings->title) && $settings->title) ? $settings->title : '';
		$nav_icon_postion = (isset($settings->nav_icon_postion) && $settings->nav_icon_postion) ? $settings->nav_icon_postion : '';
		$heading_selector = (isset($settings->heading_selector) && $settings->heading_selector) ? $settings->heading_selector : 'h3';
		$nav_text_align = (isset($settings->nav_text_align) && $settings->nav_text_align) ? $settings->nav_text_align : 'sppb-text-left';

		//Output
		$output  = '<div class="sppb-addon sppb-addon-tab ' . $class . '">';
		$output .= ($title) ? '<'.$heading_selector.' class="sppb-addon-title">' . $title . '</'.$heading_selector.'>' : '';
		$output .= '<div class="sppb-addon-content sppb-tab ' . $style . '-tab">';

		//Tab Title
		$output .='<ul class="sppb-nav sppb-nav-' . $style . '" role="tablist">';
		foreach ($settings->sp_tab_item as $key => $tab) {
            $icon_top ='';
            $icon_bottom = '';
            $icon_right = '';
            $icon_left = '';
            $icon_block = '';
            $title = (isset($tab->title) && $tab->title) ? ' '. $tab->title . ' ' : '';

            if(isset($tab->icon) && $tab->icon){
                if($tab->icon && $nav_icon_postion == 'top'){
                    $icon_top = '<span class="sppb-tab-icon tab-icon-block" aria-label="'.trim(strip_tags($title)).'"><i class="fa ' . $tab->icon . '" aria-hidden="true"></i></span>';
                } elseif ($tab->icon && $nav_icon_postion == 'bottom') {
                    $icon_bottom = '<span class="sppb-tab-icon tab-icon-block" aria-label="'.trim(strip_tags($title)).'"><i class="fa ' . $tab->icon . '" aria-hidden="true"></i></span>';
                } elseif ($tab->icon && $nav_icon_postion == 'right') {
                    $icon_right = '<span class="sppb-tab-icon" aria-label="'.trim(strip_tags($title)).'"><i class="fa ' . $tab->icon . '" aria-hidden="true"></i></span>';
                } else {
                    $icon_left = '<span class="sppb-tab-icon" aria-label="'.trim(strip_tags($title)).'"><i class="fa ' . $tab->icon . '" aria-hidden="true"></i></span>';
                }
            }
            if($nav_icon_postion == 'top' || $nav_icon_postion == 'bottom'){
                $icon_block = 'tab-icon-block-wrap';
            }
            $output .='<li class="'. ( ($key==0) ? "active" : "").'">';
            $output .= '<a data-toggle="sppb-tab" id="sppb-content-'. ($this->addon->id + $key) .'" class="'.$nav_text_align.' '.$icon_block.'" href="#sppb-tab-'. ($this->addon->id + $key) .'" role="tab" aria-controls="sppb-tab-'. ($this->addon->id + $key) .'" aria-selected="'. ( ($key==0) ? "true" : "false").'">'. $icon_top . $icon_left . $title . $icon_right . $icon_bottom .'</a>';
            $output .='</li>';
		}
		$output .='</ul>';

		//Tab Contnet
		$output .='<div class="sppb-tab-content sppb-tab-' . $style . '-content">';
		foreach ($settings->sp_tab_item as $key => $tab) {
            $output .='<div id="sppb-tab-'. ($this->addon->id + $key) .'" class="sppb-tab-pane sppb-fade'. ( ($key==0) ? " active in" : "").'" role="tabpanel" aria-labelledby="sppb-content-'. ($this->addon->id + $key) .'">' . $tab->content .'</div>';
		}
		$output .='</div>';
		$output .= '</div>';
		$output .= '</div>';

		return $output;

	}

	public function css() {
        $addon_id = '#sppb-addon-' . $this->addon->id;
        $settings = $this->addon->settings;
		$tab_style = (isset($settings->style) && $settings->style) ? $settings->style : '';
        $style = '';
        $style .= (isset($settings->active_tab_color) && $settings->active_tab_color) ? 'color: ' . $settings->active_tab_color . ';': '';
        $style .= (isset($settings->active_tab_border_width) && trim($settings->active_tab_border_width)) ? 'border-width: ' . $settings->active_tab_border_width . ';border-style: solid;': '';
        $style .= (isset($settings->active_tab_border_color) && $settings->active_tab_border_color) ? 'border-color: ' . $settings->active_tab_border_color . ';': '';

        //Font style
		$font_style = '';
		$font_style .= (isset($settings->nav_fontsize) && $settings->nav_fontsize) ? 'font-size: ' . $settings->nav_fontsize . 'px;': '';
		$font_style .= (isset($settings->nav_lineheight) && $settings->nav_lineheight) ? 'line-height: ' . $settings->nav_lineheight . 'px;': '';
        //Font style object
        $nav_font_style = (isset($settings->nav_font_style) && $settings->nav_font_style) ? $settings->nav_font_style : '';
        
        if(isset($nav_font_style->underline) && $nav_font_style->underline){
			$font_style .= 'text-decoration:underline;';
		}
		if(isset($nav_font_style->italic) && $nav_font_style->italic){
			$font_style .= 'font-style:italic;';
		}
		if(isset($nav_font_style->uppercase) && $nav_font_style->uppercase){
			$font_style .= 'text-transform:uppercase;';
		}
		if(isset($nav_font_style->weight) && $nav_font_style->weight){
			$font_style .= 'font-weight:'.$nav_font_style->weight.';';
        }
        $nav_border = (isset($settings->nav_border) && trim($settings->nav_border)) ? $settings->nav_border : '';
        if(strpos($nav_border, 'px')){
            $font_style .= (isset($settings->nav_border) && trim($settings->nav_border)) ? 'border-width: ' . $settings->nav_border . ';border-style:solid;': '';
        } else {
		    $font_style .= (isset($settings->nav_border) && trim($settings->nav_border)) ? 'border: ' . $settings->nav_border . 'px solid;': '';
        }
		$font_style .= (isset($settings->nav_border_color) && $settings->nav_border_color) ? 'border-color: ' . $settings->nav_border_color . ';': '';
		$font_style .= (isset($settings->nav_color) && $settings->nav_color) ? 'color: ' . $settings->nav_color . ';': '';
		$font_style .= (isset($settings->nav_bg_color) && $settings->nav_bg_color) ? 'background-color: ' . $settings->nav_bg_color . ';': '';
		$font_style .= (isset($settings->nav_border_radius) && $settings->nav_border_radius) ? 'border-radius: ' . $settings->nav_border_radius . 'px;': '';
		$font_style .= (isset($settings->nav_padding) && trim($settings->nav_padding)) ? 'padding: ' . $settings->nav_padding . ';': '';
		
        $font_style_sm = (isset($settings->nav_fontsize_sm) && $settings->nav_fontsize_sm) ? 'font-size: ' . $settings->nav_fontsize_sm . 'px;': '';
        $font_style_sm .= (isset($settings->nav_padding_sm) && trim($settings->nav_padding_sm)) ? 'padding: ' . $settings->nav_padding_sm . ';': '';
        $font_style_sm .= (isset($settings->nav_lineheight_sm) && $settings->nav_lineheight_sm) ? 'line-height: ' . $settings->nav_lineheight_sm . 'px;': '';
        
        $font_style_xs = (isset($settings->nav_fontsize_xs) && $settings->nav_fontsize_xs) ? 'font-size: ' . $settings->nav_fontsize_xs . 'px;': '';
        $font_style_xs .= (isset($settings->nav_padding_xs) && trim($settings->nav_padding_xs)) ? 'padding: ' . $settings->nav_padding_xs . ';': '';
        $font_style_xs .= (isset($settings->nav_lineheight_xs) && $settings->nav_lineheight_xs) ? 'line-height: ' . $settings->nav_lineheight_xs . 'px;': '';
        
        //Nav Width
        $nav_width = (isset($settings->nav_width) && $settings->nav_width) ? $settings->nav_width : 30;
        $nav_width_sm = (isset($settings->nav_width_sm) && $settings->nav_width_sm) ? $settings->nav_width_sm : 30;
        $nav_width_xs = (isset($settings->nav_width_xs) && $settings->nav_width_xs) ? $settings->nav_width_xs : 30;
        //Nav Margin
        $nav_margin = (isset($settings->nav_margin) && trim($settings->nav_margin)) ? 'padding: ' . $settings->nav_margin . ';': 'padding: 0px 0px 5px 0px;';
        $nav_margin_sm = (isset($settings->nav_margin_sm) && trim($settings->nav_margin_sm)) ? 'padding: ' . $settings->nav_margin_sm . ';': '';
        $nav_margin_xs = (isset($settings->nav_margin_xs) && trim($settings->nav_margin_xs)) ? 'padding: ' . $settings->nav_margin_xs . ';': '';
        //Nav Gutter
        $nav_gutter_right = (isset($settings->nav_gutter) && $settings->nav_gutter) ? 'padding-right: ' . $settings->nav_gutter . 'px;': 'padding-right: 15px;';
        $nav_gutter_right_sm = (isset($settings->nav_gutter_sm) && $settings->nav_gutter_sm) ? 'padding-right: ' . $settings->nav_gutter_sm . 'px;': 'padding-right: 15px;';
        $nav_gutter_right_xs = (isset($settings->nav_gutter_xs) && $settings->nav_gutter_xs) ? 'padding-right: ' . $settings->nav_gutter_xs . 'px;': 'padding-right: 15px;';
        
        $nav_gutter_left = (isset($settings->nav_gutter) && $settings->nav_gutter) ? 'padding-left: ' . $settings->nav_gutter . 'px;': 'padding-left: 15px;';
        $nav_gutter_left_sm = (isset($settings->nav_gutter_sm) && $settings->nav_gutter_sm) ? 'padding-left: ' . $settings->nav_gutter_sm . 'px;': 'padding-left: 15px;';
        $nav_gutter_left_xs = (isset($settings->nav_gutter_xs) && $settings->nav_gutter_xs) ? 'padding-left: ' . $settings->nav_gutter_xs . 'px;': 'padding-left: 15px;';
        //Content Style
        $content_style = '';
        $content_style .= (isset($settings->content_backround) && $settings->content_backround) ? 'background-color: ' . $settings->content_backround . ';': '';
        $content_style .= (isset($settings->content_border) && $settings->content_border) ? 'border: ' . $settings->content_border . 'px solid;': '';
        $content_style .= (isset($settings->content_color) && $settings->content_color) ? 'color: ' . $settings->content_color . ';': '';
        $content_style .= (isset($settings->content_border_color) && $settings->content_border_color) ? 'border-color: ' . $settings->content_border_color . ';': '';
        $content_style .= (isset($settings->content_border_radius) && $settings->content_border_radius) ? 'border-radius: ' . $settings->content_border_radius . 'px;': '';
        $content_style .= (isset($settings->content_margin) && trim($settings->content_margin)) ? 'margin: ' . $settings->content_margin . ';': '';
        $content_style .= (isset($settings->content_padding) && trim($settings->content_padding)) ? 'padding: ' . $settings->content_padding . ';': '';
        $content_style .= (isset($settings->content_fontsize) && $settings->content_fontsize) ? 'font-size: ' . $settings->content_fontsize . 'px;': '';
        $content_style .= (isset($settings->content_lineheight) && $settings->content_lineheight) ? 'line-height: ' . $settings->content_lineheight . 'px;': '';
        //Font style object
        $content_font_style = (isset($settings->content_font_style) && $settings->content_font_style) ?  $settings->content_font_style : '';
        if(isset($content_font_style->underline) && $content_font_style->underline){
			$content_style .= 'text-decoration:underline;';
		}
		if(isset($content_font_style->italic) && $content_font_style->italic){
			$content_style .= 'font-style:italic;';
		}
		if(isset($content_font_style->uppercase) && $content_font_style->uppercase){
			$content_style .= 'text-transform:uppercase;';
		}
		if(isset($content_font_style->weight) && $content_font_style->weight){
			$content_style .= 'font-weight:'.$content_font_style->weight.';';
		}
        //Content tablet style
        $content_style_sm = (isset($settings->content_margin_sm) && trim($settings->content_margin_sm)) ? 'margin: ' . $settings->content_margin_sm . ';': '';
        $content_style_sm .= (isset($settings->content_padding_sm) && $settings->content_padding_sm) ? 'padding: ' . $settings->content_padding_sm . ';': '';
        $content_style_sm .= (isset($settings->content_fontsize_sm) && $settings->content_fontsize_sm) ? 'font-size: ' . $settings->content_fontsize_sm . 'px;': '';
        $content_style_sm .= (isset($settings->content_lineheight_sm) && $settings->content_lineheight_sm) ? 'line-height: ' . $settings->content_lineheight_sm . 'px;': '';
        
        //Content Mobile style
        $content_style_xs = (isset($settings->content_margin_xs) && trim($settings->content_margin_xs)) ? 'margin: ' . $settings->content_margin_xs . ';': '';
        $content_style_xs .= (isset($settings->content_padding_xs) && $settings->content_padding_xs) ? 'padding: ' . $settings->content_padding_xs . ';': '';
        $content_style_xs .= (isset($settings->content_fontsize_xs) && $settings->content_fontsize_xs) ? 'font-size: ' . $settings->content_fontsize_xs . 'px;': '';
        $content_style_xs .= (isset($settings->content_lineheight_xs) && $settings->content_lineheight_xs) ? 'line-height: ' . $settings->content_lineheight_xs . 'px;': '';
        //Box shadow
        $show_boxshadow = (isset($settings->show_boxshadow) && $settings->show_boxshadow) ?  $settings->show_boxshadow : '';
        $box_shadow = '';
        if($show_boxshadow){
            $box_shadow .= (isset($settings->shadow_horizontal) && $settings->shadow_horizontal) ?  $settings->shadow_horizontal . 'px ' : '0 ';
            $box_shadow .= (isset($settings->shadow_vertical) && $settings->shadow_vertical) ?  $settings->shadow_vertical . 'px ' : '0 ';
            $box_shadow .= (isset($settings->shadow_blur) && $settings->shadow_blur) ?  $settings->shadow_blur . 'px ' : '0 ';
            $box_shadow .= (isset($settings->shadow_spread) && $settings->shadow_spread) ?  $settings->shadow_spread . 'px ' : '0 ';
            $box_shadow .= (isset($settings->shadow_color) && $settings->shadow_color) ?  $settings->shadow_color : 'rgba(0, 0, 0, .5)';
        }
        //Icon Style
        $icon_style = '';
        $icon_style .= (isset($settings->icon_fontsize) && $settings->icon_fontsize) ?  'font-size: ' . $settings->icon_fontsize .'px;' : '';
        $icon_style .= (isset($settings->icon_margin) && trim($settings->icon_margin)) ?  'margin: ' . $settings->icon_margin . ';' : '';
        $icon_style .= (isset($settings->icon_color) && $settings->icon_color) ?  'color: ' . $settings->icon_color . ';' : '';
        
        $icon_style_sm = (isset($settings->icon_fontsize_sm) && $settings->icon_fontsize_sm) ?  'font-size: ' . $settings->icon_fontsize_sm .'px;' : '';
        $icon_style_sm .= (isset($settings->icon_margin_sm) && trim($settings->icon_margin_sm)) ?  'margin: ' . $settings->icon_margin_sm . ';' : '';
        
        $icon_style_xs = (isset($settings->icon_fontsize_xs) && $settings->icon_fontsize_xs) ?  'font-size: ' . $settings->icon_fontsize_xs .'px;' : '';
        $icon_style_xs .= (isset($settings->icon_margin_xs) && trim($settings->icon_margin_xs)) ?  'margin: ' . $settings->icon_margin_xs . ';' : '';
        //Css output            
		$css = '';
		if($tab_style == 'pills') {
            $style .= (isset($settings->active_tab_bg) && $settings->active_tab_bg) ? 'background-color: ' . $settings->active_tab_bg . ';': '';
            if($style) {
                $css .= $addon_id . ' .sppb-nav-pills > li.active > a,' . $addon_id . ' .sppb-nav-pills > li.active > a:hover,' . $addon_id . ' .sppb-nav-pills > li.active > a:focus {';
                $css .= $style;
                $css .= '}';
            }
		} else if ($tab_style == 'lines') {
            $style .= (isset($settings->active_tab_bg) && $settings->active_tab_bg) ? 'border-bottom-color: ' . $settings->active_tab_bg . ';': '';
            if($style) {
                $css .= $addon_id . ' .sppb-nav-lines > li.active > a,' . $addon_id . ' .sppb-nav-lines > li.active > a:hover,' . $addon_id . ' .sppb-nav-lines > li.active > a:focus {';
                $css .= $style;
                $css .= '}';
            }
		} else if ($tab_style == 'custom') {
            //Active Nav style
            $style .= (isset($settings->active_tab_bg) && $settings->active_tab_bg) ? 'background-color: ' . $settings->active_tab_bg . ';': '';

            if($style) {
                $css .= $addon_id . ' .sppb-nav-custom > li.active > a,' . $addon_id . ' .sppb-nav-custom > li.active > a:hover,' . $addon_id . ' .sppb-nav-custom > li.active > a:focus {';
                $css .= $style;
                $css .= '}';
            }
            $css .= $addon_id . ' .sppb-nav-custom {';
            $css .= 'width: ' . $nav_width . '%;';
            $css .= $nav_gutter_right;
            $css .= '}';
            $css .= $addon_id . ' .sppb-tab-custom-content {';
            $css .= 'width:'. (100-$nav_width) .'%;';
            $css .= $nav_gutter_left;
            $css .= '}';
            $css .= $addon_id . ' .sppb-tab-custom-content > div {';
            $css .= $content_style;
            $css .= 'box-shadow:'.$box_shadow.';';
            $css .= '}';
            $css .= $addon_id . ' .sppb-nav-custom a {';
            $css .= $font_style;
            $css .= 'box-shadow:'.$box_shadow.';';
            $css .= '}';
            $css .= $addon_id . ' .sppb-nav-custom li {';
                $css .= $nav_margin;
            $css .= '}';
            $css .= $addon_id . ' .sppb-tab-icon {';
            $css .= $icon_style;
            $css .= '}';
            //Nav Hover Style
            $hover_style = '';
            $hover_style .= (isset($settings->hover_tab_color) && $settings->hover_tab_color) ? 'color: ' . $settings->hover_tab_color . ';': '';
            $hover_style .= (isset($settings->hover_tab_border_width) && trim($settings->hover_tab_border_width)) ? 'border-width: ' . $settings->hover_tab_border_width . ';border-style: solid;': '';
            $hover_style .= (isset($settings->hover_tab_border_color) && $settings->hover_tab_border_color) ? 'border-color: ' . $settings->hover_tab_border_color . ';': '';
            $hover_style .= (isset($settings->hover_tab_bg) && $settings->hover_tab_bg) ? 'background-color: ' . $settings->hover_tab_bg . ';': '';
            
            if($hover_style) {
                $css .= $addon_id . ' .sppb-nav-custom > li > a:hover,' . $addon_id . ' .sppb-nav-custom > li > a:focus {';
                $css .= $hover_style;
                $css .= '}';
            }

            //Icon hover and active color
            $icon_color_hover = (isset($settings->icon_color_hover) && $settings->icon_color_hover) ? 'color: ' . $settings->icon_color_hover . ';': '';
            if($icon_color_hover) {
                $css .= $addon_id . ' .sppb-nav-custom > li > a:hover  > .sppb-tab-icon,' . $addon_id . ' .sppb-nav-custom > li > a:focus > .sppb-tab-icon {';
                $css .= $icon_color_hover;
                $css .= '}';
            }
            $icon_color_active = (isset($settings->icon_color_active) && $settings->icon_color_active) ? 'color: ' . $settings->icon_color_active . ';': '';
            if($icon_color_active) {
                $css .= $addon_id . ' .sppb-nav-custom > li.active > a > .sppb-tab-icon,' . $addon_id . ' .sppb-nav-custom > li.active > a:hover  > .sppb-tab-icon,' . $addon_id . ' .sppb-nav-custom > li.active > a:focus > .sppb-tab-icon {';
                $css .= $icon_color_active;
                $css .= '}';
            }
        }
        if (!empty($font_style_sm) || !empty($nav_width_sm) || !empty($content_style_sm) || !empty($nav_margin_sm)) {
            $css .= '@media (min-width: 768px) and (max-width: 991px) {';
            
            $css .= $addon_id . ' .sppb-nav-custom {';
            if(!empty($nav_width_sm)){
                $css .= 'width: ' . $nav_width_sm . '%;';
            }
            $css .= $nav_gutter_right_sm;
            $css .= '}';
            $css .= $addon_id . ' .sppb-tab-custom-content {';
            if(!empty($nav_width_sm) && $nav_width_sm != 100){
                $css .= 'width:'. (100 - $nav_width_sm) .'%;';
            } else {
                $css .= 'width: 100%;';    
            }
            $css .= $nav_gutter_left_sm;
            $css .= '}';
            $css .= $addon_id . ' .sppb-tab-custom-content > div {';
            $css .= $content_style_sm;
            $css .= '}';
            $css .= $addon_id . ' .sppb-nav-custom a {';
            $css .= $font_style_sm;
            $css .= '}';
            $css .= $addon_id . ' .sppb-nav-custom li {';
            $css .= $nav_margin_sm;
            $css .= '}';
            $css .= $addon_id . ' .sppb-tab-icon {';
            $css .= $icon_style_sm;
            $css .= '}';
            
            $css .= '}';
        }
        if (!empty($font_style_xs) || !empty($nav_width_xs) || !empty($content_style_xs) || !empty($nav_margin_xs)) {
            $css .= '@media (max-width: 767px) {';
            
            $css .= $addon_id . ' .sppb-nav-custom {';
            if(!empty($nav_width_xs)){
            $css .= 'width: ' . $nav_width_xs . '%;';
            }
            $css .= $nav_gutter_right_xs;
            $css .= '}';
            $css .= $addon_id . ' .sppb-tab-custom-content {';
            if(!empty($nav_width_xs) && $nav_width_xs != 100){
                $css .= 'width:'. (100 - $nav_width_xs) .'%;';
            } else {
                $css .= 'width: 100%;';    
            }
            $css .= $nav_gutter_left_xs;
            $css .= '}';
            $css .= $addon_id . ' .sppb-tab-custom-content > div {';
            $css .= $content_style_xs;
            $css .= '}';
            $css .= $addon_id . ' .sppb-nav-custom a {';
            $css .= $font_style_xs;
            $css .= '}';
            $css .= $addon_id . ' .sppb-nav-custom li {';
            $css .= $nav_margin_xs;
            $css .= '}';
            $css .= $addon_id . ' .sppb-tab-icon {';
            $css .= $icon_style_xs;
            $css .= '}';
            
            $css .= '}';
        }

		return $css;
	}

	public static function getTemplate(){
		$output = '
		<style type="text/css">
			<# 
                var box_shadow = "";
                if(data.show_boxshadow){
                    box_shadow += (!_.isEmpty(data.shadow_horizontal) && data.shadow_horizontal) ?  data.shadow_horizontal + \'px \' : "0 ";
                    box_shadow += (!_.isEmpty(data.shadow_vertical) && data.shadow_vertical) ?  data.shadow_vertical + \'px \' : "0 ";
                    box_shadow += (!_.isEmpty(data.shadow_blur) && data.shadow_blur) ?  data.shadow_blur + \'px \' : "0 ";
                    box_shadow += (!_.isEmpty(data.shadow_spread) && data.shadow_spread) ?  data.shadow_spread + \'px \' : "0 ";
                    box_shadow += (!_.isEmpty(data.shadow_color) && data.shadow_color) ?  data.shadow_color : "rgba(0, 0, 0, .5)";
                }
                if(data.style == "pills"){ #>
                    #sppb-addon-{{ data.id }} .sppb-nav-pills > li.active > a,
                    #sppb-addon-{{ data.id }} .sppb-nav-pills > li.active > a:hover,
                    #sppb-addon-{{ data.id }} .sppb-nav-pills > li.active > a:focus{
                        color: {{ data.active_tab_color }};
                        background-color: {{ data.active_tab_bg }};
                    }
			<# } #>

			<# if(data.style == "lines"){ #>
                #sppb-addon-{{ data.id }} .sppb-nav-lines > li.active > a,
                #sppb-addon-{{ data.id }} .sppb-nav-lines > li.active > a:hover,
                #sppb-addon-{{ data.id }} .sppb-nav-lines > li.active > a:focus{
                    color: {{ data.active_tab_color }};
                    border-bottom-color: {{ data.active_tab_bg }};
                }
			<# } #>
            <# if (data.style == "custom") { #>
                #sppb-addon-{{ data.id }} .sppb-nav-custom > li > a:hover,
                #sppb-addon-{{ data.id }} .sppb-nav-custom > li > a:focus{
                    color: {{ data.hover_tab_color }};
                    border-width: {{ data.hover_tab_border_width }};
                    border-color: {{ data.hover_tab_border_color }};
                    background-color: {{ data.hover_tab_bg }};
                }
                #sppb-addon-{{ data.id }} .sppb-nav-custom > li.active > a,
                #sppb-addon-{{ data.id }} .sppb-nav-custom > li.active > a:hover,
                #sppb-addon-{{ data.id }} .sppb-nav-custom > li.active > a:focus{
                    color: {{ data.active_tab_color }};
                    border-width: {{ data.active_tab_border_width }};
                    border-color: {{ data.active_tab_border_color }};
                    background-color: {{ data.active_tab_bg }};
                }
                #sppb-addon-{{ data.id }} .sppb-nav-custom > li > a:hover > .sppb-tab-icon,
                #sppb-addon-{{ data.id }} .sppb-nav-custom > li > a:focus > .sppb-tab-icon{
                    color: {{ data.icon_color_hover }};
                }
                #sppb-addon-{{ data.id }} .sppb-nav-custom > li.active > a > .sppb-tab-icon,
                #sppb-addon-{{ data.id }} .sppb-nav-custom > li.active > a:hover > .sppb-tab-icon,
                #sppb-addon-{{ data.id }} .sppb-nav-custom > li.active > a:focus > .sppb-tab-icon{
                    color: {{ data.icon_color_active }};
                }
                #sppb-addon-{{ data.id }} .sppb-nav-custom li {
                    <# if(_.isObject(data.nav_margin)){ #>
                        padding: {{data.nav_margin.md}};
                    <# } else { #>
                        padding: {{data.nav_margin}};
                    <# } #>
                }
                #sppb-addon-{{ data.id }} .sppb-nav-custom li a {
                    <# if(_.isObject(data.nav_fontsize)){ #>
                        font-size: {{data.nav_fontsize.md}}px;
                    <# } else { #>
                        font-size: {{data.nav_fontsize}}px;
                    <# } #>
                    <# if(_.endsWith(data.nav_border, "x")) { #>
                        border-width: {{data.nav_border}};
                        border-style: solid;
                    <# } else { #>
                        border: {{_.trim(data.nav_border, " ")}}px solid;
                    <# } #>
                    border-color: {{data.nav_border_color}};
                    color: {{data.nav_color}};
                    background-color: {{data.nav_bg_color}};
                    border-radius: {{data.nav_border_radius}}px;
                    <# if(_.isObject(data.nav_padding)){ #>
                        padding: {{data.nav_padding.md}};
                    <# } else { #>
                        padding: {{data.nav_padding}};
                    <# } #>
                    box-shadow: {{box_shadow}};
                    font-family:{{data.nav_font_family}};
                    <# if(_.isObject(data.nav_lineheight)){ #>
                        line-height:{{data.nav_lineheight.md}}px;
                    <# }
                    if(_.isObject(data.nav_font_style)){
                        if(data.nav_font_style.underline){
                    #>
                            text-decoration:underline;
                        <# }
                        if(data.nav_font_style.italic){
                        #>
                            font-style:italic;
                        <# }
                        if(data.nav_font_style.uppercase){
                        #>
                            text-transform:uppercase;
                        <# }
                        if(data.nav_font_style.weight){
                        #>
                            font-weight:{{data.nav_font_style.weight}};
                        <# }
                    } #>
                }
                #sppb-addon-{{ data.id }} .sppb-tab-icon {
                    <# if(data.icon_color){ #>
                        color:{{data.icon_color}};
                    <# } #>
                    <# if(_.isObject(data.icon_fontsize)){ #>
                        font-size: {{data.icon_fontsize.md}}px;
                    <# } else { #>
                        font-size: {{data.icon_fontsize}}px;
                    <# } #>
                    <# if(_.isObject(data.icon_margin)){ #>
                        margin: {{data.icon_margin.md}};
                    <# } else { #>
                        margin: {{data.icon_margin}};
                    <# } #>
                }
                #sppb-addon-{{ data.id }} .sppb-nav-custom {
                    <# if(_.isObject(data.nav_width)){ #>
                        width: {{data.nav_width.md}}%;
                    <# } else { #>
                        width: {{data.nav_width}}%;
                    <# } #>
                    <# if(_.isObject(data.nav_gutter)){ #>
                        padding-right: {{data.nav_gutter.md}}px;
                    <# } else { #>
                        padding-right: {{data.nav_gutter}}px;
                    <# } #>
                }
                #sppb-addon-{{ data.id }} .sppb-tab-custom-content {
                    <# if(_.isObject(data.nav_width)){ #>
                        width: {{100-data.nav_width.md}}%;
                    <# } else { #>
                        width: {{100-data.nav_width}}%; 
                    <# } #>
                    <# if(_.isObject(data.nav_gutter)){ #>
                        padding-left: {{data.nav_gutter.md}}px;
                    <# } else { #>
                        padding-left: {{data.nav_gutter}}px;
                    <# } #>
                }
                #sppb-addon-{{ data.id }} .sppb-tab-custom-content > div {
                    background-color: {{data.content_backround}};
                    border: {{data.content_border}}px solid;
                    border-color: {{data.content_border_color}};
                    border-radius: {{data.content_border_radius}}px;
                    color: {{data.content_color}};
                    <# if(_.isObject(data.content_padding)){ #>
                        padding: {{data.content_padding.md}};
                    <# } else { #>
                        padding: {{data.content_padding}};
                    <# } #>
                    <# if(_.isObject(data.content_margin)){ #>
                        margin: {{data.content_margin.md}};
                    <# } else { #>
                        margin: {{data.content_margin}};
                    <# } #>
                    box-shadow: {{box_shadow}};
                    font-family:{{data.content_font_family}};
                    <# if(_.isObject(data.content_fontsize)){ #>
                        font-size:{{data.content_fontsize.md}}px;
                    <# }
                    if(_.isObject(data.content_lineheight)){ #>
                        line-height:{{data.content_lineheight.md}}px;
                    <# }
                    if(_.isObject(data.content_font_style)){
                        if(data.content_font_style.underline){
                    #>
                            text-decoration:underline;
                        <# }
                        if(data.content_font_style.italic){
                        #>
                            font-style:italic;
                        <# }
                        if(data.content_font_style.uppercase){
                        #>
                            text-transform:uppercase;
                        <# }
                        if(data.content_font_style.weight){
                        #>
                            font-weight:{{data.content_font_style.weight}};
                        <# }
                    } #>
                }
                @media (min-width: 768px) and (max-width: 991px) {
                    #sppb-addon-{{ data.id }} .sppb-nav-custom li {
                        <# if(_.isObject(data.nav_margin)){ #>
                            padding: {{data.nav_margin.sm}};
                        <# } #>
                    }
                    #sppb-addon-{{ data.id }} .sppb-nav-custom li a {
                        <# if(_.isObject(data.nav_fontsize)){ #>
                            font-size: {{data.nav_fontsize.sm}}px;
                        <# } #>
                        <# if(_.isObject(data.nav_padding)){ #>
                            padding: {{data.nav_padding.sm}};
                        <# } #>
                        <# if(_.isObject(data.nav_lineheight)){ #>
                            line-height:{{data.nav_lineheight.sm}}px;
                        <# } #>
                    }
                    #sppb-addon-{{ data.id }} .sppb-tab-icon {
                        <# if(_.isObject(data.icon_fontsize)){ #>
                            font-size: {{data.icon_fontsize.sm}}px;
                        <# } #>
                        <# if(_.isObject(data.icon_margin)){ #>
                            margin: {{data.icon_margin.sm}};
                        <# } #>
                    }
                    <# if(_.isObject(data.nav_width)){ #>
                        #sppb-addon-{{ data.id }} .sppb-nav-custom {
                            width: {{data.nav_width.sm}}%;
                            <# if(_.isObject(data.nav_gutter)){ #>
                                padding-right: {{data.nav_gutter.sm}}px;
                            <# } #>
                        }
                        #sppb-addon-{{ data.id }} .sppb-tab-custom-content {
                            <# if(data.nav_width.sm !== "100"){ #>
                                width: {{100-data.nav_width.sm}}%;
                            <# } else { #>
                                width: 100%;
                            <# } #>
                            <# if(_.isObject(data.nav_gutter)){ #>
                                padding-left: {{data.nav_gutter.sm}}px;
                            <# } #>
                        }
                    <# } #>
                    #sppb-addon-{{ data.id }} .sppb-tab-custom-content > div {
                        <# if(_.isObject(data.content_padding)){ #>
                            padding: {{data.content_padding.sm}};
                        <# } #>
                        <# if(_.isObject(data.content_margin)){ #>
                            margin: {{data.content_margin.sm}};
                        <# }
                        if(_.isObject(data.content_fontsize)){ #>
                            font-size:{{data.content_fontsize.sm}}px;
                        <# }
                        if(_.isObject(data.content_lineheight)){ #>
                            line-height:{{data.content_lineheight.sm}}px;
                        <# } #>
                    }
                }
                @media (max-width: 767px) {
                    #sppb-addon-{{ data.id }} .sppb-nav-custom li {
                        <# if(_.isObject(data.nav_margin)){ #>
                            padding: {{data.nav_margin.xs}};
                        <# } #>
                    }
                    #sppb-addon-{{ data.id }} .sppb-nav-custom li a {
                        <# if(_.isObject(data.nav_fontsize)){ #>
                            font-size: {{data.nav_fontsize.xs}}px;
                        <# } #>
                        <# if(_.isObject(data.nav_padding)){ #>
                            padding: {{data.nav_padding.xs}};
                        <# } #>
                        <# if(_.isObject(data.nav_lineheight)){ #>
                            line-height:{{data.nav_lineheight.xs}}px;
                        <# } #>
                    }
                    #sppb-addon-{{ data.id }} .sppb-tab-icon {
                        <# if(_.isObject(data.icon_fontsize)){ #>
                            font-size: {{data.icon_fontsize.xs}}px;
                        <# } #>
                        <# if(_.isObject(data.icon_margin)){ #>
                            margin: {{data.icon_margin.xs}};
                        <# } #>
                    }
                    <# if(_.isObject(data.nav_width)){ #>
                        #sppb-addon-{{ data.id }} .sppb-nav-custom {
                            width: {{data.nav_width.xs}}%;
                            <# if(_.isObject(data.nav_gutter)){ #>
                                padding-right: {{data.nav_gutter.xs}}px;
                            <# } #>
                        }
                        #sppb-addon-{{ data.id }} .sppb-tab-custom-content {
                            <# if(data.nav_width.xs !== "100"){ #>
                                width: {{100-data.nav_width.xs}}%;
                            <# } else { #>
                                width: 100%;
                            <# } #>
                            <# if(_.isObject(data.nav_gutter)){ #>
                                padding-left: {{data.nav_gutter.xs}}px;
                            <# } #>
                        }
                    <# } #>
                    #sppb-addon-{{ data.id }} .sppb-tab-custom-content > div {
                        <# if(_.isObject(data.content_padding)){ #>
                            padding: {{data.content_padding.xs}};
                        <# } #>
                        <# if(_.isObject(data.content_margin)){ #>
                            margin: {{data.content_margin.xs}};
                        <# }
                        if(_.isObject(data.content_fontsize)){ #>
                            font-size:{{data.content_fontsize.xs}}px;
                        <# }
                        if(_.isObject(data.content_lineheight)){ #>
                            line-height:{{data.content_lineheight.xs}}px;
                        <# } #>
                    }
                }
            <# } #>
		</style>
		<div class="sppb-addon sppb-addon-tab {{ data.class }}">
            <# if( !_.isEmpty( data.title ) ){ #>
                <{{ data.heading_selector }} class="sppb-addon-title">{{{ data.title }}}</{{ data.heading_selector }}>
            <# } 
            let icon_postion = (data.nav_icon_postion == \'top\' || data.nav_icon_postion == \'bottom\') ? \'tab-icon-block\' : \'\';
            #>
            <div class="sppb-addon-content sppb-tab {{data.style}}-tab">
                <ul class="sppb-nav sppb-nav-{{ data.style }}">
                    <# _.each(data.sp_tab_item, function(tab, key){ #>
                        <#
                            var active = "";
                            if(key == 0){
                                active = "active";
                            }

                            var title = "";
                            var icon_top ="";
                            var icon_bottom = "";
                            var icon_right = "";
                            var icon_left = "";
                            var icon_block = "";
                            if(!_.isEmpty(tab.icon) && tab.icon && data.nav_icon_postion == "top"){
                                icon_top = \'<span class="sppb-tab-icon tab-icon-block"><i class="fa \' + tab.icon + \'"></i></span>\';
                            } else if (!_.isEmpty(tab.icon) && tab.icon && data.nav_icon_postion == "bottom") {
                                icon_bottom = \'<span class="sppb-tab-icon tab-icon-block"><i class="fa \' + tab.icon + \'"></i></span>\';
                            } else if (!_.isEmpty(tab.icon) && tab.icon && data.nav_icon_postion == "right") {
                                icon_right = \'<span class="sppb-tab-icon"><i class="fa \' + tab.icon + \'"></i></span>\';
                            } else {
                                icon_left = \'<span class="sppb-tab-icon"><i class="fa \' + tab.icon + \'"></i></span>\';
                            }
                            if(tab.title){
                                title = tab.title;
                            }
                            if(data.nav_icon_postion == "top" || data.nav_icon_postion == "bottom"){
                                icon_block = "tab-icon-block-wrap";
                            }
                        #>
                        <li class="{{ active }}"><a data-toggle="sppb-tab" class="{{data.nav_text_align}} {{icon_block}}" href="#sppb-tab-{{ data.id }}{{ key }}">{{{icon_top}}} {{{icon_left}}} {{title}} {{{icon_right}}} {{{icon_bottom}}}</a></li>
                    <# }); #>
                </ul>
                <div class="sppb-tab-content sppb-tab-{{ data.style }}-content">
                    <# _.each(data.sp_tab_item, function(tab, key){ #>
                        <#
                            var active = "";
                            if(key == 0){
                                active = "active in";
                            }
                        #>
                        <div id="sppb-tab-{{ data.id }}{{ key }}" class="sppb-tab-pane sppb-fade {{ active }}">
                            <#
                            var htmlContent = "";
                            _.each(tab.content, function(content){
                                    htmlContent += content;
                            });
                            #>
                            {{{ htmlContent }}}
                        </div>
                    <# }); #>
                </div>
            </div>
		</div>
		';

		return $output;
	}

}