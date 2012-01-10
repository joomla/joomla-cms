<?php
//Get IP location from ipinfodb.com
//06/06/2010 10.11
function ipLocation($ip,$apikey){
	$d = @file_get_contents("http://api.ipinfodb.com/v2/ip_query.php?key=".$apikey."&ip=".$ip);
$response=array('status' => 'ko', 'latitude' => '0', 'longitude' => '0', 'zippostalcode' => '', 'city' => 'unknown', 'region_name' => '', 'country_name' => 'Unknown', 'country_code' => 'UN', 'ip' => $ip);		   
	//Use backup server if cannot make a connection
	if (!$d){
	
       // $backup = @file_get_contents("http://backup.ipinfodb.com/ip_query.php?ip=$ip&output=xml");
       // if( !$backup ) {
        	//return false; // Failed to open connection
			return array('status' => 'ko', 'latitude' => '0', 'longitude' => '0', 'zippostalcode' => '', 'city' => 'unknown', 'region_name' => '', 'country_name' => 'Unknown', 'country_code' => 'UN', 'ip' => $ip);		   
       //	}
       // $answer = new SimpleXMLElement( $backup );
	}else{
		$answer = new SimpleXMLElement( $d );
		if ($answer->Status=='INVALID API KEY'){
			return array('status' => strtolower($answer->Status), 'latitude' => '0', 'longitude' => '0', 'zippostalcode' => '', 'city' => 'INVALID API KEY', 'region_name' => '', 'country_name' => 'Unknown', 'country_code' => 'UN', 'ip' => $ip);		   
		}	
		if ($answer->Status=='OK'){
		   $country_code	  = $answer->CountryCode ;
	       $country_name	  = $answer->CountryName ;
	       $region_name	  = $answer->RegionName ;
	       $city			      = $answer->City ;
	       $zippostalcode	= $answer->ZipPostalCode ;
	       $latitude	    	= $answer->Latitude ;
	       $longitude		  = $answer->Longitude;
	  // } else   {  
	   		
		}   

	//Return the data as an array
    return array('status' => strtolower($answer->Status), 'latitude' => $latitude, 'longitude' => $longitude, 'zippostalcode' => $zippostalcode, 'city' => $city, 'region_name' => $region_name, 'country_name' => $country_name, 'country_code' => $country_code, 'ip' => $ip);
	}
 
/*
	$country_code	= utf8_decode( $answer->CountryCode );
	$country_name	= utf8_decode( $answer->CountryName );
	$region_name	= utf8_decode( $answer->RegionName );
	$city			= utf8_decode( $answer->City );
	$zippostalcode	= utf8_decode( $answer->ZipPostalCode );
	$latitude		= utf8_decode( $answer->Latitude );
	$longitude		= utf8_decode( $answer->Longitude );

    $country_code	  = $answer->CountryCode ;
	$country_name	  = $answer->CountryName ;
	$region_name	  = $answer->RegionName ;
	$city			      = $answer->City ;
	$zippostalcode	= $answer->ZipPostalCode ;
	$latitude	    	= $answer->Latitude ;
	$longitude		  = $answer->Longitude;
*/
	//Return the data as an array
   // return array('status' => strtolower($answer->Status), 'latitude' => $latitude, 'longitude' => $longitude, 'zippostalcode' => $zippostalcode, 'city' => $city, 'region_name' => $region_name, 'country_name' => $country_name, 'country_code' => $country_code, 'ip' => $ip);
   return $response;
}
?>