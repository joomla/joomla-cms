<?php
/**
 * @package	HikaShop for Joomla!
 * @version	2.6.0
 * @author	hikashop.com
 * @copyright	(C) 2010-2015 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php

class Env_Country extends Env_WebService {

  protected $codesRel = array("NL" => "A", "PT" => "P", "DE" => "D", "IT" => "I", "ES" => "E", 
                              "VI" => "V", "GR" => "G");

  public $countries = array();

  public $country = array();

  public function getCountries() { 
    $this->setOptions(array("action" => "/api/v1/countries",
		)); 
    $this->doCtrRequest();
  }

  private function doCtrRequest() {
    $source = parent::doRequest();




    if($source !== false) {
      parent::parseResponse($source);
	  	if(count($this->respErrorsList) == 0) {


				$countries = $this->xpath->query("/countries/country");
				foreach($countries as $c => $country) {
					$code = $this->xpath->query("./code",$country)->item(0)->nodeValue;
					$this->countries[$code] = array(
						'label' => $this->xpath->query('./label',$country)->item(0)->nodeValue,
						'code' => $code
						);
				}
			}
    }
  }

  public function getCountry($code) {
    $this->country = array(0 => $this->countries[$code]);
    $isoRel = $this->codesRel[$code];
    if($isoRel != "") {
      $i = 1;
      foreach($this->countries as $c => $country) {
        if(preg_match("/$isoRel\d/", $c)) {
          $this->country[$i] = $country;
          $i++;
        }
      }
    }
  }


}
