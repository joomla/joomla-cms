<?php
/**
* Embedd a GoogleMap on Joomla! components or retrieve visitor location
* @author: Alikon
* @version: 1.5.0
* @release: 24/09/2010 14.54
* @package: Alikonweb.googlemap
* @copyright: (C) 2007-2010 Alikonweb.it
* @license: http://www.gnu.org/copyleft/gpl.html GNU/GPL
*
*
*
**/
#-NO DIRECT ACCESS--------------------------------------------------------------------------------#
defined( '_JEXEC' ) or die( 'Restricted access' );
jimport( 'joomla.plugin.plugin' );
$app = JFactory::getApplication();
$app->registerEvent( 'onStreetView', 'plgGmap_Streetview_show' );
$app->registerEvent( 'onDetect', 'plgGmap_detect' );
$app->registerEvent( 'onXml', 'plgGmap_xml' );
$app->registerEvent( 'onShowMap', 'plgGmap_show' );
$app->registerEvent( 'onShowDirections', 'plgGmap_directions' );
$app->registerEvent( 'onShowAnimDirections', 'plgGmap_anim_directions' );
$app->registerEvent( 'onMap', 'plgGmap' );
function rnd_mapname(){
$aa='';
  for ($i=0; $i<6; $i++) {
    $d=rand(1,30)%2;
	$aa.=chr(rand(65,90)) ;   
  } 
  return  $aa ;
}
// code.google.com/apis/maps
function plgGmap_Streetview_show( $mode,$testo,$indirizzo, $zoomlevel,$width,$eight,$lat,$lon ) {
$code='';
$msg1='No results found';
$msg2='Geocoder failed due to:';
$document =& JFactory::getDocument();
$document->setMetaData( 'viewport', 'initial-scale=1.0, user-scalable=no' );
$js = "http://maps.google.com/maps/api/js?sensor=false";

$document->addScript($js);
//JHTML::script('show_address3.js', $link_url, true);
$url='<br /><!-- inizio alikonweb GoogleMaps v.3 plugin for joomla --><br />';
$lat = 41.89610448989056;
$long =12.4858;
$zoom = $zoomlevel;
$mapName=rnd_mapname();
$mapType ='ROADMAP';
$js = "http://maps.google.com/maps/api/js?sensor=false";

$document->addScript($js);
$mapOptions = '';
$markerOptions = '';

$navControls = true;
/*
if($params->get('navControls', false) == 0){
	$mapOptions .= ',disableDefaultUI: false'. PHP_EOL;
	$navControls = false;	
}

if($params->get('smallmap')){
	$mapOptions .=  ', navigationControlOptions: {style: google.maps.NavigationControlStyle.SMALL} ' . PHP_EOL;
	$navControls = true;
}
*/
if(!$navControls)
	$mapOptions .= ',navigationControl: false' . PHP_EOL;
	
/*	
if($params->get('static')){
	$mapOptions .= 	', draggable: false' .PHP_EOL;
}
*/
$mapTypeControl = 'true' ;

$mapOptions .= ",mapTypeControl: {$mapTypeControl}". PHP_EOL;
$mapOptions .= ",streetViewControl: true". PHP_EOL;
$script =<<<EOL
	google.maps.event.addDomListener(window, 'load', {$mapName}load);
	 var {$mapName};
	 var geocoder;
	  var roma = new google.maps.LatLng(41.126001,16.8683788);
	 var posizione=null;
    function {$mapName}load() {
    geocoder = new google.maps.Geocoder();	
		{$mapName}codeAddress();	
		
     }
    function {$mapName}codeAddress() {
    var address = document.getElementById("{$mapName}address").value;
    geocoder.geocode( { 'address': address}, function(results, status) {
      if (status == google.maps.GeocoderStatus.OK) {       
      	latti=results[0].geometry.location.lat();
      	lonni=results[0].geometry.location.lng()
		    var nome=document.getElementById("{$mapName}nomecontatto").value		;
		    //document.getElementById("lati").value=parseFloat(latti);
		    //document.getElementById("loni").value=parseFloat(lonni);
		    //posizione=new google.maps.LatLng(latti,lonni);
		     var panoOptions = {
          position: new google.maps.LatLng(latti,lonni),
          addressControlOptions: {
           position: google.maps.ControlPosition.BOTTOM,         
          },
         linksControl: true,
         navigationControlOptions: {
         style: google.maps.NavigationControlStyle.SMALL
        },
       enableCloseButton: false
       };
       var panorama = new google.maps.StreetViewPanorama(
      document.getElementById("{$mapName}pano"), panoOptions); 
		   // alert(latti);
      } else {
        alert("Geocode was not successful for the following reason: " + status);
      }
    });
  } 
EOL;

JHTML::_('behavior.mootools');

$document->addScriptDeclaration($script);
//----------------------------------------//
$url.='<div>
 <div id="'.$mapName.'pano" style="width: '.$width.'px; height: '.$eight.'px"></div> 
    <input id="'.$mapName.'address" type="hidden" value="'.$indirizzo.'">
    <input id="zoomlevel" type="hidden" value="'.$zoomlevel.'" >
  
	<input id="'.$mapName.'nomecontatto" type="hidden" value="'.$testo.'" >
  </div>';		
$url.='<br /><!-- fine alikonweb GoogleMaps plugin for joomla --><br />';
return $url;
}

