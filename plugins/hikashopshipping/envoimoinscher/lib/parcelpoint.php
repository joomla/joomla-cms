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

class Env_ParcelPoint extends Env_WebService {

  protected $types = array('pickup_point', 'dropoff_point');

  public $points = array();


  public $constructList = false;

  public function getParcelPoint($type = "", $code = "", $country = "FR") {
    if(in_array($type, $this->types)) {
      $this->setOptions(array("action" => "/api/v1/$type/$code/$country/informations",
			)); 
      $this->doSimpleRequest($type);
    }
    else {
      $this->respError = true;
      $this->respErrorsList[0] = array("code" => "type_not_correct", "url" => "");
    }
  }

  private function doSimpleRequest($type) {
    $source = parent::doRequest();




    if($source !== false) {
			parent::parseResponse($source);

			$point = $this->xpath->query('/'.$type)->item(0);
      $pointDetail = array(
				'code' => $this->xpath->query('./code',$point)->item(0)->nodeValue,
        'name' =>  $this->xpath->query('./name',$point)->item(0)->nodeValue,
        'address' =>  $this->xpath->query('./address',$point)->item(0)->nodeValue,
        'city' =>  $this->xpath->query('./city',$point)->item(0)->nodeValue,
        'zipcode' =>  $this->xpath->query('./zipcode',$point)->item(0)->nodeValue,
        'country' =>  $this->xpath->query('./country',$point)->item(0)->nodeValue,
        'phone' =>  $this->xpath->query('./phone',$point)->item(0)->nodeValue,
        'description' => $this->xpath->query('./description',$point)->item(0)->nodeValue
      );


      $schedule = array();
      foreach($this->xpath->query('./schedule/day',$point) as $d => $dayNode) {
        foreach($dayNode->childNodes as $c => $childNode) {
          if($childNode->nodeName != '#text') {
            $schedule[$d][$childNode->nodeName] = $childNode->nodeValue;
          }
        }
      }
      $pointDetail['schedule'] = $schedule;


      if($this->constructList) {
        if(!isset($this->points[$type]))
        {
          $this->points[$type] = array();
        }
        $this->points[$type][count($this->points[$type])] = $pointDetail;
      }
      else {
        $this->points[$type] = $pointDetail;
      }
    }
  }

}
