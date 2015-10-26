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

define('EWAY_CURL_ERROR_OFFSET', 1000);
define('EWAY_XML_ERROR_OFFSET',  2000);

define('EWAY_TRANSACTION_OK',       0);
define('EWAY_TRANSACTION_FAILED',   1);
define('EWAY_TRANSACTION_UNKNOWN',  2);

class EwayPaymentLib {
	var $parser;
	var $xmlData;
	var $currentTag;

	var $myGatewayURL;
	var $myCustomerID;

	var $myTotalAmount;
	var $myCustomerFirstname;
	var $myCustomerLastname;
	var $myCustomerEmail;
	var $myCustomerAddress;
	var $myCustomerPostcode;
	var $myCustomerInvoiceDescription;
	var $myCustomerInvoiceRef;
	var $myCardHoldersName;
	var $myCardNumber;
	var $myCardExpiryMonth;
	var $myCardExpiryYear;
	var $myCardCVN;
	var $myTrxnNumber;
	var $myOption1;
	var $myOption2;
	var $myOption3;

	var $myResultTrxnStatus;
	var $myResultTrxnNumber;
	var $myResultTrxnOption1;
	var $myResultTrxnOption2;
	var $myResultTrxnOption3;
	var $myResultTrxnReference;
	var $myResultTrxnError;
	var $myResultAuthCode;
	var $myResultReturnAmount;
	var $myCardName;

	var $myError;
	var $myErrorMessage;

	function EwayPaymentLib( $customerID = EWAY_DEFAULT_CUSTOMER_ID, $gatewayURL = EWAY_DEFAULT_GATEWAY_URL ) {
		$this->myCustomerID = $customerID;
		$this->myGatewayURL = $gatewayURL;
	}


	function epXmlElementStart ($parser, $tag, $attributes) {
		$this->currentTag = $tag;
	}

	function epXmlElementEnd ($parser, $tag) {
		$this->currentTag = "";
	}

	function epXmlData ($parser, $cdata) {
		$this->xmlData[$this->currentTag] = $cdata;
	}

	function setCustomerID( $customerID ) {
		$this->myCustomerID = $customerID;
	}

	function setTotalAmount( $totalAmount ) {
		$this->myTotalAmount = $totalAmount;
	}

	function setCustomerFirstname( $customerFirstname ) {
		$this->myCustomerFirstname = $customerFirstname;
	}

	function setCustomerLastname( $customerLastname ) {
		$this->myCustomerLastname = $customerLastname;
	}

	function setCustomerEmail( $customerEmail ) {
		$this->myCustomerEmail = $customerEmail;
	}

	function setCustomerAddress( $customerAddress ) {
		$this->myCustomerAddress = $customerAddress;
	}

	function setCustomerPostcode( $customerPostcode ) {
		$this->myCustomerPostcode = $customerPostcode;
	}

	function setCustomerInvoiceDescription( $customerInvoiceDescription ) {
		$this->myCustomerInvoiceDescription = $customerInvoiceDescription;
	}

	function setCustomerInvoiceRef( $customerInvoiceRef ) {
		$this->myCustomerInvoiceRef = $customerInvoiceRef;
	}

	function setCardHoldersName( $cardHoldersName ) {
		$this->myCardHoldersName = $cardHoldersName;
	}

	function setCardNumber( $cardNumber ) {
		$this->myCardNumber = $cardNumber;
	}

	function setCardExpiryMonth( $cardExpiryMonth ) {
		$this->myCardExpiryMonth = $cardExpiryMonth;
	}

	function setCardExpiryYear( $cardExpiryYear ) {
		$this->myCardExpiryYear = $cardExpiryYear;
	}

	function setCardCVN( $cardCVN ) {
		$this->myCardCVN = $cardCVN;
	}

	function setTrxnNumber( $trxnNumber ) {
		$this->myTrxnNumber = $trxnNumber;
	}

	function setOption1( $option1 ) {
		$this->myOption1 = $option1;
	}

	function setOption2( $option2 ) {
		$this->myOption2 = $option2;
	}

	function setOption3( $option3 ) {
		$this->myOption3 = $option3;
	}

	function getTrxnStatus() {
		return $this->myResultTrxnStatus;
	}

	function getTrxnNumber() {
		return $this->myResultTrxnNumber;
	}

	function getTrxnOption1() {
		return $this->myResultTrxnOption1;
	}

	function getTrxnOption2() {
		return $this->myResultTrxnOption2;
	}

	function getTrxnOption3() {
		return $this->myResultTrxnOption3;
	}

	function getTrxnReference() {
		return $this->myResultTrxnReference;
	}

	function getTrxnError() {
		return $this->myResultTrxnError;
	}

	function getAuthCode() {
		return $this->myResultAuthCode;
	}

	function getReturnAmount() {
		return $this->myResultReturnAmount;
	}

	function getError()
	{
		if( $this->myError != 0 ) {
			return $this->myError;
		}
		if( $this->getTrxnStatus() == 'True' ) {
			return EWAY_TRANSACTION_OK;
		}
		if( $this->getTrxnStatus() == 'False' ) {
			return EWAY_TRANSACTION_FAILED;
		}
		return EWAY_TRANSACTION_UNKNOWN;
	}