//
function plgGmap_xml( $mode,$filexml,$indirizzo, $zoomlevel,$width,$eight,$lat,$lon ) {
$code='';
$mapName=rnd_mapname();
$msg1='No results found';
$msg2='Geocoder failed due to:';
$js = "http://maps.google.com/maps/api/js?sensor=false";
$document =& JFactory::getDocument();
$document->addScript($js);
$filexml=juri::base().$filexml;
//-------------------------------------------------
$script =<<<EOL
google.maps.event.addDomListener(window, 'load', {$mapName}initialize);
      // this variable will collect the html which will eventually be placed in the side_bar 
      var {$mapName}side_bar_html = ""; 
    
      // arrays to hold copies of the markers and html used by the side_bar 
      // because the function closure trick doesnt work there 
      var {$mapName}gmarkers = []; 

     // global "map" variable
      var {$mapName} = null;
	  var {$mapName}infowindow =null;
// A function to create the marker and set up the event window function 
function {$mapName}createMarker(latlng, name, html,city) {
    var contentString = 'Name:'+name+'<br/>Nation:'+html+'<br/>City:'+city;
    var marker = new google.maps.Marker({
        position: latlng,
        map: {$mapName},
       // icon: 'http://labs.google.com/ridefinder/images/mm_20_blue.png',
        zIndex: Math.round(latlng.lat()*-100000)<<5
        });

    google.maps.event.addListener(marker, 'click', function() {
        {$mapName}infowindow.setContent(contentString); 
        {$mapName}infowindow.open({$mapName},marker);
        });
    // save the info we need to use later for the side_bar
    {$mapName}gmarkers.push(marker);
    // add a line to the side_bar html
    {$mapName}side_bar_html += '<a href="javascript:{$mapName}myclick(' + ({$mapName}gmarkers.length-1) + ')" title="From:'+ html +'">' + name + '<\/a><br>';
}
 
// This function picks up the click and opens the corresponding info window
function {$mapName}myclick(i) {
  google.maps.event.trigger({$mapName}gmarkers[i], "click");
}

function {$mapName}initialize() {
  // create the map
  var myOptions = {
    zoom: parseInt({$zoomlevel}),
    center: new google.maps.LatLng(parseFloat({$lat}),parseFloat({$lon})),
    mapTypeControl: true,
    mapTypeControlOptions: {style: google.maps.MapTypeControlStyle.DROPDOWN_MENU},
    navigationControl: true,
    mapTypeId: google.maps.MapTypeId.ROADMAP
  }
  {$mapName} = new google.maps.Map(document.getElementById("{$mapName}"),
                                myOptions);
 
  google.maps.event.addListener({$mapName}, 'click', function() {
        {$mapName}infowindow.close();
        });
      // Read the data from example.xml
      {$mapName}downloadUrl('{$filexml}', function(doc) {
        var xmlDoc = xmlParse(doc);
        var markers = xmlDoc.documentElement.getElementsByTagName("marker");
        for (var i = 0; i < markers.length; i++) {
          // obtain the attribues of each marker
          var lat = parseFloat(markers[i].getAttribute("lat"));
          var lng = parseFloat(markers[i].getAttribute("lng"));
          var point = new google.maps.LatLng(lat,lng);
          var itemlink = markers[i].getAttribute("username");
         // var from = html.indexOf("City:");
         // var to = html.indexOf("<br />",from);
          var city=markers[i].getAttribute("city")
           var html=markers[i].getAttribute("html")
          
          // create the marker
          var marker = {$mapName}createMarker(point,itemlink,html,city);
        }
        // put the assembled side_bar_html contents into the side_bar div
        document.getElementById("{$mapName}side_bar").innerHTML = {$mapName}side_bar_html;
      });
{$mapName}infowindow = new google.maps.InfoWindow(
   { 
    size: new google.maps.Size(150,50)
   }); 
    }
 

    
/**
* Returns an XMLHttp instance to use for asynchronous
* downloading. This method will never throw an exception, but will
* return NULL if the browser does not support XmlHttp for any reason.
* @return {XMLHttpRequest|Null}
*/
function createXmlHttpRequest() {
 try {
   if (typeof ActiveXObject != 'undefined') {
     return new ActiveXObject('Microsoft.XMLHTTP');
   } else if (window["XMLHttpRequest"]) {
     return new XMLHttpRequest();
   }
 } catch (e) {
   changeStatus(e);
 }
 return null;
};

/**
* This functions wraps XMLHttpRequest open/send function.
* It lets you specify a URL and will call the callback if
* it gets a status code of 200.
* @param {String} url The URL to retrieve
* @param {Function} callback The function to call once retrieved.
*/
function {$mapName}downloadUrl(url, callback) {
 var status = -1;
 var request = createXmlHttpRequest();
 if (!request) {
   return false;
 }

 request.onreadystatechange = function() {
   if (request.readyState == 4) {
     try {
       status = request.status;
     } catch (e) {
       // Usually indicates request timed out in FF.
     }
     if ((status == 200) || (status == 0)) {
       callback(request.responseText, request.status);
       request.onreadystatechange = function() {};
     }
   }
 }
 request.open('GET', url, true);
 try {
   request.send(null);
 } catch (e) {
   changeStatus(e);
 }
};

/**
 * Parses the given XML string and returns the parsed document in a
 * DOM data structure. This function will return an empty DOM node if
 * XML parsing is not supported in this browser.
 * @param {string} str XML string.
 * @return {Element|Document} DOM.
 */
function xmlParse(str) {
  if (typeof ActiveXObject != 'undefined' && typeof GetObject != 'undefined') {
    var doc = new ActiveXObject('Microsoft.XMLDOM');
    doc.loadXML(str);
    return doc;
  }

  if (typeof DOMParser != 'undefined') {
    return (new DOMParser()).parseFromString(str, 'text/xml');
  }

  return createElement('div', null);
}

/**
 * Appends a JavaScript file to the page.
 * @param {string} url
 */
function downloadScript(url) {
  var script = document.createElement('script');
  script.src = url;
  document.body.appendChild(script);
}

    // This Javascript is based on code provided by the
    // Community Church Javascript Team
    // http://www.bisphamchurch.org.uk/   
    // http://econym.org.uk/gmap/
    // from the v2 tutorial page at:
    // http://econym.org.uk/gmap/basic3.htm 
	
EOL;

JHTML::_('behavior.mootools');

$document->addScriptDeclaration($script);

//--------------------------------------------------
$url='<br /><!-- inizio alikonweb GoogleMaps v.3 plugin for joomla --><br />';

$url.='
<!-- you can use tables or divs for the overall layout --> 
           <div>    
           <div id="'.$mapName.'" style="margin:5px 20px 5px 5px; float: left;  width: '.$width.'px; height: '.$eight.'px"></div>         
           List:';
		   
$url.='<div id="'.$mapName.'side_bar" style="width:170px; height: '.$eight.'px; overflow:auto;"></div> 
           </div>
        ';
 

$url.='<br /><!-- fine alikonweb GoogleMaps plugin for joomla --><br />';
return $url;
}
//
function plgGmap_xml0( $mode,$testo,$indirizzo, $zoomlevel,$width,$eight,$lat,$lon ) {
$code='';
$mappa='acmapxml';
$msg1='No results found';
$msg2='Geocoder failed due to:';
$document =& JFactory::getDocument();
$document->addCustomTag('<script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=false"></script>');
$link_url = JURI::base().'plugins/alikonweb/alikonweb_plgAgmap/';
JHTML::script('show_xml.js', $link_url, true);
$url='<br /><!-- inizio alikonweb GoogleMaps v.3 plugin for joomla --><br />';
$js = "window.addEvent('domready', function(){ initializex('".$lat."','".$lon."','".$mappa."','".$zoomlevel."','".$testo."'); })";
 $document->addScriptDeclaration($js);		
//$url.='<div id="'.$mappa.'" style="width: '.$width.'px; height: '.$eight.'px"></div>';			
$url.='
<!-- you can use tables or divs for the overall layout --> 
           <div>    
           <div id="'.$mappa.'" style="margin:5px 20px 5px 5px; float: left;  width: '.$width.'px; height: '.$eight.'px"></div>         
           Members:
           <div id="side_bar" style="width:170px; height: '.$eight.'px; overflow:auto;"></div> 
           </div>
        ';
 

$url.='<br /><!-- fine alikonweb GoogleMaps plugin for joomla --><br />';
return $url;
}

