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

class mpgGlobals {
	var $Globals=array(
		'MONERIS_PROTOCOL' => 'https',
		'MONERIS_HOST' => 'www3.moneris.com',
		'MONERIS_HOST_DEBUG' => 'esqa.moneris.com',
		'MONERIS_PORT' =>'443',
		'MONERIS_FILE' => '/gateway2/servlet/MpgRequest',
		'API_VERSION'  =>'PHP - 2.5.6',
		'CLIENT_TIMEOUT' => '60'
	);

	function mpgGlobals() {}
	function getGlobals() {
		return($this->Globals);
	}

}

class mpgHttpsPost {
	var $api_token;
	var $store_id;
	var $mpgRequest;
	var $mpgResponse;

	function mpgHttpsPost($store_id, $api_token, $mpgRequestOBJ, $debug = false) {

		$this->store_id=$store_id;
		$this->api_token= $api_token;
		$this->mpgRequest=$mpgRequestOBJ;

		$dataToSend=$this->toXML();

		$g=new mpgGlobals();
		$gArray=$g->getGlobals();

		$url=$gArray['MONERIS_PROTOCOL']."://".($debug ? $gArray['MONERIS_HOST_DEBUG'] : $gArray['MONERIS_HOST'] ).":".$gArray['MONERIS_PORT'].$gArray['MONERIS_FILE'];

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS,$dataToSend);
		curl_setopt($ch, CURLOPT_TIMEOUT,$gArray['CLIENT_TIMEOUT']);
		curl_setopt($ch, CURLOPT_USERAGENT,$gArray['API_VERSION']);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

		$response = curl_exec($ch);
		$error = curl_errno($ch);
		$err_msg = curl_error($ch);

		curl_close ($ch);

		$this->curl_err = $error;
		$this->curl_err_msg = $err_msg;
		$this->curl_raw_response = $response;

		if(!$response) {
			$response="<?xml version=\"1.0\"?><response><receipt>".
				"<ReceiptId>Global Error Receipt</ReceiptId>".
				"<ReferenceNum>null</ReferenceNum><ResponseCode>null</ResponseCode>".
				"<ISO>null</ISO> <AuthCode>null</AuthCode><TransTime>null</TransTime>".
				"<TransDate>null</TransDate><TransType>null</TransType><Complete>false</Complete>".
				"<Message>null</Message><TransAmount>null</TransAmount>".
				"<CardType>null</CardType>".
				"<TransID>null</TransID><TimedOut>null</TimedOut>".
				"</receipt></response>";
		}

		$this->mpgResponse=new mpgResponse($response);
	}

	function getMpgResponse() {
		return $this->mpgResponse;
	}

	function toXML() {
		$req=$this->mpgRequest ;
		$reqXMLString=$req->toXML();

		$xmlString='';
		$xmlString .="<?xml version=\"1.0\"?>".
			"<request>".
			"<store_id>$this->store_id</store_id>".
			"<api_token>$this->api_token</api_token>".
			$reqXMLString.
			"</request>";
		return ($xmlString);
	}

}

class mpgHttpsPostStatus {

	var $api_token;
	var $store_id;
	var $status;
	var $mpgRequest;
	var $mpgResponse;

	function mpgHttpsPostStatus($store_id,$api_token,$status, $mpgRequestOBJ) {
		$this->store_id=$store_id;
		$this->api_token= $api_token;
		$this->status=$status;
		$this->mpgRequest=$mpgRequestOBJ;

		$dataToSend=$this->toXML();

		$g=new mpgGlobals();
		$gArray=$g->getGlobals();

		$url=$gArray['MONERIS_PROTOCOL']."://".
			$gArray['MONERIS_HOST'].":".
			$gArray['MONERIS_PORT'].
			$gArray['MONERIS_FILE'];

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt ($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS,$dataToSend);
		curl_setopt($ch,CURLOPT_TIMEOUT,$gArray['CLIENT_TIMEOUT']);
		curl_setopt($ch,CURLOPT_USERAGENT,$gArray['API_VERSION']);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);

		$response=curl_exec ($ch);

		curl_close ($ch);

