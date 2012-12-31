<?php
// We are a valid Joomla entry point.
define('_JEXEC', 1);

// Setup the base path related constant.
define('JPATH_BASE', dirname(__FILE__));

// Maximise error reporting.
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Bootstrap the application.
require dirname(__FILE__).'/tests/bootstrap.php';

class OsmApp extends JApplicationWeb
{
	/**
	 * Display the application.
	 */
	function doExecute(){
		$key = "8DVVqjUxRiruXsMYZegClYbWODIMoKZxLl8w9pXR";
		$secret = "HvZkpFOsyq9oD7GEHYASyRgzKasRAMsvQ53Qb483";
// 		$key = "9o2s3leTWNqLhg4NvBguBR2fT4M8Q7nYSORwRP0W";
// 		$secret = "ZJrLaPyu3gANBDVH5v2x5Heiw04t4cybquFvNTSb";

		$option = new JRegistry;
		$option->set('consumer_key', $key);
		$option->set('consumer_secret', $secret);
		$option->set('sendheaders', true);

		$oauth = new JOpenstreetmapOauth($option);

		
		//$access_token = array('key' => '617544537-uMhDHjkCPGbgsb8NASkyWOfQj6wkIGWNjtZOIxDX', 'secret' => 'x9VpWp0tGK7q7lIlTyij7c0kfpRKWEWNJo2daPqHU8');
		//$oauth->setToken($access_token);
		
	//	$new_token = $oauth->authenticate();
		
//  		$oauth->setToken($new_token);
		
		$oauth->authenticate();
 		$osm=new JOpenstreetmap($oauth);
// 		$osm=new JOpenstreetmap();
		$changeset= $osm ->changesets;
 		//$result = $changeset -> readChangeset($oauth, '14153877');
		
		//print_r($result);
		//echo '<br />';
		
		$element=$osm->elements;
		//$result= $element->createNode($oauth, '1', '34', '54', array("A"=>"Apple","B"=>"Ball"));
		
		$gps=$osm->gps;
		
		$changesets = array
		(
// 				array
// 				(
// 						"A"=>"Apple",
// 						"B"=>"Ball",
// 						"c"=>"Call"JTwitterPlaces
// 				),
// 				array
// 				(
// 						"B"=>"Ball"
// 				),
				array
				(
						"comment"=>"my changeset comment",
						"created_by"=>"JOSM/1.0 (5581 en)"
				),
				array
				(
						"A"=>"Apple",
						"F"=>"Apple",
						"B"=>"Ball"
				) 
		);
		
// 		$result = $changeset ->createChangeset($changesets);
// 		$result = $changeset ->updateChangeset($oauth, '14153877',array("C"=>"Cat","D"=>"Dogs"));
		//$result = $changeset ->closeChangeset($oauth, '14153877');
		//$result = $changeset -> readChangeset($oauth, '14153877');
// 		$result=$element->createNode($oauth, '14153708', '5.5', '6.7', array("C"=>"Cat","D"=>"Dogs"));
// 		print_r($result);
		echo '$$$$<br />';
//  		$result = $changeset -> readChangeset('10');
 		print_r($result);
		echo '<br />';
// 		$result=$element->readElement($oauth, 'node', '2050021859');
 		$result=$element->readElement('node', 123);
 		print_r($result);
		
		//$result = $changeset ->updateChangeset($oauth, '1',$tags);
		
// 		print_r($result);
// 		echo '<br />';
		
// 		$node_list=array(array(4,5),array(6,7));
// 		$result = $changeset ->expandBBoxChangeset($oauth,'1',$node_list);
// 		print_r('DDDDPPPPP');
		//print_r($new_token);
		$result =$gps->downloadTraceMetadetails('1370260','bswije','buddhima');
		print_r($result);

	}
}

$web = JApplicationWeb::getInstance('OsmApp');
JFactory::$application = $web;

$session = JFactory::getSession();
if($session->isActive() == false){
	$session->initialise(JFactory::getApplication()->input);
	$session->start();
}

// Run the application
$web->execute();