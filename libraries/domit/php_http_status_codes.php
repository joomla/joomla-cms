<?php
//*******************************************************************
//php_http_status_codes list the http static codes as constants
//*******************************************************************
//by John Heinstein
//johnkarl@nbnet.nb.ca
//*******************************************************************
//Version 0.1
//copyright 2004 Engage Interactive
//http://www.engageinteractive.com/dom_xmlrpc/
//All rights reserved
//*******************************************************************
//Licensed under the GNU General Public License (GPL)
//http://www.gnu.org/copyleft/gpl.html
//*******************************************************************
class php_http_status_codes {
	var $codes;

	function php_http_status_codes() {
		$this->codes = array(
			200 => 'OK',
			201 => 'CREATED',
			202 => 'Accepted',
			203 => 'Partial Information',
			204 => 'No Response',
			301 => 'Moved',
			302 => 'Found',
			303 => 'Method',
			304 => 'Not Modified',
			400 => 'Bad Request',
			401 => 'Unauthorized',
			402 => 'PaymentRequired',
			403 => 'Forbidden',
			404 => 'Not found',
			500 => 'Internal Error',
			501 => 'Not implemented',
			502 => 'Service temporarily overloaded',
			503 => 'Gateway timeout');
	} //php_http_status_codes

	function getCodes() {
		return $this->codes;
	} //getCodes

	function getCodeString($code) {
		return $this->codes[$code];
	} //getCodeString
} //class php_http_status_codes

?>