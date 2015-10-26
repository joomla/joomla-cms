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

class Env_OrderStatus extends Env_WebService {

  public $orderInfo = array("emcRef" => "", "state" => "", "opeRef" => "", "labelAvailable" => false);

  public function getOrderInformations($reference) { 
    $this->setOptions(
			array("action" => "/api/v1/order_status/$reference/informations",)
		);
    $this->doStatusRequest();
  }

  private function doStatusRequest() {
    $source = parent::doRequest();




    if($source !== false) {
      parent::parseResponse($source);
	  	if(count($this->respErrorsList) == 0) {


				$labels = array();
				$orderLabels = $this->xpath->evaluate("/order/labels");
				foreach($orderLabels as $labelIndex => $label) {
					$labels[$labelIndex] = $label->nodeValue;  
				}
				$this->orderInfo = array(
					'emcRef' => $this->xpath->evaluate("/order/emc_reference")->item(0)->nodeValue, 
					'state' => $this->xpath->evaluate("/order/state")->item(0)->nodeValue, 
					'opeRef' => $this->xpath->evaluate("/order/carrier_reference")->item(0)->nodeValue, 
					'labelAvailable' => (bool)$this->xpath->evaluate("/order/label_available")->item(0)->nodeValue, 
					'labelUrl' => $this->xpath->evaluate("/order/label_url")->item(0)->nodeValue,
					'labels' => $labels
					);
			}
    }
  }

}
