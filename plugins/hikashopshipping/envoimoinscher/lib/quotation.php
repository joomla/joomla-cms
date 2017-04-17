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

class Env_Quotation extends Env_WebService {

	public $offers = array();

	public $order = array();

	protected $palletDims = array(130110 => '130x110', 122102 => '122x102', 120120 => '120x120', 120100 => '120x100',
													 12080 => '120x80' , 114114 => '114x114', 11476 => '114x76', 110110 => '110x110',
													 107107 => '107x107', 8060 => '80x60'
													);

	protected $shipReasons = array('sale' => 'sale', 'repair' => 'repr', 'return' => 'rtrn', 'gift' => 'gift',
													 'sample' => 'smpl' , 'personnal' => 'prsu', 'document' => 'icdt', 'other' => 'othr');

	public function setProforma($data) {
		foreach($data as $key => $value) {
			if (((!isset($value['number']) || $value['number'] <= 0) && (!isset($value['nombre']) || $value['nombre'] <= 0))
				|| isset($value['number']) && isset($value['nombre'])){
				continue;
			}
			foreach($value as $lineKey => $lineValue) {
				$this->param['proforma_'.$key.'.'.$lineKey] = $lineValue;
			}
		}
	}

	public function setType($type, $dimensions) {
		foreach($dimensions as $d => $data) {
			$this->param[$type.'_'.$d.'.poids'] = $data['poids'];
			if($type == 'palette') {
				$palletDim = explode('x', $this->palletDims[$data['palletDims']]);
				$data[$type.'_'.$d.'.longueur'] = (int)$palletDim[0];
				$data[$type.'_'.$d.'.largeur'] = (int)$palletDim[1];
			}
			$this->param[$type.'_'.$d.'.longueur'] =  isset($data['longueur']) ? $data['longueur'] : $data[$type.'_'.$d.'.longueur'];
			$this->param[$type.'_'.$d.'.largeur'] =  isset($data['largeur']) ? $data['largeur'] : $data[$type.'_'.$d.'.largeur'];
			if($type != 'pli') {
				$this->param[$type.'_'.$d.'.hauteur'] = $data['hauteur'];
			}
		}
	}

	public function setPerson($type, $data) {
		foreach($data as $key => $value) {
			$this->param[$type.'.'.$key] = $value;
		}
	}

	public function getQuotation($quotInfo) {
		$this->param = array_merge($this->param, $quotInfo);
		$this->setGetParams(array());
		$this->setOptions(array('action' => '/api/v1/cotation'));
		return $this->doSimpleRequest();
	}

	private function doSimpleRequest() {
		$source = parent::doRequest();



		if($source !== false) {
			parent::parseResponse($source);
			return (count($this->respErrorsList) == 0);
		}
		return false;
	}

