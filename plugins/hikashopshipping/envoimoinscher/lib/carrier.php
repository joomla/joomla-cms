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

class Env_Carrier extends Env_WebService {

  public $carriers = array();

  public function getCarriers() { 
    $this->setOptions(array("action" => "/api/v1/carriers",
	));
    $this->doCarrierRequest();
  }

  private function doCarrierRequest() {
    $source = $this->doRequest();




    if($source !== false) {
      parent::parseResponse($source);
	  	if(count($this->respErrorsList) == 0) {


				$carriers = $this->xpath->query("/operators/operator");
				foreach($carriers as $c => $carrier) {
					$result = $this->parseCarrierNode($carrier);

					$code = $this->xpath->query('./code',$carrier)->item(0)->nodeValue;
					$this->carriers[$result['code']] = $result;
				}
			}
    }
  }

	protected function parseCarrierNode($carrier)
	{

		$code = $this->xpath->query('./code',$carrier)->item(0)->nodeValue;
		$result = array(
			'label' => $this->xpath->query('./label',$carrier)->item(0)->nodeValue,
			'code' => $this->xpath->query('./code',$carrier)->item(0)->nodeValue, 
			'logo' => $this->xpath->query('./logo',$carrier)->item(0)->nodeValue,
			'logo_modules' => $this->xpath->query('./logo_modules',$carrier)->item(0)->nodeValue,
			'description' => $this->xpath->query('./description',$carrier)->item(0)->nodeValue,
			'address' => $this->xpath->query('./address',$carrier)->item(0)->nodeValue,
			'url' => $this->xpath->query('./url',$carrier)->item(0)->nodeValue,
			'tracking' => $this->xpath->query('./tracking_url',$carrier)->item(0)->nodeValue,
			'tel' => $this->xpath->query('./telephone',$carrier)->item(0)->nodeValue,
			'cgv' => $this->xpath->query('./cgv',$carrier)->item(0)->nodeValue);
		return $result;
	}

}