function plgGmap_detect( $lat,$lon, $zoomlevel,$width,$eight ) {
$code='';
$mappa='acmap';
$msg1='No results found';
$msg2='Geocoder failed due to:';
$document =& JFactory::getDocument();
$document->addCustomTag('<script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=false&language=en"></script>');

$link_url = JURI::base().'plugins/alikonweb/alikonweb_plgAgmap/';
//JHTML::script('show_detect.js', $link_url, true);
$url='<br /><!-- inizio alikonweb GoogleMaps v.3 plugin for joomla --><br />';
$js = "window.addEvent('domready', function(){ initialize('".$lat."','".$lon."','".$mappa."','".$zoomlevel."'); })";
 $document->addScriptDeclaration($js);		
$url.='<div id="'.$mappa.'" style="width: '.$width.'px; height: '.$eight.'px"></div>';			
$url.='<br /><!-- fine alikonweb GoogleMaps plugin for joomla --><br />';
return $url;
}
function plgGmap_anim_directions( $mode,$testo,$indirizzo, $zoomlevel,$width,$eight,$lat,$lon ) {
$code='';
$mappa='acmap';
$msg1='No results found';
$msg2='Geocoder failed due to:';
$document =& JFactory::getDocument();
$document->addCustomTag('<script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=false"></script>');
$link_url = JURI::base().'plugins/alikonweb/alikonweb_plgAgmap/';
JHTML::script('show_anim_directions.js', $link_url, true);
$url='<br /><!-- inizio alikonweb GoogleMaps v.3 plugin for joomla --><br />';
$js = "window.addEvent('domready', function(){ initialize('".$indirizzo."','".$mappa."','".$zoomlevel."'); })";
 $document->addScriptDeclaration($js);		
$url.='<div id="'.$mappa.'" style="width: '.$width.'px; height: '.$eight.'px"></div>';			
$url.='<br /><!-- fine alikonweb GoogleMaps plugin for joomla --><br />';
return $url;
}
function plgGmap_directions( $mode,$testo,$indirizzo, $zoomlevel,$width,$eight,$lat,$lon,$base) {
		// Get plugin info
	$plugin =& JPluginHelper::getPlugin('alikonweb', 'alikonweb.googlemap');
	$params = new JRegistry;
	 	//$pluginParams = new JParameter( $plugin->params );
	 	if(!$base){
	 	//$base=$pluginParams->get( 'base', 'bari' );
	 	$base=$params->get( 'base', 'bari' );
	 	}
$code='';
$mappa='acmap';
$msg1='No results found';
$msg2='Geocoder failed due to:';
$document =& JFactory::getDocument();
$document->addCustomTag('<script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=false"></script>');
$document->setMetaData( 'viewport', 'initial-scale=1.0, user-scalable=no' );
$link_url = JURI::base().'plugins/alikonweb/alikonweb.googlemap/alikonweb_plgAgmap/';
JHTML::script('show_directions2.js', $link_url, true);
$url='<!-- inizio alikonweb GoogleMaps v.3 plugin for joomla -->';
//$js = "window.addEvent('load', function(){ initialize('".$indirizzo."','".$mappa."',".$zoomlevel."); })";
$js = "window.addEvent('load', function(){ initialize() })";
 $document->addScriptDeclaration($js);		
//$url.='<div><form action="#">';
$url.='<div>';
$url.='Start from:<input id="partenza" type="textbox" name="partenza" value="'.$base.'">';
$url.='<input id="arrivo" type="hidden" value="'.$indirizzo.'">';
$url.='<input type="button" value="Directions" onclick="calcRoute()">';

$url.='</div>';
$url.='<div>';
$url.='<div id="'.$mappa.'" style=" float:left; width: '.$width.'px; height: '.$eight.'px"></div>';			
$url.='<div id="directionsPanel" style="width: '.$width.'px; height: '.$eight.'px; overflow:auto;"></div>';
$url.='</div>';
$url.='<!-- fine alikonweb GoogleMaps plugin for joomla -->';
return $url;
}