	function getErrorMessage()
	{
		if( $this->myError != 0 ) {
			return $this->myErrorMessage;
		} else {
			return $this->getTrxnError();
		}
	}

	function doPayment() {
		$xmlRequest = "<ewaygateway>".
			"<ewayCustomerID>".htmlentities( $this->myCustomerID )."</ewayCustomerID>".
			"<ewayTotalAmount>".htmlentities( $this->myTotalAmount)."</ewayTotalAmount>".
			"<ewayCustomerFirstName>".htmlspecialchars( $this->myCustomerFirstname , ENT_QUOTES, 'UTF-8')."</ewayCustomerFirstName>".
			"<ewayCustomerLastName>".htmlspecialchars( $this->myCustomerLastname, ENT_QUOTES, 'UTF-8' )."</ewayCustomerLastName>".
			"<ewayCustomerEmail>".htmlspecialchars( $this->myCustomerEmail, ENT_QUOTES, 'UTF-8' )."</ewayCustomerEmail>".
			"<ewayCustomerAddress>".htmlspecialchars( $this->myCustomerAddress, ENT_QUOTES, 'UTF-8' )."</ewayCustomerAddress>".
			"<ewayCustomerPostcode>".htmlspecialchars( $this->myCustomerPostcode , ENT_QUOTES, 'UTF-8')."</ewayCustomerPostcode>".
			"<ewayCustomerInvoiceDescription>".htmlspecialchars( $this->myCustomerInvoiceDescription, ENT_QUOTES, 'UTF-8' )."</ewayCustomerInvoiceDescription>".
			"<ewayCustomerInvoiceRef>".htmlentities( $this->myCustomerInvoiceRef )."</ewayCustomerInvoiceRef>".
			"<ewayCardHoldersName>".htmlspecialchars( $this->myCardHoldersName, ENT_QUOTES, 'UTF-8' )."</ewayCardHoldersName>".
			"<ewayCardNumber>".htmlentities( $this->myCardNumber )."</ewayCardNumber>".
			"<ewayCardExpiryMonth>".htmlentities( $this->myCardExpiryMonth )."</ewayCardExpiryMonth>".
			"<ewayCardExpiryYear>".htmlentities( $this->myCardExpiryYear )."</ewayCardExpiryYear>".
			"<ewayTrxnNumber>".htmlentities( $this->myTrxnNumber )."</ewayTrxnNumber>".
			"<ewayOption1>".htmlentities( $this->myOption1 )."</ewayOption1>".
			"<ewayOption2>".htmlentities( $this->myOption2 )."</ewayOption2>".
			"<ewayOption3>".htmlentities( $this->myOption3 )."</ewayOption3>".
			"<ewayCVN>".htmlentities( $this->myCardCVN )."</ewayCVN>".
			"</ewaygateway>";


		$ch = curl_init( $this->myGatewayURL );
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt( $ch, CURLOPT_POST, 1 );
		curl_setopt( $ch, CURLOPT_POSTFIELDS, $xmlRequest );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt( $ch, CURLOPT_TIMEOUT, 240 );
		$xmlResponse = curl_exec( $ch );

		if( curl_errno( $ch ) == CURLE_OK ) {
			$this->parser = xml_parser_create();

			xml_parser_set_option ($this->parser, XML_OPTION_CASE_FOLDING, FALSE);

			xml_set_object($this->parser, $this);
			xml_set_element_handler ($this->parser, "epXmlElementStart", "epXmlElementEnd");
			xml_set_character_data_handler ($this->parser, "epXmlData");

			xml_parse($this->parser, $xmlResponse, TRUE);

			if( xml_get_error_code( $this->parser ) == XML_ERROR_NONE ) {
				$this->myResultTrxnStatus = @$this->xmlData['ewayTrxnStatus'];
				$this->myResultTrxnNumber = @$this->xmlData['ewayTrxnNumber'];
				$this->myResultTrxnOption1 = @$this->xmlData['ewayTrxnOption1'];
				$this->myResultTrxnOption2 = @$this->xmlData['ewayTrxnOption2'];
				$this->myResultTrxnOption3 = @$this->xmlData['ewayTrxnOption3'];
				$this->myResultTrxnReference = @$this->xmlData['ewayTrxnReference'];
				$this->myResultAuthCode = @$this->xmlData['ewayAuthCode'];
				$this->myResultReturnAmount = @$this->xmlData['ewayReturnAmount'];
				$this->myResultTrxnError = @$this->xmlData['ewayTrxnError'];
				$this->myError = 0;
				$this->myErrorMessage = '';
			} else {
				$this->myError = xml_get_error_code( $this->parser ) + EWAY_XML_ERROR_OFFSET;
				$this->myErrorMessage = xml_error_string( xml_get_error_code( $this->parser ) );
			}
			xml_parser_free( $this->parser );
		} else {
			$this->myError = curl_errno( $ch ) + EWAY_CURL_ERROR_OFFSET;
			$this->myErrorMessage = curl_error( $ch );
		}
		curl_close( $ch );
		return $this->getError();
	}
}
