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

class Env_ListPoints extends Env_WebService {

	public $listPoints = array();

	public function getListPoints($ope, $infos) {
		$this->param = $infos;
		$this->setGetParams(array());
		$this->setOptions(array("action" => "/api/v1/$ope/listpoints"));
		$this->doListRequest();
	}

	private function doListRequest() {
		$source = parent::doRequest();




		if($source !== false) {
			parent::parseResponse($source);
			if(count($this->respErrorsList) == 0) {


				$points = $this->xpath->query("/points/point");
				foreach($points as $pointIndex => $point){
					$pointInfo = array(
						'code' => $this->xpath->query('./code',$point)->item(0)->nodeValue,
						'name' => $this->xpath->query('./name',$point)->item(0)->nodeValue,
						'address' => $this->xpath->query('./address',$point)->item(0)->nodeValue,
						'city' => $this->xpath->query('./city',$point)->item(0)->nodeValue,
						'zipcode' => $this->xpath->query('./zipcode',$point)->item(0)->nodeValue,
						'country' => $this->xpath->query('./country',$point)->item(0)->nodeValue,
						'phone' => $this->xpath->query('./phone',$point)->item(0)->nodeValue,
						'description' => $this->xpath->query('./description',$point)->item(0)->nodeValue,
						'days' => array()
						);
					$days = $this->xpath->query('./schedule/day',$point);
					foreach($days as $dayIndex => $day){
						$pointInfo['days'][$dayIndex] = array(
							'weekday' => $this->xpath->query('./weekday',$day)->item(0)->nodeValue,
							'open_am' => $this->xpath->query('./open_am',$day)->item(0)->nodeValue,
							'close_am' => $this->xpath->query('./close_am',$day)->item(0)->nodeValue,
							'open_pm' => $this->xpath->query('./open_pm',$day)->item(0)->nodeValue,
							'close_pm' => $this->xpath->query('./close_pm',$day)->item(0)->nodeValue,
							);
					}
					$this->listPoints[$pointIndex] = $pointInfo;
				}
			}
		}
	}

}
