<?php
/**
 * @package SP Page Builder
 * @author JoomShaper http://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2016 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/
//no direct accees
defined ('_JEXEC') or die ('restricted aceess');

$doc = JFactory::getDocument();

$selector_css = new JLayoutFile('addon.css.selector', JPATH_ROOT . '/components/com_sppagebuilder/layouts');

$addon = $displayData['addon'];

$inlineCSS = '';
$addon_css = '';
$addon_link_css = '';
$addon_link_hover_css = '';
$addon_id = "#sppb-addon-". $addon->id;
// Color
if(isset($addon->settings->global_text_color) && $addon->settings->global_text_color) {
    $addon_css .= "\tcolor: " . $addon->settings->global_text_color . ";\n";
}

if(isset($addon->settings->global_link_color) && $addon->settings->global_link_color) {
    $addon_link_css .= "\tcolor: " . $addon->settings->global_link_color . ";\n";
}

if(isset($addon->settings->global_link_hover_color) && $addon->settings->global_link_hover_color) {
    $addon_link_hover_css .= "\tcolor: " . $addon->settings->global_link_hover_color . ";\n";
}

// Background
if(isset($addon->settings->global_use_background) && $addon->settings->global_use_background) {
    if(isset($addon->settings->global_background_color) && $addon->settings->global_background_color) {
        $addon_css .= "\tbackground-color: " . $addon->settings->global_background_color . ";\n";
    }

    if(isset($addon->settings->global_background_image) && $addon->settings->global_background_image) {
        if(strpos($addon->settings->global_background_image, "http://") !== false || strpos($addon->settings->global_background_image, "https://") !== false){
            $addon_css .= "\tbackground-image: url(" . $addon->settings->global_background_image . ");\n";
        } else {
            $addon_css .= "\tbackground-image: url(" . JURI::base(true) . '/' . $addon->settings->global_background_image . ");\n";
        }

        if(isset($addon->settings->global_background_repeat) && $addon->settings->global_background_repeat) {
            $addon_css .= "\tbackground-repeat: " . $addon->settings->global_background_repeat . ";\n";
        }

        if(isset($addon->settings->global_background_size) && $addon->settings->global_background_size) {
            $addon_css .= "\tbackground-size: " . $addon->settings->global_background_size . ";\n";
        }

        if(isset($addon->settings->global_background_attachment) && $addon->settings->global_background_attachment) {
            $addon_css .= "\tbackground-attachment: " . $addon->settings->global_background_attachment . ";\n";
        }
    }
}

// Box Shadow
if(isset($addon->settings->global_boxshadow) && $addon->settings->global_boxshadow){
    if(is_object($addon->settings->global_boxshadow)){
        $ho = (isset($addon->settings->global_boxshadow->ho) && $addon->settings->global_boxshadow->ho != '') ? $addon->settings->global_boxshadow->ho.'px' : '0px';
        $vo = (isset($addon->settings->global_boxshadow->vo) && $addon->settings->global_boxshadow->vo != '') ? $addon->settings->global_boxshadow->vo.'px' : '0px';
        $blur = (isset($addon->settings->global_boxshadow->blur) && $addon->settings->global_boxshadow->blur != '') ? $addon->settings->global_boxshadow->blur.'px' : '0px';
        $spread = (isset($addon->settings->global_boxshadow->spread) && $addon->settings->global_boxshadow->spread != '') ? $addon->settings->global_boxshadow->spread.'px' : '0px';
        $color = (isset($addon->settings->global_boxshadow->color) && $addon->settings->global_boxshadow->color != '') ? $addon->settings->global_boxshadow->color : '#fff';
        $addon_css .= "\tbox-shadow: ${ho} ${vo} ${blur} ${spread} ${color};\n";
    } else {
        $addon_css .= "\tbox-shadow: " . $addon->settings->global_boxshadow . ";\n";
    }
}

// Border
if(isset($addon->settings->global_user_border) && $addon->settings->global_user_border) {

    if (is_object($addon->settings->global_border_width)) {
        $addon_css .= isset($addon->settings->global_border_width->md) && $addon->settings->global_border_width->md ? "border-width: " . $addon->settings->global_border_width->md . "px;\n" : "";
    } else {
        $addon_css .= isset($addon->settings->global_border_width) && $addon->settings->global_border_width ? "border-width: " . $addon->settings->global_border_width . "px;\n" : "";
    }

    if(isset($addon->settings->global_border_color) && $addon->settings->global_border_color) {
        $addon_css .= "border-color: " . $addon->settings->global_border_color . ";\n";
    }

    if(isset($addon->settings->global_boder_style) && $addon->settings->global_boder_style) {
        $addon_css .= "border-style: " . $addon->settings->global_boder_style . ";\n";
    }
}

// Border radius

$addon_css .= (isset($addon->settings->global_border_radius) && $addon->settings->global_border_radius) ? "border-radius: " . $addon->settings->global_border_radius . "px;\n" : "";

// Padding & Margin
$addon_css .= (isset($addon->settings->global_margin) && $addon->settings->global_margin) ? SppagebuilderHelperSite::getPaddingMargin($addon->settings->global_margin, 'margin') : '';

$addon_css .= (isset($addon->settings->global_padding) && $addon->settings->global_padding) ? SppagebuilderHelperSite::getPaddingMargin($addon->settings->global_padding, 'padding') : '';

if(isset($addon->settings->use_global_width) && $addon->settings->use_global_width && isset($addon->settings->global_width) && $addon->settings->global_width){
    $addon_css .= "width: " . $addon->settings->global_width . "%;\n";
}

if($addon_css) {
    $inlineCSS .= $addon_id ." {\n" . $addon_css . "}\n";
}

if($addon_link_css) {
    $inlineCSS .= $addon_id ." a {\n" . $addon_link_css . "}\n";
}

if($addon_link_hover_css) {
    $inlineCSS .= $addon_id ." a:hover,\n#sppb-addon-". $addon->id ." a:focus,\n#sppb-addon-". $addon->id ." a:active {\n" . $addon_link_hover_css . "}\n";
}

//Addon Title
if(isset($addon->settings->title) && $addon->settings->title) {

    $title_style = '';

    if (isset($addon->settings->title_margin_top) && is_object($addon->settings->title_margin_top)) {
        $title_style .= (isset($addon->settings->title_margin_top->md) && $addon->settings->title_margin_top->md != '') ? 'margin-top:' . (int) $addon->settings->title_margin_top->md . 'px;' : '';
    } else {
        $title_style .= (isset($addon->settings->title_margin_top) && $addon->settings->title_margin_top != '') ? 'margin-top:' . (int) $addon->settings->title_margin_top . 'px;' : '';
    }

    if(isset($addon->settings->title_margin_bottom) && is_object($addon->settings->title_margin_bottom)) {
        $title_style .= (isset($addon->settings->title_margin_bottom->md) && $addon->settings->title_margin_bottom->md != '') ? 'margin-bottom:' . (int) $addon->settings->title_margin_bottom->md . 'px;' : '';
    } else {
        $title_style .= (isset($addon->settings->title_margin_bottom) && $addon->settings->title_margin_bottom != '') ? 'margin-bottom:' . (int) $addon->settings->title_margin_bottom . 'px;' : '';
    }

    $title_style .= (isset($addon->settings->title_text_color) && $addon->settings->title_text_color) ? 'color:' . $addon->settings->title_text_color . ';' : '';

    if (isset($addon->settings->title_fontsize) && is_object($addon->settings->title_fontsize)) {
        $title_style .= (isset($addon->settings->title_fontsize->md) && $addon->settings->title_fontsize->md) ? 'font-size:' . $addon->settings->title_fontsize->md . 'px;line-height:' . $addon->settings->title_fontsize->md . 'px;' : '';
    } else {
        $title_style .= (isset($addon->settings->title_fontsize) && $addon->settings->title_fontsize) ? 'font-size:' . $addon->settings->title_fontsize . 'px;line-height:' . $addon->settings->title_fontsize . 'px;' : '';
    }

    if (isset($addon->settings->title_lineheight) && is_object($addon->settings->title_lineheight)) {
        $title_style .= (isset($addon->settings->title_lineheight->md) && $addon->settings->title_lineheight->md) ? 'line-height:' . $addon->settings->title_lineheight->md . 'px;' : '';
    } else {
        $title_style .= (isset($addon->settings->title_lineheight) && $addon->settings->title_lineheight) ? 'line-height:' . $addon->settings->title_lineheight . 'px;' : '';
    }

    $title_style .= (isset($addon->settings->title_letterspace) && $addon->settings->title_letterspace) ? 'letter-spacing:' . $addon->settings->title_letterspace . ';' : '';

    // Font Style
    $modern_font_style = false;
    if(isset($addon->settings->title_font_style->underline) && $addon->settings->title_font_style->underline) {
        $title_style .= 'text-decoration: underline;';
        $modern_font_style = true;
    }

    if(isset($addon->settings->title_font_style->italic) && $addon->settings->title_font_style->italic) {
        $title_style .= 'font-style: italic;';
        $modern_font_style = true;
    }

    if(isset($addon->settings->title_font_style->uppercase) && $addon->settings->title_font_style->uppercase) {
        $title_style .= 'text-transform: uppercase;';
        $modern_font_style = true;
    }

    if(isset($addon->settings->title_font_style->weight) && $addon->settings->title_font_style->weight) {
        $title_style .= 'font-weight: ' . $addon->settings->title_font_style->weight . ';';
        $modern_font_style = true;
    }

    // legcy font style
    if(!$modern_font_style) {
        $title_fontstyle = (isset($addon->settings->title_fontstyle) && $addon->settings->title_fontstyle) ? $addon->settings->title_fontstyle : '';
        if(is_array($title_fontstyle) && count($title_fontstyle)) {

            if(in_array('underline', $title_fontstyle)) {
                $title_style .= 'text-decoration: underline;';
            }

            if(in_array('uppercase', $title_fontstyle)) {
                $title_style .= 'text-transform: uppercase;';
            }

            if(in_array('italic', $title_fontstyle)) {
                $title_style .= 'font-style: italic;';
            }

            if(in_array('lighter', $title_fontstyle)) {
                $title_style .= 'font-weight: lighter;';
            } else if(in_array('normal', $title_fontstyle)) {
                $title_style .= 'font-weight: normal;';
            } else if(in_array('bold', $title_fontstyle)) {
                $title_style .= 'font-weight: bold;';
            } else if(in_array('bolder', $title_fontstyle)) {
                $title_style .= 'font-weight: bolder;';
            }

            $title_style .= (isset($addon->settings->title_fontweight) && $addon->settings->title_fontweight) ? 'font-weight:' . $addon->settings->title_fontweight . ';' : '';

        }
    }

    if($title_style) {
        $inlineCSS .= $addon_id ." .sppb-addon-title {\n" . $title_style . "}\n";
    }
}

// Responsive Tablet
$inlineCSS .= "@media (min-width: 768px) and (max-width: 991px) {";
    $inlineCSS .= $addon_id." {";
        $inlineCSS .= (isset($addon->settings->global_margin_sm) && $addon->settings->global_margin_sm) ? SppagebuilderHelperSite::getPaddingMargin($addon->settings->global_margin_sm, 'margin') : '';

        $inlineCSS .= (isset($addon->settings->global_padding_sm) && $addon->settings->global_padding_sm) ? SppagebuilderHelperSite::getPaddingMargin($addon->settings->global_padding_sm, 'padding') : '';


        $inlineCSS .= (isset($addon->settings->global_border_radius_sm) && $addon->settings->global_border_radius_sm) ? "border-radius: " . $addon->settings->global_border_radius_sm . "px;\n" : "";

        if(isset($addon->settings->global_user_border) && $addon->settings->global_user_border) {
            $inlineCSS .= isset($addon->settings->global_border_width_sm) && $addon->settings->global_border_width_sm ? "border-width: " . $addon->settings->global_border_width_sm . "px;\n" : "";
        }


        if(isset($addon->settings->use_global_width) && $addon->settings->use_global_width && isset($addon->settings->global_width_sm) && $addon->settings->global_width_sm){
            $inlineCSS .= "width: " . $addon->settings->global_width_sm . "%;\n";
        }

    $inlineCSS .= "}";
    if(isset($addon->settings->title) && $addon->settings->title) {
        $title_style_sm = (isset($addon->settings->title_fontsize_sm) && $addon->settings->title_fontsize_sm) ? 'font-size:' . $addon->settings->title_fontsize_sm . 'px;line-height:' . $addon->settings->title_fontsize_sm . 'px;' : '';
        $title_style_sm .= (isset($addon->settings->title_lineheight_sm) && $addon->settings->title_lineheight_sm) ? 'line-height:' . $addon->settings->title_lineheight_sm . 'px;' : '';
        $title_style_sm  .= (isset($addon->settings->title_margin_top_sm) && $addon->settings->title_margin_top_sm != '') ? 'margin-top:' . (int) $addon->settings->title_margin_top_sm . 'px;' : '';
        $title_style_sm .= (isset($addon->settings->title_margin_bottom_sm) && $addon->settings->title_margin_bottom_sm != '') ? 'margin-bottom:' . (int) $addon->settings->title_margin_bottom_sm . 'px;' : '';

        if($title_style_sm) {
            $inlineCSS .= $addon_id ." .sppb-addon-title {\n" . $title_style_sm . "}\n";
        }
    }
$inlineCSS .= "}";

// Responsive Phone
$inlineCSS .= "@media (max-width: 767px) {";
    $inlineCSS .= $addon_id." {";
    $inlineCSS .= (isset($addon->settings->global_margin_xs) && $addon->settings->global_margin_xs) ? SppagebuilderHelperSite::getPaddingMargin($addon->settings->global_margin_xs, 'margin') : '';

    $inlineCSS .= (isset($addon->settings->global_padding_xs) && $addon->settings->global_padding_xs) ? SppagebuilderHelperSite::getPaddingMargin($addon->settings->global_padding_xs, 'padding') : '';


    $inlineCSS .= (isset($addon->settings->global_border_radius_xs) && $addon->settings->global_border_radius_xs) ? "border-radius: " . $addon->settings->global_border_radius_xs . "px;\n" : "";

    if(isset($addon->settings->global_user_border) && $addon->settings->global_user_border) {
        $inlineCSS .= isset($addon->settings->global_border_width_xs) && $addon->settings->global_border_width_xs ? "border-width: " . $addon->settings->global_border_width_xs . "px;\n" : "";
    }


    if(isset($addon->settings->use_global_width) && $addon->settings->use_global_width && isset($addon->settings->global_width_xs) && $addon->settings->global_width_xs){
        $inlineCSS .= "width: " . $addon->settings->global_width_xs . "%;\n";
    }
    $inlineCSS .= "}";
    if(isset($addon->settings->title) && $addon->settings->title) {
        $title_style_xs = (isset($addon->settings->title_fontsize_xs) && $addon->settings->title_fontsize_xs) ? 'font-size:' . $addon->settings->title_fontsize_xs . 'px;line-height:' . $addon->settings->title_fontsize_xs . 'px;' : '';
        $title_style_xs .= (isset($addon->settings->title_lineheight_xs) && $addon->settings->title_lineheight_xs) ? 'line-height:' . $addon->settings->title_lineheight_xs . 'px;' : '';
        $title_style_xs  .= (isset($addon->settings->title_margin_top_xs) && $addon->settings->title_margin_top_xs != '') ? 'margin-top:' . (int) $addon->settings->title_margin_top_xs . 'px;' : '';
        $title_style_xs .= (isset($addon->settings->title_margin_bottom_xs) && $addon->settings->title_margin_bottom_xs != '') ? 'margin-bottom:' . (int) $addon->settings->title_margin_bottom_xs . 'px;' : '';

        if($title_style_xs) {
            $inlineCSS .= $addon_id ." .sppb-addon-title {\n" . $title_style_xs . "}\n";
        }
    }
$inlineCSS .= "}";


// Selector
$inlineCSS .= $selector_css->render(
    array(
        'options'=>$addon->settings,
        'addon_id'=>$addon_id
    )
);

echo $inlineCSS;
