<?php
/**
 * @package SP Page Builder
 * @author JoomShaper http://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2019 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/
//no direct accees
defined ('_JEXEC') or die ('Restricted access');

class SppagebuilderAddonTestimonial extends SppagebuilderAddons {

	public function render() {
		$settings = $this->addon->settings;
		$class = (isset($settings->class) && $settings->class) ? $settings->class : '';
		$text_align = (isset($settings->alignment) && $settings->alignment) ? $settings->alignment : 'sppb-text-center';
		$style = (isset($settings->style) && $settings->style) ? $settings->style : '';
		$title = (isset($settings->title) && $settings->title) ? $settings->title : '';
		$heading_selector = (isset($settings->heading_selector) && $settings->heading_selector) ? $settings->heading_selector : 'h3';
		$show_quote = (isset($settings->show_quote)) ? $settings->show_quote : true;
		$designation_position = (isset($settings->designation_position)) ? $settings->designation_position : 'bottom';

		//Options
		$review = (isset($settings->review) && $settings->review) ? $settings->review : '';
		$name = (isset($settings->name) && $settings->name) ? $settings->name : '';
		$company = (isset($settings->company) && $settings->company) ? $settings->company : '';
		$avatar = (isset($settings->avatar) && $settings->avatar) ? $settings->avatar : '';
		$avatar_shape = (isset($settings->avatar_shape) && $settings->avatar_shape) ? $settings->avatar_shape : 'sppb-avatar-circle';
		$link = (isset($settings->link) && $settings->link) ? $settings->link : '';
		$link_target = (isset($settings->link_target) && $settings->link_target) ? ' rel="noopener noreferrer" target="' . $settings->link_target . '"' : '';

		//Rating
		$client_rating_enable = (isset($settings->client_rating_enable)) ? $settings->client_rating_enable : '';
		$client_rating = (isset($settings->client_rating)) ? $settings->client_rating : '';

		//Output
		$output  = '';
		$output  .= '<div class="sppb-addon sppb-addon-testimonial ' . $class . ' '. $text_align .'">';
		$output .= ($title) ? '<'.$heading_selector.' class="sppb-addon-title">' . $title . '</'.$heading_selector.'>' : '';
		$output .= '<div class="sppb-addon-content">';
		if($show_quote && $designation_position !== 'top'){
			$output .= '<span class="fa fa-quote-left" aria-hidden="true"></span>';
		}
		if($designation_position == 'top'){
			$output .= '<div class="sppb-testimonial-top-content sppb-addon-testimonial-footer">';
			$output .= $link ? '<a' . $link_target . ' href="'.$link.'">' : '';
			$output .= '<div class="sppb-addon-testimonial-content-wrap">';
			$output .= $avatar ? '<img src="'.$avatar.'" class="'. $avatar_shape .' sppb-addon-testimonial-avatar" alt="'.$name.'">' : '';
			$output .= '<span>';
			$output .= '<span class="sppb-addon-testimonial-client">'.$name.'</span>';
			$output .= '<br /><span class="sppb-addon-testimonial-client-url">'.$company.'</span>';
			$output .= '</span>';
			$output .= '</div>';
			$output .= $link ? '</a>' : '';
			if($show_quote){
				$output .= '<span class="fa fa-quote-right" aria-hidden="true"></span>';
			}
			$output .= '</div>';
			if($client_rating_enable){
				$output .= '<div class="sppb-addon-testimonial-rating">';
				if($client_rating == 1){
					$output .= '<i class="fa fa-star" aria-hidden="true"></i>';
					$output .= '<i class="fa fa-star-o" aria-hidden="true"></i>';
					$output .= '<i class="fa fa-star-o" aria-hidden="true"></i>';
					$output .= '<i class="fa fa-star-o" aria-hidden="true"></i>';
					$output .= '<i class="fa fa-star-o" aria-hidden="true"></i>';
				} elseif($client_rating == 2){
					$output .= '<i class="fa fa-star" aria-hidden="true"></i>';
					$output .= '<i class="fa fa-star" aria-hidden="true"></i>';
					$output .= '<i class="fa fa-star-o" aria-hidden="true"></i>';
					$output .= '<i class="fa fa-star-o" aria-hidden="true"></i>';
					$output .= '<i class="fa fa-star-o" aria-hidden="true"></i>';
				} elseif($client_rating == 3){
					$output .= '<i class="fa fa-star" aria-hidden="true"></i>';
					$output .= '<i class="fa fa-star" aria-hidden="true"></i>';
					$output .= '<i class="fa fa-star" aria-hidden="true"></i>';
					$output .= '<i class="fa fa-star-o" aria-hidden="true"></i>';
					$output .= '<i class="fa fa-star-o" aria-hidden="true"></i>';
				} elseif($client_rating == 4){
					$output .= '<i class="fa fa-star" aria-hidden="true"></i>';
					$output .= '<i class="fa fa-star" aria-hidden="true"></i>';
					$output .= '<i class="fa fa-star" aria-hidden="true"></i>';
					$output .= '<i class="fa fa-star" aria-hidden="true"></i>';
					$output .= '<i class="fa fa-star-o" aria-hidden="true"></i>';
				} elseif($client_rating == 5){
					$output .= '<i class="fa fa-star" aria-hidden="true"></i>';
					$output .= '<i class="fa fa-star" aria-hidden="true"></i>';
					$output .= '<i class="fa fa-star" aria-hidden="true"></i>';
					$output .= '<i class="fa fa-star" aria-hidden="true"></i>';
					$output .= '<i class="fa fa-star" aria-hidden="true"></i>';
				}
				$output .= '</div>';
			}
		}
		$output .= '<div class="sppb-addon-testimonial-review">';
		$output .= $review;
		$output .= '</div>';
		if($designation_position !== 'top'){
			$output .= '<div class="sppb-addon-testimonial-footer">';
			$output .= $link ? '<a' . $link_target . ' href="'.$link.'">' : '';
			$output .= '<div class="sppb-addon-testimonial-content-wrap">';
			$output .= $avatar ? '<img src="'.$avatar.'" class="'. $avatar_shape .' sppb-addon-testimonial-avatar" alt="'.$name.'">' : '';
			$output .= '<span>';
			$output .= '<span class="sppb-addon-testimonial-client">'.$name.'</span>';
			$output .= '<br /><span class="sppb-addon-testimonial-client-url">'.$company.'</span>';
			$output .= '</span>';
			$output .= '</div>';
			$output .= $link ? '</a>' : '';
			$output .= '</div>';
			if($client_rating_enable){
				$output .= '<div class="sppb-addon-testimonial-rating">';
				if($client_rating == 1){
					$output .= '<i class="fa fa-star" aria-hidden="true"></i>';
					$output .= '<i class="fa fa-star-o" aria-hidden="true"></i>';
					$output .= '<i class="fa fa-star-o" aria-hidden="true"></i>';
					$output .= '<i class="fa fa-star-o" aria-hidden="true"></i>';
					$output .= '<i class="fa fa-star-o" aria-hidden="true"></i>';
				} elseif($client_rating == 2){
					$output .= '<i class="fa fa-star" aria-hidden="true"></i>';
					$output .= '<i class="fa fa-star" aria-hidden="true"></i>';
					$output .= '<i class="fa fa-star-o" aria-hidden="true"></i>';
					$output .= '<i class="fa fa-star-o" aria-hidden="true"></i>';
					$output .= '<i class="fa fa-star-o" aria-hidden="true"></i>';
				} elseif($client_rating == 3){
					$output .= '<i class="fa fa-star" aria-hidden="true"></i>';
					$output .= '<i class="fa fa-star" aria-hidden="true"></i>';
					$output .= '<i class="fa fa-star" aria-hidden="true"></i>';
					$output .= '<i class="fa fa-star-o" aria-hidden="true"></i>';
					$output .= '<i class="fa fa-star-o" aria-hidden="true"></i>';
				} elseif($client_rating == 4){
					$output .= '<i class="fa fa-star" aria-hidden="true"></i>';
					$output .= '<i class="fa fa-star" aria-hidden="true"></i>';
					$output .= '<i class="fa fa-star" aria-hidden="true"></i>';
					$output .= '<i class="fa fa-star" aria-hidden="true"></i>';
					$output .= '<i class="fa fa-star-o" aria-hidden="true"></i>';
				} elseif($client_rating == 5){
					$output .= '<i class="fa fa-star" aria-hidden="true"></i>';
					$output .= '<i class="fa fa-star" aria-hidden="true"></i>';
					$output .= '<i class="fa fa-star" aria-hidden="true"></i>';
					$output .= '<i class="fa fa-star" aria-hidden="true"></i>';
					$output .= '<i class="fa fa-star" aria-hidden="true"></i>';
				}
				$output .= '</div>';
			}
			
		}

		$output .= '</div>';
		$output .= '</div>';

		return $output;

	}

	public function css() {
		$settings = $this->addon->settings;
		$css = '';

		$style = '';
		
		$style .= (isset($settings->review_color) && $settings->review_color) ? "color: " . $settings->review_color . ";" : "";

		$style .= (isset($settings->review_size) && $settings->review_size) ? "font-size: " . $settings->review_size . "px;" : "";
		$style .= (isset($settings->review_line_height) && $settings->review_line_height) ? "line-height: " . $settings->review_line_height . "px;" : "";
		$style .= (isset($settings->review_fontweight) && $settings->review_fontweight) ? "font-weight: " . $settings->review_fontweight . ";" : "";
		$style .= (isset($settings->review_margin) && trim($settings->review_margin)) ? "margin: " . $settings->review_margin . ";" : "";
		
		if($style){
			$css .= '#sppb-addon-' . $this->addon->id . ' .sppb-addon-testimonial-review{';
				$css .= $style;
			$css .= '}';
		}
		//Name style
        $name_style = '';
        $name_style .= (isset($settings->name_color) && $settings->name_color) ? 'color:'.$settings->name_color . ';' : '';
        $name_style .= (isset($settings->name_font_size) && $settings->name_font_size) ? 'font-size:'.$settings->name_font_size . 'px;' : '';
        $name_style .= (isset($settings->name_line_height) && $settings->name_line_height) ? 'line-height:'.$settings->name_line_height . 'px;' : '';
        $name_style .= (isset($settings->name_margin) && trim($settings->name_margin)) ? 'margin:'.$settings->name_margin . ';' : '';
        $name_font_style = (isset($settings->name_font_style) && $settings->name_font_style) ? $settings->name_font_style : '';
        if(isset($name_font_style->underline) && $name_font_style->underline){
			$name_style .= 'text-decoration:underline;';
		}
		if(isset($name_font_style->italic) && $name_font_style->italic){
			$name_style .= 'font-style:italic;';
		}
		if(isset($name_font_style->uppercase) && $name_font_style->uppercase){
			$name_style .= 'text-transform:uppercase;';
		}
		if(!isset($name_font_style->weight)){
			$name_style .= 'font-weight:700;';
		}
		if(isset($name_font_style->weight) && $name_font_style->weight){
			$name_style .= 'font-weight:'.$name_font_style->weight.';';
		}
        if($name_style){
            $css .= '#sppb-addon-' . $this->addon->id . ' .sppb-addon-testimonial-footer .sppb-addon-testimonial-client {';
                $css .= $name_style;
            $css .= '}';
		}
		
		//Company style
        $company_style = '';
        $company_style .= (isset($settings->company_color) && $settings->company_color) ? 'color:'.$settings->company_color . ';' : '';
        $company_style .= (isset($settings->company_font_size) && $settings->company_font_size) ? 'font-size:'.$settings->company_font_size . 'px;' : '';
        $company_style .= (isset($settings->company_line_height) && $settings->company_line_height) ? 'line-height:'.$settings->company_line_height . 'px;' : '';
        $company_font_style = (isset($settings->company_font_style) && $settings->company_font_style) ? $settings->company_font_style : '';
        if(isset($company_font_style->underline) && $company_font_style->underline){
			$company_style .= 'text-decoration:underline;';
		}
		if(isset($company_font_style->italic) && $company_font_style->italic){
			$company_style .= 'font-style:italic;';
		}
		if(isset($company_font_style->uppercase) && $company_font_style->uppercase){
			$company_style .= 'text-transform:uppercase;';
		}
		if(isset($company_font_style->weight) && $company_font_style->weight){
			$company_style .= 'font-weight:'.$company_font_style->weight.';';
		}
        if($company_style){
            $css .= '#sppb-addon-' . $this->addon->id . ' .sppb-addon-testimonial-footer .sppb-addon-testimonial-client-url {';
                $css .= $company_style;
            $css .= '}';
		}
		
		//Avatar style
		$avatar_size = (isset($settings->avatar_width) && $settings->avatar_width) ? $settings->avatar_width : '';
		$avatar_margin = (isset($settings->avatar_margin) && trim($settings->avatar_margin)) ? $settings->avatar_margin : '';
		
		if($avatar_size || $avatar_margin){
			$css .= '#sppb-addon-' . $this->addon->id . ' .sppb-addon-testimonial-content-wrap img {';
				$css .= 'height:'.$avatar_size.'px;';
				$css .= 'width:'.$avatar_size.'px;';
				$css .= 'margin:'.$avatar_margin.';';
			$css .= '}';
		}

		$avatar_dis_block = (isset($settings->avatar_dis_block) && $settings->avatar_dis_block) ? $settings->avatar_dis_block : '';
		if($avatar_dis_block){
			$css .= '#sppb-addon-' . $this->addon->id . ' .sppb-addon-testimonial-content-wrap > span,';
			$css .= '#sppb-addon-' . $this->addon->id . ' .sppb-addon-testimonial-content-wrap {';
				$css .= 'display:block;';
			$css .= '}';
		}
		$icon_style = '';
		$icon_style_sm = '';
		$icon_style_xs = '';

		$icon_style .= (isset($settings->icon_color) && $settings->icon_color) ? "color: " . $settings->icon_color . ";" : "";
		
		$icon_style .= (isset($settings->icon_size) && $settings->icon_size) ? "font-size: " . $settings->icon_size . "px;" : "";
		$icon_style_sm .= (isset($settings->icon_size_sm) && $settings->icon_size_sm) ? "font-size: " . $settings->icon_size_sm . "px;" : "";
		$icon_style_xs .= (isset($settings->icon_size_xs) && $settings->icon_size_xs) ? "font-size: " . $settings->icon_size_xs . "px;" : "";

		if($icon_style){
			$css .= '#sppb-addon-' . $this->addon->id . ' .sppb-addon-testimonial .fa-quote-left{ ' . $icon_style . ' }';
			$css .= '#sppb-addon-' . $this->addon->id . ' .sppb-addon-testimonial .fa-quote-right{ ' . $icon_style . ' }';
		}

		//Rating style
		$client_rating_color = (isset($settings->client_rating_color) && $settings->client_rating_color) ? 'color:'.$settings->client_rating_color.';' : '';
		$client_unrated_color = (isset($settings->client_unrated_color) && $settings->client_unrated_color) ? 'color:'.$settings->client_unrated_color.';' : '';
		$rating_style = '';
		$rating_style .= (isset($settings->client_rating_fontsize) && $settings->client_rating_fontsize) ? 'font-size:'.$settings->client_rating_fontsize.'px;' : '';
		$rating_style .= (isset($settings->client_rating_margin) && trim($settings->client_rating_margin)) ? 'margin:'.$settings->client_rating_margin.';' : '';
		if($rating_style){
			$css .= '#sppb-addon-' . $this->addon->id . ' .sppb-addon-testimonial-rating i{ ' . $rating_style . ' }';
		}
		if($client_rating_color){
			$css .= '#sppb-addon-' . $this->addon->id . ' .sppb-addon-testimonial-rating i.fa-star{ ' . $client_rating_color . ' }';
		}
		if($client_unrated_color){
			$css .= '#sppb-addon-' . $this->addon->id . ' .sppb-addon-testimonial-rating i.fa-star-o{ ' . $client_unrated_color . ' }';
		}

		//Content tablet Style
		$style_sm = '';
		$style_sm .= (isset($settings->review_size_sm) && $settings->review_size_sm) ? "font-size: " . $settings->review_size_sm . "px;" : "";
		$style_sm .= (isset($settings->review_line_height_sm) && $settings->review_line_height_sm) ? "line-height: " . $settings->review_line_height_sm . "px;" : "";
		$style_sm .= (isset($settings->review_margin_sm) && trim($settings->review_margin_sm)) ? "margin: " . $settings->review_margin_sm . ";" : "";
		//Name tablet Style
		$name_style_sm = '';
		$name_style_sm .= (isset($settings->name_font_size_sm) && $settings->name_font_size_sm) ? 'font-size:'.$settings->name_font_size_sm . 'px;' : '';
        $name_style_sm .= (isset($settings->name_line_height_sm) && $settings->name_line_height_sm) ? 'line-height:'.$settings->name_line_height_sm . 'px;' : '';
		$name_style_sm .= (isset($settings->name_margin_sm) && trim($settings->name_margin_sm)) ? 'margin:'.$settings->name_margin_sm . ';' : '';
		//Company tablet style
		$company_style_sm = '';
		$company_style_sm .= (isset($settings->company_font_size_sm) && $settings->company_font_size_sm) ? 'font-size:'.$settings->company_font_size_sm . 'px;' : '';
		$company_style_sm .= (isset($settings->company_line_height_sm) && $settings->company_line_height_sm) ? 'line-height:'.$settings->company_line_height_sm . 'px;' : '';
		//Avatar tablet style
		$avatar_margin_sm = (isset($settings->avatar_margin_sm) && trim($settings->avatar_margin_sm)) ? $settings->avatar_margin_sm : '';
		//Rating tablet style
		$rating_style_sm = '';
		$rating_style_sm .= (isset($settings->client_rating_fontsize_sm) && $settings->client_rating_fontsize_sm) ? 'font-size:'.$settings->client_rating_fontsize_sm.'px;' : '';
		$rating_style_sm .= (isset($settings->client_rating_margin_sm) && trim($settings->client_rating_margin_sm)) ? 'margin:'.$settings->client_rating_margin_sm.';' : '';
		
		if($icon_style_sm || $name_style_sm || $company_style_sm || $style_sm){
			$css .= '@media (min-width: 768px) and (max-width: 991px) {';
				if($icon_style_sm){
					$css .= '#sppb-addon-' . $this->addon->id . ' .sppb-addon-testimonial .fa-quote-left,';
					$css .= '#sppb-addon-' . $this->addon->id . ' .sppb-addon-testimonial .fa-quote-right{';
						$css .= $icon_style_sm;
					$css .= '}';
				}
				if($name_style_sm){
					$css .= '#sppb-addon-' . $this->addon->id . ' .sppb-addon-testimonial-footer .sppb-addon-testimonial-client {';
						$css .= $name_style_sm;
					$css .= '}';
				}
				if($company_style_sm){
					$css .= '#sppb-addon-' . $this->addon->id . ' .sppb-addon-testimonial-footer .sppb-addon-testimonial-client-url {';
						$css .= $company_style_sm;
					$css .= '}';
				}
				if($style_sm){
					$css .= '#sppb-addon-' . $this->addon->id . ' .sppb-addon-testimonial-review{'. $style_sm .'}';
				}
				if($avatar_margin_sm){
					$css .= '#sppb-addon-' . $this->addon->id . ' .sppb-addon-testimonial-content-wrap img {';
						$css .= 'margin:'.$avatar_margin_sm.';';
					$css .= '}';
				}
				if($rating_style_sm){
					$css .= '#sppb-addon-' . $this->addon->id . ' .sppb-addon-testimonial-rating i{ ' . $rating_style_sm . ' }';
				}
			$css .= '}';
		}

		//Content mobile Style
		$style_xs = '';
		$style_xs .= (isset($settings->review_size_xs) && $settings->review_size_xs) ? "font-size: " . $settings->review_size_xs . "px;" : "";
		$style_xs .= (isset($settings->review_line_height_xs) && $settings->review_line_height_xs) ? "line-height: " . $settings->review_line_height_xs . "px;" : "";
		$style_xs .= (isset($settings->review_margin_xs) && trim($settings->review_margin_xs)) ? "margin: " . $settings->review_margin_xs . ";" : "";
		
		//Name mobile Style
		$name_style_xs = '';
		$name_style_xs .= (isset($settings->name_font_size_xs) && $settings->name_font_size_xs) ? 'font-size:'.$settings->name_font_size_xs . 'px;' : '';
        $name_style_xs .= (isset($settings->name_line_height_xs) && $settings->name_line_height_xs) ? 'line-height:'.$settings->name_line_height_xs . 'px;' : '';
		$name_style_xs .= (isset($settings->name_margin) && trim($settings->name_margin)) ? 'margin:'.$settings->name_margin . ';' : '';
		//Company mobile style
		$company_style_xs = '';
		$company_style_xs .= (isset($settings->company_font_size_xs) && $settings->company_font_size_xs) ? 'font-size:'.$settings->company_font_size_xs . 'px;' : '';
		$company_style_xs .= (isset($settings->company_line_height_xs) && $settings->company_line_height_xs) ? 'line-height:'.$settings->company_line_height_xs . 'px;' : '';
		//Avatar mobile style
		$avatar_margin_xs = (isset($settings->avatar_margin_xs) && trim($settings->avatar_margin_xs)) ? $settings->avatar_margin_xs : '';
		//Rating tablet style
		$rating_style_xs = '';
		$rating_style_xs .= (isset($settings->client_rating_fontsize_xs) && $settings->client_rating_fontsize_xs) ? 'font-size:'.$settings->client_rating_fontsize_xs.'px;' : '';
		$rating_style_xs .= (isset($settings->client_rating_margin_xs) && trim($settings->client_rating_margin_xs)) ? 'margin:'.$settings->client_rating_margin_xs.';' : '';
		
		if($icon_style_xs || $name_style_xs || $company_style_xs || $style_xs || $avatar_margin_xs){
			$css .= '@media (max-width: 767px) {';
				if($icon_style_xs){
					$css .= '#sppb-addon-' . $this->addon->id . ' .sppb-addon-testimonial .fa-quote-left,';
					$css .= '#sppb-addon-' . $this->addon->id . ' .sppb-addon-testimonial .fa-quote-right{';
						$css .= $icon_style_xs;
					$css .= '}';
				}
				if($name_style_xs){
					$css .= '#sppb-addon-' . $this->addon->id . ' .sppb-addon-testimonial-footer .sppb-addon-testimonial-client {';
						$css .= $name_style_xs;
					$css .= '}';
				}
				if($company_style_xs){
					$css .= '#sppb-addon-' . $this->addon->id . ' .sppb-addon-testimonial-footer .sppb-addon-testimonial-client-url {';
						$css .= $company_style_xs;
					$css .= '}';
				}
				if($style_xs){
					$css .= '#sppb-addon-' . $this->addon->id . ' .sppb-addon-testimonial-review{ ' . $style_xs . '}';
				}
				if($avatar_margin_xs){
					$css .= '#sppb-addon-' . $this->addon->id . ' .sppb-addon-testimonial-content-wrap img {';
						$css .= 'margin:'.$avatar_margin_xs.';';
					$css .= '}';
				}
				if($rating_style_xs){
					$css .= '#sppb-addon-' . $this->addon->id . ' .sppb-addon-testimonial-rating i{ ' . $rating_style_xs . ' }';
				}
			$css .= '}';
		}

		return $css;
	}

	public static function getTemplate()
	{

		$output = '
			<#
				let testimonialAlignment = data.alignment || "sppb-text-center"
				let avatar_position = data.avatar_position || "left"
				let avatar = data.avatar || ""
				let avatar_shape = data.avatar_shape || "sppb-avatar-circle"
				let reviewer_link = data.link || ""
				let link_target = (data.link_target)? "target=\'"+ data.link_target +"\'": ""
				if(!data.designation_position){
					data.designation_position = "bottom";
				}
			#>

			<style type="text/css">
				<# if(data.show_quote){ #>
					#sppb-addon-{{ data.id }} .sppb-addon-testimonial .fa-quote-left,
					#sppb-addon-{{ data.id }} .sppb-addon-testimonial .fa-quote-right{
						<# if(_.isObject(data.icon_size)){ #>
							font-size: {{ data.icon_size.md }}px;
						<# } #>
						color: {{ data.icon_color }};
					}
				<# }
				if(data.avatar_width || data.avatar_margin){ #>
					#sppb-addon-{{ data.id }} .sppb-addon-testimonial-content-wrap img {
						height:{{data.avatar_width}}px;
						width:{{data.avatar_width}}px;
						<# if(_.isObject(data.avatar_margin)){ #>
							margin: {{data.avatar_margin.md}};
						<# } #>
					}
				<# }
				if(data.avatar_dis_block){ #>
					#sppb-addon-{{ data.id }} .sppb-addon-testimonial-content-wrap > span,
					#sppb-addon-{{ data.id }} .sppb-addon-testimonial-content-wrap {
						display:block;
					}
				<# } #>
				#sppb-addon-{{ data.id }} .sppb-addon-testimonial-review{
					<# if(_.isObject(data.review_size)){ #>
						font-size: {{ data.review_size.md }}px;
					<# }
					if(_.isObject(data.review_line_height)){ #>
						line-height: {{ data.review_line_height.md }}px;
					<# }
					if(_.isObject(data.review_margin)){ #>
						margin: {{ data.review_margin.md }};
					<# } #>
					color: {{ data.review_color }};
					font-weight: {{ data.review_fontweight }};
				}
                #sppb-addon-{{ data.id }} .sppb-addon-testimonial-footer .sppb-addon-testimonial-client {
                    <# if(data.name_color){ #>
                        color: {{data.name_color}};
                    <# }
                    if(_.isObject(data.name_font_size)){ #>
                        font-size: {{data.name_font_size.md}}px;
                    <# }
                    if(_.isObject(data.name_line_height)){ #>
                        line-height: {{data.name_line_height.md}}px;
                    <# }
                    if(_.isObject(data.name_margin)){ #>
                        margin: {{data.name_margin.md}};
                    <# }
                    if(_.isEmpty(data.name_font_style) && !data.name_font_style){
					#>
                        font-weight:700;
                    <# }
                    if(_.isObject(data.name_font_style)){ #>
                        <# if(data.name_font_style.underline){ #>
                            text-decoration:underline;
                        <# }
                        if(data.name_font_style.italic){
                        #>
                            font-style:italic;
                        <# }
                        if(data.name_font_style.uppercase){
                        #>
                            text-transform:uppercase;
                        <# }
                        if(data.name_font_style.weight){
                        #>
                            font-weight:{{data.name_font_style.weight}};
                        <# } #>
                    <# } #>
                }
                #sppb-addon-{{ data.id }} .sppb-addon-testimonial-footer .sppb-addon-testimonial-client-url {
                    <# if(data.company_color){ #>
                        color: {{data.company_color}};
                    <# }
                    if(_.isObject(data.company_font_size)){ #>
                        font-size: {{data.company_font_size.md}}px;
                    <# }
                    if(_.isObject(data.company_line_height)){ #>
                        line-height: {{data.company_line_height.md}}px;
                    <# }
                    if(_.isObject(data.company_font_style)){ #>
                        <# if(data.company_font_style.underline){ #>
                            text-decoration:underline;
                        <# }
                        if(data.company_font_style.italic){
                        #>
                            font-style:italic;
                        <# }
                        if(data.company_font_style.uppercase){
                        #>
                            text-transform:uppercase;
                        <# }
                        if(data.company_font_style.weight){
                        #>
                            font-weight:{{data.company_font_style.weight}};
                        <# } #>
                    <# } #>
				}
				<# if(data.client_rating_enable){ #>
					#sppb-addon-{{ data.id }} .sppb-addon-testimonial-rating i{
						<# if(_.isObject(data.client_rating_fontsize)){ #>
							font-size:{{data.client_rating_fontsize.md}}px;
						<# }
						if(_.isObject(data.client_rating_margin)){ #>
							margin:{{data.client_rating_margin.md}};
						<# } else { #>
							margin:{{data.client_rating_margin}};
						<# } #>
					}
				<# }
				if(data.client_rating_color){ #>
					#sppb-addon-{{ data.id }} .sppb-addon-testimonial-rating i.fa-star{ 
						color: {{data.client_rating_color}};
					}
				<# }
				if(data.client_unrated_color){
				#>
					#sppb-addon-{{ data.id }} .sppb-addon-testimonial-rating i.fa-star-o{
						color: {{data.client_unrated_color}};
					}
				<# } #>
				@media (min-width: 768px) and (max-width: 991px) {
					<# if(data.show_quote){ #>
						#sppb-addon-{{ data.id }} .sppb-addon-testimonial .fa-quote-left,
						#sppb-addon-{{ data.id }} .sppb-addon-testimonial .fa-quote-right{
							<# if(_.isObject(data.icon_size)){ #>
								font-size: {{ data.icon_size.sm }}px;
							<# } #>
						}
					<# } #>
	
					#sppb-addon-{{ data.id }} .sppb-addon-testimonial-review{
						<# if(_.isObject(data.review_size)){ #>
							font-size: {{ data.review_size.sm }}px;
						<# } #>
						<# if(_.isObject(data.review_line_height)){ #>
							line-height: {{ data.review_line_height.sm }}px;
						<# }
						if(_.isObject(data.review_margin)){ #>
							margin: {{ data.review_margin.sm }};
						<# } #>
					}
					<# if(data.name_font_size || data.name_line_height || data.name_margin){ #>
						#sppb-addon-{{ data.id }} .sppb-addon-testimonial-footer .sppb-addon-testimonial-client {
							<# if(_.isObject(data.name_font_size)){ #>
								font-size: {{data.name_font_size.sm}}px;
							<# } #>
							<# if(_.isObject(data.name_line_height)){ #>
								line-height: {{data.name_line_height.sm}}px;
							<# }
							if(_.isObject(data.name_margin)){ #>
								margin: {{data.name_margin.sm}};
							<# } #>
						}
					<# } #>
					<# if(data.company_font_size || data.company_line_height){ #>
						#sppb-addon-{{ data.id }} .sppb-addon-testimonial-footer .sppb-addon-testimonial-client-url {
							<# if(_.isObject(data.company_font_size)){ #>
								font-size: {{data.company_font_size.sm}}px;
							<# }
							if(_.isObject(data.company_line_height)){ #>
								line-height: {{data.company_line_height.sm}}px;
							<# } #>
						}
					<# }
					if(data.avatar_margin){ #>
						#sppb-addon-{{ data.id }} .sppb-addon-testimonial-content-wrap img {
							<# if(_.isObject(data.avatar_margin)){ #>
								margin: {{data.avatar_margin.sm}};
							<# } #>
						}
					<# } #>
					<# if(data.client_rating_enable){ #>
						#sppb-addon-{{ data.id }} .sppb-addon-testimonial-rating i{
							<# if(_.isObject(data.client_rating_fontsize)){ #>
								font-size:{{data.client_rating_fontsize.sm}}px;
							<# }
							if(_.isObject(data.client_rating_margin)){ #>
								margin:{{data.client_rating_margin.sm}};
							<# } #>
						}
					<# } #>
				}
				@media (max-width: 767px) {
					<# if(data.show_quote){ #>
						#sppb-addon-{{ data.id }} .sppb-addon-testimonial .fa-quote-left,
						#sppb-addon-{{ data.id }} .sppb-addon-testimonial .fa-quote-right{
							<# if(_.isObject(data.icon_size)){ #>
								font-size: {{ data.icon_size.xs }}px;
							<# } #>
						}
					<# } #>
	
					#sppb-addon-{{ data.id }} .sppb-addon-testimonial-review{
						<# if(_.isObject(data.review_size)){ #>
							font-size: {{ data.review_size.xs }}px;
						<# } #>
						<# if(_.isObject(data.review_line_height)){ #>
							line-height: {{ data.review_line_height.xs }}px;
						<# }
						if(_.isObject(data.review_margin)){ #>
							margin: {{ data.review_margin.xs }};
						<# } #>
					}
					<# if(data.name_font_size || data.name_line_height || data.name_margin){ #>
						#sppb-addon-{{ data.id }} .sppb-addon-testimonial-footer .sppb-addon-testimonial-client {
							<# if(_.isObject(data.name_font_size)){ #>
								font-size: {{data.name_font_size.xs}}px;
							<# } #>
							<# if(_.isObject(data.name_line_height)){ #>
								line-height: {{data.name_line_height.xs}}px;
							<# }
							if(_.isObject(data.name_margin)){ #>
								margin: {{data.name_margin.xs}};
							<# } #>
						}
					<# } #>
					<# if(data.company_font_size || data.company_line_height){ #>
						#sppb-addon-{{ data.id }} .sppb-addon-testimonial-footer .sppb-addon-testimonial-client-url {
							<# if(_.isObject(data.company_font_size)){ #>
								font-size: {{data.company_font_size.xs}}px;
							<# }
							if(_.isObject(data.company_line_height)){ #>
								line-height: {{data.company_line_height.xs}}px;
							<# } #>
						}
					<# }
					if(data.avatar_margin){ #>
						#sppb-addon-{{ data.id }} .sppb-addon-testimonial-content-wrap img {
							<# if(_.isObject(data.avatar_margin)){ #>
								margin: {{data.avatar_margin.xs}};
							<# } #>
						}
					<# } #>
					<# if(data.client_rating_enable){ #>
						#sppb-addon-{{ data.id }} .sppb-addon-testimonial-rating i{
							<# if(_.isObject(data.client_rating_fontsize)){ #>
								font-size:{{data.client_rating_fontsize.xs}}px;
							<# }
							if(_.isObject(data.client_rating_margin)){ #>
								margin:{{data.client_rating_margin.xs}};
							<# } #>
						}
					<# } #>
				}
			</style>

			<div class="sppb-addon sppb-addon-testimonial {{ data.class }} {{ testimonialAlignment }}">
				<# if( !_.isEmpty( data.title ) ){ #><{{ data.heading_selector }} class="sppb-addon-title sp-inline-editable-element" data-id={{data.id}} data-fieldName="title" contenteditable="true">{{ data.title }}</{{ data.heading_selector }}><# } #>
				<div class="sppb-addon-content">
					<# if(data.show_quote && data.designation_position !== "top"){ #>
						<span class="fa fa-quote-left"></span>
					<# } #>
					<# if(data.designation_position == "top") { #>
						<div class="sppb-testimonial-top-content sppb-addon-testimonial-footer">
							<# if (reviewer_link) { #>
								<a {{ link_target }} href=\'{{{ reviewer_link }}}\'>
							<# } #>
							<div class="sppb-addon-testimonial-content-wrap">
							<# if (avatar && avatar.indexOf("https://") == -1 && avatar.indexOf("http://") == -1) { #>
								<img class="{{ avatar_shape }} sppb-addon-testimonial-avatar" src=\'{{ pagebuilder_base + data.avatar }}\' width="{{ data.avatar_width }}" height="{{ data.avatar_width }}" alt="{{ data.name }}">
							<# } else if(avatar){ #>
								<img class="{{ avatar_shape }} sppb-addon-testimonial-avatar" src=\'{{ data.avatar }}\' width="{{ data.avatar_width }}" height="{{ data.avatar_width }}" alt="{{ data.name }}">
							<# } #>
							<span>
								<span class="sppb-addon-testimonial-client">{{ data.name }}</span>
								<br /><span class="sppb-addon-testimonial-client-url">{{ data.company }}</span>
							</span>
							</div>
							<# if (reviewer_link) { #>
								</a>
							<# } #>
							<# if(data.show_quote){ #>
								<span class="fa fa-quote-right"></span>
							<# } #>
						</div>
						<# if(data.client_rating_enable){ #>
							<div class="sppb-addon-testimonial-rating">
							<# if(data.client_rating == 1){ #>
								<i class="fa fa-star" aria-hidden="true"></i>
								<i class="fa fa-star-o" aria-hidden="true"></i>
								<i class="fa fa-star-o" aria-hidden="true"></i>
								<i class="fa fa-star-o" aria-hidden="true"></i>
								<i class="fa fa-star-o" aria-hidden="true"></i>
							<# } else if(data.client_rating == 2){ #>
								<i class="fa fa-star" aria-hidden="true"></i>
								<i class="fa fa-star" aria-hidden="true"></i>
								<i class="fa fa-star-o" aria-hidden="true"></i>
								<i class="fa fa-star-o" aria-hidden="true"></i>
								<i class="fa fa-star-o" aria-hidden="true"></i>
							<# } else if(data.client_rating == 3){ #>
								<i class="fa fa-star" aria-hidden="true"></i>
								<i class="fa fa-star" aria-hidden="true"></i>
								<i class="fa fa-star" aria-hidden="true"></i>
								<i class="fa fa-star-o" aria-hidden="true"></i>
								<i class="fa fa-star-o" aria-hidden="true"></i>
							<# } else if(data.client_rating == 4){ #>
								<i class="fa fa-star" aria-hidden="true"></i>
								<i class="fa fa-star" aria-hidden="true"></i>
								<i class="fa fa-star" aria-hidden="true"></i>
								<i class="fa fa-star" aria-hidden="true"></i>
								<i class="fa fa-star-o" aria-hidden="true"></i>
							<# } else if(data.client_rating == 5){ #>
								<i class="fa fa-star" aria-hidden="true"></i>
								<i class="fa fa-star" aria-hidden="true"></i>
								<i class="fa fa-star" aria-hidden="true"></i>
								<i class="fa fa-star" aria-hidden="true"></i>
								<i class="fa fa-star" aria-hidden="true"></i>
							<# } #>
							</div>
						<# }
					} #>
					<div id="addon-review-{{data.id}}" class="sppb-addon-testimonial-review sp-editable-content" data-id={{data.id}} data-fieldName="review">
					{{{ data.review }}}
					</div>
					<# if(data.designation_position !== "top") { #>
						<div class="sppb-addon-testimonial-footer">
						<# if (reviewer_link) { #>
							<a {{ link_target }} href=\'{{{ reviewer_link }}}\'>
						<# } #>
						<div class="sppb-addon-testimonial-content-wrap">
						<# if (avatar && avatar.indexOf("https://") == -1 && avatar.indexOf("http://") == -1) { #>
							<img class="{{ avatar_shape }} sppb-addon-testimonial-avatar" src=\'{{ pagebuilder_base + data.avatar }}\' width="{{ data.avatar_width }}" height="{{ data.avatar_width }}" alt="{{ data.name }}">
						<# } else if(avatar){ #>
							<img class="{{ avatar_shape }} sppb-addon-testimonial-avatar" src=\'{{ data.avatar }}\' width="{{ data.avatar_width }}" height="{{ data.avatar_width }}" alt="{{ data.name }}">
						<# } #>
						<span>
							<span class="sppb-addon-testimonial-client">{{ data.name }}</span>
							<br /><span class="sppb-addon-testimonial-client-url">{{ data.company }}</span>
						</span>
						</div>
						<# if (reviewer_link) { #>
							</a>
						<# } #>
						</div>
						<# if(data.client_rating_enable){ #>
							<div class="sppb-addon-testimonial-rating">
							<# if(data.client_rating == 1){ #>
								<i class="fa fa-star" aria-hidden="true"></i>
								<i class="fa fa-star-o" aria-hidden="true"></i>
								<i class="fa fa-star-o" aria-hidden="true"></i>
								<i class="fa fa-star-o" aria-hidden="true"></i>
								<i class="fa fa-star-o" aria-hidden="true"></i>
							<# } else if(data.client_rating == 2){ #>
								<i class="fa fa-star" aria-hidden="true"></i>
								<i class="fa fa-star" aria-hidden="true"></i>
								<i class="fa fa-star-o" aria-hidden="true"></i>
								<i class="fa fa-star-o" aria-hidden="true"></i>
								<i class="fa fa-star-o" aria-hidden="true"></i>
							<# } else if(data.client_rating == 3){ #>
								<i class="fa fa-star" aria-hidden="true"></i>
								<i class="fa fa-star" aria-hidden="true"></i>
								<i class="fa fa-star" aria-hidden="true"></i>
								<i class="fa fa-star-o" aria-hidden="true"></i>
								<i class="fa fa-star-o" aria-hidden="true"></i>
							<# } else if(data.client_rating == 4){ #>
								<i class="fa fa-star" aria-hidden="true"></i>
								<i class="fa fa-star" aria-hidden="true"></i>
								<i class="fa fa-star" aria-hidden="true"></i>
								<i class="fa fa-star" aria-hidden="true"></i>
								<i class="fa fa-star-o" aria-hidden="true"></i>
							<# } else if(data.client_rating == 5){ #>
								<i class="fa fa-star" aria-hidden="true"></i>
								<i class="fa fa-star" aria-hidden="true"></i>
								<i class="fa fa-star" aria-hidden="true"></i>
								<i class="fa fa-star" aria-hidden="true"></i>
								<i class="fa fa-star" aria-hidden="true"></i>
							<# } #>
							</div>
						<# }
					} #>
				</div>
			</div>
			';

		return $output;
	}
}
