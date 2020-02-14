<?php
/**
 * @package SP Page Builder
 * @author JoomShaper http://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2016 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/
//no direct accees
defined ('_JEXEC') or die ('restricted aceess');

$options = $displayData['options'];

$doc 					= JFactory::getDocument();
$row_id     			= (isset($options->id) && $options->id )? $options->id : 'section-id-'.$options->dynamicId;

$row_styles = '';
$style ='';
$style_sm ='';
$style_xs ='';


if( isset( $options->padding ) ){
	if( is_object( $options->padding ) ){
		if (isset($options->padding->md) && $options->padding->md) $style .= SppagebuilderHelperSite::getPaddingMargin($options->padding->md, 'padding');
		if (isset($options->padding->sm) && $options->padding->sm) $style_sm .= SppagebuilderHelperSite::getPaddingMargin($options->padding->sm, 'padding');
		if (isset($options->padding->xs) && $options->padding->xs) $style_xs .= SppagebuilderHelperSite::getPaddingMargin($options->padding->xs, 'padding');
	} else {
		if ($options->padding) $style .= 'padding: '.$options->padding.';';
	}
}

if( isset( $options->margin ) ){
	if( is_object( $options->margin ) ){
		if (isset($options->margin->md) && $options->margin->md) $style .= SppagebuilderHelperSite::getPaddingMargin($options->margin->md, 'margin');
		if (isset($options->margin->sm) && $options->margin->sm) $style_sm .= SppagebuilderHelperSite::getPaddingMargin($options->margin->sm, 'margin');
		if (isset($options->margin->xs) && $options->margin->xs) $style_xs .= SppagebuilderHelperSite::getPaddingMargin($options->margin->xs, 'margin');
	} else {
		if ($options->margin) $style .= 'margin: '.$options->margin.';';
	}
}


if (isset($options->color) && $options->color) $style .= 'color:'.$options->color.';';
if (isset($options->background_color) && $options->background_color) $style .= 'background-color:'.$options->background_color.';';

if (isset($options->background_image) && $options->background_image) {
	if(strpos($options->background_image, "http://") !== false || strpos($options->background_image, "https://") !== false){
		$style .= 'background-image:url(' . $options->background_image.');';
	} else {
		$style .= 'background-image:url('. JURI::base(true) . '/' . $options->background_image.');';
	}
	if (isset($options->background_repeat) && $options->background_repeat) $style .= 'background-repeat:'.$options->background_repeat.';';
	if (isset($options->background_size) && $options->background_size) $style .= 'background-size:'.$options->background_size.';';
	if (isset($options->background_attachment) && $options->background_attachment) $style .= 'background-attachment:'.$options->background_attachment.';';
	if (isset($options->background_position) && $options->background_position) $style .= 'background-position:'.$options->background_position.';';
}

if($style) {
	$row_styles .= '.sp-page-builder .page-content #' . $row_id . '{'. $style .'}';
}

if($style_sm) {
	$row_styles .=  '@media (min-width: 768px) and (max-width: 991px) { .sp-page-builder .page-content #' . $row_id . '{'. $style_sm .'} }';
}
if($style_xs) {
	$row_styles .= '@media (max-width: 767px) { .sp-page-builder .page-content #' . $row_id . '{'. $style_xs .'} }';
}

// Overlay
if (isset($options->overlay) && $options->overlay) {
	$row_styles .= '.sp-page-builder .page-content #' . $row_id . ' > .sppb-row-overlay {background-color: '. $options->overlay .'}';
}

// Row Title
if ( (isset($options->title) && $options->title) || (isset($options->subtitle) && $options->subtitle) ) {
	
	if(isset($options->title) && $options->title) {
		$title_style = '';
		$title_style_sm = '';
		$title_style_xs = '';
		//Title Font Size
		if(isset($options->title_fontsize)) {
			if(is_object($options->title_fontsize)) {
				$title_style .= (isset($options->title_fontsize->md) && $options->title_fontsize->md != '') ? 'font-size:'.$options->title_fontsize->md.'px;line-height: '.$options->title_fontsize->md.'px;' : '';
				$title_style_sm .= (isset($options->title_fontsize->sm) && $options->title_fontsize->sm != '') ? 'font-size:'.$options->title_fontsize->sm.'px;line-height: '.$options->title_fontsize->sm.'px;' : '';
				$title_style_xs .= (isset($options->title_fontsize->xs) && $options->title_fontsize->xs != '') ? 'font-size:'.$options->title_fontsize->xs.'px;line-height: '.$options->title_fontsize->xs.'px;' : '';
			} else {
				$title_style .= (isset($options->title_fontsize) && $options->title_fontsize != '') ? 'font-size:'.$options->title_fontsize.'px;line-height: '.$options->title_fontsize.'px;' : '';
			}
		}

		//Title Font Weight
		if(isset($options->title_fontweight)) {
			if($options->title_fontweight != '') {
				$title_style .= 'font-weight:'.$options->title_fontweight.';';
			}
		}

		//Title Text Color
		if(isset($options->title_text_color)) {
			if($options->title_text_color != '') {
				$title_style .= 'color:'.$options->title_text_color. ';';
			}
		}

		//Title Margin Top
		if(isset($options->title_margin_top)) {
			if(is_object($options->title_margin_top)) {
				$title_style .= (isset($options->title_margin_top->md) && $options->title_margin_top->md != '') ? 'margin-top:' . $options->title_margin_top->md . 'px;' : '';
				$title_style_sm .= (isset($options->title_margin_top->sm) && $options->title_margin_top->sm != '') ? 'margin-top:' . $options->title_margin_top->sm . 'px;' : '';
				$title_style_xs .= (isset($options->title_margin_top->xs) && $options->title_margin_top->xs != '') ? 'margin-top:' . $options->title_margin_top->xs . 'px;' : '';
			} else {
				$title_style .= (isset($options->title_margin_top) && $options->title_margin_top != '') ? 'margin-top:' . $options->title_margin_top . 'px;' : '';
			}
		}

		//Title Margin Bottom
		if(isset($options->title_margin_bottom)) {
			if(is_object($options->title_margin_bottom)) {
				$title_style .= (isset($options->title_margin_bottom->md) && $options->title_margin_bottom->md != '') ? 'margin-bottom:' . $options->title_margin_bottom->md . 'px;' : '';
				$title_style_sm .= (isset($options->title_margin_bottom->sm) && $options->title_margin_bottom->sm != '') ? 'margin-bottom:' . $options->title_margin_bottom->sm . 'px;' : '';
				$title_style_xs .= (isset($options->title_margin_bottom->xs) && $options->title_margin_bottom->xs != '') ? 'margin-bottom:' . $options->title_margin_bottom->xs . 'px;' : '';
			} else {
				$title_style .= (isset($options->title_margin_bottom) && $options->title_margin_bottom != '') ? 'margin-bottom:' . $options->title_margin_bottom . 'px;' : '';
			}
		}

		$row_styles .= ($title_style) ? '.sp-page-builder .page-content #' . $row_id . ' .sppb-section-title .sppb-title-heading {'. $title_style .'}' : '';
		$row_styles .= ($title_style_sm) ? '@media (min-width: 768px) and (max-width: 991px) { .sp-page-builder .page-content #' . $row_id . ' .sppb-section-title .sppb-title-heading {'. $title_style_sm .'}}' : '';
		$row_styles .= ($title_style_xs) ? '@media (max-width: 767px) { .sp-page-builder .page-content #' . $row_id . ' .sppb-section-title .sppb-title-heading {'. $title_style_xs .'}}' : '';

	}

	// Subtitle font size
	if( isset( $options->subtitle ) && $options->subtitle ) {
		if( isset( $options->subtitle_fontsize ) ) {
			$subtitle_fontsize = '';
			$subtitle_fontsize_sm = '';
			$subtitle_fontsize_xs = '';

			if(is_object($options->subtitle_fontsize)) {
				$subtitle_fontsize = (isset($options->subtitle_fontsize->md) && $options->subtitle_fontsize->md != '') ? 'font-size:'.$options->subtitle_fontsize->md.'px;' : '';
				$subtitle_fontsize_sm = (isset($options->subtitle_fontsize->sm) && $options->subtitle_fontsize->sm != '') ? 'font-size:'.$options->subtitle_fontsize->sm.'px;' : '';
				$subtitle_fontsize_xs = (isset($options->subtitle_fontsize->xs) && $options->subtitle_fontsize->xs != '') ? 'font-size:'.$options->subtitle_fontsize->xs.'px;' : '';
			} else {
				$subtitle_fontsize = (isset($options->subtitle_fontsize) && $options->subtitle_fontsize != '') ? 'font-size:'.$options->subtitle_fontsize.'px;' : '';
			}
			$row_styles .= ($subtitle_fontsize) ? '.sp-page-builder .page-content #' . $row_id . ' .sppb-section-title .sppb-title-subheading {'. $subtitle_fontsize .'}' : '';
			$row_styles .= ($subtitle_fontsize_sm) ? '@media (min-width: 768px) and (max-width: 991px) {.sp-page-builder .page-content #' . $row_id . ' .sppb-section-title .sppb-title-subheading {'. $subtitle_fontsize_sm .'}}' : '';
			$row_styles .= ($subtitle_fontsize_xs) ? '@media (max-width: 767px) {.sp-page-builder .page-content #' . $row_id . ' .sppb-section-title .sppb-title-subheading {'. $subtitle_fontsize_xs .'}}' : '';
		}
	}
}

echo $row_styles;