function plgGmap_show( $mode,$testo,$indirizzo, $zoomlevel,$width,$eight,$lat,$long ) {
$code='';


$msg1='No results found';
$msg2='Geocoder failed due to:';
$document =& JFactory::getDocument();
$document->setMetaData( 'viewport', 'initial-scale=1.0, user-scalable=no' );
//$document->addStyleDeclaration( 'html { height: 100% } body { height: 100%; margin: 0px; padding: 0px } #acmap { width: 480px; height: 320px }' , 'text/css' );
//$document->addCustomTag('<script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=false"></script>');
$js = "http://maps.google.com/maps/api/js?sensor=false";

$document->addScript($js);
//$document->addCustomTag('<script src="http://www.google.com/jsapi" type="text/javascript"></script>');
//$link_url = JURI::base().'plugins/alikonweb/alikonweb_plgAgmap/';
//JHTML::script('show_address3.js', $link_url, true);
$url='<br /><!-- inizio alikonweb GoogleMaps v.3 plugin for joomla --><br />';
//$js = "window.addEvent('load', function(){ initialize('".$indirizzo."','".$mappa."',".$zoomlevel."); })";
//$js = "window.addEvent('load', function(){ initialize(); })";
//$document->addScriptDeclaration($js);		
//$url.='<div id="'.$mappa.'" style="width: '.$width.'px; height: '.$eight.'px"></div>';			
//----------------------------------------//
//$width =160;
//$height = 120;
//$lat = 41.89610448989056;
//$long =12.4858;
$zoom = $zoomlevel;
$mapName=rnd_mapname();
$mapType ='ROADMAP';
$js = "http://maps.google.com/maps/api/js?sensor=false";

$document->addScript($js);
$mapOptions = '';
$markerOptions = '';
/*
if(true){
	$title = 'titolo';

	$markerOptions =<<<EOL
	
	var opts = new Object;
	opts.title = "{$title}";
	opts.position = {$mapName}.getCenter();
	opts.map = $mapName;
	marker = new google.maps.Marker(opts);
EOL;
}
*/
$navControls = true;
/*
if($params->get('navControls', false) == 0){
	$mapOptions .= ',disableDefaultUI: false'. PHP_EOL;
	$navControls = false;	
}

if($params->get('smallmap')){
	$mapOptions .=  ', navigationControlOptions: {style: google.maps.NavigationControlStyle.SMALL} ' . PHP_EOL;
	$navControls = true;
}
*/
if(!$navControls)
	$mapOptions .= ',navigationControl: false' . PHP_EOL;
	
/*	
if($params->get('static')){
	$mapOptions .= 	', draggable: false' .PHP_EOL;
}
*/
$mapTypeControl = 'true' ;

$mapOptions .= ",mapTypeControl: {$mapTypeControl}". PHP_EOL;
$mapOptions .= ",streetViewControl: true". PHP_EOL;
$script =<<<EOL
	google.maps.event.addDomListener(window, 'load', {$mapName}load);
	 var {$mapName};
	 var geocoder;
    function {$mapName}load() {
    geocoder = new google.maps.Geocoder();	
		var options = {
			zoom : {$zoomlevel},
			center: new google.maps.LatLng({$lat}, {$long}),
			mapTypeId: google.maps.MapTypeId.{$mapType}
			{$mapOptions}
		}
		
       {$mapName} = new google.maps.Map(document.getElementById("{$mapName}"), options);
		{$markerOptions}	
		{$mapName}codeAddress();	
    }
     
     
    function {$mapName}codeAddress() {
    var address = document.getElementById("{$mapName}address").value;
    geocoder.geocode( { 'address': address}, function(results, status) {
      if (status == google.maps.GeocoderStatus.OK) {
        {$mapName}.setCenter(results[0].geometry.location);
        var marker = new google.maps.Marker({
            map: {$mapName}, 
            position: results[0].geometry.location
        });
		 var nome=document.getElementById("{$mapName}nomecontatto").value
		 var infoWindow = new google.maps.InfoWindow(
		 /*
                { content: '<b>'+nome+'</b>&nbsp;'+results[0].geometry.location,
                  size: new google.maps.Size(250,250),
				  position:{$mapName}.getCenter()
		 */		
                 { content: '<b>'+nome+'</b>&nbsp;'+results[0].geometry.location,
                  size: new google.maps.Size(250,250),
				  position:{$mapName}.getCenter()		 

         });
		 infoWindow.open({$mapName});
      } else {
        alert("Geocode was not successful for the following reason: " + status);
      }
    });
  } 
EOL;

JHTML::_('behavior.mootools');

$document->addScriptDeclaration($script);
//----------------------------------------//

$url.='<div id="'.$mapName.'" style="float:left; width: '.$width.'px; height: '.$eight.'px"></div>';			
$url.='<div style="float:clear;">
    <input id="'.$mapName.'address" type="hidden" value="'.$indirizzo.'">
    <input id="zoomlevel" type="hidden" value="'.$zoomlevel.'" >
	<input id="'.$mapName.'nomecontatto" type="hidden" value="'.$testo.'" >
  </div>';
$url.='<br /><!-- fine alikonweb GoogleMaps plugin for joomla --><br />';
return $url;
}
function plgGmap_show0( $mode,$testo,$indirizzo, $zoomlevel,$width,$eight,$lat,$lon ) {
$code='';
$mappa='acmap';
$msg1='No results found';
$msg2='Geocoder failed due to:';
$document =& JFactory::getDocument();
$document->addCustomTag('<script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=true"></script>');
$link_url = JURI::base().'plugins/alikonweb/alikonweb_plgAgmap/';
JHTML::script('show_address2.js', $link_url, true);
$url='<br /><!-- inizio alikonweb GoogleMaps v.3 plugin for joomla --><br />';
$js = "window.addEvent('domready', function(){ initialize('".$indirizzo."','".$mappa."',".$zoomlevel."); })";
 $document->addScriptDeclaration($js);		
//$js = "window.addEvent('domready', function(){ codeAddress('".$indirizzo."'); })";
//$document->addScriptDeclaration($js);		
$url.='<div id="'.$mappa.'" style="width: '.$width.'px; height: '.$eight.'px"></div>';			
$url.='<br /><!-- fine alikonweb GoogleMaps plugin for joomla --><br />';
return $url;
}
function plgGmap( $mode,$testo,$indirizzo, $zoomlevel,$width,$eight,$lat,$lon ) {
JPlugin::loadLanguage( 'plg_alikonweb_alikonweb.googlemap',JPATH_ADMINISTRATOR );
$plugin =& JPluginHelper::getPlugin('alikonweb', 'alikonweb.googlemap');
$botParams = new JParameter( $plugin->params );
$gmap_api_key = $botParams->def('gmap_api_key', '');
$url='<br /><!-- alikonweb GoogleMaps plugin for joomla --><br />';
$document =& JFactory::getDocument();
$document->addCustomTag('<script src="http://maps.google.com/maps?file=api&amp;v=2&amp;key='.$gmap_api_key.'" type="text/javascript"></script>');
$link_url = JURI::base().'plugins/alikonweb/alikonweb_plgAgmap/';
//-------------
	switch ($mode)
		{			             
			case 'address':				
                JHTML::script('agmap.js', $link_url, true);
                $document =& JFactory::getDocument();
				$funzione='showAddress';
                $js = "window.addEvent('domready', function(){".$funzione."('".
                                                                $indirizzo.
                                                              "','".
                                                                $testo.
                                                              "',".
                                                                $zoomlevel.
                                                              "); })";
                $document->addScriptDeclaration($js);
                $url.='<div id="acmap" style="width: '.$width.'px; height: '.$eight.'px"></div>';
                $url.='<br /><!-- alikonweb GoogleMaps plugin for joomla--><br />';
				break;
			case 'coord':
				$func='usermap';
				$layout='default';
				break;
			case 'xml':
			    JHTML::script('agmap.js', $link_url, true);
                $document =& JFactory::getDocument();
				$funzione='mapxml';								
				$js = "window.addEvent('domready', function(){".$funzione."('".
                                                                $testo.
                                                              "','".
                                                                $lat.
															  "','".
                                                                $lon.	
                                                              "',".
                                                                $zoomlevel.
                                                              "); })";
				$document->addScriptDeclaration($js);
                $url.='<div id="acmap" style="width: '.$width.'px; height: '.$eight.'px"></div>';
                $url.='<br /><!-- alikonweb GoogleMaps plugin for joomla--><br />';
				//$par1=$matches;   //url			
				break;	
            case 'percorso':				
                JHTML::script('agmapper.js', $link_url, true);
                $document =& JFactory::getDocument();
				$funzione='showAddress2';
				$from='';
                $js = "window.addEvent('domready', function(){".$funzione."('".
                                                                $indirizzo.
                                                              "','".
                                                                $testo.
                                                              "',".
                                                                $zoomlevel.
															  ",'".
                                                                $from.	
                                                              "'); })";
                $document->addScriptDeclaration($js);
                $url.='<div id="location"> 
	                   <form action="#" onsubmit="setDirections(this.partenza.value,this.arrivo.value); return false"> 
	    	            Start from:<input id="partenza"type="text" name="partenza" value=""><input type="submit" value="show"> 
	    	            <input id="arrivo" name="arrivo" type="hidden" value="'.$indirizzo.'"> 
	                   </form> 
                      </div>';
	            $url.='      
                      <div id="acmap" style="width:'.$width.'px; height:'.$eight.'px"></div>       
                      <div id="directions" style="width:'.$width.'px; height:'.$eight.'px; overflow:auto; border:1px solid black"></div>';
                $url.='<br /><!-- alikonweb GoogleMaps plugin for joomla--><br />';
				break;				
		   case 'street':				
                JHTML::script('astreetview.js', $link_url, true);
                $document =& JFactory::getDocument();
				$funzione='load';
				$url.='
<style>

#content {
  width: 590px;
  padding: 8px;
  background-color: #eeeeff;
  border: 1px solid #000066;
}

#svPanel {
  width:588px;
  height: 300px;
  position: relative;
}