		if(!$response) {
			$response="<?xml version=\"1.0\"?><response><receipt>".
			"<ReceiptId>Global Error Receipt</ReceiptId>".
			"<ReferenceNum>null</ReferenceNum><ResponseCode>null</ResponseCode>".
			"<ISO>null</ISO> <AuthCode>null</AuthCode><TransTime>null</TransTime>".
			"<TransDate>null</TransDate><TransType>null</TransType><Complete>false</Complete>".
			"<Message>null</Message><TransAmount>null</TransAmount>".
			"<CardType>null</CardType>".
			"<TransID>null</TransID><TimedOut>null</TimedOut>".
			"</receipt></response>";
		}

		$this->mpgResponse=new mpgResponse($response);
	}

	function getMpgResponse() {
		return $this->mpgResponse;
	}

	function toXML() {
		$req=$this->mpgRequest ;
		$reqXMLString=$req->toXML();

		$xmlString='';
		$xmlString .="<?xml version=\"1.0\"?>".
			"<request>".
			"<store_id>$this->store_id</store_id>".
			"<api_token>$this->api_token</api_token>".
			"<status_check>$this->status</status_check>".
			$reqXMLString.
			"</request>";
		return ($xmlString);
	}
}

class mpgResponse {
	var $responseData;

	var $p; //parser

	var $currentTag;
	var $purchaseHash = array();
	var $refundHash;
	var $correctionHash = array();
	var $isBatchTotals;
	var $term_id;
	var $receiptHash = array();
	var $ecrHash = array();
	var $CardType;
	var $currentTxnType;
	var $ecrs = array();
	var $cards = array();
	var $cardHash= array();

	var $ACSUrl;

	function mpgResponse($xmlString) {
		$this->p = xml_parser_create();
		xml_parser_set_option($this->p,XML_OPTION_CASE_FOLDING,0);
		xml_parser_set_option($this->p,XML_OPTION_TARGET_ENCODING,"UTF-8");
		xml_set_object($this->p,$this);
		xml_set_element_handler($this->p,"startHandler","endHandler");
		xml_set_character_data_handler($this->p,"characterHandler");
		xml_parse($this->p,$xmlString);
		xml_parser_free($this->p);
	}

	function getMpgResponseData() {
		return($this->responseData);
	}

	function getAvsResultCode() {
		return ($this->responseData['AvsResultCode']);
	}

	function getCvdResultCode()	{
		return ($this->responseData['CvdResultCode']);
	}

	function getCavvResultCode() {
		return ($this->responseData['CavvResultCode']);
	}

	function getITDResponse() {
		return ($this->responseData['ITDResponse']);
	}

	function getStatusCode() {
		return ($this->responseData['status_code']);
	}

	function getStatusMessage() {
		return ($this->responseData['status_message']);
	}

	function getRecurSuccess() {
		return ($this->responseData['RecurSuccess']);
	}

	function getCardType() {
		return ($this->responseData['CardType']);
	}

	function getTransAmount() {
		return ($this->responseData['TransAmount']);
	}

	function getTxnNumber() {
		return ($this->responseData['TransID']);
	}

	function getReceiptId() {
		return ($this->responseData['ReceiptId']);
	}

	function getTransType() {
		return ($this->responseData['TransType']);
	}

	function getReferenceNum() {
		return ($this->responseData['ReferenceNum']);
	}

	function getResponseCode() {
		return ($this->responseData['ResponseCode']);
	}

	function getISO() {
		return ($this->responseData['ISO']);
	}

	function getBankTotals() {
		return ($this->responseData['BankTotals']);
	}

	function getMessage() {
		return ($this->responseData['Message']);
	}

	function getAuthCode() {
		return ($this->responseData['AuthCode']);
	}

	function getComplete() {
		return ($this->responseData['Complete']);
	}

	function getTransDate() {
		return ($this->responseData['TransDate']);
	}

	function getTransTime() {
		return ($this->responseData['TransTime']);
	}

	function getTicket() {
		return ($this->responseData['Ticket']);
	}

	function getTimedOut() {
		return ($this->responseData['TimedOut']);
	}

	function getRecurUpdateSuccess() {
		return ($this->responseData['RecurUpdateSuccess']);
	}

	function getNextRecurDate() {
		return ($this->responseData['NextRecurDate']);
	}

	function getRecurEndDate() {
		return ($this->responseData['RecurEndDate']);
	}

	function getTerminalStatus($ecr_no) {
		return ($this->ecrHash[$ecr_no]);
	}

