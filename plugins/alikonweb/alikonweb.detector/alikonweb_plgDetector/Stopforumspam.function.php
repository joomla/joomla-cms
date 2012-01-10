<?php
/*06/06/2010 10.12 */
function checkSpambots($mail,$ip){

	$spambot = array('status' => 'ok','text' => JText::_( 'Not found' ),'score' => 0);

	//check the e-mail adress
	//   $xml_string = @file_get_contents('http://www.stopforumspam.com/api?email='.$mail);

	//e-mail not found in the database, now check the ip
	$xml_string = @file_get_contents('http://www.stopforumspam.com/api?ip='.$ip);

	if( $xml_string != '' ) {
		$xml = @new SimpleXMLElement($xml_string);
		if( $xml->appears == 'yes' ){
			$spambot = array('status' => 'ok','text' => JText::_( 'Stopforumspam:Found in database ' ).$xml->frequency.JText::_( ' times' ),'score' => 9);
		}
	}

    return $spambot;
}
#----spamreport  end------------------------------------------------------------------------------#
?>