	public function getOffers($onlyCom = false) {
		$offers = $this->xpath->query('/cotation/shipment/offer');
		foreach($offers as $o => $offer) {
			$offerMode = $this->xpath->query('./mode',$offer)->item(0)->nodeValue;
			if(!$onlyCom || ($onlyCom && $offerMode == 'COM')) {

				$informations = $this->xpath->query('./mandatory_informations/parameter',$offer);
				$mandInfos = array();
				foreach($informations as $m => $mandatory) {
					$arrKey = $this->xpath->query('./code',$mandatory)->item(0)->nodeValue;
					$mandInfos[$arrKey] = array();
					foreach($mandatory->childNodes as $mc => $mandatoryChild) {
						$mandInfos[$arrKey][$mandatoryChild->nodeName] = trim($mandatoryChild->nodeValue);
						if($mandatoryChild->nodeName == 'type') {
							foreach($mandatoryChild->childNodes as $node) {
								if($node->nodeName == 'enum') {
									$mandInfos[$arrKey][$mandatoryChild->nodeName] = 'enum';
									$mandInfos[$arrKey]['array'] = array();
									foreach($node->childNodes as $child) {
										if(trim($child->nodeValue) != '') {
											$mandInfos[$arrKey]['array'][] = $child->nodeValue;
										}
									}
								}
								else {
									$mandInfos[$arrKey][$mandatoryChild->nodeName] = $node->nodeName;
								}
							}
						}
					}
					unset($mandInfos[$arrKey]['#text']);
				}
				$optionsXpath = $this->xpath->query('./options/option',$offer);
				$options = array();
				foreach($optionsXpath as $oKey => $option)
				{
					$codeOption = $this->xpath->query('./code',$option)->item(0)->nodeValue;
					$s = $oKey + 1;
					$options[$codeOption] = array("name" => $this->xpath->query('./name',$option)->item(0)->nodeValue,
						"parameters" => array()
					);
					$parameters = $this->xpath->query('./parameter',$option);
					foreach($parameters as $p => $parameter)
					{
						$paramCode = $this->xpath->query('./code',$parameter)->item(0);
						$paramLabel = $this->xpath->query('./label',$parameter)->item(0);
						$paramType = $this->xpath->query('./type',$parameter)->item(0);
						$options[$codeOption]["parameters"][$paramCode->nodeValue] = array("code" => $paramCode->nodeValue, "label" => $paramLabel->nodeValue,
						"values" => array());
						if(trim($paramType->nodeValue) != "")
						{
							$values = array();
							foreach($paramType->getElementsByTagName("enum")->item(0)->childNodes as $po => $paramOption)
							{
								if(trim($paramType->nodeValue) != "" && isset($paramType->getElementsByTagName("enum")->item(0)->childNodes))
									$values[$paramOption->nodeValue] = $paramOption->nodeValue;
							}
							$options[$codeOption]["parameters"][$paramCode->nodeValue]["values"] = $values;
						}
					}
				}

				$charactDetail = $this->xpath->evaluate('./characteristics',$offer)->item(0)->childNodes;
				$charactArray = array();
				foreach($charactDetail as $c => $char) {
					if(trim($char->nodeValue) != "") {
						$charactArray[$c] = $char->nodeValue;
					}
				}

				$alert = '';
				$alertNode = $this->xpath->query('./alert',$offer)->item(0);
				if(!empty($alertNode)) {
					$alert = $alertNode->nodeValue;
				}
				else
				{
					$alert = '';
				}

				$this->offers[$o] = array(
					'mode' => $offerMode,
					'url' => $this->xpath->query('./url',$offer)->item(0)->nodeValue,
					'operator' => array(
						'code' => $this->xpath->query('./operator/code',$offer)->item(0)->nodeValue,
						'label' => $this->xpath->query('./operator/label',$offer)->item(0)->nodeValue,
						'logo' => $this->xpath->query('./operator/logo',$offer)->item(0)->nodeValue
					),
					'service' => array(
						'code' => $this->xpath->query('./service/code',$offer)->item(0)->nodeValue,
						'label' => $this->xpath->query('./service/label',$offer)->item(0)->nodeValue
					),
					'price' => array(
						'currency' => $this->xpath->query('./price/currency',$offer)->item(0)->nodeValue,
						'tax-exclusive' => $this->xpath->query('./price/tax-exclusive',$offer)->item(0)->nodeValue,
						'tax-inclusive' => $this->xpath->query('./price/tax-inclusive',$offer)->item(0)->nodeValue
					),
					'collection' => array(
						'type' => $this->xpath->query('./collection/type/code',$offer)->item(0)->nodeValue,
						'date' => $this->xpath->query('./collection/date',$offer)->item(0)->nodeValue,
						'label' => $this->xpath->query('./collection/type/label',$offer)->item(0)->nodeValue
					),
					'delivery' => array(
						'type' => $this->xpath->query('./delivery/type/code',$offer)->item(0)->nodeValue,
						'date' => $this->xpath->query('./delivery/date',$offer)->item(0)->nodeValue,
						'label' => $this->xpath->query('./delivery/type/label',$offer)->item(0)->nodeValue
					),
					'characteristics' => $charactArray,
					'alert' => $alert,
					'mandatory' => $mandInfos,
					'options' =>$options
				);
				if ($this->xpath->evaluate('boolean(./insurance)',$offer)) {
					$this->offers[$o]['insurance'] = array(
						'currency' => $this->xpath->query('./insurance/currency',$offer)->item(0)->nodeValue,
						'tax-exclusive' => $this->xpath->query('./insurance/tax-exclusive',$offer)->item(0)->nodeValue,
						'tax-inclusive' => $this->xpath->query('./insurance/tax-inclusive',$offer)->item(0)->nodeValue
					);
					$this->offers[$o]['hasInsurance'] = true;
				}
				else
				{
					$this->offers[$o]['hasInsurance'] = false;
				}
			}
		}
	}