	function getPurchaseAmount($ecr_no,$card_type) {
		return ($this->purchaseHash[$ecr_no][$card_type]['Amount']=="" ? 0:$this->purchaseHash[$ecr_no][$card_type]['Amount']);
	}

	function getPurchaseCount($ecr_no,$card_type) {
		return ($this->purchaseHash[$ecr_no][$card_type]['Count']=="" ? 0:$this->purchaseHash[$ecr_no][$card_type]['Count']);
	}

	function getRefundAmount($ecr_no,$card_type) {
		return ($this->refundHash[$ecr_no][$card_type]['Amount']=="" ? 0:$this->refundHash[$ecr_no][$card_type]['Amount']);
	}

	function getRefundCount($ecr_no,$card_type) {
		return ($this->refundHash[$ecr_no][$card_type]['Count']=="" ? 0:$this->refundHash[$ecr_no][$card_type]['Count']);
	}

	function getCorrectionAmount($ecr_no,$card_type) {
		return ($this->correctionHash[$ecr_no][$card_type]['Amount']=="" ? 0:$this->correctionHash[$ecr_no][$card_type]['Amount']);
	}

	function getCorrectionCount($ecr_no,$card_type) {
		return ($this->correctionHash[$ecr_no][$card_type]['Count']=="" ? 0:$this->correctionHash[$ecr_no][$card_type]['Count']);
	}

	function getTerminalIDs() {
		return ($this->ecrs);
	}

	function getCreditCardsAll() {
		return (array_keys($this->cards));
	}

	function getCreditCards($ecr_no) {
		return ($this->cardHash[$ecr_no]);
	}

	function characterHandler($parser,$data) {
		if($this->isBatchTotals) {
			switch($this->currentTag) {
				case "term_id": {
					$this->term_id=$data;
					array_push($this->ecrs,$this->term_id);
					$this->cardHash[$data]=array();
					break;
				}

				case "closed": {
					$ecrHash=$this->ecrHash;
					$ecrHash[$this->term_id]=$data;
					$this->ecrHash = $ecrHash;
					break;
				}

				case "CardType": {
					$this->CardType=$data;
					$this->cards[$data]=$data;
					array_push($this->cardHash[$this->term_id],$data) ;
					break;
				}

				case "Amount": {
					if($this->currentTxnType == "Purchase") {
						$this->purchaseHash[$this->term_id][$this->CardType]['Amount']=$data;
					} else if( $this->currentTxnType == "Refund") {
						$this->refundHash[$this->term_id][$this->CardType]['Amount']=$data;
					} else if( $this->currentTxnType == "Correction") {
						$this->correctionHash[$this->term_id][$this->CardType]['Amount']=$data;
					}
					break;
				}

				case "Count": {
					if($this->currentTxnType == "Purchase") {
						$this->purchaseHash[$this->term_id][$this->CardType]['Count']=$data;
					} else if( $this->currentTxnType == "Refund") {
						$this->refundHash[$this->term_id][$this->CardType]['Count']=$data;
					} else if( $this->currentTxnType == "Correction") {
						$this->correctionHash[$this->term_id][$this->CardType]['Count']=$data;
					}
					break;
				}
			}
		} else {
			@$this->responseData[$this->currentTag] .=$data;
		}
	}

	function startHandler($parser,$name,$attrs) {
		$this->currentTag=$name;
		if($this->currentTag == "BankTotals") {
			$this->isBatchTotals=1;
		} else if($this->currentTag == "Purchase") {
			$this->purchaseHash[$this->term_id][$this->CardType]=array();
			$this->currentTxnType="Purchase";
		} else if($this->currentTag == "Refund") {
			$this->refundHash[$this->term_id][$this->CardType]=array();
			$this->currentTxnType="Refund";
		} else if($this->currentTag == "Correction") {
			$this->correctionHash[$this->term_id][$this->CardType]=array();
			$this->currentTxnType="Correction";
		}
	}

	function endHandler($parser,$name) {
		$this->currentTag=$name;
		if($name == "BankTotals") {
			$this->isBatchTotals=0;
		}
		$this->currentTag="/dev/null";
	}
}

