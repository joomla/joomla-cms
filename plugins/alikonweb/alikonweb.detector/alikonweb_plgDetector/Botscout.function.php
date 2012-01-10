<?php
function http_botscout($pip,$papi){
	//06/06/2010 10.11
	//adapted from
	/////////////////////////////////////////////////////
	// sample API code for use with the BotScout.com API
	// code by MrMike / version 2.0 / LDM 2-2009
	/////////////////////////////////////////////////////

	/////////////////// START CONFIGURATION ////////////////////////
	// use diagnostic output? ('1' to use, '0' to suppress)
	// (normally set to '0')
	$diag		= '0';
	////////////////////////
	// init vars
	$data		= '';
	$botdata	= '';
	$APIKEY		= '';
	$USEXML		= '';
	$XMAIL		= '';
	$XIP		= '';
	$multi_test	= '';
	$ch			= '';
	$botdata	= '';
	////////////////////////
	// your optional API key (don't have one? get one here: http://botscout.com/
	$APIKEY		= $papi;
	////////////////////////
	// use XML output responses?
	// '1' to use XML, '0' to use standard responses
	$USEXML		= '0';
	/////////////////// END CONFIGURATION ////////////////////////

	$response	= '';
	////////////////////////
	//$XIP		= getRealIpAddr();
  $XIP		= $pip;
	// make the url compliant with urlencode()
	$XMAIL		= urlencode($XMAIL);

	// for this example we'll use the MULTI test
	$test_string = "http://botscout.com/test/?ip=$XIP&key=$APIKEY"; // test IP - reliable

	////////////////////////
	// use file_get_contents() or cURL?
	// we'll user file_get_contents() unless it's not available
	if( function_exists('file_get_contents') ){
		// Use file_get_contents
		$data	= @file_get_contents($test_string);
	}elseif( function_exists( 'curl_init' ) ) {
		$ch		= curl_init($test_string);

		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

		$data	= curl_exec($ch);

		curl_close($ch);
	}else{
		// new mic
		return array('status' => 'ko','text' => JText::_( 'Server does not support wether fopen nor cURL!' ),'score' => 0);
		//return $response = JText::_( 'Server does not support wether fopen nor cURL!' );
		exit;
	}

	// diagnostic output
	if($diag=='1'){
		print "RETURNED DATA: $returned_data";
		// sanity check
		if($data=='') {
			print 'Error: No return data from API query.';
			exit;
		}
	}
	//echo 'returndata:'.$data.'<br/>';

	// take the returned value and parse it (standard API, not XML)
	$botdata = explode('|', $data);

	// sample 'MULTI' return string
	// Y|MULTI|IP|4|MAIL|26|NAME|30

	// $botdata[0] - 'Y' if found in database, 'N' if not found, '!' if an error occurred
	// $botdata[1] - type of test (will be 'MAIL', 'IP', 'NAME', or 'MULTI')
	// $botdata[2] - descriptor field for item (IP)
	// $botdata[3] - how many times the IP was found in the database
	// $botdata[4] - descriptor field for item (MAIL)
	// $botdata[5] - how many times the EMAIL was found in the database
	// $botdata[6] - descriptor field for item (NAME)
	// $botdata[7] - how many times the NAME was found in the database
//jexit('uno'. $botdata[1].'due'. $botdata[2].'times'. $botdata[3]);
	if(substr($data, 0,1) == '!'){
		// if the first character is an exclamation mark, an error has occurred
		$response = array('status' => 'ko','text' => JText::_( 'Got no info' ),'score' => 0);

	}else{
		if($botdata[0] =='Y'){
			$response = array('status' => 'ok','text' => JText::sprintf( 'Found in database %s times',  $botdata[2] ),'score' => 8);
		} else {
			$response = array('status' => 'ok','text' => JText::_( 'Not found in database' ),'score' => 0);
		}
	}

	return $response;
}
?>

