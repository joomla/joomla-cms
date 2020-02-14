<?php
/**
 * @package SP Page Builder
 * @author JoomShaper http://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2016 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/
//no direct accees
defined ('_JEXEC') or die ('restricted aceess');

$addon_id = $displayData['addon_id'];
$id = $displayData['id'];
$options = $displayData['options'];

$btn_style = (isset($options->button_type) && $options->button_type) ? $options->button_type : '';
$appearance = (isset($options->button_appearance) && $options->button_appearance) ? $options->button_appearance : '';

$custom_style = '';
$custom_style_sm = '';
$custom_style_xs = '';
if($appearance == 'outline') {
  $custom_style .= (isset($options->button_background_color) && $options->button_background_color) ? ' border-color: ' . $options->button_background_color . ';': '';
} else if($appearance == '3d') {
  $custom_style .= (isset($options->button_background_color_hover) && $options->button_background_color_hover) ? ' border-bottom-color: ' . $options->button_background_color_hover . ';': '';
  $custom_style .= (isset($options->button_background_color) && $options->button_background_color) ? ' background-color: ' . $options->button_background_color . ';': '';
} else {
  $custom_style .= (isset($options->button_background_color) && $options->button_background_color) ? ' background-color: ' . $options->button_background_color . ';': '';
}
$custom_style .= (isset($options->button_color) && $options->button_color) ? ' color: ' . $options->button_color . ';': '';

$custom_style .= (isset($options->button_padding) && $options->button_padding) ? ' padding: ' . $options->button_padding . ';': '';
$custom_style_sm .= (isset($options->button_padding_sm) && $options->button_padding_sm) ? ' padding: ' . $options->button_padding_sm . ';': '';
$custom_style_xs .= (isset($options->button_padding_xs) && $options->button_padding_xs) ? ' padding: ' . $options->button_padding_xs . ';': '';

if(isset($options->button_margin_top) && is_object($options->button_margin_top)){
  $custom_style .= (isset($options->button_margin_top->md) && $options->button_margin_top->md) ? ' margin-top: ' . $options->button_margin_top->md . ';': '';
  $custom_style_sm .= (isset($options->button_margin_top->sm) && $options->button_margin_top->sm) ? ' margin-top: ' . $options->button_margin_top->sm . ';': '';
  $custom_style_xs .= (isset($options->button_margin_top->xs) && $options->button_margin_top->xs) ? ' margin-top: ' . $options->button_margin_top->xs . ';': '';
}

$hover_style  = ($appearance == 'outline') ? ((isset($options->button_background_color_hover) && $options->button_background_color_hover) ? ' border-color: ' . $options->button_background_color_hover . ';': '') : '';
$hover_style .= (isset($options->button_background_color_hover) && $options->button_background_color_hover) ? ' background-color: ' . $options->button_background_color_hover . ';': '';
$hover_style .= (isset($options->button_color_hover) && $options->button_color_hover) ? ' color: ' . $options->button_color_hover . ';': '';
$style = (isset($options->button_letterspace) && $options->button_letterspace) ? 'letter-spacing: ' . $options->button_letterspace . ';' : '';

$css = '';

// Font Style
$modern_font_style = false;
if(isset($options->button_font_style->underline) && $options->button_font_style->underline) {
    $style .= 'text-decoration: underline;';
    $modern_font_style = true;
}

if(isset($options->button_font_style->italic) && $options->button_font_style->italic) {
    $style .= 'font-style: italic;';
    $modern_font_style = true;
}

if(isset($options->button_font_style->uppercase) && $options->button_font_style->uppercase) {
    $style .= 'text-transform: uppercase;';
    $modern_font_style = true;
}

if(isset($options->button_font_style->weight) && $options->button_font_style->weight) {
    $style .= 'font-weight: ' . $options->button_font_style->weight . ';';
    $modern_font_style = true;
}

// legcy font style
if(!$modern_font_style) {
  $font_style = (isset($options->button_fontstyle) && $options->button_fontstyle) ? $options->button_fontstyle : '';
  if(is_array($font_style) && count($font_style)) {
    if(in_array('underline', $font_style)) {
      $style .= 'text-decoration: underline;';
    }

    if(in_array('uppercase', $font_style)) {
      $style .= 'text-transform: uppercase;';
    }

    if(in_array('italic', $font_style)) {
      $style .= 'font-style: italic;';
    }

    if(in_array('lighter', $font_style)) {
      $style .= 'font-weight: lighter;';
    } else if(in_array('normal', $font_style)) {
      $style .= 'font-weight: normal;';
    } else if(in_array('bold', $font_style)) {
      $style .= 'font-weight: bold;';
    } else if(in_array('bolder', $font_style)) {
      $style .= 'font-weight: bolder;';
    }
  }
}

if($style) {
  $css .= $addon_id . ' #'. $id .'.sppb-btn-' . $btn_style  . '{' . $style . '}';
}

if($btn_style == 'custom') {
  if($custom_style) {
    $css .= $addon_id . ' #'. $id .'.sppb-btn-custom {' . $custom_style . '}';
  }

  if($hover_style) {
    $css .= $addon_id . ' #'. $id .'.sppb-btn-custom:hover {' . $hover_style . '}';
  }

  // Responsive Tablet
  if(!empty($custom_style_sm)) {
    $css .= "@media (min-width: 768px) and (max-width: 991px) {";
    $css .= $addon_id . ' #'. $id .".sppb-btn-custom {\n" . $custom_style_sm . "}\n";
    $css .= "}";
  }

  // Responsive Phone
  if(!empty($custom_style_xs)) {
    $css .= "@media (max-width: 767px) {";
    $css .= $addon_id . ' #'. $id .".sppb-btn-custom {\n" . $custom_style_xs . "}\n";
    $css .= "}";
  }
}

echo $css;