class mpgRequest {
	var $txnTypes = array(
		'purchase'=> array('order_id','cust_id', 'amount', 'pan', 'expdate', 'crypt_type', 'dynamic_descriptor'),
		'refund' => array('order_id', 'amount', 'txn_number', 'crypt_type'),
		'idebit_purchase'=>array('order_id', 'cust_id', 'amount','idebit_track2', 'dynamic_descriptor'),
		'idebit_refund'=>array('order_id','amount','txn_number'),
		'purchase_reversal'=>array('order_id','amount'),
		'ind_refund' => array('order_id','cust_id', 'amount','pan','expdate', 'crypt_type', 'dynamic_descriptor'),
		'preauth' =>array('order_id','cust_id', 'amount', 'pan', 'expdate', 'crypt_type', 'dynamic_descriptor'),
		'reauth' =>array('order_id','cust_id', 'amount', 'orig_order_id', 'txn_number', 'crypt_type'),
		'completion' => array('order_id', 'comp_amount','txn_number', 'crypt_type','ship_indicator'),
		'purchasecorrection' => array('order_id', 'txn_number', 'crypt_type','ship_indicator'),
		'opentotals' => array('ecr_number'),
		'batchclose' => array('ecr_number'),
		'cavv_purchase'=> array('order_id','cust_id', 'amount', 'pan', 'expdate', 'cavv', 'dynamic_descriptor'),
		'cavv_preauth' =>array('order_id','cust_id', 'amount', 'pan', 'expdate', 'cavv', 'dynamic_descriptor'),
		'card_verification' =>array('order_id','cust_id','pan','expdate','crypt_type'),
		'recur_update' => array('order_id', 'cust_id', 'pan', 'expdate', 'recur_amount','add_num_recurs', 'total_num_recurs', 'hold', 'terminate')
	);

	var $txnArray;

	function mpgRequest($txn) {
		if(is_array($txn)) {
			$txn=$txn[0];
		}
		$this->txnArray=$txn;
	}

	function toXML() {
		$tmpTxnArray=$this->txnArray;

		$txnArrayLen=count($tmpTxnArray); //total number of transactions

		$txnObj=$tmpTxnArray;

		$txn=$txnObj->getTransaction();	//call to a non-member function

		$txnType=array_shift($txn);
		$tmpTxnTypes=$this->txnTypes;
		$txnTypeArray=$tmpTxnTypes[$txnType];
		$txnTypeArrayLen=count($txnTypeArray); //length of a specific txn type

		$txnXMLString="";

		for($i=0;$i < $txnTypeArrayLen ;$i++) {
			$txnXMLString  .="<".$txnTypeArray[$i].">"
				.$txn[$txnTypeArray[$i]]
				. "</".$txnTypeArray[$i].">";
		}

		$txnXMLString = "<".$txnType.">".$txnXMLString;

		$recur  = $txnObj->getRecur();
		if($recur != null) {
			$txnXMLString .= $recur->toXML();
		}

		$avsInfo  = $txnObj->getAvsInfo();
		if($avsInfo != null) {
			$txnXMLString .= $avsInfo->toXML();
		}

		$cvdInfo  = $txnObj->getCvdInfo();
		if($cvdInfo != null) {
			$txnXMLString .= $cvdInfo->toXML();
		}

		$custInfo = $txnObj->getCustInfo();
		if($custInfo != null) {
			$txnXMLString .= $custInfo->toXML();
		}
		$txnXMLString .="</".$txnType.">";
		return $txnXMLString;
	}
}

class mpgCustInfo {

	var $level3template = array(
		'cust_info'=> array(
			'email',
			'instructions',
			'billing' => array ('first_name', 'last_name', 'company_name', 'address', 'city', 'province', 'postal_code', 'country', 'phone_number', 'fax','tax1', 'tax2','tax3', 'shipping_cost'),
			'shipping' => array('first_name', 'last_name', 'company_name', 'address', 'city', 'province', 'postal_code', 'country', 'phone_number', 'fax','tax1', 'tax2', 'tax3', 'shipping_cost'),
			'item' => array ('name', 'quantity', 'product_code', 'extended_amount')
		)
	);
	var $level3data;
	var $email;
	var $instructions;

	function mpgCustInfo($custinfo=0,$billing=0,$shipping=0,$items=0) {
		if($custinfo) {
			$this->setCustInfo($custinfo);
		}
	}

	function setCustInfo($custinfo)	{
		$this->level3data['cust_info']=array($custinfo);
	}

	function setEmail($email) {
		$this->email=$email;
		$this->setCustInfo(array('email'=>$email,'instructions'=>$this->instructions));
	}

