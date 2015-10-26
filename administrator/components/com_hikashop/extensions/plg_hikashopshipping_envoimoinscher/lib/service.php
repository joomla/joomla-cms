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

class Env_Service extends Env_Carrier {

  public function getServices() { 
    $this->setOptions(array('action' => '/api/v1/services',
		));
    $this->doServicesRequest();
  }

  private function doServicesRequest() {
    $source = $this->doRequest();




    if($source !== false) {
      parent::parseResponse($source);
	  	if(count($this->respErrorsList) == 0) {


				$carriers = $this->xpath->query('/operators/operator');
				foreach($carriers as $c => $carrier) {
					$index = $c + 1;
					$result = $this->parseCarrierNode($carrier);
					$this->carriers[$result["code"]] = $result;
					$this->carriers[$result["code"]]["services"] = $this->parseServicesNode($index);
				}
			}
    }
  }

  public function getServicesByCarrier($code) {
    if(isset($this->carriers[$code]["services"])) {
      return $this->carriers[$code]["services"];
    }
    $this->setOptions(array("action" => '/api/v1/carrier/'.$code.'/services'));
    $this->doServicesRequest();
  }

  private function parseServicesNode($c) {
    $result = array();
    $services = $this->xpath->query('/operators/operator['.$c.']/services/service');
    foreach($services as $se => $service) {
      $s = $se + 1;
      $code = $this->xpath->query('./code',$service)->item(0)->nodeValue;
      $result[$code] = array(
				"code" => $code,
        "label" => $this->xpath->query('./label',$service)->item(0)->nodeValue,
        "mode" => $this->xpath->query('./mode',$service)->item(0)->nodeValue,
        "alert" => $this->xpath->query('./alert',$service)->item(0)->nodeValue,
        "collection" => $this->xpath->query('./collection_type',$service)->item(0)->nodeValue,
        "delivery" => $this->xpath->query('./delivery_type',$service)->item(0)->nodeValue,
        "is_pluggable" => ($this->xpath->query('./plug_available',$service)->item(0)->nodeValue == "true" ? true : false)
      );
      $options = array();
      $exclusions = array();
      $apiOptions = array();
      foreach($this->xpath->evaluate('./options/option',$service) as $o => $option) {
        $options[$this->xpath->evaluate('./code',$option)->item(0)->nodeValue] = $this->xpath->evaluate('./name',$option)->item(0)->nodeValue;
      }
      $result[$code]['options'] = $options;
      foreach($this->xpath->evaluate('./excluded_contents/contenu',$service) as $e => $exclusion) {
        $exclusions[$this->xpath->evaluate('./id',$exclusion)->item(0)->nodeValue] = $this->xpath->evaluate("./label",$exclusion)->item(0)->nodeValue;
      }
      $result[$code]['exclusions'] = $exclusions;
      foreach($this->xpath->evaluate('./api_options',$service) as $o => $option) {
        for($i = 1; $i < $option->childNodes->length; $i++) {
          $apiNode = $option->childNodes->item($i);
          $apiNodeChild = $apiNode->childNodes;
          $apiOptions[$apiNode->nodeName] = array();
          for($a = 1; $a < $apiNodeChild->length; $a++) {
            $apiOptions[$apiNode->nodeName][$apiNodeChild->item($a)->nodeName] = $apiNodeChild->item($a)->nodeValue;
            $a++;
          }
          $i++; 
        }
      }
      $result[$code]['apiOptions'] = $apiOptions;
    }
    return $result;
  }

}
