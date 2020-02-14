<?php

defined ('_JEXEC') or die('resticted aceess');

AddonParser::addAddon('sp_call_to_action','sp_call_to_action_addon');

function sp_call_to_action_addon($atts){

	extract(spAddonAtts(array(
		"title" 				=> '',
		"heading_selector" 		=> 'h3',
		"title_fontsize" 		=> '',
		"title_text_color" 		=> '',
		"title_margin_top" 		=> '',
		"title_margin_bottom" 	=> '',
		"subtitle_fontsize" 	=> '',
		"subtitle" 				=> '',
		"subtitle_text_color" 	=> '',
		"text" 					=> '',
		"background" 			=> '',
		"color" 				=> '',
		"padding" 				=> '',
		"button_text"			=>'',
		"button_url"			=>'',
		"button_size"			=>'',
		"button_type"			=>'',
		"button_icon"			=>'',
		"button_block"			=>'',
		"button_target"			=>'',
		"button_position"		=>'',
		"class"=>'',
		), $atts));

	$style = '';

	if($button_icon) {
		$button_text = '<i class="fa ' . $button_icon . '"></i> ' . $button_text;
	}

	if($background) {
		$style .= 'background-color: ' . $background . ';padding:40px 20px;';
	}

	if($color) {
		$style .= 'color: ' . $color . ';';
	}

	if($padding) {
		$style .= 'padding: ' . (int)$padding . 'px;';
	}

	$button_output = '<a target="' . $button_target . '" href="' . $button_url . '" class="sppb-btn sppb-btn-' . $button_type . ' sppb-btn-' . $button_size . ' ' . $button_block . '" role="button">' . $button_text . '</a>';

	$output  = '<div class="sppb-addon sppb-addon-cta ' . $class . '" style="' . $style . '">';

	if($button_position=='right') {

		$output .= '<div class="sppb-row">';

		$output .= '<div class="sppb-col-sm-8">';

		if($title) {

			$title_style = '';
			if($title_margin_top) $title_style .= 'margin-top:' . (int) $title_margin_top . 'px;';
			if($title_margin_bottom) $title_style .= 'margin-bottom:' . (int) $title_margin_bottom . 'px;';
			if($title_text_color) $title_style .= 'color:' . $title_text_color  . ';';
			if($title_fontsize) $title_style .= 'font-size:'.$title_fontsize.'px;line-height:'.$title_fontsize.'px;';

			$output .= '<'.$heading_selector.' class="sppb-cta-title" style="' . $title_style . '">' . $title . '</'.$heading_selector.'>';
		}

		if($subtitle) {

			$subtitle_style = '';

			if($subtitle_text_color) $subtitle_style .= 'color:' . $subtitle_text_color  . ';';
			if($subtitle_fontsize) $subtitle_style .= 'font-size:'.$subtitle_fontsize.'px;line-height:'.$subtitle_fontsize.'px;';

			$output .= '<p class="sppb-lead sppb-cta-subtitle" style="' . $subtitle_style . '">' . $subtitle . '</p>';
		}


		if($text) $output .= '<p class="sppb-cta-text">' . $text . '</p>';

		$output .= '</div>';

		$output .= '<div class="sppb-col-sm-4 sppb-text-right">';
		$output .= $button_output;
		$output .= '</div>';

		$output .= '</div>';


	} else {

		$output .= '<div class="text-center">';

		if($title) {

			$title_style = '';
			if($title_margin_top) $title_style .= 'margin-top:' . (int) $title_margin_top . 'px;';
			if($title_margin_bottom) $title_style .= 'margin-bottom:' . (int) $title_margin_bottom . 'px;';
			if($title_text_color) $title_style .= 'color:' . $title_text_color  . ';';
			if($title_fontsize) $title_style .= 'font-size:'.$title_fontsize.'px;line-height:'.$title_fontsize.'px;';

			$output .= '<'.$heading_selector.' class="sppb-cta-title" style="' . $title_style . '">' . $title . '</'.$heading_selector.'>';
		}

		if($subtitle) {

			$subtitle_style = '';

			if($subtitle_text_color) $subtitle_style .= 'color:' . $subtitle_text_color  . ';';
			if($subtitle_fontsize) $subtitle_style .= 'font-size:'.$subtitle_fontsize.'px;line-height:'.$subtitle_fontsize.'px;';

			$output .= '<p class="sppb-lead sppb-cta-subtitle" style="' . $subtitle_style . '">' . $subtitle . '</p>';
		}

		if($text) $output .= '<p class="sppb-cta-text">' . $text . '</p>';

		$output .= '<div>';
		$output .= $button_output;
		$output .= '</div>';

		$output .= '</div>';

	}

	$output .= '</div>';

	return $output;

}
