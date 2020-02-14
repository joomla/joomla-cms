<?php
/**
 * @package Helix Ultimate Framework
 * @author JoomShaper https://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2018 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or Later
*/

defined ('_JEXEC') or die();

class HelixUltimateFeatureSocial
{

	private $params;

	public function __construct( $params )
	{
		$this->params = $params;
		$this->position = $this->params->get('social_position');
		$this->load_pos = $this->params->get('social_position');
	}

	public function renderFeature()
	{

		$facebook = $this->params->get('facebook');
		$twitter = $this->params->get('twitter');
		$pinterest = $this->params->get('pinterest');
		$youtube = $this->params->get('youtube');
		$linkedin = $this->params->get('linkedin');
		$dribbble = $this->params->get('dribbble');
		$instagram = $this->params->get('instagram');
		$behance = $this->params->get('behance');
		$skype = $this->params->get('skype');
		$whatsapp = $this->params->get('whatsapp');
		$flickr = $this->params->get('flickr');
		$vk = $this->params->get('vk');
		$custom = $this->params->get('custom');

		if( $this->params->get('show_social_icons') && ( $facebook || $twitter || $pinterest || $youtube || $linkedin || $dribbble || $instagram || $behance || $skype || $flickr || $vk || $custom ) )
		{
			$html  = '<ul class="social-icons">';

			if( $facebook )
			{
				$html .= '<li class="social-icon-facebook"><a target="_blank" href="'. $facebook .'" aria-label="facebook"><span class="fa fa-facebook" aria-hidden="true"></span></a></li>';
			}

			if( $twitter )
			{
				$html .= '<li class="social-icon-twitter"><a target="_blank" href="'. $twitter .'" aria-label="twitter"><span class="fa fa-twitter" aria-hidden="true"></span></a></li>';
			}

			if( $pinterest )
			{
				$html .= '<li class="social-icon-pinterest"><a target="_blank" href="'. $pinterest .'" aria-label="Pinterest"><span class="fa fa-pinterest" aria-hidden="true"></span></a></li>';
			}

			if( $youtube )
			{
				$html .= '<li><a target="_blank" href="'. $youtube .'" aria-label="Youtube"><span class="fa fa-youtube" aria-hidden="true"></span></a></li>';
			}

			if( $linkedin )
			{
				$html .= '<li class="social-icon-linkedin"><a target="_blank" href="'. $linkedin .'" aria-label="LinkedIn"><span class="fa fa-linkedin" aria-hidden="true"></span></a></li>';
			}

			if( $dribbble )
			{
				$html .= '<li class="social-icon-dribbble"><a target="_blank" href="'. $dribbble .'" aria-label="Dribbble"><span class="fa fa-dribbble" aria-hidden="true"></span></a></li>';
			}

			if( $instagram )
			{
				$html .= '<li class="social-icon-instagram"><a target="_blank" href="'. $instagram .'" aria-label="Instagram"><span class="fa fa-instagram" aria-hidden="true"></span></a></li>';
			}

			if( $behance )
			{
				$html .= '<li class="social-icon-behance"><a target="_blank" href="'. $behance .'" aria-label="Behance"><span class="fa fa-behance" aria-hidden="true"></span></a></li>';
			}

			if( $flickr )
			{
				$html .= '<li class="social-icon-flickr"><a target="_blank" href="'. $flickr .'" aria-label="Flickr"><span class="fa fa-flickr" aria-hidden="true"></span></a></li>';
			}

			if( $vk )
			{
				$html .= '<li class="social-icon-vk"><a target="_blank" href="'. $vk .'" aria-label="VK"><span class="fa fa-vk" aria-hidden="true"></span></a></li>';
			}

			if( $skype )
			{
				$html .= '<li class="social-icon-skype"><a href="skype:'. $skype .'?chat" aria-label="Skype"><span class="fa fa-skype" aria-hidden="true"></span></a></li>';
			}

			if( $whatsapp )
			{
				$html .= '<li class="social-icon-whatsapp"><a href="whatsapp://send?abid='. $whatsapp .'&text=Hi" aria-label="WhatsApp"><span class="fa fa-whatsapp" aria-hidden="true"></span></a></li>';
			}

			if( $custom ) {
				$explt_custom = explode(' ', $custom);
				$html .= '<li class="social-icon-custom"><a target="_blank" href="'. $explt_custom[1] .'"><span class="fa '. $explt_custom[0] .'" aria-hidden="true"></span></a></li>';
			}

			$html .= '</ul>';

			return $html;
		}

	}
}
