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

class Env_CarriersList extends Env_WebService {

  public $carriers = array();

  public function loadCarriersList($channel,$version) {
		$this->param["channel"] = strtolower($channel);
		$this->param["version"] = strtolower($version);
    $this->setGetParams(array());
    $this->setOptions(array('action' => '/api/v1/carriers_list'));
    if ($this->doSimpleRequest()){
			$this->getCarriersList();
			return true;
		}
		return false;
  }

  private function doSimpleRequest() {
    $source = parent::doRequest();	



    if($source !== false) {
      parent::parseResponse($source);
      return (count($this->respErrorsList) == 0);
    }
    return false;
  }

  public function getCarriersList() {
		$this->carriers = array();
    $operators = $this->xpath->query('/operators/operator');
    foreach($operators as $operator) {
			$ope_code = $this->xpath->query('./code',$operator)->item(0)->nodeValue;
			$ope_name = $this->xpath->query('./name',$operator)->item(0)->nodeValue;
			$ope_carriers = $this->xpath->query('./services/service',$operator);
			foreach($ope_carriers as $carrier) {
				$id = count($this->carriers);
				$this->carriers[$id]["ope_code"] = $ope_code;
				$this->carriers[$id]["ope_name"] = $ope_name;
				$this->carriers[$id]["srv_code"] = $this->xpath->query('./code',$carrier)->item(0)->nodeValue;
				$this->carriers[$id]["srv_name"] = $this->xpath->query('./label',$carrier)->item(0)->nodeValue;
				$this->carriers[$id]["label_store"] = $this->xpath->query('./label_store',$carrier)->item(0)->nodeValue;
				$this->carriers[$id]["description"] = $this->xpath->query('./description',$carrier)->item(0)->nodeValue;
				$this->carriers[$id]["description_store"] = $this->xpath->query('./description_store',$carrier)->item(0)->nodeValue;
				$this->carriers[$id]["family"] = $this->xpath->query('./family',$carrier)->item(0)->nodeValue;
				$this->carriers[$id]["zone"] = $this->xpath->query('./zone',$carrier)->item(0)->nodeValue;
				$this->carriers[$id]["parcel_pickup_point"] = $this->xpath->query('./parcel_pickup_point',$carrier)->item(0)->nodeValue;
				$this->carriers[$id]["parcel_dropoff_point"] = $this->xpath->query('./parcel_dropoff_point',$carrier)->item(0)->nodeValue;
			}
    }
  }


}