	function setInstructions($instructions) {
		$this->instructions=$instructions;
		$this->setCustinfo(array('email'=>$this->email,'instructions'=>$instructions));
	}

	function setShipping($shipping) {
		$this->level3data['shipping']=array($shipping);
	}

	function setBilling($billing) {
		$this->level3data['billing']=array($billing);
	}

	function setItems($items) {
		if(!isset($this->level3data['item'])) {
			$this->level3data['item']=array($items);
		} else {
			$index=count($this->level3data['item']);
			$this->level3data['item'][$index]=$items;
		}
	}

	function toXML() {
		$xmlString=$this->toXML_low($this->level3template,"cust_info");
		return $xmlString;
	}

	function toXML_low($template,$txnType) {
		$xmlString = '';
		for($x=0;$x<count($this->level3data[$txnType]);$x++) {
			if($x>0) {
				$xmlString .="</$txnType><$txnType>";
			}
			$keys=array_keys($template);

			for($i=0; $i < count($keys);$i++) {
				$tag=$keys[$i];

				if(is_array($template[$keys[$i]])) {
					$data=$template[$tag];

					if(!count($this->level3data[$tag])) {
						continue;
					}

					$beginTag="<$tag>";
					$endTag="</$tag>";

					$xmlString .=$beginTag;

					if(is_array($data))  {
						$returnString=$this->toXML_low($data,$tag);
						$xmlString .= $returnString;
					}
					$xmlString .=$endTag;
				} else {
					$tag=$template[$keys[$i]];
					$beginTag="<$tag>";
					$endTag="</$tag>";
					$data=$this->level3data[$txnType][$x][$tag];

					$xmlString .=$beginTag.$data.$endTag;
				}
			}
		}
		return $xmlString;
	}
}

class mpgRecur {
	var $params;
	var $recurTemplate = array('recur_unit','start_now','start_date','num_recurs','period','recur_amount');

	function mpgRecur($params)  {
		$this->params = $params;
		if( (! $this->params['period']) ) {
			$this->params['period'] = 1;
		}
	}

	function toXML() {
		foreach($this->recurTemplate as $tag) {
			$xmlString .= "<$tag>". $this->params[$tag] ."</$tag>";
		}
		return "<recur>$xmlString</recur>";
	}

}

class mpgTransaction {
	var $txn;
	var $custInfo = null;
	var $avsInfo = null;
	var $cvdInfo = null;
	var $recur = null;

	function mpgTransaction($txn) {
		$this->txn=$txn;
	}

	function getCustInfo() {
		return $this->custInfo;
	}

	function setCustInfo($custInfo) {
		$this->custInfo = $custInfo;
		array_push($this->txn,$custInfo);
	}

	function getCvdInfo() {
		return $this->cvdInfo;
	}

	function setCvdInfo($cvdInfo) {
		$this->cvdInfo = $cvdInfo;
	}

	function getAvsInfo() {
		return $this->avsInfo;
	}

	function setAvsInfo($avsInfo) {
		$this->avsInfo = $avsInfo;
	}

	function getRecur() {
		return $this->recur;
	}

	function setRecur($recur) {
		$this->recur = $recur;
	}

	function getTransaction() {
		return $this->txn;
	}

}

class mpgAvsInfo {
	var $params;
	var $avsTemplate = array('avs_street_number','avs_street_name','avs_zipcode','avs_email','avs_hostname','avs_browser','avs_shiptocountry','avs_shipmethod','avs_merchprodsku','avs_custip','avs_custphone');

	function mpgAvsInfo($params) {
		$this->params = $params;
	}

	function toXML() {
		$xmlString = '';
		foreach($this->avsTemplate as $tag) {
			$xmlString .= "<$tag>". $this->params[$tag] ."</$tag>";
		}
		return "<avs_info>$xmlString</avs_info>";
	}

}

class mpgCvdInfo {
	var $params;
	var $cvdTemplate = array('cvd_indicator','cvd_value');

	function mpgCvdInfo($params) {
		$this->params = $params;
	}

	function toXML() {
		$xmlString = '';
		foreach($this->cvdTemplate as $tag) {
			$xmlString .= "<$tag>". $this->params[$tag] ."</$tag>";
		}
		return "<cvd_info>$xmlString</cvd_info>";
	}

}