#status {
  position: absolute;
  top: 120px;
  left: 0px;
  width: 588px;
  text-align: center;
  font: 32pt sans-serif;
  color: #666666;
  background-color: white;
}

#instruction {
  position: absolute;
  top: 295px;
  left: 0px;
  width:588px;
  text-align: center;
  font: 16pt sans-serif;
  color: #eeeeee;
  display: none;
}

#svPanel, #directions, #map {
  border: 1px solid black;
  background-color: white;
}

#streetview {
  position: absolute;
  top: 0px;
  left: 0px;
  width: 588px;
  height: 300px;
}

#progressBorder {
  position: relative;
  width: 590px;
  height: 10px;
  margin: 2px 0px 2px 0px;
  border: 1px solid #000066;
  background-color: white;
  overflow: hidden;
}

#progressBar {
  position: absolute;
  background-color: #000066;
  width: 598px;
  height: 8px;
  top: 1px;
  right:1px;
}

#acmap {
  width: 270px;
  height: 317px;
  margin-right: 1px;
  overflow: hidden;
}

#directions {
  width: 270px;
  height: 400px;
  margin-left: 1px;
  position: relative;
  overflow: auto;
}

.waypoint {
  position: relative;
  background-color: #eeeeee;
  border: 1px solid #666666;
  padding: 6px;
  margin: 4px;
  font: 10pt sans-serif;
}

