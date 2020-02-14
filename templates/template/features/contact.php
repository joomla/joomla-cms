<?php
/**
 * @package Helix Ultimate Framework
 * @author JoomShaper https://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2018 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or Later
*/

defined ('_JEXEC') or die();

class HelixUltimateFeatureContact
{
	private $params;

	public function __construct($params)
	{
		$this->params = $params;
		$this->position = $this->params->get('contact_position', 'top1');
	}

	public function renderFeature()
	{
		$conditions = $this->params->get('contactinfo') && ($this->params->get('contact_phone') || $this->params->get('contact_mobile') || $this->params->get('contact_email') || $this->params->get('contact_time'));

		if($conditions)
		{
			$output = '<ul class="sp-contact-info">';

			if($this->params->get('contact_phone'))
			{
				$output .= '<li class="sp-contact-phone"><span class="fa fa-phone" aria-hidden="true"></span> <a href="tel:' . str_replace(array(')','(',' ','-'),array('','','',''), $this->params->get('contact_phone')) . '">' . $this->params->get('contact_phone') . '</a></li>';
			}

			if($this->params->get('contact_mobile'))
			{
				$output .= '<li class="sp-contact-mobile"><span class="fa fa-mobile" aria-hidden="true"></span> <a href="tel:' . str_replace(array(')','(',' ','-'),array('','','',''), $this->params->get('contact_mobile')) . '">' . $this->params->get('contact_mobile') . '</a></li>';
			}

			if($this->params->get('contact_email'))
			{
				$output .= '<li class="sp-contact-email"><span class="fa fa-envelope" aria-hidden="true"></span> <a href="mailto:'. $this->params->get('contact_email') .'">' . $this->params->get('contact_email') . '</a></li>';
			}

			if($this->params->get('contact_time'))
			{
				$output .= '<li class="sp-contact-time"><span class="fa fa-clock-o" aria-hidden="true"></span> ' . $this->params->get('contact_time') . '</li>';
			}

			$output .= '</ul>';

			return $output;
		}

	}
}
