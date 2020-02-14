<?php
/**
 * @package Helix3 Framework
 * @author JoomShaper http://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2018 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or Later
*/
//no direct accees
defined ('_JEXEC') or die('resticted aceess');

class Helix3FeatureContact {

	private $helix3;

	public function __construct($helix3){
		$this->helix3 = $helix3;
		$this->position = $this->helix3->getParam('contact_position');
	}

	public function renderFeature() {

		if($this->helix3->getParam('enable_contactinfo')) {

			$output = '<ul class="sp-contact-info">';
			if($this->helix3->getParam('contact_phone')) $output .= '<li class="sp-contact-phone"><i class="fa fa-phone" aria-hidden="true"></i> <a href="tel:' . str_replace(' ', '', $this->helix3->getParam('contact_phone')) . '">' . $this->helix3->getParam('contact_phone') . '</a></li>';
			if($this->helix3->getParam('contact_mobile')) $output .= '<li class="sp-contact-mobile"><i class="fa fa-mobile" aria-hidden="true"></i> <a href="tel:'. str_replace(' ', '', $this->helix3->getParam('contact_mobile')) .'">' . $this->helix3->getParam('contact_mobile') . '</a></li>';
			if($this->helix3->getParam('contact_email')) $output .= '<li class="sp-contact-email"><i class="fa fa-envelope" aria-hidden="true"></i> <a href="mailto:'. $this->helix3->getParam('contact_email') .'">' . $this->helix3->getParam('contact_email') . '</a></li>';
			if($this->helix3->getParam('contact_time')) $output .= '<li class="sp-contact-time"><i class="fa fa-clock-o" aria-hidden="true"></i>' . $this->helix3->getParam('contact_time') . '</li>';
			$output .= '</ul>';

			return $output;
		}
		
	}    
}