.letterIcon {
  width: 24px;
  height: 38px;
  background-image: none;
}

.waypointAddress {
  position: absolute;
  top: 17px;
  left: 32px;
}

#summary {
  padding: 4px;
  font: 10pt sans-serif;
}

.dstep {
  border-top: 1px solid #666666;
  padding: 4px;
  padding-left: 8px;
  font: 10pt sans-serif;
  margin-left: 4px;
  margin-right: 4px;
  cursor: pointer;
  background-color: white;
}

.label {
  width: 52px;
  text-align: right;
  font: 12pt sans-serif;
  float: left;
  position: relative;
  top: 4px;
  margin-right: 5px;
}

.input {
  float: left;
  width: 252px;
  text-align: left;
}

.controls {
 clear: both;
 padding: 4px;
}

#speed {
  float: left;
}

#buttons {
 float: right;
}

table {
  border-collapse: collapse;
}

td {
  vertical-align: top;
}

  </style>

<div id="content">
    <table cellpadding="0" cellspacing="0">
    
    <tr>
      <td>

        <div id="acmap"></div>
          <div class="controls">
            <div class="label">From</div>
            <div class="input"><input  id="from" size="30" value="via mazzini, 202,minervino murge, italy"/></div>
          </div>
          <div class="controls">
            <div class="label">To</div>
            <div class="input"><input type="hidden" id="to" size="30" value="'.$par1.'"/></div>
          </div>
          <div class="controls">
            <div class="label">Speed</div>
            <div id="actions">
              <select id="speed" onChange="setSpeed()">
                <option value="0">Fast</option>
                <option value="1" SELECTED>Medium</option>
                <option value="2">Slow</option>
              </select>
              <div id="buttons">
                <input type="button" value="Route" id="route" onClick="generateRoute()" />
                <input type="button" value="Drive" id="stopgo"  onclick="startDriving()"  disabled />
              </div>
            </div>
          </div>

        </div>
      </td>
      <td>
        <div id="directions"></div>
      </td>
    </tr>
    <tr>
      <td colspan="2">
        <div id="svPanel">
          <div id="streetview" style="width:590px; height: 319px;"></div>
          <div id="status">Enter your start and end addresses and click <b>Route</b></div>

          <div id="instruction"></div>
        </div>
        <div id="progressBorder">
          <div id="progressBar"></div>
        </div>
      </td>
    </tr>
  </table>
  </div>';
$url.='<!-- alikonweb GoogleMaps plugin for joomla-->';
$js = "window.addEvent('domready', function(){".$funzione."('".
//$js = "window.addEvent('domready', function(){usermap('".
                                                       $indirizzo.
                                                       "','".
                                                       $testo.                                                       
                                                       "',".
                                                 $zoomlevel.
                                                     "); })";

$document->addScriptDeclaration($js);
				break;						
		}


return $url;

}

?>