	private function getOrderInfos() {
		$shipment = $this->xpath->query('/order/shipment')->item(0);
		$offer = $this->xpath->query('./offer',$shipment)->item(0);
		$this->order['url'] = $this->xpath->query('./url',$offer)->item(0)->nodeValue;
		$this->order['mode'] = $this->xpath->query('./mode',$offer)->item(0)->nodeValue;
		$this->order['offer']["operator"]["code"] = $this->xpath->query('./operator/code',$offer)->item(0)->nodeValue;
		$this->order['offer']['operator']['label'] = $this->xpath->query('./operator/label',$offer)->item(0)->nodeValue;
		$this->order['offer']['operator']['logo'] = $this->xpath->query('./operator/logo',$offer)->item(0)->nodeValue;
		$this->order['service']['code'] = $this->xpath->query('./service/code',$offer)->item(0)->nodeValue;
		$this->order['service']['label'] = $this->xpath->query('./service/label',$offer)->item(0)->nodeValue;
		$this->order['price']['currency'] = $this->xpath->query('./service/code',$offer)->item(0)->nodeValue;
		$this->order['price']['tax-exclusive'] = $this->xpath->query('./price/tax-exclusive',$offer)->item(0)->nodeValue;
		$this->order['price']['tax-inclusive'] = $this->xpath->query('./price/tax-inclusive',$offer)->item(0)->nodeValue;
		$this->order['collection']['code'] = $this->xpath->query('./collection/type/code',$offer)->item(0)->nodeValue;
		$this->order['collection']['type_label'] = $this->xpath->query('./collection/type/label',$offer)->item(0)->nodeValue;
		$this->order['collection']['date'] = $this->xpath->query('./collection/date',$offer)->item(0)->nodeValue;
		$time = $this->xpath->query('./collection/time',$offer)->item(0);
		if ($time) {
			$this->order['collection']['time'] = $time->nodeValue;
		}
		else {
			$this->order['collection']['time'] = '';
		}
		$this->order['collection']['label'] = $this->xpath->query('./collection/label',$offer)->item(0)->nodeValue;
		$this->order['delivery']['code'] = $this->xpath->query('./delivery/type/code',$offer)->item(0)->nodeValue;
		$this->order['delivery']['type_label'] = $this->xpath->query('./delivery/type/label',$offer)->item(0)->nodeValue;
		$this->order['delivery']['date'] = $this->xpath->query('./delivery/date',$offer)->item(0)->nodeValue;
		$time = $this->xpath->query('./delivery/time',$offer)->item(0);
		if ($time) {
			$this->order['delivery']['time'] = $time->nodeValue;
		}
		else {
			$this->order['delivery']['time'] = '';
		}
		$this->order['delivery']['label'] = $this->xpath->query('./delivery/label',$offer)->item(0)->nodeValue;
		$proforma = $this->xpath->query('./proforma',$shipment)->item(0);
		if ($proforma) {
			$this->order['proforma'] = $proforma->nodeValue;
		}
		else {
			$this->order['proforma'] = '';
		}
		$this->order['alerts'] = array();
		$alertsNodes = $this->xpath->query('./alert',$offer);
		foreach($alertsNodes as $a => $alert) {
			$this->order['alerts'][$a] = $alert->nodeValue;
		}
		$this->order['chars'] = array();
		$charNodes = $this->xpath->query('./characteristics/label',$offer);
		foreach($charNodes as $c => $char) {
			$this->order['chars'][$c] = $char->nodeValue;
		}
		$this->order['labels'] = array();
		$labelNodes = $this->xpath->query('./labels/label',$shipment);
		foreach($labelNodes as $l => $label) {
			$this->order['labels'][$l] = trim($label->nodeValue);
		}
	}

	public function makeOrder($quotInfo, $getInfo = false) {
		$this->quotInfo = $quotInfo;
		$this->getInfo = $getInfo;
		if(isset($quotInfo['reason']) && $quotInfo['reason']) {
			$quotInfo['envoi.raison'] = $this->shipReasons[$quotInfo['reason']];
			unset($quotInfo['reason']);
		}
		if(!isset($quotInfo['assurance.selected']) || $quotInfo['assurance.selected'] == '') {
			$quotInfo['assurance.selected'] = false;
		}
		$this->param = array_merge($this->param, $quotInfo);
		$this->setOptions(array('action' => '/api/v1/order'));
		$this->setPost();

		if($this->doSimpleRequest() && !$this->respError) {
			$nodes = $this->xpath->query('/order/shipment');
			$reference = $nodes->item(0)->getElementsByTagName('reference')->item(0)->nodeValue;
			if(preg_match("/^[0-9a-zA-Z]{20}$/", $reference)) {
				$this->order['ref'] = $reference;
				$this->order['date'] = date('Y-m-d H:i:s');
				if($getInfo) {
					$this->getOrderInfos();
				}
				return true;
			}
			return false;
		}
		else {
			return false;
		}
	}


	public function getReasons($translations) {
		$reasons = array();
		if(count($translations) == 0)
		{
			$translations = $this->shipReasons;
		}
		foreach($this->shipReasons as $r => $reason)
		{
			$reasons[$reason] = $translations[$r];
		}
		return $reasons;
	}


	public function makeDoubleOrder($quotInfo = array(), $getInfo = false) {
		if(count($quotInfo) == 0) {
			$quotInfo = $this->quotInfo;
		}
		else {
			$quotInfo = $this->setNewQuotInfo($quotInfo);
		}
		$this->switchPeople();
		$this->makeOrder($quotInfo, $getInfo);
	}

	private function switchPeople() {
		$localParams = $this->param;
		$old = array('expediteur', 'destinataire', 'tmp_exp', 'tmp_dest');
		$new = array('tmp_exp', 'tmp_dest', 'destinataire', 'expediteur');
		foreach($localParams as $key => $value) {
			$this->param[str_replace($old, $new, $key)] = $value;
		}
	}

	private function setNewQuotInfo($quotInfo) {
		foreach((array)$this->quotInfo as $q => $info) {
			if(array_key_exists($q, $quotInfo)) {
				$this->quotInfo[$q] = $quotInfo[$q];
			}
		}
		foreach($quotInfo as $q => $info) {
			if(!array_key_exists($q, (array)$this->quotInfo)) {
				$this->quotInfo[$q] = $quotInfo[$q];
			}
		}
		return $this->quotInfo;
	}

	public function unsetParams($quotInfo) {
		foreach($quotInfo as $info) {
			unset($this->quotInfo[$info]);
			unset($this->param[$info]);
		}
	}

}
