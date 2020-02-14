<?php
/**
 * @package SP Page Builder
 * @author JoomShaper http://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2017 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/
//no direct accees
defined ('_JEXEC') or die ('restricted aceess');

class SppagebuilderAddonTestimonialpro extends SppagebuilderAddons {

	public function render() {

		$class = (isset($this->addon->settings->class) && $this->addon->settings->class) ? $this->addon->settings->class : '';
		$style = (isset($this->addon->settings->style) && $this->addon->settings->style) ? $this->addon->settings->style : '';

		//Options
		$autoplay = (isset($this->addon->settings->autoplay) && $this->addon->settings->autoplay) ? ' data-sppb-ride="sppb-carousel"' : '';
		$arrows = (isset($this->addon->settings->arrows) && $this->addon->settings->arrows) ? $this->addon->settings->arrows : '';
		$controls = '';
		$interval = (isset($this->addon->settings->interval) && $this->addon->settings->interval) ? ((int) $this->addon->settings->interval * 1000) : 5000;
		$avatar_size = (isset($this->addon->settings->avatar_size) && $this->addon->settings->avatar_size) ? $this->addon->settings->avatar_size : '';
		$avatar_shape = (isset($this->addon->settings->avatar_shape) && $this->addon->settings->avatar_shape) ? $this->addon->settings->avatar_shape : 'sppb-avatar-circle';

		//Output
		$output  = '<div id="sppb-testimonial-pro-'. $this->addon->id .'" data-interval="'.$interval.'" class="sppb-carousel sppb-testimonial-pro sppb-slide sppb-text-center' . $class . '"'. $autoplay .'>';

		if($controls) {
			$output .= '<ol class="sppb-carousel-indicators">';
			foreach ($this->addon->settings->sp_testimonialpro_item as $key1 => $value) {
				$output .= '<li data-sppb-target="#sppb-carousel-'. $this->addon->id .'" '. (($key1 == 0) ? ' class="active"': '' ) .'  data-sppb-slide-to="'. $key1 .'"></li>' . "\n";
			}
			$output .= '</ol>';
		}

		//$output  .= '<span class="fa fa-quote-left"></span>';
		$output .= '<div class="sppb-carousel-inner">';

		foreach ($this->addon->settings->sp_testimonialpro_item as $key => $value) {
			$output   .= '<div class="sppb-item ' . (($key == 0) ? ' active' : '') .'">';
			$name = (isset($value->title) && $value->title) ? $value->title : '';

			$output .= (isset($value->avatar) && $value->avatar) ? '<img src="'.$value->avatar.'" height="' . $avatar_size . '" width="' . $avatar_size . '" class="'. $avatar_shape .'" alt="'.$name.'">' : '';
			$output  .= '<div class="sppb-testimonial-message">' . $value->message . '</div>';
			$output .= '<div class="sppb-addon-testimonial-pro-footer">';
			$output .= $name ? '<strong>' . $name . '</strong>' : '';
			$output .= (isset($value->url) && $value->url) ? '&nbsp;<span class="sppb-addon-testimonial-pro-client-url">' . $value->url . '</span>' : '';
			$output .= '</div>';

			$output  .= '</div>';
		}
		$output	.= '</div>';

		if($arrows) {
			$output	.= '<a href="#sppb-testimonial-pro-'. $this->addon->id .'" class="left sppb-carousel-control" data-slide="prev"><i class="fa fa-angle-left"></i></a>';
			$output	.= '<a href="#sppb-testimonial-pro-'. $this->addon->id .'" class="right sppb-carousel-control" data-slide="next"><i class="fa fa-angle-right"></i></a>';
		}

		$output .= '</div>';

		return $output;

	}

	public function css() {
		$addon_id = '#sppb-addon-' . $this->addon->id;
		$css = '';

		$speed = (isset($this->addon->settings->speed) && $this->addon->settings->speed) ? $this->addon->settings->speed : 600;

		$css .= $addon_id.' .sppb-carousel-inner > .sppb-item{-webkit-transition-duration: '.$speed.'ms; transition-duration: '.$speed.'ms;}';

		return $css;
	}

	public static function getTemplate(){

		$output = '
			<#
				let interval = (data.interval)? (data.interval*1000):5000
				let autoplay = (data.autoplay)? \'data-sppb-ride="sppb-carousel"\':""
				let avatar_size = data.avatar_width || 76
				let avatar_shape = data.avatar_shape || "sppb-avatar-circle"
				
			#>
			<div id="sppb-testimonial-pro-{{ data.id }}" data-interval="{{ interval }}" class="sppb-carousel sppb-testimonial-pro sppb-slide sppb-text-center {{ data.class }}" {{{ autoplay }}}>

				<# if(data.controls) { #>
					<ol class="sppb-carousel-indicators">
					<#
						_.each(data.sp_testimonialpro_item, function(item,key){
							let activeClass
							if (key == 0) {
								activeClass = "class=active"
							}else{
								activeClass = ""
							}
					#>
						<li data-sppb-target="#sppb-testimonial-pro-{{ data.id }}" {{ activeClass }} data-sppb-slide-to="{{ key }}"></li>
					<# }) #>
					</ol>
				<# } #>

				
				<div class="sppb-carousel-inner">
					<#
						_.each(data.sp_testimonialpro_item, function(itemSlide, index) {
							let slideActClass = ""
							if (index == 0) {
								slideActClass = " active"
							} else {
								slideActClass = ""
							}
					#>

						<div class="sppb-item{{ slideActClass }}">

							<# if (!_.isEmpty(itemSlide.avatar)) { #>
								<img class="{{ avatar_shape }}" src=\'{{ itemSlide.avatar }}\' height="{{avatar_size}}" width="{{avatar_size}}" alt="">
							<# } #>

							<div class="sppb-testimonial-message">{{{ itemSlide.message }}}</div>

							<div class="sppb-addon-testimonial-pro-footer">
							
							<# if( !_.isEmpty(itemSlide.title) ) { #>
							<strong>{{ itemSlide.title }}</strong>
							<# if( !_.isEmpty(itemSlide.url) ) { #>
								&nbsp;<span class="sppb-addon-testimonial-pro-client-url">{{ itemSlide.url }}</span>
							<# } #>
							<# } #>
							</div>
					  </div>

					<# }) #>
				</div>

				<# if(data.arrows) { #>
					<a href="#sppb-testimonial-pro-{{ data.id }}" class="left sppb-carousel-control" data-slide="prev"><i class="fa fa-angle-left"></i></a>
					<a href="#sppb-testimonial-pro-{{ data.id }}" class="right sppb-carousel-control" data-slide="next"><i class="fa fa-angle-right"></i></a>
				<# } #>

			</div>
			';

		return $output;
	}
}
