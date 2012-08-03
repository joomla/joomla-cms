<?php
/*------------------------------------------------------------------------
# plugin_googlemap2_helper.php - Google Maps plugin
# ------------------------------------------------------------------------
# author    Mike Reumer
# copyright Copyright (C) 2011 tech.reumer.net. All Rights Reserved.
# @license - http://www.gnu.org/copyleft/gpl.html GNU/GPL
# Websites: http://tech.reumer.net
# Technical Support: http://tech.reumer.net/Contact-Us/Mike-Reumer.html 
# Documentation: http://tech.reumer.net/Google-Maps/Documentation-of-plugin-Googlemap/
--------------------------------------------------------------------------*/

defined( '_JEXEC' ) or die( 'Restricted access' );

if (!defined('_CMN_JAVASCRIPT')) define('_CMN_JAVASCRIPT', "<b>JavaScript must be enabled in order for you to use Google Maps.</b> <br/>However, it seems JavaScript is either disabled or not supported by your browser. <br/>To view Google Maps, enable JavaScript by changing your browser options, and then try again.");

class plgSystemPlugin_googlemap2_helper
{
	var $jversion;
	var $params;
	var $regex;
	var $document;
	var $brackets;
	var $debug_plugin;
	var $debug_text;
	var $protocol;
	var $googlewebsite;
	var $urlsetting;
	var $googlekey;
	var $language;
	var $langtype;
	var $iso;
	var $no_javascript;
	var $pagebreak;
	var	$google_API_version;
	var	$timeinterval;
	var	$googleindexing;
	var	$langanim;
	var	$first_google;
	var	$first_googlemaps;
	var	$first_mootools;
	var	$first_modalbox;
	var	$first_localsearch;
	var $first_googleearth;
	var	$first_kmlrenderer;
	var	$first_kmlelabel;
	var	$first_svcontrol;
	var	$first_animdir;
	var	$first_arcgis;
	var	$first_panoramiolayer;
	var $initparams;
	var $clientgeotype;
	var $event;
	var $_text;
	var	$_langanim;
	var	$_client_geo;
	var $_inline_coords;
	var $_inline_tocoords;
	var $_kmlsbwidthorig;
	var $_lbxwidthorig;
	
	/**
	 * Constructor
	 *
	 * @access      protected
	 * @since       1.0
	 */
	 // Can we use _construct or should we use init?
	 //	function init() {
	public function __construct($jversion, $params, $regex, $document, $brackets)
	{
		// The params of the plugin
		$this->jversion = $jversion;
		$this->params = $params;
		$this->regex = $regex;
		$this->document = $document;
		$this->brackets = $brackets;
		// Set debug
		$this->debug_plugin = $this->params->get( 'debug', '0' );
		$this->debug_text = '';
		// Get ID
		$this->id = intval( JRequest::getVar('id', null) );	
		$this->id = explode(":", $this->id);
		$this->id = $this->id[0];
		// What is the url of website without / at the end
		$this->url = preg_replace('/\/$/', '', JURI::base());
		$this->_debug_log("url base(): ".$this->url);			
		$this->base = JURI::base(true);
		$this->_debug_log("url base(true): ".$this->base);			
		// Protocol not working with maps.google.com only with enterprise account
		if ($_SERVER['SERVER_PORT'] == 443)
			$this->protocol = "https://";
		else
			$this->protocol = "http://";
		$this->_debug_log("Protocol: ".$this->protocol);
		// Get language
		$this->langtype = $this->params->get( 'langtype', '' );
		$this->lang = JFactory::getLanguage();
		$this->lang->load("plg_system_plugin_googlemap2", JPATH_SITE."/administrator", $this->lang->getTag(), true);
		$this->language = $this->_getlang();
		$this->no_javascript = JText::_( 'CMN_JAVASCRIPT', _CMN_JAVASCRIPT);
		// Get region
		$this->region = $this->params->get( 'region', '' );
		// Define encoding
		$this->iso = "utf-8";
		// Get params
		$this->googlewebsite = $this->params->get( 'googlewebsite', 'maps.google.com' );
		$this->_debug_log("googlewebsite: ".$this->googlewebsite);
		$this->urlsetting = $this->params->get( 'urlsetting', 'http_host' );
		$this->_debug_log("urlsetting: ".$this->urlsetting);
		if ($this->urlsetting=='mosconfig')
			$this->urlsetting = $this->url;
		else 
			$this->urlsetting = $_SERVER['HTTP_HOST'];
		$this->google_API_version = $this->params->get( 'Google_API_version', '2.x' );
		$this->googleindexing = $this->params->get( 'googleindexing', '1' );
		$this->timeinterval = $this->params->get( 'timeinterval', '500' );
		$this->clientgeotype = $this->params->get( 'clientgeotype', '0' );
		$this->langanim = $this->params->get( 'langanim', 'en;The requested panorama could not be displayed|Could not generate a route for the current start and end addresses|Street View coverage is not available for this route|You have reached your destination|miles|miles|ft|kilometers|kilometer|meters|In|You will reach your destination|Stop|Drive|Press Drive to follow your route|Route|Speed|Fast|Medium|Slow' );
		// Get key
		$this->googlekey = $this->_get_API_key();
		// Pagebreak regular expression
		$this->pagebreak = '/<hr\s(title=".*"\s)?class="system-pagebreak"(\stitle=".*")?\s\/>/si';
		// load scripts once
		$this->first_google=true;
		$this->first_googlemaps=true;
		$this->first_mootools=true;
		$this->first_modalbox=true;
		$this->first_localsearch=true;
		$this->first_googleearth=true;
		$this->first_kmlrenderer=true;
		$this->first_kmlelabel=true;
		$this->first_svcontrol=true;
		$this->first_animdir= true;
		$this->first_arcgis=true;
		$this->first_panoramiolayer = true;
		$this->_debug_log("brackets: ".$this->brackets);
		// Get params
		$this->initparams = (object) null;
		$this->_getInitialParams();
	}	
	
	function process($match, $params, &$text, $counter, $event) {
		$startmem = round($this->_memory_get_usage()/1024);
		$this->_debug_log("Memory Usage Start (_process): " . $startmem . " KB");
		$this->_text = &$text;
		$this->event = $event;
		
		// Parameters can get the default from the plugin if not empty or from the administrator part of the plugin
		$this->_mp = clone $this->initparams;

		// Language initial value
		$this->_mp->lang = $this->language;
		
		// Next parameters can be set as default out of the administrtor module or stay empty and the plugin-code decides the default. 
		$this->_mp->zoomtype = $this->params->get( 'zoomType', '' );
		$this->_mp->mapType = strtolower($this->params->get( 'mapType', '' )); 

		// Default global process parameters
		$this->_client_geo = 0;
		//track if coordinates different from config
		$this->_inline_coords = 0;
		$this->_inline_tocoords = 0;
		$this->_mp->geocoded = 0;

		// default empty and should be filled as a parameter with the plugin out of the content item
		$this->_mp->tolat='';
		$this->_mp->tolon='';
		$this->_mp->toaddress='';
		$this->_mp->description='';
		$this->_mp->tooltip='';
		$this->_mp->kml = array();
		$this->_mp->kmlsb = array();
		$this->_mp->layer = array();
		$this->_mp->lookat = array();
		$this->_mp->camera = array();
		$this->_mp->msid='';
		$this->_mp->searchtext='';
		$this->_mp->latitude='';
		$this->_mp->longitude='';
		$this->_mp->waypoints = array();

		// Give the map a random name so it won't interfere with another map
		$this->_mp->mapnm = $this->id."_".$this->_randomkeys(5)."_".$counter;
		
		// Match the field details to build the html
		$fields = explode("|", $params);

		foreach($fields as $value) {
			$value = trim($value, " \xC2\xA0\n\t\r\0\x0B");
			$values = explode("=",$value, 2);
			$values[0] = trim(strtolower($values[0]), " \xC2\xA0\n\t\r\0\x0B");
			$values[0] = preg_replace(array('/\r/','/\n/','/\<.*?\b[^>]*>/si'), '', $values[0]);
			$values=preg_replace("/^'/", '', $values);
			$values=preg_replace("/'$/", '', $values);
			$values=preg_replace("/^&#0{0,2}39;/",'',$values);
			$values=preg_replace("/&#0{0,2}39;$/",'',$values);
//			echo "<br/>".$values[0]." = ".$values[1];
				
			if (count($values)>1) {
				$values[1] = trim($values[1], " \xC2\xA0\n\t\r\0\x0B");

				if($values[0]=='debug'){
					$this->debug_plugin=$values[1];
				}else if($values[0]=='gmv'){
					$this->google_API_version = $values[1];
				}else if($values[0]=='lat'&&$values[1]!=''){
					$this->_mp->latitude=$this->_remove_html_tags($values[1]);
					$this->_inline_coords = 1;
				}else if($values[0]=='lon'&&$values[1]!=''){
					$this->_mp->longitude=$this->_remove_html_tags($values[1]);
					$this->_inline_coords = 1;
				}else if($values[0]=='centerlat'){
					$this->_mp->centerlat=$this->_remove_html_tags($values[1]);
					$this->_inline_coords = 1;
				}else if($values[0]=='centerlon'){
					$this->_mp->centerlon=$this->_remove_html_tags($values[1]);
					$this->_inline_coords = 1;
				}else if($values[0]=='tolat'){
					$this->_mp->tolat=$this->_remove_html_tags($values[1]);
					$this->_inline_tocoords = 1;
				}else if($values[0]=='tolon'){
					$this->_mp->tolon=$this->_remove_html_tags($values[1]);
					$this->_inline_tocoords = 1;
				}else if($values[0]=='text'){
					$this->_mp->description=html_entity_decode(html_entity_decode(trim($values[1])));
					if(!$this->_is_utf8($this->_mp->description)) 
						$this->_mp->description = utf8_encode($this->_mp->description);
					if (substr($this->google_API_version,0,1)=='2')
						$this->_mp->description=str_replace("\"","\\\"", $this->_mp->description);
					$this->_mp->description=str_replace("&#0{0,2}39;","'", $this->_mp->description);
				}else if($values[0]=='tooltip'){
					$this->_mp->tooltip=html_entity_decode(html_entity_decode(trim($values[1])));
					$this->_mp->tooltip=str_replace("&amp;","&", $this->_mp->tooltip);
					if(!$this->_is_utf8($this->_mp->tooltip)) 
						$this->_mp->tooltip= utf8_encode($this->_mp->tooltip);
				}else if($values[0]=='maptype'){
					$this->_mp->mapType=strtolower($values[1]);
				}else if ($values[0]=='waypoint'){
					$this->_mp->waypoints[0] = $values[1];
				}else if(($this->brackets=='both'||$this->brackets=='[')&&preg_match("/waypoint\([0-9]+\)/", $values[0])){
					$this->_mp->waypoints[$this->_get_index($values[0], '(')] = $values[1];
				}else if(($this->brackets=='both'||$this->brackets=='{')&&preg_match("/waypoint\[[0-9]+\]/", $values[0])){
					$this->_mp->waypoints[$this->_get_index($values[0], '[')] = $values[1];
				}else if($values[0]=='kml'){
					$this->_mp->kml[0]=$this->_remove_html_tags($values[1]);
				}else if(($this->brackets=='both'||$this->brackets=='[')&&preg_match("/kml\([0-9]+\)/", $values[0])){
					$this->_mp->kml[$this->_get_index($values[0], '(')] = $this->_remove_html_tags($values[1]);
				}else if(($this->brackets=='both'||$this->brackets=='{')&&preg_match("/kml\[[0-9]+\]/", $values[0])){
					$this->_mp->kml[$this->_get_index($values[0], '[')] = $this->_remove_html_tags($values[1]);
				}else if($values[0]=='kmlsb'){
					$this->_mp->kmlsb[0]=$this->_remove_html_tags($values[1]);
				}else if(($this->brackets=='both'||$this->brackets=='[')&&preg_match("/kmlsb\([0-9]+\)/", $values[0])){
					$this->_mp->kmlsb[$this->_get_index($values[0], '(')] = $this->_remove_html_tags($values[1]);
				}else if(($this->brackets=='both'||$this->brackets=='{')&&preg_match("/kmlsb\[[0-9]+\]/", $values[0])){
					$this->_mp->kmlsb[$this->_get_index($values[0], '[')] = $this->_remove_html_tags($values[1]);
				}else if($values[0]=='layer'){
					$this->_mp->layer[0]=$this->_remove_html_tags($values[1]);
				}else if(($this->brackets=='both'||$this->brackets=='[')&&preg_match("/layer\([0-9]+\)/", $values[0])){
					$this->_mp->layer[$this->_get_index($values[0], '(')] = $this->_remove_html_tags($values[1]);
				}else if(($this->brackets=='both'||$this->brackets=='{')&&preg_match("/layer\[[0-9]+\]/", $values[0])){
					$this->_mp->layer[$this->_get_index($values[0], '[')] = $this->_remove_html_tags($values[1]);
				}else if($values[0]=='lookat'){
					$this->_mp->lookat[0]=$values[1];
				}else if(($this->brackets=='both'||$this->brackets=='[')&&preg_match("/lookat\([0-9]+\)/", $values[0])){
					$this->_mp->lookat[$this->_get_index($values[0], '(')] = $values[1];
				}else if(($this->brackets=='both'||$this->brackets=='{')&&preg_match("/lookat\[[0-9]+\]/", $values[0])){
					$this->_mp->lookat[$this->_get_index($values[0], '[')] = $values[1];
				}else if($values[0]=='camera'){
					$this->_mp->camera[0]=$values[1];
				}else if(($this->brackets=='both'||$this->brackets=='[')&&preg_match("/camera\([0-9]+\)/", $values[0])){
					$this->_mp->camera[$this->_get_index($values[0], '(')] = $values[1];
				}else if(($this->brackets=='both'||$this->brackets=='{')&&preg_match("/camera\[[0-9]+\]/", $values[0])){
					$this->_mp->camera[$this->_get_index($values[0], '[')] = $values[1];
				}else if($values[0]=='tilelayer'){
					$this->_mp->tilelayer=$this->_remove_html_tags($values[1]);
				}else {
					// other parameters
					if ($values[0]!='')
						$this->_mp->$values[0]=$values[1];
				}
			}
		}
		
		// Search for geo parameters inside the text
		//$this->_findgeoparam();
		
		//Translate parameters
		$this->_mp->erraddr = $this->_translate($this->_mp->erraddr, $this->_mp->lang);
		$this->_mp->txtaddr = $this->_translate($this->_mp->txtaddr, $this->_mp->lang);
		$this->_mp->txtaddr = str_replace(array("\r\n", "\r", "\n"), '', $this->_mp->txtaddr );
		$this->_mp->txtgetdir = $this->_translate($this->_mp->txtgetdir, $this->_mp->lang);
		$this->_mp->txtfrom = $this->_translate($this->_mp->txtfrom, $this->_mp->lang);
		$this->_mp->txtto = $this->_translate($this->_mp->txtto, $this->_mp->lang);
		$this->_mp->txtdiraddr = $this->_translate($this->_mp->txtdiraddr, $this->_mp->lang);
		$this->_mp->txtdir = $this->_translate($this->_mp->txtdir, $this->_mp->lang);
		$this->_mp->txtlightbox = $this->_translate(html_entity_decode($this->_mp->txtlightbox), $this->_mp->lang);
		$this->_mp->txt_driving = $this->_translate($this->_mp->txt_driving, $this->_mp->lang);
		$this->_mp->txt_avhighways = $this->_translate($this->_mp->txt_avhighways, $this->_mp->lang);
		$this->_mp->txt_walking = $this->_translate($this->_mp->txt_walking, $this->_mp->lang);
		$this->_mp->txt_optimize = $this->_translate($this->_mp->txt_optimize, $this->_mp->lang);
		$this->_mp->txt_alternatives = $this->_translate($this->_mp->txt_alternatives, $this->_mp->lang);
		$this->_langanim = $this->_translate($this->langanim, $this->_mp->lang);
		$this->_langanim = explode("|", $this->_langanim);

		$this->_debug_log("clientgeotype: ".$this->clientgeotype);
		
		// Latitude only when no coordinates are specified and no address
		if(!empty($this->_mp->latitudeid)) {
			// Get information
			$url = "http://www.google.de/latitude/apps/badge/api?user=".$this->_mp->latitudeid."&type=kml";
			unset($this->_mp->latitudeid);
			$getpage = $this->_getURL($url);
			if ($getpage!='') {
				$expr = '/xmlns/';
				$getpage = preg_replace($expr, 'id', $getpage);
				$xml = new SimpleXMLElement($getpage);
				$coords = "";
				foreach($xml->xpath('//coordinates') as $coordinates) {
					$coords = $coordinates;
					break;
				}
				if ($coords!='') {
					$this->_debug_log("Coordinates: ".join(", ", explode(",", $coords)));
					list ($this->_mp->longitude, $this->_mp->latitude) = explode(",", $coords);
					$this->_inline_coords = 1;
					
					if ($this->_mp->centerlat==''&&$this->_mp->centerlon=='') {
						$this->_mp->zoom = 19 + $this->_mp->corzoom;
					}
					
					// Get icon
					if ($this->_mp->icon=='') {
						foreach($xml->xpath('//Icon/href') as $href) {
							$this->_mp->icon = (string) $href;
							break;
						}
						if ($this->_mp->icon!=""&&$this->_mp->iconwidth==""&&$this->_mp->iconheight=="") {
							$this->_mp->iconwidth = "32";
							$this->_mp->iconheight = "32";
						}
						if ($this->_mp->icon!=""&&$this->_mp->iconanchorx==""&&$this->_mp->iconanchory=="") {
							$this->_mp->iconanchorx = "16";
							$this->_mp->iconanchory = "32";
						}
					}
					// show description -> add to text
					if ($this->_mp->latitudedesc=="1") {
						foreach($xml->xpath('//description') as $descr) {
							$desc = $descr;
							break;
						}
						$desc=html_entity_decode(html_entity_decode(trim($desc)));
						$desc=str_replace("\"","\\\"", $desc);
						$desc=str_replace("&#0{0,2}39;","'", $desc);
						
						$this->_mp->description .= "<p class='latitude'>".str_replace(' http://www.google.com/latitude/apps/badge', '', $desc)."</p>";
					}
					// show coordinates -> add to text
					if ($this->_mp->latitudecoord=="1") {
						$this->_mp->description .= "<table class=latitudetable><tr><td>Latitude</td><td>".$this->_mp->latitude."</td></tr><tr><td>Longitude</td><td>".$this->_mp->longitude."</td></tr></table>";
					}
				} else
					$this->_debug_log("Latitude coordinates: null");
			} else
				$this->_debug_log("Latitude totally wrong!");
			unset($url, $getpage, $expr, $xml, $coord, $coordinates, $descr, $desc);
		}

		if($this->_inline_coords == 0 && !empty($this->_mp->address))	{
			if ($this->clientgeotype=="local")
				$coord = "";
			else
				$coord = $this->get_geo($this->_mp->address);
				
			if ($coord=='') {
				$this->_client_geo = 1;
			} else {
				list ($this->_mp->longitude, $this->_mp->latitude, $altitude) = explode(",", $coord);
				$this->_inline_coords = 1;
				$this->_mp->geocoded = 1;
			}
		}

		if($this->_inline_tocoords == 0 && !empty($this->_mp->toaddress))	{
			if ($this->clientgeotype=="local")
				$tocoord = "";
			else
				$tocoord = $this->get_geo($this->_mp->toaddress);
			if ($tocoord=='') {
				$client_togeo = 1;
			} else {
				list ($this->_mp->tolon, $this->_mp->tolat, $altitude) = explode(",", $tocoord);
				$this->_inline_tocoords = 1;
			}
		}

		if (is_numeric($this->_mp->svwidth)) 
			$this->_mp->svwidth .= "px";
			
		if (is_numeric($this->_mp->svheight))
			$this->_mp->svheight.= "px";

		if (is_numeric($this->_mp->kmlsbwidth)) {
			$this->_kmlsbwidthorig = $this->_mp->kmlsbwidth;
			$this->_mp->kmlsbwidth .= "px";
		} else 
			$this->_kmlsbwidthorig = 0;
			
		$this->_lbxwidthorig = $this->_mp->lbxwidth;
		
		if (is_numeric($this->_mp->lbxwidth))
			$this->_mp->lbxwidth .= "px";
		
		if (is_numeric($this->_mp->lbxheight))
			$this->_mp->lbxheight .= "px";
			
		if (is_numeric($this->_mp->width))
			$this->_mp->width .= "px";
			
		if (is_numeric($this->_mp->height))
			$this->_mp->height .= "px";

		if (!is_numeric($this->_mp->panomax))
			$this->_mp->panomax= "50";
			
		if ($this->_mp->msid!=''&&count($this->_mp->kml)==0) {
			$this->_mp->kml[0]=$this->protocol.$this->googlewebsite.'/maps/ms?';
			if ($this->_mp->lang!='')
				$this->_mp->kml[0] .= "hl=".$this->_mp->lang."&amp;";
			$this->_mp->kml[0].='ie='.$this->iso.'&amp;msa=0&amp;msid='.$this->_mp->msid.'&amp;output=kml';
			$this->_debug_log("- msid: ".$this->_mp->kml[0]);
		}

		// Get the code to be added to the text
		if (substr($this->google_API_version,0,1)=='2')
			list ($code, $lbcode) = $this->_processMapv2();
		else
			list ($code, $lbcode) = $this->_processMapv3();
		
		// Get memory before adding code to text
		$endmem = round($this->_memory_get_usage()/1024);
		$diffmem = $endmem-$startmem;
		$this->_debug_log("Memory Usage End: " . $endmem . " KB (".$diffmem." KB)");

		// Add code to text
		$code = "\n<!-- Plugin Google Maps version 2.17 by Mike Reumer ".(($this->debug_text!='')?$this->debug_text."\n":"")."-->".$code;

		// Clean up debug text for next _process
		$this->debug_text = '';
		
		// Depending of show place the code at end of page or on the {mosmap} position		
		if ($this->_mp->show==0) {
			$offset = strpos($this->_text, $match);
			$this->_text = preg_replace($this->regex, $lbcode, $this->_text, 1);
			// If pagebreak add code before pagebreak
			preg_match($this->pagebreak, $this->_text, $m, PREG_OFFSET_CAPTURE, $offset);
			if (count($m)>0)
				$offsetpagebreak = $m[0][1];
			else
				$offsetpagebreak = 0;
			if ($offsetpagebreak!=0) 
				$this->_text = substr($this->_text, 0, $offsetpagebreak).$code.substr($this->_text, $offsetpagebreak);
			else
				$this->_text .= $code;
		} else
			$this->_text = preg_replace($this->regex, $code, $this->_text, 1);

		// Clean up generated variables
		unset($startmem, $endmem, $diffmem, $offset, $lbcode, $m, $offsetpagebreak, $code);
		
		return true;
	}
	
	function _processMapv2() {
		// Variables of process
		$code='';
		$lbcode='';
		
		if ($this->_mp->googlebar=='1'||$this->_mp->localsearch=='1') {
			$searchoption = array();

			switch ($this->_mp->searchlist) {
			case "suppress":
				$searchoption[] ="resultList : G_GOOGLEBAR_RESULT_LIST_SUPPRESS";
				break;
			
			case "inline":
				$searchoption[] ="resultList : G_GOOGLEBAR_RESULT_LIST_INLINE";
				break;

			case "div":
				$searchoption[] ="resultList : document.getElementById('searchresult".$this->_mp->mapnm."')";
				break;

			default:
				if(empty($this->_mp->searchlist))
					$searchoption[] ="resultList : G_GOOGLEBAR_RESULT_LIST_INLINE";
				else {
					$searchoption[] ="resultList : document.getElementById('".$this->_mp->searchlist."')";
					$extsearchresult= true;
				}
				break;
			}
			
			switch ($this->_mp->searchtarget) {
			case "_self":
				$searchoption[] ="linkTarget : G_GOOGLEBAR_LINK_TARGET_SELF";
				break;
			
			case "_blank":
				$searchoption[] ="linkTarget : G_GOOGLEBAR_LINK_TARGET_BLANK";
				break;

			case "_top":
				$searchoption[] ="linkTarget : G_GOOGLEBAR_LINK_TARGET_TOP";
				break;

			case "_parent":
				$searchoption[] ="linkTarget : G_GOOGLEBAR_LINK_TARGET_PARENT";
				break;

			default:
				$searchoption[] ="linkTarget : G_GOOGLEBAR_LINK_TARGET_BLANK";
				break;
			}
			
			if ($this->_mp->searchzoompan=="1")
				$searchoption[] ="suppressInitialResultSelection : false
								  , suppressZoomToBounds : false";
			else

				$searchoption[] ="suppressInitialResultSelection : true
								  , suppressZoomToBounds : true";
								  
			$searchoptions = implode(', ', $searchoption);
		} else 
			$searchoptions = "";

		if ($this->_mp->icon!='') {
			$code .= "\n<img src='".$this->_mp->icon."' style='display:none' alt='icon' />";
			if ($this->_mp->iconshadow!='')
				$code .= "\n<img src='".$this->_mp->iconshadow."' style='display:none' alt='icon shadow' />";
			if ($this->_mp->icontransparent!='')
				$code .= "\n<img src='".$this->_mp->icontransparent."' style='display:none' alt='icon transparent' />";
		} 
		
		if ($this->_mp->sv!='none'&&$this->_mp->animdir=='0') {
			$code .= "\n<img src='".$this->base."/media/plugin_googlemap2/site/StreetViewControl/images/man_arrow-0.png' style='display:none' alt='streetview icon' />";
			$code .= "\n<img src='".$this->base."/media/plugin_googlemap2/site/StreetViewControl/images/man_arrow-1.png' style='display:none' alt='streetview icon' />";
			$code .= "\n<img src='".$this->base."/media/plugin_googlemap2/site/StreetViewControl/images/man_arrow-2.png' style='display:none' alt='streetview icon' />";
			$code .= "\n<img src='".$this->base."/media/plugin_googlemap2/site/StreetViewControl/images/man_arrow-3.png' style='display:none' alt='streetview icon' />";
			$code .= "\n<img src='".$this->base."/media/plugin_googlemap2/site/StreetViewControl/images/man_arrow-4.png' style='display:none' alt='streetview icon' />";
			$code .= "\n<img src='".$this->base."/media/plugin_googlemap2/site/StreetViewControl/images/man_arrow-5.png' style='display:none' alt='streetview icon' />";
			$code .= "\n<img src='".$this->base."/media/plugin_googlemap2/site/StreetViewControl/images/man_arrow-6.png' style='display:none' alt='streetview icon' />";
			$code .= "\n<img src='".$this->base."/media/plugin_googlemap2/site/StreetViewControl/images/man_arrow-7.png' style='display:none' alt='streetview icon' />";
			$code .= "\n<img src='".$this->base."/media/plugin_googlemap2/site/StreetViewControl/images/man_arrow-8.png' style='display:none' alt='streetview icon' />";
			$code .= "\n<img src='".$this->base."/media/plugin_googlemap2/site/StreetViewControl/images/man_arrow-9.png' style='display:none' alt='streetview icon' />";
			$code .= "\n<img src='".$this->base."/media/plugin_googlemap2/site/StreetViewControl/images/man_arrow-10.png' style='display:none' alt='streetview icon' />";
			$code .= "\n<img src='".$this->base."/media/plugin_googlemap2/site/StreetViewControl/images/man_arrow-11.png' style='display:none' alt='streetview icon' />";
			$code .= "\n<img src='".$this->base."/media/plugin_googlemap2/site/StreetViewControl/images/man_arrow-12.png' style='display:none' alt='streetview icon' />";
			$code .= "\n<img src='".$this->base."/media/plugin_googlemap2/site/StreetViewControl/images/man_arrow-13.png' style='display:none' alt='streetview icon' />";
			$code .= "\n<img src='".$this->base."/media/plugin_googlemap2/site/StreetViewControl/images/man_arrow-14.png' style='display:none' alt='streetview icon' />";
			$code .= "\n<img src='".$this->base."/media/plugin_googlemap2/site/StreetViewControl/images/man_arrow-15.png' style='display:none' alt='streetview icon' />";
			$code .= "\n<img src='".$this->base."/media/plugin_googlemap2/site/StreetViewControl/images/man-pick.png' style='display:none' alt='streetview icon' />";
		}
		// Generate the map position prior to any Google Scripts so that these can parse the code
		$code.= "<!-- fail nicely if the browser has no Javascript -->
				<noscript><blockquote class='warning'><p>".$this->no_javascript."</p></blockquote></noscript>";			

		if ($this->_mp->align!='none')
			$code.="<div id='mapbody".$this->_mp->mapnm."' style=\"display: none; text-align:".$this->_mp->align."\">";
		else
			$code.="<div id='mapbody".$this->_mp->mapnm."' style=\"display: none;\">";

		if ($this->_mp->lightbox=='1') {
			$lboptions = array();
			if ($this->_mp->lbxzoom!="")
				$lboptions[] = "zoom : ".$this->_mp->lbxzoom;
			if ($this->_mp->lbxcenterlat!=""&&$this->_mp->lbxcenterlon!="")
				$lboptions[] = "mapcenter : \"".$this->_mp->lbxcenterlat." ".$this->_mp->lbxcenterlon."\"";
				
			$this->_lbxwidthorig = (is_numeric($this->_lbxwidthorig)?(($this->_mp->kmlsidebar=="left"||$this->_mp->kmlsidebar=="right")?$this->_lbxwidthorig+$this->_kmlsbwidthorig+5:$this->_lbxwidthorig)."px":$this->_lbxwidthorig);
			$lbname = (($this->_mp->gotoaddr=='1'||(($this->_mp->kmlrenderer=="google"&&count($this->_mp->kmlsb)!=0)||($this->_mp->kmlrenderer=="geoxml"&&(count($this->_mp->kml)!=0||count($this->_mp->kmlsb)!=0))&&($this->_mp->kmlsidebar=="left"||$this->_mp->kmlsidebar=="right"))||$this->_mp->animdir!='0'||$this->_mp->sv=='top'||$this->_mp->sv=='bottom'||$this->_mp->searchlist=='div'||$this->_mp->dir=='5'||($this->_mp->formaddress==1&&$this->_mp->animdir==0))?"lightbox":"googlemap");
			
			if ($this->_mp->show==1) {
				$code.="<a href='javascript:void(0)' onclick='javascript:MOOdalBox.open(\"".$lbname.$this->_mp->mapnm."\", \"".$this->_mp->lbxcaption."\", \"".$this->_lbxwidthorig." ".$this->_mp->lbxheight."\", map".$this->_mp->mapnm.", {".implode(",",$lboptions)."});return false;' class='lightboxlink'>".html_entity_decode($this->_mp->txtlightbox)."</a>";
				$code .= "<div id='lightbox".$this->_mp->mapnm."'>";
			} else {
				$lbcode.="<a href='javascript:void(0)' onclick='javascript:MOOdalBox.open(\"".$lbname.$this->_mp->mapnm."\", \"".$this->_mp->lbxcaption."\", \"".$this->_lbxwidthorig." ".$this->_mp->lbxheight."\", map".$this->_mp->mapnm.", {".implode(",",$lboptions)."});return false;' class='lightboxlink'>".html_entity_decode($this->_mp->txtlightbox)."</a>";
				$code .= "<div id='lightbox".$this->_mp->mapnm."' style='display:none'>";
			}
		}

		if ($this->_mp->gotoaddr=='1')	{
			$code.="<form name=\"gotoaddress".$this->_mp->mapnm."\" class=\"gotoaddress\" onSubmit=\"javascript:gotoAddress".$this->_mp->mapnm."();return false;\">";
			$code.="	<input id=\"txtAddress".$this->_mp->mapnm."\" name=\"txtAddress".$this->_mp->mapnm."\" type=\"text\" size=\"25\" value=\"\">";
			$code.="	<input name=\"goto\" type=\"button\" class=\"button\" onClick=\"gotoAddress".$this->_mp->mapnm."();return false;\" value=\"Goto\">";
			$code.="</form>";
		}
		
		if ($this->_mp->formaddress==1&&$this->_mp->animdir==0) {
			$code.="<form id='directionform".$this->_mp->mapnm."' action='".$this->protocol.$this->googlewebsite."/maps' method='get' target='_blank' onsubmit='DirectionMarkersubmit".$this->_mp->mapnm."(this);return false;' class='mapdirform'>";
			$code.=$this->_mp->txtdir;
			$code.=(($this->_mp->txtfrom=='')?"":"<br />").$this->_mp->txtfrom."<input ".(($this->_mp->txtfrom=='')?"type='hidden' ":"type='text'")." class='inputbox' size='20' name='saddr' id='saddr' value='".(($this->_mp->formdir=='1')?$this->_mp->address:(($this->_mp->formdir=='2')?$this->_mp->toaddress:""))."' />";
			$code.=(($this->_mp->txtto=='')?"":"<br />").$this->_mp->txtto."<input ".(($this->_mp->txtto=='')?"type='hidden' ":"type='text'")." class='inputbox' size='20' name='daddr' id='daddr' value='".(($this->_mp->formdir=='1')?$this->_mp->toaddress:(($this->_mp->formdir=='2')?$this->_mp->address:""))."' />";

			if ($this->_mp->txt_driving!=''||$this->_mp->dirtype=="D")
				$code.="<br/><input ".(($this->_mp->txt_driving=='')?"type='hidden' ":"type='radio' ")."class='radio' name='dirflg' value='' ".(($this->_mp->dirtype=="D")?"checked='checked'":"")." />".$this->_mp->txt_driving.(($this->_mp->txt_driving!='')?"&nbsp;":"");
			if ($this->_mp->txt_avhighways!=''||$this->_mp->dirtype=="1")
				$code.="<input ".(($this->_mp->txt_avhighways=='')?"type='hidden' ":"type='radio' ")."class='radio' name='dirflg' value='h' ".(($this->_mp->avoidhighways=='1')?"checked='checked'":"")." />".$this->_mp->txt_avhighways.(($this->_mp->txt_avhighways!='')?"&nbsp;":"");
			if ($this->_mp->txt_walking!=''||$this->_mp->dirtype=="W")
				$code.="<input ".(($this->_mp->txt_walking=='')?"type='hidden' ":"type='radio' ")."class='radio' name='dirflg' value='w' ".(($this->_mp->dirtype=="W")?"checked='checked'":"")." />".$this->_mp->txt_walking.(($this->_mp->txt_walking!='')?"&nbsp;":"");
			$code.="<input value='".$this->_mp->txtgetdir."' class='button' type='submit' style='margin-top: 2px;'>";

			if ($this->_mp->dir=='2')
				$code.= "<input type='hidden' name='pw' value='2'/>";

			if ($this->_mp->lang!='') 
				$code.= "<input type='hidden' name='hl' value='".$this->_mp->lang."'/>";
			$code.="</form>";
		}
		
		if ((($this->_mp->kmlrenderer=="google"&&count($this->_mp->kmlsb)!=0)||($this->_mp->kmlrenderer=="geoxml"&&(count($this->_mp->kml)!=0||count($this->_mp->kmlsb)!=0)))&&($this->_mp->kmlsidebar=="left"||$this->_mp->kmlsidebar=="right"))
			$code.="<table style=\"width:100%;border-spacing:0px;\">
					<tr>";

		if ((($this->_mp->kmlrenderer=="google"&&count($this->_mp->kmlsb)!=0)||($this->_mp->kmlrenderer=="geoxml"&&(count($this->_mp->kml)!=0||count($this->_mp->kmlsb)!=0)))&&$this->_mp->kmlsidebar=="left")
			$code.="<td style=\"width:".$this->_mp->kmlsbwidth.";height:".$this->_mp->height.";vertical-align:top;\"><div id=\"kmlsidebar".$this->_mp->mapnm."\" class=\"kmlsidebar\" style=\"align:left;width:".$this->_mp->kmlsbwidth.";height:".$this->_mp->height.";overflow:auto;\"></div></td>";

		if ((($this->_mp->kmlrenderer=="google"&&count($this->_mp->kmlsb)!=0)||($this->_mp->kmlrenderer=="geoxml"&&(count($this->_mp->kml)!=0||count($this->_mp->kmlsb)!=0)))&&($this->_mp->kmlsidebar=="left"||$this->_mp->kmlsidebar=="right"))
			$code.="<td>";
			
		if ($this->_mp->sv=='top'||($this->_mp->animdir!='0'&&$this->_mp->animdir!='3')) {
			$code.="<div id='svpanel".$this->_mp->mapnm."' class='svPanel' style='" . ($this->_mp->align != 'none' ? ($this->_mp->align == 'center' || $this->_mp->align == 'left' ? 'margin-right: auto; ' : '') . ($this->_mp->align == 'center' || $this->_mp->align == 'right' ? 'margin-left: auto; ' : '') : '') . "width:".$this->_mp->svwidth."; height:".$this->_mp->svheight."'><div id='svpanorama".$this->_mp->mapnm."' class='streetview' style='width:".$this->_mp->svwidth."; height:".$this->_mp->svheight.(($this->_mp->kmlsidebar=="right")?"float:left;":"").";'></div>";

			if ($this->_mp->animdir!='0') {
				$code.="<div id='status".$this->_mp->mapnm."' class='status' style='top: -".floor($this->_mp->svheight/2)."px'><b>Loading</b></div><div id='instruction".$this->_mp->mapnm."' class='instruction'></div></div><div id='progressBorder".$this->_mp->mapnm."' class='progressBorder'><div id='progressBar".$this->_mp->mapnm."' class='progressBar'></div></div>";
				$code.= "<div class='animforms'>";
				$code.= "<div class='animbuttonforms'><input type='button' value='Drive' id='stopgo".$this->_mp->mapnm."'  onclick='route".$this->_mp->mapnm.".startDriving()'  disabled='disabled' /></div>";

				if ($this->_mp->formspeed==1)
					$code.= "<div class='animformspeed'>
								<div class='animlabel'>".((array_key_exists(16, $this->_langanim))?$this->_langanim[16]:"Drive")."</div>
								<select id='speed".$this->_mp->mapnm."' onchange='route".$this->_mp->mapnm.".setSpeed()'>
									<option value='0'>".((array_key_exists(17, $this->_langanim))?$this->_langanim[17]:"Fast")."</option>
									<option value='1' selected='selected'>".((array_key_exists(18, $this->_langanim))?$this->_langanim[18]:"Normal")."</option>
									<option value='2'>".((array_key_exists(19, $this->_langanim))?$this->_langanim[19]:"Slow")."</option>
								</select>
							</div>";

				if ($this->_mp->formdirtype==1)
					$code.= "<div class='animformdirtype'>
								<input ".(($this->_mp->txt_driving=='')?"type='hidden' ":"type='radio' ")."class='radio' name='dirflg".$this->_mp->mapnm."' value='' ".(($this->_mp->dirtype=="D")?"checked='checked'":"")." />".$this->_mp->txt_driving.(($this->_mp->txt_driving!='')?"&nbsp;":"")."<br />
								<input ".(($this->_mp->txt_avhighways=='')?"type='hidden' ":"type='radio' ")."class='radio' name='dirflg".$this->_mp->mapnm."' value='h' ".(($this->_mp->avoidhighways=='1')?"checked='checked'":"")." />".$this->_mp->txt_avhighways.(($this->_mp->txt_avhighways!='')?"&nbsp;":"")."<br />
								<input ".(($this->_mp->txt_walking=='')?"type='hidden' ":"type='radio' ")."class='radio' name='dirflg".$this->_mp->mapnm."' value='w' ".(($this->_mp->dirtype=="W")?"checked='checked'":"")." />".$this->_mp->txt_walking.(($this->_mp->txt_walking!='')?"&nbsp;":"")."<br />
							</div>";

				if ($this->_mp->formaddress==1)
					$code.= "<div class='animformaddress'>
								".(($this->_mp->txtfrom=='')?"":"<div class='animlabel'>".$this->_mp->txtfrom."</div>")."
								<div class='animinput'><input id='from".$this->_mp->mapnm."' ".(($this->_mp->txtfrom=='')?"type='hidden' ":"")." size='30' value='".(($this->_mp->formdir=='1')?$this->_mp->address:(($this->_mp->formdir=='2')?$this->_mp->toaddress:""))."'/></div>
								<div style='clear: both;'></div>
								".(($this->_mp->txtto=='')?"":"<div class='animlabel'>".$this->_mp->txtto."</div>")."
								<div class='animinput'><input id='to".$this->_mp->mapnm."' ".(($this->_mp->txtto=='')?"type='hidden' ":"")." size='30' value='".(($this->_mp->formdir=='1')?$this->_mp->toaddress:(($this->_mp->formdir=='2')?$this->_mp->address:""))."'/></div>
							</div>
							<div class='animbuttons'>
								<input type='button' value='".((array_key_exists(15, $this->_langanim))?$this->_langanim[15]:"Route")."' class='animroute' onclick='route".$this->_mp->mapnm.".generateRoute()' />
							</div>
							";
			}
			$code.="<div style=\"clear: both;\"></div>";
			$code.="</div>";
		}

		if (($this->_mp->animdir=='2'||$this->_mp->animdir=='3')&&$this->_mp->showdir!='0') {
			$code.="<table style=\"width:".$this->_mp->width.";\"><tr>";
			$code.="<td style='width:50%;'><div id=\"googlemap".$this->_mp->mapnm."\" ".((!empty($this->_mp->mapclass))?"class=\"".$this->_mp->mapclass."\"" :"class=\"map\"")." style=\"" . ($this->_mp->align != 'none' ? ($this->_mp->align == 'center' || $this->_mp->align == 'left' ? 'margin-right: auto; ' : '') . ($this->_mp->align == 'center' || $this->_mp->align == 'right' ? 'margin-left: auto; ' : '') : '') . "width:100%; height:".$this->_mp->height.";".(($this->_mp->show==0&&$this->_mp->lightbox==0)?"display:none;":"").(((($this->_mp->kmlrenderer=="google"&&count($this->_mp->kmlsb)!=0)||($this->_mp->kmlrenderer=="geoxml"&&(count($this->_mp->kml)!=0||count($this->_mp->kmlsb)!=0))&&$this->_mp->kmlsidebar=="right")||$this->_mp->animdir=='2')?"float:left;":"")."\"></div></td>";
			$code.= "<td style='width:50%;'><div id=\"dirsidebar".$this->_mp->mapnm."\" class='directions' style='float:left;width:100%;height: ".$this->_mp->height.";overflow:auto; '></div></td>";				
			$code.="</tr></table>";
		} else {
			$code.="<div id=\"googlemap".$this->_mp->mapnm."\" ".((!empty($this->_mp->mapclass))?"class=\"".$this->_mp->mapclass."\"" :"class=\"map\"")." style=\"" . ($this->_mp->align != 'none' ? ($this->_mp->align == 'center' || $this->_mp->align == 'left' ? 'margin-right: auto; ' : '') . ($this->_mp->align == 'center' || $this->_mp->align == 'right' ? 'margin-left: auto; ' : '') : '') . "width:".$this->_mp->width."; height:".$this->_mp->height.";".(($this->_mp->show==0&&$this->_mp->lightbox==0)?"display:none;":"").(((($this->_mp->kmlrenderer=="google"&&count($this->_mp->kmlsb)!=0)||($this->_mp->kmlrenderer=="geoxml"&&(count($this->_mp->kml)!=0||count($this->_mp->kmlsb)!=0))&&$this->_mp->kmlsidebar=="right")||$this->_mp->animdir=='2')?"float:left;":"")."\"></div>";
		}
					
		if ($this->_mp->sv=='bottom'||$this->_mp->animdir=="3") {
			if ($this->_mp->animdir=='3') {
				$code.="<div id='progressBorder".$this->_mp->mapnm."' class='progressBorder'><div id='progressBar".$this->_mp->mapnm."' class='progressBar'></div></div>";
				$code.= "<div class='animforms'>";
				$code.= "<div class='animbuttonforms'><input type='button' value='Drive' id='stopgo".$this->_mp->mapnm."'  onclick='route".$this->_mp->mapnm.".startDriving()'  disabled='disabled' /></div>";


				if ($this->_mp->formspeed==1)
					$code.= "<div class='animformspeed'>
								<div class='animlabel'>".((array_key_exists(16, $this->_langanim))?$this->_langanim[16]:"Drive")."</div>
								<select id='speed".$this->_mp->mapnm."' onchange='route".$this->_mp->mapnm.".setSpeed()'>
									<option value='0'>".((array_key_exists(17, $this->_langanim))?$this->_langanim[17]:"Fast")."</option>
									<option value='1' selected='selected'>".((array_key_exists(18, $this->_langanim))?$this->_langanim[18]:"Normal")."</option>
									<option value='2'>".((array_key_exists(19, $this->_langanim))?$this->_langanim[19]:"Slow")."</option>
								</select>
							</div>";

				if ($this->_mp->formdirtype==1)
					$code.= "<div class='animformdirtype'>
								<input ".(($this->_mp->txt_driving=='')?"type='hidden' ":"type='radio' ")."class='radio' name='dirflg".$this->_mp->mapnm."' value='' ".(($this->_mp->dirtype=="D")?"checked='checked'":"")." />".$this->_mp->txt_driving.(($this->_mp->txt_driving!='')?"&nbsp;":"")."<br />
								<input ".(($this->_mp->txt_avhighways=='')?"type='hidden' ":"type='radio' ")."class='radio' name='dirflg".$this->_mp->mapnm."' value='h' ".(($this->_mp->avoidhighways=='1')?"checked='checked'":"")." />".$this->_mp->txt_avhighways.(($this->_mp->txt_avhighways!='')?"&nbsp;":"")."<br />
								<input ".(($this->_mp->txt_walking=='')?"type='hidden' ":"type='radio' ")."class='radio' name='dirflg".$this->_mp->mapnm."' value='w' ".(($this->_mp->dirtype=="W")?"checked='checked'":"")." />".$this->_mp->txt_walking.(($this->_mp->txt_walking!='')?"&nbsp;":"")."<br />
							</div>";

				if ($this->_mp->formaddress==1)
					$code.= "<div class='animformaddress'>
								".(($this->_mp->txtfrom=='')?"":"<div class='animlabel'>".$this->_mp->txtfrom."</div>")."
								<div class='animinput'><input id='from".$this->_mp->mapnm."' ".(($this->_mp->txtfrom=='')?"type='hidden' ":"")." size='30' value='".(($this->_mp->formdir=='1')?$this->_mp->address:(($this->_mp->formdir=='2')?$this->_mp->toaddress:""))."'/></div>
								<div style='clear: both;'></div>
								".(($this->_mp->txtto=='')?"":"<div class='animlabel'>".$this->_mp->txtto."</div>")."
								<div class='animinput'><input id='to".$this->_mp->mapnm."' ".(($this->_mp->txtto=='')?"type='hidden' ":"")." size='30' value='".(($this->_mp->formdir=='1')?$this->_mp->toaddress:(($this->_mp->formdir=='2')?$this->_mp->address:""))."'/></div>
							</div>
							<div class='animbuttons'>
								<input type='button' value='".((array_key_exists(15, $this->_langanim))?$this->_langanim[15]:"Route")."' class='animroute' onclick='route".$this->_mp->mapnm.".generateRoute()' />
							</div>
							";
			}
			$code.="<div style=\"clear: both;\"></div>";
			$code.="</div>";
			$code.="<div id='svpanel".$this->_mp->mapnm."' class='svPanel' style='" . ($this->_mp->align != 'none' ? ($this->_mp->align == 'center' || $this->_mp->align == 'left' ? 'margin-right: auto; ' : '') . ($this->_mp->align == 'center' || $this->_mp->align == 'right' ? 'margin-left: auto; ' : '') : '') . "width:".$this->_mp->svwidth."; height:".$this->_mp->svheight."'><div id='svpanorama".$this->_mp->mapnm."' class='streetview' style='width:".$this->_mp->svwidth."; height:".$this->_mp->svheight.(($this->_mp->kmlsidebar=="right")?"float:left;":"").";'></div>";
			if ($this->_mp->animdir!='0')
				$code.="<div id='status".$this->_mp->mapnm."' class='status' style='top: -".floor($this->_mp->svheight/2)."px'><b>Loading</b></div><div id='instruction".$this->_mp->mapnm."' class='instruction'></div></div>";
		}

		if ((($this->_mp->kmlrenderer=="google"&&count($this->_mp->kmlsb)!=0)||($this->_mp->kmlrenderer=="geoxml"&&(count($this->_mp->kml)!=0||count($this->_mp->kmlsb)!=0)))&&($this->_mp->kmlsidebar=="left"||$this->_mp->kmlsidebar=="right"))
			$code.="</td>";
		
		if ((($this->_mp->kmlrenderer=="google"&&count($this->_mp->kmlsb)!=0)||($this->_mp->kmlrenderer=="geoxml"&&(count($this->_mp->kml)!=0||count($this->_mp->kmlsb)!=0)))&&$this->_mp->kmlsidebar=="right")
			$code.="<td style=\"width:".$this->_mp->kmlsbwidth.";height:".$this->_mp->height.";vertical-align:top;\"><div id=\"kmlsidebar".$this->_mp->mapnm."\"  class=\"kmlsidebar\" style=\"align:left;width:".$this->_mp->kmlsbwidth.";height:".$this->_mp->height.";overflow:auto;\"></div></td>";
			
		if ((($this->_mp->kmlrenderer=="google"&&count($this->_mp->kmlsb)!=0)||($this->_mp->kmlrenderer=="geoxml"&&(count($this->_mp->kml)!=0||count($this->_mp->kmlsb)!=0)))&&($this->_mp->kmlsidebar=="left"||$this->_mp->kmlsidebar=="right"))
			$code.="</tr>
					</table>";

		if ($this->_mp->searchlist=='div')
			$code.="<div id=\"searchresult".$this->_mp->mapnm."\"></div>";

		if ($this->_mp->kmlsidebar=="left"||$this->_mp->kmlsidebar=="right")
			$code.="<div style=\"clear: both;\"></div>";
		
		if (((!empty($this->_mp->tolat)&&!empty($this->_mp->tolon))||!empty($this->_mp->address)||($this->_mp->dir=='5'))&&($this->_mp->animdir!='2'||($this->_mp->animdir=='2'&&$this->_mp->showdir=='0')))
			$code.= "<div id=\"dirsidebar".$this->_mp->mapnm."\" class='directions' ".(($this->_mp->showdir=='0')?"style='display:none'":"")."></div>";

		if ($this->_mp->lightbox=='1')
			$code .= "</div>";

		// Close of mapbody div
		$code.="</div>";

		// Only add the scripts and css once
		if($this->first_google) {
			$url = $this->protocol.$this->googlewebsite."/maps?file=api&amp;v=".$this->google_API_version."&amp;oe=".$this->iso;				
			if ($this->_mp->lang!='') 
				$url .= "&amp;hl=".$this->_mp->lang;

			$url .= "&amp;key=".$this->googlekey;
			$url .= "&amp;sensor=false";
			$url .= "&amp;indexing=".(($this->googleindexing)?"true":"false");
			
			$this->_addscript($url);
			$this->first_google=false;
		}

		if (($this->_mp->loadmootools=="1"||$this->_mp->kmllightbox=="1"||$this->_mp->lightbox=="1"||$this->_mp->effect!="none"||$this->_mp->dir=="3"||$this->_mp->dir=="4"||strpos($this->_mp->description, "MOOdalBox"))&&$this->first_mootools) {
			if ($this->event!='onAfterRender') {
				if (substr($this->jversion,0,3)=='1.5')
					JHTML::_('behavior.mootools');
				else
					JHTML::_('behavior.framework',false);				
			} else {
				if (substr($this->jversion,0,3)=='1.5')
					$url = $this->base."/plugins/system/mtupgrade/mootools.js";
				else {
					$mooconfig = JFactory::getConfig();
		            $moodebug = $mooconfig->get('debug');
			        $moouncompressed   = $moodebug ? '-uncompressed' : '';
					$url = $this->base."/media/system/js/mootools-core".$moouncompressed.".js";
					unset($mooconfig, $moodebug, $moouncompressed);
				}
				$this->_addscript($url);
			}
			$this->first_mootools = false;
		}

		if (($this->_mp->kmllightbox=="1"||$this->_mp->lightbox=="1"||$this->_mp->dir=="3"||$this->_mp->dir=="4"||strpos($this->_mp->description, "MOOdalBox"))&&$this->first_modalbox)	{
			if (substr($this->jversion,0,3)=='1.5')
				$this->_addscript($this->base."/media/plugin_googlemap2/site/moodalbox/js/modalbox1.2hack.js");
			else
				$this->_addscript($this->base."/media/plugin_googlemap2/site/moodalbox/js/moodalbox1.3hack.js");
			
			$this->_addstylesheet($this->base."/media/plugin_googlemap2/site/moodalbox/css/moodalbox.css");
			$this->first_modalbox = false;
		}

		if (($this->_mp->localsearch=="1"||$this->_client_geo==1)&&$this->first_localsearch) {
			$this->_addscript($this->protocol."www.google.com/uds/api?file=uds.js&amp;v=1.0&amp;key=".$this->googlekey);
			$this->_addscript($this->protocol."www.google.com/uds/solutions/localsearch/gmlocalsearch.js".((!empty($this->_mp->adsense))?"?adsense=".$this->_mp->adsense:"").((!empty($this->_mp->channel)&&!empty($this->_mp->adsense))?"&amp;channel=".$this->_mp->channel:""));
			$style = "@import url('".$this->protocol."www.google.com/uds/css/gsearch.css');\n@import url('".$this->protocol."www.google.com/uds/solutions/localsearch/gmlocalsearch.css');";
			$this->_addstyledeclaration($style);
			$this->first_localsearch = false;
		}
		
		if ($this->first_kmlelabel&&(($this->_mp->kmlpolylabel!=""&&$this->_mp->kmlpolylabelclass!="")||($this->_mp->kmlmarkerlabel!=""&&$this->_mp->kmlmarkerlabelclass!=""))) {
			$this->_addscript($this->base."/media/plugin_googlemap2/site/elabel/elabel.js");
			$this->first_kmlelabel = false;
		}
		
		if (($this->_mp->kmlrenderer=='geoxml'||count($this->_mp->kmlsb)!=0)&&$this->first_kmlrenderer) {
			$this->_addscript($this->base."/media/plugin_googlemap2/site/geoxml/geoxml.js");
			$this->first_kmlrenderer = false;
		}
		
		if ($this->_mp->zoomtype=='3D-largeSV'&&$this->first_svcontrol) {
			$this->_addscript($this->base."/media/plugin_googlemap2/site/StreetViewControl/StreetViewControl.js");
			$this->first_svcontrol = false;
		}

		if ($this->_mp->animdir!='0'&&$this->first_animdir) {
			$this->_addscript($this->base."/media/plugin_googlemap2/site/directions/directions.js");
			$this->_addstylesheet($this->base."/media/plugin_googlemap2/site/directions/directions.css");
			$this->first_animdir = false;
		}
		
		if ($this->_mp->kmlrenderer=='arcgis'&&$this->first_arcgis) {
			$this->_addscript($this->protocol."serverapi.arcgisonline.com/jsapi/gmaps/?v=1.4");
			$this->first_arcgis = false;
		}

		if ($this->_mp->panotype!='none'&&$this->first_panoramiolayer) {
			$this->_addscript($this->base."/media/plugin_googlemap2/site/panoramiolayer/panoramiolayer.js");
			$this->first_panoramiolayer = false;
		}

		$code.="<script type='text/javascript'>/*<![CDATA[*/\n";
		if ($this->debug_plugin=="1")
			$code.="function VersionControl(opt_no_style){
					  this.noStyle = opt_no_style;
					};
					VersionControl.prototype = new GControl();
					VersionControl.prototype.initialize = function(map) {
					  var display = document.createElement('div');
					  map.getContainer().appendChild(display);
					  display.innerHTML = '2.'+G_API_VERSION;
					  display.className = 'api-version-display';
					  if(!this.noStyle){
						display.style.fontFamily = 'Arial, sans-serif';
						display.style.fontSize = '11px';
					  }
					  this.htmlElement = display;
					  return display;
					};
					VersionControl.prototype.getDefaultPosition = function() {
					  return new GControlPosition(G_ANCHOR_BOTTOM_LEFT, new GSize(3, 38));
					};
				";

		// Globale map variable linked to the div
		$code.="var tst".$this->_mp->mapnm."=document.getElementById('googlemap".$this->_mp->mapnm."');
		var tstint".$this->_mp->mapnm.";
		var map".$this->_mp->mapnm.";
		var mySlidemap".$this->_mp->mapnm.";
		var overviewmap".$this->_mp->mapnm.";
		var overmap".$this->_mp->mapnm.";
		var xml".$this->_mp->mapnm.";
		var imageovl".$this->_mp->mapnm.";
		var directions".$this->_mp->mapnm.";
		";
		
		if ($this->_mp->proxy=="1") {
			if (substr($this->jversion,0,3)=="1.5")
				$code .= "\nvar proxy = '".$this->base."/plugins/system/plugin_googlemap2_proxy.php?';";
			else
				$code .= "\nvar proxy = '".$this->base."/plugins/system/plugin_googlemap2/plugin_googlemap2_proxy.php?';";
		}

		if ($this->_mp->traffic=='1') 
			$code.="\nvar trafficInfo".$this->_mp->mapnm.";";
		if ($this->_mp->localsearch=='1') 
			$code.="\nvar localsearch".$this->_mp->mapnm.";";
		if ($this->_mp->adsmanager=='1') 
			$code.="\nvar adsmanager".$this->_mp->mapnm.";";
		if ($this->_mp->kmlrenderer=='geoxml'||count($this->_mp->kmlsb)!=0) {
			$code.="\nvar exml".$this->_mp->mapnm.";";

			$code.="\ntop.publishdirectory = '".$this->base."/media/plugin_googlemap2/site/geoxml/';";
		}
		if (count($this->_mp->lookat)>0||count($this->_mp->camera)>0||$this->_mp->tilelayer!=''||$this->_mp->mapType=='earth'||$this->_mp->showearthmaptype=="1")
			$code.="\nvar geplugin".$this->_mp->mapnm.";";

		if ($this->_mp->panotype!='none')
			$code.="\nvar panoLayer".$this->_mp->mapnm.";";

		if ($this->_mp->icon!='') {
			$code.="\nmarkericon".$this->_mp->mapnm." = new GIcon(G_DEFAULT_ICON);";
			$code.="\nmarkericon".$this->_mp->mapnm.".image = '".$this->_mp->icon."';";
			if ($this->_mp->iconwidth!=''&&$this->_mp->iconheight!='')
				$code.="\nmarkericon".$this->_mp->mapnm.".iconSize = new GSize(".$this->_mp->iconwidth.", ".$this->_mp->iconheight.");";
			if ($this->_mp->iconshadow !='') {
				$code.="\nmarkericon".$this->_mp->mapnm.".shadow = '".$this->_mp->iconshadow."';";

				if ($this->_mp->iconshadowwidth!=''&&$this->_mp->iconshadowheight!='') 
					$code.="\nmarkericon".$this->_mp->mapnm.".shadowSize = new GSize(".$this->_mp->iconshadowwidth.", ".$this->_mp->iconshadowheight.");";
			}
			if ($this->_mp->iconanchorx!=''&&$this->_mp->iconanchory!='')
				$code.="\nmarkericon".$this->_mp->mapnm.".iconAnchor = new GPoint(".$this->_mp->iconanchorx.", ".$this->_mp->iconanchory.");";
			if ($this->_mp->iconinfoanchorx!=''&&$this->_mp->iconinfoanchory!='')
				$code.="\nmarkericon".$this->_mp->mapnm.".infoWindowAnchor = new GPoint(".$this->_mp->iconinfoanchorx.", ".$this->_mp->iconinfoanchory.");";
			if ($this->_mp->icontransparent!='') 			
				$code.="\nmarkericon".$this->_mp->mapnm.".transparent = '".$this->_mp->icontransparent."';";
			if ($this->_mp->iconimagemap!='')
				$code.="\nmarkericon".$this->_mp->mapnm.".imageMap = [".$this->_mp->iconimagemap."];";
		}
		
		if ($this->_mp->sv!='none'||$this->_mp->animdir!='0') {
			$code.="\nvar svclient".$this->_mp->mapnm.";
					var svmarker".$this->_mp->mapnm.";
					var svlastpoint".$this->_mp->mapnm.";
					var svpanorama".$this->_mp->mapnm.";
					";
			if ($this->_mp->svautorotate=="1")
				$code.="\nvar timer".$this->_mp->mapnm." = null;
						var svfocus".$this->_mp->mapnm." = false;
						var panobj".$this->_mp->mapnm.";
					";
		}

		if ($this->_mp->animdir!='0')				
			$code.="\nvar route".$this->_mp->mapnm.";
					";
		
		if ($this->_mp->sv!='none'&&$this->_mp->animdir=='0') {
			$code.="\nvar guyIcon".$this->_mp->mapnm." = new GIcon(G_DEFAULT_ICON);
					guyIcon".$this->_mp->mapnm.".image = '".$this->base."/media/plugin_googlemap2/site/StreetViewControl/images/man_arrow-0.png';
					guyIcon".$this->_mp->mapnm.".transparent = '".$this->base."/media/plugin_googlemap2/site/StreetViewControl/images/man-pick.png';
					guyIcon".$this->_mp->mapnm.".imageMap = [26,13, 30,14, 32,28, 27,28, 28,36, 18,35, 18,27, 16,26, 16,20, 16,14, 19,13, 22,8];
					guyIcon".$this->_mp->mapnm.".iconSize = new GSize(49, 52);
					guyIcon".$this->_mp->mapnm.".iconAnchor = new GPoint(25, 35);
					guyIcon".$this->_mp->mapnm.".infoWindowAnchor = new GPoint(25, 5);
					";
		}
		if ($this->_mp->tilelayer!="") {
			$code.="\nvar tilelayer".$this->_mp->mapnm.";
					var mercator".$this->_mp->mapnm.";
					var copyright".$this->_mp->mapnm.";
					";
		}

		if ( array_key_exists('HTTP_USER_AGENT',$_SERVER) && strpos(" ".$_SERVER['HTTP_USER_AGENT'], 'Opera') ) {
			$code.="var _mSvgForced = true;
					var _mSvgEnabled = true; ";
		}

		if($this->_mp->zoomwheel=='1') {
			$code.="function CancelEvent".$this->_mp->mapnm."(event) { 
						var e = event; 
						if (typeof e.preventDefault == 'function') e.preventDefault(); 
							if (typeof e.stopPropagation == 'function') e.stopPropagation(); 

						if (window.event) { 
							window.event.cancelBubble = true; // for IE 
							window.event.returnValue = false; // for IE 
						} 
					}
				";
		}
		
		$code.="\nfunction resetposition".$this->_mp->mapnm."() {
			map".$this->_mp->mapnm.".returnToSavedPosition();
		}";

		if ($this->_mp->gotoaddr=='1') {
			$code.="function gotoAddress".$this->_mp->mapnm."() {
						var address = document.getElementById('txtAddress".$this->_mp->mapnm."').value;

						if (address.length > 0) {
							var geocoder = new GClientGeocoder();
							geocoder.setViewport(map".$this->_mp->mapnm.".getBounds());

							geocoder.getLatLng(address,
							function(point) {
								if (!point) {
									var erraddr = '{$this->_mp->erraddr}';
									erraddr = erraddr.replace(/##/, address);
								  alert(erraddr);
								} else {
								  var txtaddr = '{$this->_mp->txtaddr}';
								  txtaddr = txtaddr.replace(/##/, address);
								  map".$this->_mp->mapnm.".setCenter(point".(($this->_mp->gotoaddrzoom!=0)?",".$this->_mp->gotoaddrzoom:"").");
								  map".$this->_mp->mapnm.".openInfoWindowHtml(point,txtaddr);
								  setTimeout('map".$this->_mp->mapnm.".closeInfoWindow();', 5000);
								}
							  });
						  }
						  return false;
						  
					}";
		}
		
		if (($this->_mp->dir!='0')||((!empty($this->_mp->tolat)&&!empty($this->_mp->tolon))||!empty($this->_mp->toaddress))&&$this->_mp->animdir=='0') {
			$code .="function handleErrors".$this->_mp->mapnm."(){
						var dirsidebar".$this->_mp->mapnm." = document.getElementById('dirsidebar".$this->_mp->mapnm."');
						var newelem = document.createElement('p');
						if (directions".$this->_mp->mapnm.".getStatus().code == G_GEO_UNKNOWN_ADDRESS)
							newelem.innerHTML = 'No corresponding geographic location could be found for one of the specified addresses. This may be due to the fact that the address is relatively new, or it may be incorrect.<br />Error code: ' + directions".$this->_mp->mapnm.".getStatus().code;
						else if (directions".$this->_mp->mapnm.".getStatus().code == G_GEO_SERVER_ERROR)
							newelem.innerHTML = 'A geocoding or directions request could not be successfully processed, yet the exact reason for the failure is not known.<br />Error code: ' + directions".$this->_mp->mapnm.".getStatus().code;
						else if (directions".$this->_mp->mapnm.".getStatus().code == G_GEO_MISSING_QUERY)
							 newelem.innerHTML = 'The HTTP q parameter was either missing or had no value. For geocoder requests, this means that an empty address was specified as input. For directions requests, this means that no query was specified in the input.<br />Error code: ' + directions".$this->_mp->mapnm.".getStatus().code;
						//   else if (directions".$this->_mp->mapnm.".getStatus().code == G_UNAVAILABLE_ADDRESS)  <--- Doc bug... this is either not defined, or Doc is wrong
						//     newelem.innerHTML = 'The geocode for the given address or the route for the given directions query cannot be returned due to legal or contractual reasons.<br />Error code: ' + directions".$this->_mp->mapnm.".getStatus().code;
						   else if (directions".$this->_mp->mapnm.".getStatus().code == G_GEO_BAD_KEY)
							 newelem.innerHTML = 'The given key is either invalid or does not match the domain for which it was given.<br />Error code: ' + directions".$this->_mp->mapnm.".getStatus().code;
						
						   else if (directions".$this->_mp->mapnm.".getStatus().code == G_GEO_BAD_REQUEST)
							 newelem.innerHTML = 'A directions request could not be successfully parsed.<br />Error code: ' + directions".$this->_mp->mapnm.".getStatus().code;
						   else newelem.innerHTML = 'An unknown error occurred.';
						dirsidebar".$this->_mp->mapnm.".appendChild(newelem); 
					}
						";
			}
			
		if ($this->_mp->dir!='0'&&$this->_mp->animdir=='0') {
			$code.="\nDirectionMarkersubmit".$this->_mp->mapnm." = function( formObj ){
						if(formObj.dir&&formObj.dir[1].checked ){
							tmp = formObj.daddr.value;
							formObj.daddr.value = formObj.saddr.value;
							formObj.saddr.value = tmp;
						}";
			if ($this->_mp->dir=='1')
				$code.="\nformObj.submit();";
			elseif ($this->_mp->dir=='2')
				$code.="\nformObj.submit();";
			elseif ($this->_mp->dir=='3')
				$code.="\nfor (var i=0; i < formObj.dirflg.length; i++) {
						   if (formObj.dirflg[i].checked) {
							  var dirflg= formObj.dirflg[i].value;
							  break;
						   }
						}
						MOOdalBox.open('".$this->protocol.$this->googlewebsite."/maps?dir=to&dirflg='+dirflg+'&saddr='+formObj.saddr.value+'&hl=en&daddr='+formObj.daddr.value+'".(($this->_mp->lang!='')?"&amp;hl=".$this->_mp->lang:"")."&pw=2', '".$this->_mp->lbxcaption."', '".$this->_mp->lbxwidth." ".$this->_mp->lbxheight."', null, 16);";
			elseif ($this->_mp->dir=='5') 
					$code .= "\nfor (var i=0; i < formObj.dirflg.length; i++) {
								   if (formObj.dirflg[i].checked) {
									  var dirflg= formObj.dirflg[i].value;
									  break;
								   }
								}
								var dirsidebar".$this->_mp->mapnm." = document.getElementById('dirsidebar".$this->_mp->mapnm."');
								if (directions".$this->_mp->mapnm.") {
									directions".$this->_mp->mapnm.".clear();
									if ( dirsidebar".$this->_mp->mapnm.".hasChildNodes() )
										{
											while ( dirsidebar".$this->_mp->mapnm.".childNodes.length >= 1 )
											{
												dirsidebar".$this->_mp->mapnm.".removeChild( dirsidebar".$this->_mp->mapnm.".firstChild );       
											} 
										}
								} else {
									directions".$this->_mp->mapnm." = new GDirections(map".$this->_mp->mapnm.", dirsidebar".$this->_mp->mapnm.");
									GEvent.addListener(directions".$this->_mp->mapnm.", 'error', handleErrors".$this->_mp->mapnm.");
								}
								options = Array();
								if (dirflg=='w')
									options.travelMode = G_TRAVEL_MODE_WALKING;
								if (dirflg=='h')
									options.avoidHighways = true;
								directions".$this->_mp->mapnm.".load('from: '+formObj.saddr.value+' to: '+formObj.daddr.value, options);
							";
			else
				$code.="\nfor (var i=0; i < formObj.dirflg.length; i++) {
						   if (formObj.dirflg[i].checked) {
							  var dirflg= formObj.dirflg[i].value;
							  break;
						   }
						}
						MOOdalBox.open('".$this->protocol.$this->googlewebsite."/maps?dir=to&dirflg='+dirflg+'&saddr='+formObj.saddr.value+'&hl=en&daddr='+formObj.daddr.value+'".(($this->_mp->lang!='')?"&amp;hl=".$this->_mp->lang:"")."', '".$this->_mp->lbxcaption."', '".$this->_mp->lbxwidth." ".$this->_mp->lbxheight."', null, 16);";
				
			$code.="\nif(formObj.dir&&formObj.dir[1].checked )
						setTimeout('DirectionRevert".$this->_mp->mapnm."()',100);
					};";
			
			$code.="\nDirectionRevert".$this->_mp->mapnm." = function(){
						formObj = document.getElementById('directionform".$this->_mp->mapnm."');
						tmp = formObj.daddr.value;
						formObj.daddr.value = formObj.saddr.value;
						formObj.saddr.value = tmp;
					};";
		}
		
		// Function for overview
		if(!$this->_mp->overview==0) {
			$code.="\nfunction checkOverview".$this->_mp->mapnm."() {
						for (var i in overviewmap".$this->_mp->mapnm.") {
							if (overviewmap".$this->_mp->mapnm."[i].setMapType) {
								overmap".$this->_mp->mapnm." = overviewmap".$this->_mp->mapnm."[i];
								break;
							}
						}						
						if (overmap".$this->_mp->mapnm.") {
					";
						  
			if($this->_mp->overview==2)

			{
				$code.="\n		overviewmap".$this->_mp->mapnm.".hide(true);";
			}

			switch ($this->_mp->mapType) {
			case "satellite":
			
				$code.="\n		overmap".$this->_mp->mapnm.".setMapType(G_SATELLITE_MAP);";
				break;
			
			case "hybrid":
				$code.="\n		overmap".$this->_mp->mapnm.".setMapType(G_HYBRID_MAP);";
				break;

			case "terrain":
				$code.="\n		overmap".$this->_mp->mapnm.".setMapType(G_PHYSICAL_MAP);";
				break;
			
			case "earth":
				break;

			default:
				$code.="\n		overmap".$this->_mp->mapnm.".setMapType(G_NORMAL_MAP);";
				break;
			}
			
			if ($this->_mp->ovzoom!="") {
				$code.="\n		setTimeout('overmap".$this->_mp->mapnm.".setCenter(map".$this->_mp->mapnm.".getCenter(), map".$this->_mp->mapnm.".getZoom()+".$this->_mp->ovzoom.")', 100);";
				$code.="\n		GEvent.addListener(map".$this->_mp->mapnm.",'move',function() {
var c = Math.min(Math.max(0, map".$this->_mp->mapnm.".getZoom()+".$this->_mp->ovzoom."), 19);
overmap".$this->_mp->mapnm.".setCenter(map".$this->_mp->mapnm.".getCenter(), c);
});";
				$code.="\n		GEvent.addListener(map".$this->_mp->mapnm.",'moveend',function() {
var c = Math.min(Math.max(0, map".$this->_mp->mapnm.".getZoom()+".$this->_mp->ovzoom."), 19);
overmap".$this->_mp->mapnm.".setCenter(map".$this->_mp->mapnm.".getCenter(), c);

});";
			}
			$code.= "\n	} else {
						  setTimeout('checkOverview".$this->_mp->mapnm."()',100);
						}
					  }";
		}
		
		$code.="\nfunction initearth".$this->_mp->mapnm."(geplugin) {
			if (!geplugin".$this->_mp->mapnm.")
				geplugin".$this->_mp->mapnm." = geplugin;
			if (geplugin".$this->_mp->mapnm."&&map".$this->_mp->mapnm.".getCurrentMapType() == G_SATELLITE_3D_MAP) {";

		// Add layers
		if ($this->_mp->earthborders=="1")
			$code.="\n	geplugin".$this->_mp->mapnm.".getLayerRoot().enableLayerById(geplugin".$this->_mp->mapnm.".LAYER_BORDERS, true);";
		if ($this->_mp->earthbuildings=="1")
			$code.="\n	geplugin".$this->_mp->mapnm.".getLayerRoot().enableLayerById(geplugin".$this->_mp->mapnm.".LAYER_BUILDINGS, true);";
		else
			$code.="\n	geplugin".$this->_mp->mapnm.".getLayerRoot().enableLayerById(geplugin".$this->_mp->mapnm.".LAYER_BUILDINGS, false);";
		if ($this->_mp->earthroads=="1")
			$code.="\n	geplugin".$this->_mp->mapnm.".getLayerRoot().enableLayerById(geplugin".$this->_mp->mapnm.".LAYER_ROADS, true);";
		if ($this->_mp->earthterrain=="1")
			$code.="\n	geplugin".$this->_mp->mapnm.".getLayerRoot().enableLayerById(geplugin".$this->_mp->mapnm.".LAYER_TERRAIN, true);";
		else
			$code.="\n	geplugin".$this->_mp->mapnm.".getLayerRoot().enableLayerById(geplugin".$this->_mp->mapnm.".LAYER_TERRAIN, false);";
			
		if ($this->_mp->tilelayer) {
			$code.="\n	var url = '".$this->_mp->tilelayer."';
			var newurl = url+'/doc.kml';
			var link = geplugin".$this->_mp->mapnm.".createLink('');
			link.setHref(newurl);
			var networkLink = geplugin".$this->_mp->mapnm.".createNetworkLink('');
			networkLink.set(link, false, false);
			geplugin".$this->_mp->mapnm.".getFeatures().appendChild(networkLink);";
		}
		
		if (count($this->_mp->lookat)>0||count($this->_mp->camera)>0)
			$code.="\n	setTimeout('setearth".$this->_mp->mapnm."()', ".$this->_mp->earthtimeout.");";
			
		$code.="\n}
				}";
				
		if (count($this->_mp->lookat)>0||count($this->_mp->camera)>0) {
			$la = false;
			$cam = false;
			$code.="\nfunction setearth".$this->_mp->mapnm."() {
						var lookat = geplugin".$this->_mp->mapnm.".getView().copyAsLookAt(geplugin".$this->_mp->mapnm.".ALTITUDE_RELATIVE_TO_GROUND);
						var camera = geplugin".$this->_mp->mapnm.".getView().copyAsCamera(geplugin".$this->_mp->mapnm.".ALTITUDE_RELATIVE_TO_GROUND);";
			if (count($this->_mp->lookat)>0) {
				$values = explode(',', $this->_mp->lookat[0]);
				if (count($values)>0&&$values[0]!='') { // Latitude
					$code.="\nlookat.setLatitude(".$values[0].");";
					$la = true;
				}
				if (count($values)>1&&$values[1]!='') { // Longitude
					$code.="\nlookat.setLongitude(".$values[1].");";
					$la = true;
				}
				if (count($values)>2&&$values[2]!='') { // Range
					$code.="\nlookat.setRange(".$values[2].");";
					$la = true;
				}
				if (count($values)>3&&$values[3]!='') { // tilt
					$code.="\nlookat.setTilt(".$values[3].");";
					$la = true;
				}
				if (count($values)>4&&$values[4]!='') { // setHeading
					$code.="\nlookat.setHeading(".$values[4].");";
					$la = true;
				}
				if (count($values)>5&&$values[5]!='') { // altitude
					$code.="\nlookat.setAltitude(".$values[5].");";
					$la = true;
				}
				if (count($values)>6&&$values[6]!='') {// flyspeed
					if ($values[6]=='teleport')
						$code.="\ngeplugin".$this->_mp->mapnm.".getOptions().setFlyToSpeed(geplugin".$this->_mp->mapnm.".SPEED_TELEPORT);";
					else
						$code.="\ngeplugin".$this->_mp->mapnm.".getOptions().setFlyToSpeed(".$values[6].");";
				}
			}
			
			if (count($this->_mp->camera)>0) {
				$values = explode(',', $this->_mp->camera[0]);
				if (count($values)>0&&$values[0]!='') { // Latitude
					$code.="\ncamera.setLatitude(".$values[0].");";
					$cam = true;

				}
				if (count($values)>1&&$values[1]!='') { // Longitude
					$code.="\ncamera.setLongitude(".$values[1].");";
					$cam = true;
				}
				if (count($values)>2&&$values[2]!='') { // tilt
					$code.="\ncamera.setTilt(".$values[2].");";
					$cam = true;
				}
				if (count($values)>3&&$values[3]!='') { // heading
					$code.="\ncamera.setHeading(".$values[3].");";
					$cam = true;
				}
				if (count($values)>4&&$values[4]!='') { // altitude
					$code.="\ncamera.setAltitude(".$values[4].");";
					$cam = true;
				}
				if (count($values)>5&&$values[5]!='') { // roll
					$code.="\ncamera.setRoll(".$values[5].");";
					$cam = true;
				}
				if (count($values)>6&&$values[6]!='') {// flyspeed
					if ($values[6]=='teleport')
						$code.="\ngeplugin".$this->_mp->mapnm.".getOptions().setFlyToSpeed(geplugin".$this->_mp->mapnm.".SPEED_TELEPORT);";
					else
						$code.="\ngeplugin".$this->_mp->mapnm.".getOptions().setFlyToSpeed(".$values[6].");";
				}
			}
					
			if ($la)
				$code.="\n	geplugin".$this->_mp->mapnm.".getView().setAbstractView(lookat);";
			if ($cam)
				$code.="\n	geplugin".$this->_mp->mapnm.".getView().setAbstractView(camera);";
				
			$code.="\n}";
		}

		if ($this->_mp->kmlrenderer=='arcgis') {
			$code .="\nfunction dynmapcallback".$this->_mp->mapnm."(mapservicelayer) {
						  map".$this->_mp->mapnm.".addOverlay(mapservicelayer);
							}";	
		}
		
		if ($this->_mp->kmlrenderer=='google') {
			$code .= "\nfunction savePositionKML".$this->_mp->mapnm."() {
							ok = true;
							for (x=0;x<xml".$this->_mp->mapnm.".length;x++) {
								if (!xml".$this->_mp->mapnm."[x].hasLoaded())
									ok = false;
							}
							if (ok)
								map".$this->_mp->mapnm.".savePosition();
							else
								setTimeout('savePositionKML".$this->_mp->mapnm."()',100);
						}
					";
		}
		
			
		// Functions to watch if the map has changed
		$code.="\nfunction checkMap".$this->_mp->mapnm."()
		{
			if (tst".$this->_mp->mapnm.") {
			";
			
		if ($this->_mp->show!=0)
			$code.="\n			if (tst".$this->_mp->mapnm.".offsetWidth != tst".$this->_mp->mapnm.".getAttribute(\"oldValue\"))
					{
						tst".$this->_mp->mapnm.".setAttribute(\"oldValue\",tst".$this->_mp->mapnm.".offsetWidth);
						if (tst".$this->_mp->mapnm.".offsetWidth > 0) {
					";

		$code.="\n				if (tst".$this->_mp->mapnm.".getAttribute(\"refreshMap\")==0)

							clearInterval(tstint".$this->_mp->mapnm.");";
		if ($this->_mp->effect !='none') 
			$code .="\n					mySlidemap".$this->_mp->mapnm." = new Fx.Slide('googlemap".$this->_mp->mapnm."',{duration: 1500, mode: '".$this->_mp->effect."'});
							mySlidemap".$this->_mp->mapnm.".hide();
							mySlidemap".$this->_mp->mapnm.".slideIn();";

		$code .="\n					getMap".$this->_mp->mapnm."();
							tst".$this->_mp->mapnm.".setAttribute(\"refreshMap\", 1);";
		if ($this->_mp->show!=0)
			$code .="\n				} 
					}";
		$code .="\n	}
		}
		";

		if ($this->_mp->sv!="none"&&$this->_mp->animdir=='0') {
			$code .="\nfunction onYawChange".$this->_mp->mapnm."(newYaw) {
						var GUY_NUM_ICONS = 16;
						var GUY_ANGULAR_RES = 360/GUY_NUM_ICONS;
						if (newYaw < 0) {
							newYaw += 360;
						}
						var guyImageNum = Math.round(newYaw/GUY_ANGULAR_RES) % GUY_NUM_ICONS;
						var guyImageUrl = '".$this->base."/media/plugin_googlemap2/site/StreetViewControl/images/man_arrow-' + guyImageNum + '.png';
						svmarker".$this->_mp->mapnm.".setImage(guyImageUrl);
					}

					function onNewLocation".$this->_mp->mapnm."(point) {
						// Get the original x + y coordinates
						svmarker".$this->_mp->mapnm.".setLatLng(point.latlng);
						map".$this->_mp->mapnm.".panTo(point.latlng);
						svlastpoint".$this->_mp->mapnm." = point.latlng;";
			if ($this->_mp->svautorotate=="1")		
				$code .="\nspiralstart".$this->_mp->mapnm."();
";
						
			$code .="\n}

					function onDragEnd".$this->_mp->mapnm."() {
						var latlng = svmarker".$this->_mp->mapnm.".getLatLng();
						if (svpanorama".$this->_mp->mapnm.") {
							svclient".$this->_mp->mapnm.".getNearestPanorama(latlng, svonResponse".$this->_mp->mapnm.");
						}
					}

					function svonResponse".$this->_mp->mapnm."(response) {
						if (response.code != 200) {
							svmarker".$this->_mp->mapnm.".setLatLng(svlastpoint".$this->_mp->mapnm.");
							map".$this->_mp->mapnm.".setCenter(svlastpoint".$this->_mp->mapnm.");
						} else {
							var latlng = new GLatLng(response.Location.lat, response.Location.lng);

							svmarker".$this->_mp->mapnm.".setLatLng(latlng);
							svlastpoint".$this->_mp->mapnm." = latlng;
							svpanorama".$this->_mp->mapnm.".setLocationAndPOV(latlng, null);
						}
					}
					";

			if ($this->_mp->svautorotate=="1")		
				$code .="\nfunction spiral".$this->_mp->mapnm."() {
							var pov=svpanorama".$this->_mp->mapnm.".getPOV();
							svpanorama".$this->_mp->mapnm.".panTo({yaw:pov.yaw+2, pitch:pov.pitch, zoom:pov.zoom});
						}
						function svmouseover".$this->_mp->mapnm." () {
							svfocus".$this->_mp->mapnm." = true;
							spiralstop".$this->_mp->mapnm."();
						}
						function svmouseout".$this->_mp->mapnm." () {
							svfocus".$this->_mp->mapnm." = false;
							spiralstart".$this->_mp->mapnm."();
						}
						function spiralstop".$this->_mp->mapnm."() {
							if (timer".$this->_mp->mapnm.") {
								clearInterval(timer".$this->_mp->mapnm.");
								timer".$this->_mp->mapnm." = null;
							}
						}
						function spiralstart".$this->_mp->mapnm."() {
							if (!svfocus".$this->_mp->mapnm.") {
								if (timer".$this->_mp->mapnm.")
									spiralstop".$this->_mp->mapnm."();
								timer".$this->_mp->mapnm." = window.setInterval(spiral".$this->_mp->mapnm.", 200);
							}
						}
				";
		}

		// Function for displaying the map and marker
		$code.="\nfunction getMap".$this->_mp->mapnm."(){";
	
		if ($this->_mp->show!=0)
			$code.="\n	if (tst".$this->_mp->mapnm.".offsetWidth > 0) {";
		
		$code.="\n	map".$this->_mp->mapnm." = new GMap2(document.getElementById('googlemap".$this->_mp->mapnm."')".(($this->_mp->googlebar=='1'&&!empty($searchoptions))?", { googleBarOptions: {".$searchoptions." } }":"").");
				map".$this->_mp->mapnm.".getContainer().style.overflow='hidden';
				";
		
		if ($this->_mp->sv!="none"||$this->_mp->animdir!='0')
			$code.="\nsvclient".$this->_mp->mapnm." = new GStreetviewClient();";
			
		if($this->_mp->keyboard=='1'&&$this->_mp->controltype=='user')
		{
			$code.="\nnew GKeyboardHandler(map".$this->_mp->mapnm.");
			";
		} 
		if($this->_mp->dragging=="0")
			$code.="\nmap".$this->_mp->mapnm.".disableDragging();";
	
		if ($this->_mp->shownormalmaptype=="0")
			$code.="\nmap".$this->_mp->mapnm.".removeMapType(G_NORMAL_MAP);";
		if ($this->_mp->showsatellitemaptype=="0")
			$code.="\nmap".$this->_mp->mapnm.".removeMapType(G_SATELLITE_MAP);";
		if ($this->_mp->showhybridmaptype=="0")
			$code.="\nmap".$this->_mp->mapnm.".removeMapType(G_HYBRID_MAP);";
		if ($this->_mp->showterrainmaptype=="1")
			$code.="\nmap".$this->_mp->mapnm.".addMapType(G_PHYSICAL_MAP);";
		if ($this->_mp->showearthmaptype=="1") {
			$code.="\nmap".$this->_mp->mapnm.".addMapType(G_SATELLITE_3D_MAP);";
			$code.="\nGEvent.addListener(map".$this->_mp->mapnm.", 'maptypechanged', function() {
										if (map".$this->_mp->mapnm.".getCurrentMapType() == G_SATELLITE_3D_MAP)
											setTimeout('map".$this->_mp->mapnm.".getEarthInstance(initearth".$this->_mp->mapnm.")',100);
						 });
						";			
		}
	
		if(!$this->_mp->overview==0)
		{
			$code.="\noverviewmap".$this->_mp->mapnm." = new GOverviewMapControl();";

			$code.="\nmap".$this->_mp->mapnm.".addControl(overviewmap".$this->_mp->mapnm.", new GControlPosition(G_ANCHOR_BOTTOM_RIGHT));";
			$code.="setTimeout('checkOverview".$this->_mp->mapnm."()',100);";
	
		} elseif (!$this->_mp->overview==0) {
			$code.="\noverviewmap".$this->_mp->mapnm." = new GOverviewMapControl();";
			$code.="\nmap".$this->_mp->mapnm.".addControl(overviewmap".$this->_mp->mapnm.", new GControlPosition(G_ANCHOR_BOTTOM_RIGHT));";
			
			if($this->_mp->overview==2)
			{
				$code.="\noverviewmap".$this->_mp->mapnm.".hide(true);";
			}
		}
	
		if($this->_mp->navlabel == 1)
			$code.="\nmap".$this->_mp->mapnm.".addControl(new GNavLabelControl(), new GControlPosition(G_ANCHOR_TOP_RIGHT, new GSize(7, 30)));";
	
		if($this->_client_geo == 1) {
			if ($this->clientgeotype=="local") {
				$code.="\nvar localSearch = new GlocalSearch();";
				$replace = array("\n", "\r", "&lt;br/&gt;", "&lt;br /&gt;", "&lt;br&gt;");
				$addr = str_replace($replace, '', $this->_mp->address);
	
				$code.="\nvar address = \"".$addr."\";";
				$code.="\nlocalSearch.setSearchCompleteCallback(null,	function() {
						if (localSearch.results[0]) {
							var resultLat = localSearch.results[0].lat;
							var resultLng = localSearch.results[0].lng;
							var point = new GLatLng(resultLat,resultLng);
						} else 
						";
				if ($this->_mp->latitude !=''&&$this->_mp->longitude!='')
					$code.="var point = new GLatLng( {$this->_mp->latitude}, {$this->_mp->longitude});";
				else
					$code.="var point = new GLatLng( {$this->_mp->deflatitude}, {$this->_mp->deflongitude});";
			} else {
				$code.="var geocoder = new GClientGeocoder();";
				$replace = array("\n", "\r", "&lt;br/&gt;", "&lt;br /&gt;", "&lt;br&gt;");
				$addr = str_replace($replace, '', $this->_mp->address);
	
				$code.="var address = \"".$addr."\";";
				$code.="geocoder.getLatLng(address, function(point) {
							if (!point)";
							
				if ($this->_mp->latitude !=''&&$this->_mp->longitude!='')
					$code.="var point = new GLatLng( {$this->_mp->latitude}, {$this->_mp->longitude});";
				else
					$code.="var point = new GLatLng( {$this->_mp->deflatitude}, {$this->_mp->deflongitude});";
			}
		} else { 
			if ($this->_mp->latitude !=''&&$this->_mp->longitude!='')
				$code.="\nvar point = new GLatLng( {$this->_mp->latitude}, {$this->_mp->longitude});";
			else
				$code.="\nvar point = new GLatLng( {$this->_mp->deflatitude}, {$this->_mp->deflongitude});";
		}
		if (!empty($this->_mp->centerlat)&&!empty($this->_mp->centerlon))
			$code.="\nvar centerpoint = new GLatLng( {$this->_mp->centerlat}, {$this->_mp->centerlon});";
		else
			$code.="\nvar centerpoint = point;";
	
		if ($this->_inline_coords == 0 && count($this->_mp->kml)>0)
			$code.="map".$this->_mp->mapnm.".setCenter(new GLatLng(0, 0), 0);
			";					
		else
			$code.="map".$this->_mp->mapnm.".setCenter(centerpoint, ".$this->_mp->zoom.");
			";					
			
		if ($this->_mp->controltype=='user') {
			switch ($this->_mp->zoomtype) {
				case "Large":
					$code.="map".$this->_mp->mapnm.".addControl(new GLargeMapControl());";
					break;
				case "Small":
					$code.="map".$this->_mp->mapnm.".addControl(new GSmallMapControl());";
					break;
				case "3D-large":
					$code.="map".$this->_mp->mapnm.".addControl(new GLargeMapControl3D());";
					if ($this->_mp->rotation)
						$code.="map".$this->_mp->mapnm.".enableRotation();";
					break;
				case "3D-largeSV":
					$code.="map".$this->_mp->mapnm.".addControl(new StreetViewControl());";
					if ($this->_mp->rotation)
						$code.="map".$this->_mp->mapnm.".enableRotation();";
					break;
				case "3D-small":
					$code.="map".$this->_mp->mapnm.".addControl(new GSmallZoomControl3D());";
					if ($this->_mp->rotation)
						$code.="map".$this->_mp->mapnm.".enableRotation();";
					break;
				default:
					break;
			}
			
			switch ($this->_mp->showmaptype) {
				case "0":
					break;
				case "1":
					$code.="map".$this->_mp->mapnm.".addControl(new GMapTypeControl());";
					break;
				case "2":
					$code.="map".$this->_mp->mapnm.".addControl(new GHierarchicalMapTypeControl());";
					break;
				case "3":
					$code.="map".$this->_mp->mapnm.".addControl(new GMenuMapTypeControl());";
					break;
			} 
	
			if ($this->_mp->showscale==1)
				$code.="map".$this->_mp->mapnm.".addControl(new GScaleControl());";
		} else {
			$code.="map".$this->_mp->mapnm.".setUIToDefault();";
			if ($this->_mp->rotation)
				$code.="map".$this->_mp->mapnm.".enableRotation();";
		}
			
		if (count($this->_mp->kml)>0) {
			if ($this->_mp->kmlrenderer=="google") {
				$code .= "xml".$this->_mp->mapnm." = [];";
				$kmz= false;
				foreach ($this->_mp->kml as $idx => $val) {
					$code .= "var kmlurl = '".$this->_make_absolute($this->_mp->kml[$idx])."';";
					$code .= "kmlurl = kmlurl.replace(/&amp;/g, String.fromCharCode(38));";
					$code .= "\nxml".$this->_mp->mapnm."[".$idx."] = new GGeoXml(kmlurl);";
					$code .= "\nmap".$this->_mp->mapnm.".addOverlay(xml".$this->_mp->mapnm."[".$idx."]);";
					if (strpos($this->_mp->kml[$idx], '.kmz')!=0)
						$kmz = true;
				}
				if ($kmz) {
					$code .= "\n   GEvent.addListener(map".$this->_mp->mapnm.", 'infowindowopen', function() {
						var divs = map".$this->_mp->mapnm.".getContainer().getElementsByTagName('div');
						for (var n = 0 ; n < divs.length ; ++n) {
							if (divs[n].id == 'iw_kml') {
								var imgs = divs[n].getElementsByTagName('img');
								for (var j = 0 ; j < imgs.length ; ++j) {
									var index = imgs[j].src.indexOf('/mapsatt');
									if (index != -1)
										imgs[j].src = 'http://maps.google.com' + imgs[j].src.substr(index);
								}
							}
						}
					}
					);";
				}
				if ($this->_inline_coords==0) {
					
					$code .= "\nGEvent.addListener(xml".$this->_mp->mapnm."[0], 'load', function() {
								if (xml".$this->_mp->mapnm."[0].loadedCorrectly()) {";
					$code .= "\nxml".$this->_mp->mapnm."[0].gotoDefaultViewport(map".$this->_mp->mapnm.");";
					if ($this->_mp->corzoom!='0')
						$code .= "\nmap".$this->_mp->mapnm.".setZoom(map".$this->_mp->mapnm.".getZoom()+".$this->_mp->corzoom.");";
					$code .= "\nsavePositionKML".$this->_mp->mapnm."();"; 
					$code .= "\n}
							});";
				}
				if (count($this->_mp->kmlsb)!=0) {
					$this->_mp->kmlrenderer = 'geoxml';
					$this->_mp->kml=$this->_mp->kmlsb;
				}
			}
			
			if ($this->_mp->kmlrenderer=="arcgis") {
				$code .= "var xml = [];";
				foreach ($this->_mp->kml as $idx => $val) {
					$code .= "var kmlurl = '".$this->_make_absolute($this->_mp->kml[$idx])."';";
					$code .= "\nkmlurl = kmlurl.replace(/&amp;/g, String.fromCharCode(38));";
					$code .= "\nxml[".$idx."] = new esri.arcgis.gmaps.DynamicMapServiceLayer(kmlurl, null, 0.75, dynmapcallback".$this->_mp->mapnm.");";
				}
			}
			
			if ($this->_mp->kmlrenderer=="geoxml") {
				$code .= "\nvar kml".$this->_mp->mapnm." = [];";
				foreach ($this->_mp->kml as $idx => $val) {
					$code .= "\nvar kmlurl = '".(($this->_mp->proxy=='1')?$this->_make_absolute($this->_mp->kml[$idx]):$this->_mp->kml[$idx])."';";
					$code .= "\nkmlurl = escape(kmlurl.replace(/&amp;/g, String.fromCharCode(38)));";
					$code .= "\nkml".$this->_mp->mapnm.".push(kmlurl);";
				}
				$xmloptions = array();
				if ($this->_mp->kmlsidebar=="left"||$this->_mp->kmlsidebar=="right") {
					$xmloptions[] = "sidebarid: 'kmlsidebar".$this->_mp->mapnm."'";
				} else {
					if ($this->_mp->kmlsidebar!="none")
						$xmloptions[] = "sidebarid: '".$this->_mp->kmlsidebar."'";
				}
				if ($this->_mp->kmlmessshow=='1')
					$xmloptions[] = "messshow: true";
				
				if ($this->_inline_coords==1)
					$xmloptions[] = "nozoom: true";
	
				if ($this->_mp->dir!='0')
					$xmloptions[] = "directions: true";
					
				if ($this->_mp->kmlfoldersopen!='0')
					$xmloptions[] = "allfoldersopen: true";
					
				if ($this->_mp->kmlhide!='0')
					$xmloptions[] = "hideall: true";

				if ($this->_mp->kmlscale!='0')
					$xmloptions[] = "scale: true";

				if ($this->_mp->kmlopenmethod!='0')
					$xmloptions[] = "iwmethod: '".$this->_mp->kmlopenmethod."'";
				
				if ($this->_mp->kmlsbsort=='asc') {
					$xmloptions[] = "sortbyname: 'asc'";
				}elseif ($this->_mp->kmlsbsort=='desc') {
					$xmloptions[] = "sortbyname: 'desc'";
				} else 	
					$xmloptions[] = "sortbyname: 'none'";
	
				if ($this->_mp->kmlclickablemarkers!='1')
					$xmloptions[] = "clickablemarkers: false";
					
				if ($this->_mp->kmlzoommarkers!='0')
					$xmloptions[] = "zoommarkers: '".$this->_mp->kmlzoommarkers."'";

				if ($this->_mp->kmlopendivmarkers!='')
					$xmloptions[] = "opendivmarkers: '".$this->_mp->kmlopendivmarkers."'";

				if ($this->_mp->kmlcontentlinkmarkers!='0')
					$xmloptions[] = "contentlinkmarkers: true";

				if ($this->_mp->kmllinkablemarkers!='0')
					$xmloptions[] = "linkablemarkers: true";

				if ($this->_mp->kmllinktarget!='')
					$xmloptions[] = "linktarget: '".$this->_mp->kmllinktarget."'";

				if ($this->_mp->kmllinkmethod!='')
					$xmloptions[] = "linkmethod: '".$this->_mp->kmllinkmethod."'";

				if (($this->_mp->kmlpolylabel!=""&&$this->_mp->kmlpolylabelclass!="")) {
					$xmloptions[] = "polylabelopacity: '".$this->_mp->kmlpolylabel."'";
					$xmloptions[] = "polylabelclass: '".$this->_mp->kmlpolylabelclass."'";
				}
				if (($this->_mp->kmlmarkerlabel!=""&&$this->_mp->kmlmarkerlabelclass!="")) {
					$xmloptions[] = "pointlabelopacity: '".$this->_mp->kmlmarkerlabel."'";
					$xmloptions[] = "pointlabelclass: '".$this->_mp->kmlmarkerlabelclass."'";
				}
				if ($this->_mp->icon!='')
					$xmloptions[] ="baseicon : markericon".$this->_mp->mapnm;
	
				if ($this->_mp->maxcluster!=''&&$this->_mp->gridsize!='') {
					$clusteroptions = array();
					if ($this->_mp->maxcluster!='')
						$clusteroptions[] ="maxVisibleMarkers : ".$this->_mp->maxcluster;
					if ($this->_mp->gridsize!='')
						$clusteroptions[] ="gridSize : ".$this->_mp->gridsize;
					if ($this->_mp->minmarkerscluster!='')
						$clusteroptions[] ="minMarkersPerCluster : ".$this->_mp->minmarkerscluster;
					if ($this->_mp->maxlinesinfocluster!='')
						$clusteroptions[] ="maxLinesPerInfoBox : ".$this->_mp->maxlinesinfocluster;
					if ($this->_mp->clusterinfowindow!='')
						$clusteroptions[] ="ClusterInfoWindow : '".$this->_mp->clusterinfowindow."'" ;
					if ($this->_mp->clusterzoom!='')
						$clusteroptions[] ="ClusterZoom : '".$this->_mp->clusterzoom."'" ;
					if ($this->_mp->clustermarkerzoom!='')
						$clusteroptions[] ="ClusterMarkerZoom : ".$this->_mp->clustermarkerzoom;
					if ($this->_mp->icon!='')
						$clusteroptions[] ="Icon : markericon".$this->_mp->mapnm;
	
					$xmloptions[] = "clustering : {".implode(",",$clusteroptions)."}";
				}
				
				$xmloptions[] = "titlestyle: ' '";
					
				$code .= "\nexml".$this->_mp->mapnm." = new GeoXml(\"exml".$this->_mp->mapnm."\", map".$this->_mp->mapnm.", kml".$this->_mp->mapnm.", {".implode(",",$xmloptions)."});";
				$code .= "\nexml".$this->_mp->mapnm.".parse(); ";
				if ($this->_inline_coords==0&&$this->_mp->corzoom!='0')
					$code .= "\nsetTimeout('map".$this->_mp->mapnm.".setZoom(map".$this->_mp->mapnm.".getZoom()+".$this->_mp->corzoom.")', 750);";
			}
		}
	
		if ($this->_mp->traffic=='1') {
			$code .= "\ntrafficInfo".$this->_mp->mapnm." = new GTrafficOverlay();";
			$code .= "\nmap".$this->_mp->mapnm.".addOverlay(trafficInfo".$this->_mp->mapnm.");";
		}
	
		if ($this->_mp->panoramio!="none") {
			$code .= "\nmap".$this->_mp->mapnm.".addOverlay(new GLayer('com.panoramio.".$this->_mp->panoramio."'));";
		}
		if ($this->_mp->panotype!="none") {
			$code .= "\n  var options = {
							order: '".$this->_mp->panoorder."',
							set: '".$this->_mp->panotype."', 
							to: '".$this->_mp->panomax."' };
						panoLayer".$this->_mp->mapnm." = new PanoramioLayer(map".$this->_mp->mapnm.", options);
						panoLayer".$this->_mp->mapnm.".enable();";
		}
		
		if ($this->_mp->youtube!="none") {
			$code .= "\nmap".$this->_mp->mapnm.".addOverlay(new GLayer('com.youtube.".$this->_mp->youtube."'));";
		}
	
		if ($this->_mp->wiki!="none") {
			$code .= "\nmap".$this->_mp->mapnm.".addOverlay(new GLayer('org.wikipedia.".$this->_mp->wiki."'));";
		}
		
		if (count($this->_mp->layer)>0) {
			foreach ($this->_mp->layer as $lay) {
				$code .= "\nmap".$this->_mp->mapnm.".addOverlay(new GLayer('".$lay."'));";
			}
		}
		
		if ($this->_mp->localsearch=='1') {
			$code .= "localsearch".$this->_mp->mapnm." = new google.maps.LocalSearch(".((!empty($searchoptions))?"{ ".$searchoptions." }":"").");";
			$code .= "map".$this->_mp->mapnm.".addControl(localsearch".$this->_mp->mapnm.", new GControlPosition(G_ANCHOR_BOTTOM_RIGHT, new GSize(10,20)));";
			if (!empty($this->_mp->searchtext))
				$code .= "localsearch".$this->_mp->mapnm.".execute('".$this->_mp->searchtext."');";
		}
		
		if ($this->_mp->googlebar=='1') {
			$code .= "map".$this->_mp->mapnm.".enableGoogleBar();";
		}
	
		if ($this->_mp->adsmanager=='1') {
			$code .= "adsmanager".$this->_mp->mapnm." = new GAdsManager(map".$this->_mp->mapnm.", ".((!empty($this->_mp->adsense))?"'".$this->_mp->adsense."'":"''").", { style: 'adunit', maxAdsOnMap: ".$this->_mp->maxads.((!empty($this->_mp->searchtext))?", keywords: '".$this->_mp->searchtext."'":"").((!empty($this->_mp->channel)&&!empty($this->_mp->adsense))?", channel: '".$this->_mp->channel."'":"").(($this->_mp->localsearch=='1')?", position: new GControlPosition(G_ANCHOR_BOTTOM_LEFT, new GSize(20,20))":"")."}); ";
			$code .= "adsmanager".$this->_mp->mapnm.".enable();";
		}
	
		if ($this->debug_plugin=="1")
			$code.="map".$this->_mp->mapnm.".addControl(new VersionControl());";
	
		if (((!empty($this->_mp->tolat)&&!empty($this->_mp->tolon))||!empty($this->_mp->toaddress))&&$this->_mp->animdir=='0'&&$this->_mp->formaddress!='1') {
			// Route
			$xmloptions = array();
			if ($this->_mp->dirtype=='W')
				$xmloptions[] = "travelMode : G_TRAVEL_MODE_WALKING";
			else
				$xmloptions[] = "travelMode : G_TRAVEL_MODE_DRIVING";
			
			if ($this->_mp->avoidhighways=='1')
				$xmloptions[] = "avoidHighways : true";
			else
				$xmloptions[] = "avoidHighways : false";
			
			$code .= "var dirsidebar".$this->_mp->mapnm." = document.getElementById('dirsidebar".$this->_mp->mapnm."');";
			$code .= "if (directions".$this->_mp->mapnm.") {
							directions".$this->_mp->mapnm.".clear();
							if ( dirsidebar".$this->_mp->mapnm.".hasChildNodes() )
							{
								while ( dirsidebar".$this->_mp->mapnm.".childNodes.length >= 1 )
								{
									dirsidebar".$this->_mp->mapnm.".removeChild( dirsidebar".$this->_mp->mapnm.".firstChild );
								} 
							}
					} else {
							directions".$this->_mp->mapnm." = new GDirections(map".$this->_mp->mapnm.", dirsidebar".$this->_mp->mapnm.");
							GEvent.addListener(directions".$this->_mp->mapnm.", 'error', handleErrors".$this->_mp->mapnm.");
						}
				";
				
			if (is_array($this->_mp->waypoints)&&count($this->_mp->waypoints)>0) {
				if ($this->_mp->address!="")
					array_unshift($this->_mp->waypoints, $this->_mp->address);
				else if ($lat !=""&&$lon!="")
					array_unshift($this->_mp->waypoints, $lat.", ".$lon);
				
				if ($this->_mp->toaddress!="")
					array_push($this->_mp->waypoints, $this->_mp->toaddress);
				else if ($this->_mp->tolat!=""&&$this->_mp->tolon!="")
					array_push($this->_mp->waypoints, $this->_mp->tolat.", ".$this->_mp->tolon);
				
				$wpstring="";
				foreach ($this->_mp->waypoints as $wp) {
					if ($wpstring!="")
						$wpstring.= ", ";
					$wpstring .= "'".$wp."'";
				}
				$code.="\ndirections".$this->_mp->mapnm.".loadFromWaypoints([".$wpstring."], {".implode(",",$xmloptions)."});";
			} else
				$code.="\ndirections".$this->_mp->mapnm.".load('from: ".(($this->_mp->address!="")?$this->_mp->address:(($this->_mp->latitude!='')?$this->_mp->latitude:$this->_mp->deflatitude).", ".(($this->_mp->longitude!='')?$this->_mp->longitude:$this->_mp->deflongitude))." to: ".(($this->_mp->toaddress!="")?$this->_mp->toaddress:$this->_mp->tolat.", ".$this->_mp->tolon)."', {".implode(",",$xmloptions)."});";
		}
		
		switch (strtolower($this->_mp->mapType)) {
		case "satellite":
			$code.="\nmap".$this->_mp->mapnm.".setMapType(G_SATELLITE_MAP);";
			break;
		
		case "hybrid":
			$code.="\nmap".$this->_mp->mapnm.".setMapType(G_HYBRID_MAP);";
			break;
	
		case "terrain":
			$code.="\nmap".$this->_mp->mapnm.".setMapType(G_PHYSICAL_MAP);";
			break;
	
		case "earth":
			$code.="\nmap".$this->_mp->mapnm.".setMapType(G_SATELLITE_3D_MAP);";
			$code.="\nmap".$this->_mp->mapnm.".getEarthInstance(initearth".$this->_mp->mapnm.");";
			break;
		
		default:
			$code.="\nmap".$this->_mp->mapnm.".setMapType(G_NORMAL_MAP);";
			break;
		}
		
		$code .="\nvar mt = map".$this->_mp->mapnm.".getMapTypes();
		for (var i=0; i<mt.length; i++) {
			mt[i].getMinimumResolution = function() {return ".$this->_mp->minzoom.";};
			mt[i].getMaximumResolution = function() {return ".$this->_mp->maxzoom.";};
		}";
	
		if($this->_mp->zoomnew=='1'&&$this->_mp->controltype=='user')
		{
			$code.="
			map".$this->_mp->mapnm.".enableContinuousZoom();
			map".$this->_mp->mapnm.".enableDoubleClickZoom();
			";
		} else {
			$code.="
			map".$this->_mp->mapnm.".disableContinuousZoom();
			map".$this->_mp->mapnm.".disableDoubleClickZoom();
			";
		}
	
		if($this->_mp->zoomwheel=='1'&&$this->_mp->controltype=='user')
		{
			$code.="map".$this->_mp->mapnm.".enableScrollWheelZoom();
			";
		} 
	
		if (($this->_inline_coords == 0 && count($this->_mp->kml)==0) // No inline coordinates and no kml => standard configuration
			||($this->_mp->latitude !=''&&$this->_mp->longitude!=''&&!($this->_mp->geocoded==1&&$this->_mp->toaddress!=''&&$this->_mp->description==''))) { // Inline coordinates and text is not empty
			$options = '';
			
			if ($this->_mp->tooltip!='') 
				$options .= (($options!='')?', ':'')."title:\"".$this->_mp->tooltip."\"";
			if ($this->_mp->icon!='')
				$options .= (($options!='')?', ':'')."icon:markericon".$this->_mp->mapnm;
			
			$code.="var marker".$this->_mp->mapnm." = new GMarker(point".(($options!='')?', {'.$options.'}':'').");";
			
			$code.="map".$this->_mp->mapnm.".addOverlay(marker".$this->_mp->mapnm.");
			";
	
			if ($this->_mp->description!=''||$this->_mp->dir!='0') {
				// convert $this->_mp->description to maybe tabs?
				// Check <tab> tag
				$reg='/(<tab\s*?(title=\\\?"(.*?)\\\?")?>)(.*?)(<\/tab>)/si';
				$c=preg_match_all($reg,$this->_mp->description,$m);
	
				// if <tab> then make array of $this->_mp->description
				if ($c>0) {
					$this->_mp->description= array();
					for ($z=0;$z<$c;$z++) {
						// transform attribute title to title of tab
						$this->_mp->description[$z]->title = htmlspecialchars_decode($m[3][$z], ENT_NOQUOTES);
						$this->_mp->description[$z]->text = htmlspecialchars_decode($m[4][$z], ENT_NOQUOTES);
					}
				}
				if ($this->_mp->dir!='0') {
					$dirform="<form id='directionform".$this->_mp->mapnm."' action='".$this->protocol.$this->googlewebsite."/maps' method='get' target='_blank' onsubmit='DirectionMarkersubmit".$this->_mp->mapnm."(this);return false;' class='mapdirform'>";
					
					$dirform.=$this->_mp->txtdir."<input ".(($this->_mp->txtto=='')?"type='hidden' ":"type='radio' ")." ".(($this->_mp->dirdefault=='0')?"checked='checked'":"")." name='dir' value='to'>".(($this->_mp->txtto!='')?$this->_mp->txtto."&nbsp;":"")."<input ".(($this->_mp->txtfrom=='')?"type='hidden' ":"type='radio' ").(($this->_mp->dirdefault=='1')?"checked='checked'":"")." name='dir' value='from'>".(($this->_mp->txtfrom!='')?$this->_mp->txtfrom:"");
					$dirform.="<br />".$this->_mp->txtdiraddr."<input type='text' class='inputbox' size='20' name='saddr' id='saddr' value='' /><br />";
	
					if ($this->_mp->txt_driving!=''||$this->_mp->dirtype=="D")
	
						$dirform.="<input ".(($this->_mp->txt_driving=='')?"type='hidden' ":"type='radio' ")."class='radio' name='dirflg' value='' ".(($this->_mp->dirtype=="D")?"checked='checked'":"")." />".$this->_mp->txt_driving.(($this->_mp->txt_driving!='')?"&nbsp;":"");
					if ($this->_mp->txt_avhighways!=''||$this->_mp->dirtype=="1")
						$dirform.="<input ".(($this->_mp->txt_avhighways=='')?"type='hidden' ":"type='radio' ")."class='radio' name='dirflg' value='h' ".(($this->_mp->avoidhighways=='1')?"checked='checked'":"")." />".$this->_mp->txt_avhighways.(($this->_mp->txt_avhighways!='')?"&nbsp;":"");
					if ($this->_mp->txt_walking!=''||$this->_mp->dirtype=="W")
						$dirform.="<input ".(($this->_mp->txt_walking=='')?"type='hidden' ":"type='radio' ")."class='radio' name='dirflg' value='w' ".(($this->_mp->dirtype=="W")?"checked='checked'":"")." />".$this->_mp->txt_walking.(($this->_mp->txt_walking!='')?"&nbsp;":"");
					if ($this->_mp->txt_driving!=''||$this->_mp->txt_avhighways!=''||$this->_mp->txt_walking!='')
						$dirform.="<br />";	
					$dirform.="<input value='".$this->_mp->txtgetdir."' class='button' type='submit' style='margin-top: 2px;'>";
					
					if ($this->_mp->dir=='2')
						$dirform.= "<input type='hidden' name='pw' value='2'/>";
	
					if ($this->_mp->lang!='') 
						$dirform.= "<input type='hidden' name='hl' value='".$this->_mp->lang."'/>";
	
					if (!empty($this->_mp->address))
						$dirform.="<input type='hidden' name='daddr' value='".$this->_mp->address." (".(($this->_mp->latitude!='')?$this->_mp->latitude:$this->_mp->deflatitude).", ".(($this->_mp->longitude!='')?$this->_mp->longitude:$this->_mp->deflongitude).")'/></form>";
					else
						$dirform.="<input type='hidden' name='daddr' value='".(($this->_mp->latitude!='')?$this->_mp->latitude:$this->_mp->deflatitude).", ".(($this->_mp->longitude!='')?$this->_mp->longitude:$this->_mp->deflongitude)."'/></form>";
					
					// Add form before div or at the end of the html.
					if (is_array($this->_mp->description)) {
						$this->_mp->description[$z+1]->title = $this->_mp->txtdir;
						$this->_mp->description[$z+1]->text = htmlspecialchars_decode($dirform, ENT_NOQUOTES);
					} else {
						$pat="/&lt;\/div&gt;$/";
						if (preg_match($pat, $this->_mp->description))
							$this->_mp->description = preg_replace($pat, $dirform."</div>", $this->_mp->description);
						else {
							$pat="/<\/div>$/";
							if (preg_match($pat, $this->_mp->description))
								$this->_mp->description = preg_replace($pat, $dirform."</div>", $this->_mp->description);
							else
								$this->_mp->description.=$dirform;
						}
					}
				}
				
				if (!is_array($this->_mp->description))
					$this->_mp->description = htmlspecialchars_decode($this->_mp->description, ENT_NOQUOTES);
	
				// If marker 
				if ($this->_mp->marker==1) {
					if (is_array($this->_mp->description)) {
						$code .= "marker".$this->_mp->mapnm.".openInfoWindowTabsHtml([";
						$first = true;
						foreach ($this->_mp->description as $tab) {
							if ($first) 
								$first = false;
							else 
								$code.=",  ";
								
							$code.= "new GInfoWindowTab(\"".$tab->title."\", \"".$tab->text."\")";
						}
						
						$code .= "]);";  
						
					} else
						$code.="marker".$this->_mp->mapnm.".openInfoWindowHtml(\"".$this->_mp->description."\");"; 
				}
				
				$code.="GEvent.addListener(marker".$this->_mp->mapnm.", 'click', function() {
						marker".$this->_mp->mapnm;
				if (is_array($this->_mp->description)) {
					$code .=".openInfoWindowTabsHtml([";
					$first = true;
					foreach ($this->_mp->description as $tab) {
						if ($first) 
							$first = false;
						else 
							$code.=",  ";
							
						$code.= "new GInfoWindowTab(\"".$tab->title."\", \"".$tab->text."\")";
					}
					
					$code .= "]);";  
					
				} else
					$code.=".openInfoWindowHtml(\"".$this->_mp->description."\");";
					
				$code.="});
				";
			}
		}
		
		if ($this->_mp->imageurl!='') {
			$code .= "imageovl".$this->_mp->mapnm." = new GScreenOverlay('{$this->_mp->imageurl}',
									new GScreenPoint({$this->_mp->imagex}, {$this->_mp->imagey}, '{$this->_mp->imagexyunits}', '{$this->_mp->imagexyunits}'),  // screenXY
									new GScreenPoint({$this->_mp->imageanchorx}, {$this->_mp->imageanchory}, '{$this->_mp->imageanchorunits}', '{$this->_mp->imageanchorunits}'),  // overlayXY
									new GScreenSize({$this->_mp->imagewidth}, {$this->_mp->imageheight})  // size on screen
								);
						map".$this->_mp->mapnm.".addOverlay(imageovl".$this->_mp->mapnm.");
				";
		}
		if ($this->_mp->animdir=='0'&&($this->_mp->sv=='top'||$this->_mp->sv=='bottom'||($this->_mp->sv!='none'&&$this->_mp->sv!='top'&&$this->_mp->sv!='bottom'))) {
			if ($this->_mp->sv!='none'&&$this->_mp->sv!='top'&&$this->_mp->sv!='bottom')
				$code.="\npanobj".$this->_mp->mapnm." = document.getElementById('".$this->_mp->sv."');
						";
			else
				$code.="\npanobj".$this->_mp->mapnm." = document.getElementById('svpanorama".$this->_mp->mapnm."');
						";
			$this->_mp->svopt = "";
			if ($this->_mp->svyaw!='0')
				$this->_mp->svopt .= "yaw:".$this->_mp->svyaw;
			if ($this->_mp->svpitch!='0')
				$this->_mp->svopt .= (($this->_mp->svopt=="")?"":", ")."pitch:".$this->_mp->svpitch;
			if ($this->_mp->svzoom!='')
				$this->_mp->svopt .= (($this->_mp->svopt=="")?"":", ")."zoom:".$this->_mp->svzoom;
				
			$code.="\nsvpanorama".$this->_mp->mapnm." = new GStreetviewPanorama(panobj".$this->_mp->mapnm.");
					svlastpoint".$this->_mp->mapnm." = map".$this->_mp->mapnm.".getCenter();
					svpanorama".$this->_mp->mapnm.".setLocationAndPOV(svlastpoint".$this->_mp->mapnm.", ".(($this->_mp->svopt!='')?"{".$this->_mp->svopt."}":'null').");
					svmarker".$this->_mp->mapnm." = new GMarker(svlastpoint".$this->_mp->mapnm.", {icon: guyIcon".$this->_mp->mapnm." , draggable: true});
					map".$this->_mp->mapnm.".addOverlay(svmarker".$this->_mp->mapnm.");
					GEvent.addListener(svmarker".$this->_mp->mapnm.", 'dragend', onDragEnd".$this->_mp->mapnm.");
					GEvent.addListener(svpanorama".$this->_mp->mapnm.", 'initialized', onNewLocation".$this->_mp->mapnm.");
					GEvent.addListener(svpanorama".$this->_mp->mapnm.", 'yawchanged', onYawChange".$this->_mp->mapnm."); 
					";
			if ($this->_mp->svautorotate=="1")		
				$code.="\npanobj".$this->_mp->mapnm.".addEventListener('mouseover', svmouseover".$this->_mp->mapnm.", true);
					panobj".$this->_mp->mapnm.".addEventListener('mouseout', svmouseout".$this->_mp->mapnm.", true);
					";
		}
	
		if ($this->_mp->animdir!="0") {
			$xmloptions = array();
			$xmloptions[] = "preserveViewport: false";
			$xmloptions[] = "getSteps: true";
			
			if ($this->_mp->dirtype=='W')
				$xmloptions[] = "travelMode : G_TRAVEL_MODE_WALKING";
			else
				$xmloptions[] = "travelMode : G_TRAVEL_MODE_DRIVING";
			
			if ($this->_mp->avoidhighways=='1')
				$xmloptions[] = "avoidHighways : true";
			else
				$xmloptions[] = "avoidHighways : false";
				
			$opts = array();
			if ($this->_mp->animspeed!=1)
				$opts[] = "Speed : ".$this->_mp->animspeed;
			if ($this->_mp->animautostart!=0)
				$opts[] = "AutoStart : true";
			if ($this->_mp->animunit!='')
				$opts[] = "Unit : '".$this->_mp->animunit."'";
	//					$opts[] = "zoomlevel : ".$this->_mp->zoom;
			if ($this->_mp->dirtype=='W')
				$opts[] = "travelMode : G_TRAVEL_MODE_WALKING";
			else
				$opts[] = "travelMode : G_TRAVEL_MODE_DRIVING";
			
			if ($this->_mp->avoidhighways=='1')
				$opts[] = "avoidHighways : true";
			else
				$opts[] = "avoidHighways : false";
	
			$code.="\nvar panobj = document.getElementById('svpanorama".$this->_mp->mapnm."');
					svpanorama".$this->_mp->mapnm." = new GStreetviewPanorama(panobj);
					directions".$this->_mp->mapnm." = new GDirections(map".$this->_mp->mapnm.");
					";
	
			$lang = "";
			foreach ($this->_langanim as $al) {
				$lang.=(($lang=='')?"":",")."'".$al."'";
			}
			
			$code.="\nopts = {".implode(",",$opts)."};
					lang = [".$lang."];
					";
			$code .="\nroute".$this->_mp->mapnm." = new Directionsobj('route".$this->_mp->mapnm."', map".$this->_mp->mapnm.", '".$this->_mp->mapnm."', svpanorama".$this->_mp->mapnm.", svclient".$this->_mp->mapnm.", directions".$this->_mp->mapnm.", centerpoint, opts, lang);";
			
			if (is_array($this->_mp->waypoints)&&count($this->_mp->waypoints)>0) {
				if ($this->_mp->address!="")
					array_unshift($this->_mp->waypoints, $this->_mp->address);
				if ($this->_mp->toaddress!="")

					array_push($this->_mp->waypoints, $this->_mp->toaddress);
				$wpstring="";
				foreach ($this->_mp->waypoints as $wp) {
					if ($wpstring!="")
						$wpstring.= ", ";
					$wpstring .= "'".$wp."'";
				}
				$code.="\ndirections".$this->_mp->mapnm.".loadFromWaypoints([".$wpstring."], {".implode(",",$xmloptions)."});";
			} else
				$code.="\ndirections".$this->_mp->mapnm.".load('from: ".$this->_mp->address." to: ".$this->_mp->toaddress."', {".implode(",",$xmloptions)."});";
		}
		
		if ($this->_mp->tilelayer!="") {
			$this->_mp->tilebounds=explode(",", $this->_mp->tilebounds);
			if (count($this->_mp->tilebounds)==4) {
				$code .="\nvar tileopts = {};";				
				if ($this->_mp->tilemethod!='maptiler') { 
					$this->_mp->tilemethod = str_replace('[', '{', $this->_mp->tilemethod);
					$this->_mp->tilemethod = str_replace(']', '}', $this->_mp->tilemethod);
					$this->_mp->tilemethod = str_replace('&amp;', '&', $this->_mp->tilemethod);
					$code .="\ntileopts.tileUrlTemplate = '".$this->_make_absolute($this->_mp->tilemethod)."';";
				}
				
				$code .="\ncopyright".$this->_mp->mapnm." = new GCopyrightCollection('');";
				$code .="copyright".$this->_mp->mapnm.".addCopyright(new GCopyright('', new GLatLngBounds(new GLatLng(".$this->_mp->tilebounds[0].", ".$this->_mp->tilebounds[1]."), new GLatLng(".$this->_mp->tilebounds[2].", ".$this->_mp->tilebounds[3].")), ".$this->_mp->tileminzoom.",''));";				
				$code .="\ntilelayer".$this->_mp->mapnm." = new GTileLayer(copyright".$this->_mp->mapnm.", ".$this->_mp->tileminzoom.", ".$this->_mp->tilemaxzoom.", tileopts);";
				
				$code .="\ntilelayer".$this->_mp->mapnm.".isPng = function() { return true;};
				tilelayer".$this->_mp->mapnm.".getOpacity = function() { return ".$this->_mp->tileopacity."; };";
				if ($this->_mp->tilemethod=='maptiler') {
					$code .="\nmercator".$this->_mp->mapnm." = new GMercatorProjection(".($this->_mp->tilemaxzoom+1).");
					tilelayer".$this->_mp->mapnm.".getTileUrl = function(tile,zoom) {
						if ((zoom < ".$this->_mp->tileminzoom.") || (zoom > ".$this->_mp->tilemaxzoom.")) {
							return '".$this->_make_absolute($this->_mp->tilelayer)."/none.png';
						} 
						var ymax = 1 << zoom;
						var y = ymax - tile.y -1;
						var tileBounds = new GLatLngBounds(
							mercator".$this->_mp->mapnm.".fromPixelToLatLng( new GPoint( (tile.x)*256, (tile.y+1)*256 ) , zoom ),
							mercator".$this->_mp->mapnm.".fromPixelToLatLng( new GPoint( (tile.x+1)*256, (tile.y)*256 ) , zoom )
						);
						if (tileBounds".$this->_mp->mapnm.".intersects(tileBounds)) {
							return '".$this->_make_absolute($this->_mp->tilelayer)."/'+zoom+'/'+tile.x+'/'+y+'.png';
						} else {
							return '".$this->_make_absolute($this->_mp->tilelayer)."/none.png';
						}
					};
					tileBounds".$this->_mp->mapnm." = new GLatLngBounds(new GLatLng(".$this->_mp->tilebounds[0].", ".$this->_mp->tilebounds[1]."), new GLatLng(".$this->_mp->tilebounds[2].", ".$this->_mp->tilebounds[3]."));";
				}

				$code .="\nvar overlay".$this->_mp->mapnm." = new GTileLayerOverlay( tilelayer".$this->_mp->mapnm.", {zPriority:0 } );
				map".$this->_mp->mapnm.".addOverlay(overlay".$this->_mp->mapnm.");";
			}
		}
		
		if($this->_mp->zoomwheel=='1')
		{
			$code.="GEvent.addDomListener(tst".$this->_mp->mapnm.", 'DOMMouseScroll', CancelEvent".$this->_mp->mapnm.");
					GEvent.addDomListener(tst".$this->_mp->mapnm.", 'mousewheel', CancelEvent".$this->_mp->mapnm.");
				";
		}
	
		/* remove link in google logo. Do not use
		$code.= "\nvar func".$this->_mp->mapnm." = function () {";
		$code.= "\n	var test_div = document.getElementById('googlemap".$this->_mp->mapnm."');";
		$code.= "\n	var test_obj = test_div.childNodes[1];";
		$code.= "\n	test_obj = test_obj.getElementsByTagName('a');";
		$code.= "\n	if (test_obj&&test_obj.length>0)";
		$code.= "\n		test_obj[0].href = '".$this->protocol.$this->googlewebsite."';";
		$code.= "\n};";
		$code.= "\nsetTimeout(func".$this->_mp->mapnm.", 1500);";
		*/
		
		/* remove copyright, terms and mapdata. Do not use 					
		$code.= "test_div = document.getElementById('googlemap".$this->_mp->mapnm."');";
		$code.= "test_obj = test_div.childNodes[1].style.display='none';";
		$code.= "test_obj = test_div.childNodes[2].style.display='none';";
		*/
	
		if($this->_client_geo == 1) {
			if ($this->clientgeotype=="local")
				$code.="	});
					localSearch.execute(address);";
			else
				$code.="		       
							  });";
		}
	
		// End of script voor showing the map 
		if ($this->_mp->show!=0)
			$code.="\n	}";
			
		$code.="\n}
		/*]]>*/</script>
		";
		
		// Call the Maps through timeout to render in IE also
		// Set an event for watching the changing of the map so it can refresh itself
		$code.= "<script type=\"text/javascript\">/*<![CDATA[*/
				if (GBrowserIsCompatible()) {
					obj = document.getElementById('mapbody".$this->_mp->mapnm."');
					obj.style.display = 'block';
					window.onunload=function(){window.onunload;GUnload()};
					tst".$this->_mp->mapnm.".setAttribute(\"oldValue\",0);
					tst".$this->_mp->mapnm.".setAttribute(\"refreshMap\",0);
					";
		
		if ($this->_mp->loadmootools=='1') {
		$code.= "if (window.MooTools==null)
					tstint".$this->_mp->mapnm."=setInterval(\"checkMap".$this->_mp->mapnm."()\",".$this->timeinterval.");
				else
					window.addEvent('domready', function() {
							tstint".$this->_mp->mapnm."=setInterval('checkMap".$this->_mp->mapnm."()', ".$this->timeinterval.");
						});
				";
		} else {
			$code.= "tstint".$this->_mp->mapnm."=setInterval(\"checkMap".$this->_mp->mapnm."()\",".$this->timeinterval.");
					";
		}
		
		$code.= "}
		/*]]>*/</script>
		";
	
		// Clean up variables except generated code and memory variables
		unset($fields, $value, $values, $coord, $tocoord, $client_togeo, $searchoption, $lboptions, $url, $la, $cam, $replace, $addr, $idx, $val, $xmloptions, $clusteroptions, $wpstring, $wp, $options, $reg, $c, $z, $dirform, $first, $opts, $al, $kmz);
		
		return array($code, $lbcode);
	}
	
	function _findgeoparam() {
		// Find latitude, longitude or address inside the text
		// Later tolat, tolon or toaddress
	
		$reg='/<td\b[^>]*><strong>Latitude:<\/strong>(.*?)<\/td>/si';
		$c=preg_match_all($reg,$this->_text,$m);
		if ($c>0) {
			$this->_mp->latitude=$this->_remove_html_tags($m[1][0]);
			$this->_inline_coords = 1;
		}
			
		$reg='/<td\b[^>]*><strong>Longitude:<\/strong>(.*?)<\/td>/si';
		$c=preg_match_all($reg,$this->_text,$m);
		if ($c>0) {
			$this->_mp->longitude=$this->_remove_html_tags($m[1][0]);
			$this->_inline_coords = 1;
		}

		$reg='/<td\b[^>]*><strong>City:<\/strong>(.*?)<\/td>/si';
		$c=preg_match_all($reg,$this->_text,$m);
		if ($c>0)
			$this->_mp->address = $m[1][0];
	}
	
	function _processMapv3() {
		// Variables of process
		$code='';
		$lbcode='';
		
		//Detect browsers for special changes
		$iphone = strpos($_SERVER['HTTP_USER_AGENT']," iPhone");
		$android = strpos($_SERVER['HTTP_USER_AGENT'],"Android");
		$ipod = strpos($_SERVER['HTTP_USER_AGENT']," iPod");
//		Setting width and height is not correct because in mobile browser it's a wesbite rendering and width 100% or height 100% i snot supported.
//		if($iphone || $android || $ipod) {
//			$this->_mp->width = '100%';
//			$this->_mp->height = '100%';
//		}
		
		// Iphone or Ipod add special meta tag
//		if($iphone || $ipod) {
//			$this->document->setMetaData("viewport", "initial-scale=1.0, user-scalable=no");
//		}
		
		// No inline coordinates and no kml => standard configuration show marker based on defaults
		if ($this->_inline_coords == 0 && $this->_client_geo != 1 && count($this->_mp->kml)==0) { 
			$this->_mp->latitude = $this->_mp->deflatitude;
			$this->_mp->longitude = $this->_mp->deflongitude;
		}
		
		if (is_array($this->_mp->waypoints)) {
			$waypoints = array();
			foreach ($this->_mp->waypoints as $wp) {
				array_push($waypoints, $wp);
			}
			$this->_mp->waypoints = $waypoints;
			unset($waypoints);
		}

		if ($this->_mp->styledmap)
			$this->_styledmap = $this->_mp->styledmap;
		else
			$this->_styledmap = "null";
		
		unset($this->_mp->styledmap);
		
		$this->_processMapv3_scripts();
		
		list ($code, $lbcode) = $this->_processMapv3_template();
		
		$this->_processMapv3_markers();
		$this->_processMapv3_kml();
		$this->_processMapv3_tiles();
		$code .= $this->_processMapv3_icons();
		$this->_processMapv3_streetview();
	
		$code.="\n<script type='text/javascript'>/*<![CDATA[*/";
		
		if ($this->_mp->kmlrenderer=='geoxml') {
			if ($this->_mp->proxy=="1") {
				if (substr($this->jversion,0,3)=="1.5")
					$code .= "\nvar proxy = '".$this->base."/plugins/system/plugin_googlemap2_proxy.php?';";
				else
					$code .= "\nvar proxy = '".$this->base."/plugins/system/plugin_googlemap2/plugin_googlemap2_proxy.php?';";
			}
			$code.="\ntop.publishdirectory = '".$this->base."/media/plugin_googlemap2/site/geoxml/';";
		}

		$code.= "\nvar mapconfig".$this->_mp->mapnm." = ".$this->json_encode($this->_mp).";";
		$code.= "\nvar mapstyled".$this->_mp->mapnm." = ".$this->_styledmap.";";
		$code.= "\nvar googlemap".$this->_mp->mapnm." = new GoogleMaps('".$this->_mp->mapnm."', mapconfig".$this->_mp->mapnm.", mapstyled".$this->_mp->mapnm.");";
		$code.= "\n/*]]>*/</script>";
		
		return array($code, $lbcode);
	}
	
	function json_encode($a=false)
	{
		if (!function_exists('json_encode')) {
			if (is_null($a)) return 'null';
			if ($a === false) return 'false';
			if ($a === true) return 'true';
			if (is_scalar($a))
			{
			  if (is_float($a))
			  {
				// Always use "." for floats.
				return floatval(str_replace(",", ".", strval($a)));
			  }
			
			  if (is_string($a))
			  {
				static $jsonReplaces = array(array("\\", "/", "\n", "\t", "\r", "\b", "\f", '"'), array('\\\\', '\\/', '\\n', '\\t', '\\r', '\\b', '\\f', '\"'));
				return '"' . str_replace($jsonReplaces[0], $jsonReplaces[1], $a) . '"';

			  }
			  else
				return $a;
			}
			$isList = true;
			for ($i = 0, reset($a); $i < count($a); $i++, next($a))
			{
			  if (key($a) !== $i)
			  {
				$isList = false;
				break;
			  }
			}
			$result = array();
			if ($isList)
			{
			  foreach ($a as $v) $result[] = $this->json_encode($v);
			  return '[' . join(",", $result) . ']';
			}
			else
			{
			  foreach ($a as $k => $v) $result[] = $this->json_encode($k).':'.$this->json_encode($v);
			  return '{' . join(",", $result) . '}';
			}
		} else
			return json_encode($a);
	}
	
	function _processMapv3_scripts() {
		// Only add the scripts and css once
		//Load mootools first because it's necessary for googlemaps script instead of the extra functions
		if (($this->_mp->loadmootools=="1"&&$this->_mp->kmllightbox=="1"||$this->_mp->lightbox=="1"||$this->_mp->effect!="none"||$this->_mp->dir=="3"||$this->_mp->dir=="4"||strpos($this->_mp->description, "MOOdalBox"))&&$this->first_mootools) {
			if ($this->event!='onAfterRender') {
				if (substr($this->jversion,0,3)=='1.5')
					JHTML::_('behavior.mootools');
				else
					JHTML::_('behavior.framework',false);				
			} else {
				if (substr($this->jversion,0,3)=='1.5')
					$url = $this->base."/plugins/system/mtupgrade/mootools.js";
				else {
					$mooconfig = JFactory::getConfig();
		            $moodebug = $mooconfig->get('debug');
			        $moouncompressed   = $moodebug ? '-uncompressed' : '';
					$url = $this->base."/media/system/js/mootools-core".$moouncompressed.".js";
					unset($mooconfig, $moodebug, $moouncompressed);
				}
				$this->_addscript($url);
			}
			$this->first_mootools = false;
		}
		
		if($this->first_google) {
			if ($this->protocol=='http://')
				$url = $this->protocol.$this->googlewebsite."/maps/api/js?v=".$this->google_API_version;
			else {
				$url = 'maps.googleapis.com';
				$url = $this->protocol.$url."/maps/api/js?v=".$this->google_API_version;
			}
			
			if ($this->googlekey!="")
				$url .= "&amp;key=".$this->googlekey;

			if ($this->_mp->lang!='') 
				$url .= "&amp;language=".$this->_mp->lang;
			if ($this->region!='') 
				$url .= "&amp;region=".$this->region;

			$library = array();
			if ($this->_mp->autocompl!='none')
				$library[]='places';
			if ($this->_mp->weather=='1'||$this->_mp->weathercloud=='1')
				$library[]='weather';				

			if (count($library)>0)
				$url .= "&amp;libraries=".implode(',', $library);
				
			$url .= "&amp;sensor=false";
			
			$this->_addscript($url);
			$this->first_google=false;
		}
		
		if ($this->_mp->mapType=='earth'||$this->_mp->showearthmaptype=="1") {
			$this->_addscript($this->protocol."www.google.com/jsapi?key=".$this->googlekey);
			$this->_addscript($this->protocol."www.google.com/uds/?file=earth&amp;v=1");
			$this->_addscript($this->base."/media/plugin_googlemap2/site/googleearthv3/googleearth.js");
			$this->first_googleearth = false;
		}
		
		if($this->first_googlemaps) {
			$url = $this->base."/media/plugin_googlemap2/site/googlemaps/googlemapsv3.js";
			$this->_addscript($url);
			$this->first_googlemaps=false;
		}		
		
		if ($this->first_kmlelabel&&(($this->_mp->kmlpolylabel!=""&&$this->_mp->kmlpolylabelclass!="")||($this->_mp->kmlmarkerlabel!=""&&$this->_mp->kmlmarkerlabelclass!=""))) {
			$this->_addscript($this->base."/media/plugin_googlemap2/site/elabel/elabel_v3.js");
			$this->first_kmlelabel = false;
		}

		if (($this->_mp->kmlrenderer=='geoxml'||count($this->_mp->kmlsb)!=0)&&$this->first_kmlrenderer) {
			$this->_addscript($this->base."/media/plugin_googlemap2/site/geoxmlv3/geoxmlv3.js");
			$this->first_kmlrenderer = false;
		}

		if (($this->_mp->kmllightbox=="1"||$this->_mp->lightbox=="1"||$this->_mp->dir=="3"||$this->_mp->dir=="4"||strpos($this->_mp->description, "MOOdalBox"))&&$this->first_modalbox)	{
			if (substr($this->jversion,0,3)=='1.5')
				$this->_addscript($this->base."/media/plugin_googlemap2/site/moodalbox/js/modalbox1.2hackv3.js");
			else
				$this->_addscript($this->base."/media/plugin_googlemap2/site/moodalbox/js/moodalbox1.3hackv3.js");
			
			$this->_addstylesheet($this->base."/media/plugin_googlemap2/site/moodalbox/css/moodalbox.css");
			$this->first_modalbox = false;
		}
		
		if (($this->_mp->localsearch=="1"||$this->_mp->clientgeotype=='local')&&$this->first_localsearch) {
			$this->_addscript($this->protocol."www.google.com/uds/api?file=uds.js&amp;v=1.0&amp;key=".$this->googlekey);
			$style = "@import url('".$this->protocol."www.google.com/uds/css/gsearch.css');\n@import url('".$this->protocol."www.google.com/uds/solutions/localsearch/gmlocalsearch.css');";
			$this->_addstyledeclaration($style);
			$this->first_localsearch = false;
		}
		
		// Clean up variables except generated code and memory variables
		unset($url,$library);
	}
	
	function _processMapv3_markers() {
		$this->_mp->descr = ($this->_mp->description!='')?'1':'0';
		if ($this->_mp->description!=''||$this->_mp->dir!='0') {
			if ($this->_mp->dir!='0')
				$dirform =$this->_processMapv3_templatedirform('Marker');
			else
				$dirform = "";

			// Where to add dirform? tab or add the end of description?
			if (is_array($this->_mp->description)) {
				$this->_mp->description[$z+1]->title = $this->_mp->txtdir;
				$this->_mp->description[$z+1]->text = htmlspecialchars_decode($dirform, ENT_NOQUOTES);
			} else {
				$pat="/&lt;\/div&gt;$/";
				if (preg_match($pat, $this->_mp->description))
					$this->_mp->description = preg_replace($pat, $dirform."</div>", $this->_mp->description);
				else {
					$pat="/<\/div>$/";
					if (preg_match($pat, $this->_mp->description))
						$this->_mp->description = preg_replace($pat, $dirform."</div>", $this->_mp->description);
					else
						$this->_mp->description.=$dirform;
				}
			}

			
			if (!is_array($this->_mp->description))
				$this->_mp->description = htmlspecialchars_decode($this->_mp->description, ENT_NOQUOTES);
				
			// Encrypt description
			$this->_mp->description = htmlentities($this->_mp->description, ENT_QUOTES, "UTF-8");
		}
		$this->_mp->tooltip =  htmlentities($this->_mp->tooltip, ENT_QUOTES, "UTF-8");
	}
	
	function _processMapv3_tiles () {
		if ($this->_mp->tilelayer!="") {
			$this->_mp->tilebounds=explode(",", $this->_mp->tilebounds);
			if (count($this->_mp->tilebounds)==4) {
				$checkboundtiles = "if (googlemap".$this->_mp->mapnm.".checkboundTilelayer(coord, zoom)) {";
			} else {
				$checkboundtiles = "";
				unset($this->_mp->tilebounds);
			}
	
			if ($this->_mp->tilemethod!='maptiler') { 
				$this->_mp->tilemethod = str_replace('[', '{', $this->_mp->tilemethod);
				$this->_mp->tilemethod = str_replace(']', '}', $this->_mp->tilemethod);
				$this->_mp->tilemethod = str_replace('&amp;', '&', $this->_mp->tilemethod);
				$this->_mp->tilemethod = str_replace('{x}', '"+coord.x+"', $this->_mp->tilemethod);
				$this->_mp->tilemethod = str_replace('{X}', '"+coord.x+"', $this->_mp->tilemethod);
				$this->_mp->tilemethod = str_replace('{y}', '"+coord.y+"', $this->_mp->tilemethod);
				$this->_mp->tilemethod = str_replace('{Y}', '"+coord.y+"', $this->_mp->tilemethod);
				$this->_mp->tilemethod = str_replace('{z}', '"+zoom+"', $this->_mp->tilemethod);
				$this->_mp->tilemethod = str_replace('{Z}', '"+zoom+"', $this->_mp->tilemethod);
				$this->_mp->tilemethod = "function(coord, zoom) {".$checkboundtiles." return \"".$this->_mp->tilemethod."\";} }";
			} else {
				$this->_mp->tilemethod = "function(coord, zoom) {".$checkboundtiles." var ymax = 1 << zoom; var y = ymax - coord.y -1; return '".$this->_make_absolute($this->_mp->tilelayer)."/'+zoom+'/'+coord.x+'/'+y+'.png';} }";
			}
			
			unset($checkboundtiles);
		}
	}
	
	function _processMapv3_icons () {
		$code = "";
		if ($this->_mp->icon!='') {
			$code .= "\n<img src='".$this->_mp->icon."' style='display:none' alt='icon' />";
			if ($this->_mp->iconshadow!='')
				$code .= "\n<img src='".$this->_mp->iconshadow."' style='display:none' alt='icon shadow' />";
		
			// icon
			$icon = new stdClass();
			$icon->name = "A";
			$icon->imageurl = $this->_mp->icon;
			$icon->iconwidth = $this->_mp->iconwidth;
			$icon->iconheight = $this->_mp->iconheight;
			$icon->iconshadow = $this->_mp->iconshadow;
			$icon->iconshadowwidth = $this->_mp->iconshadowwidth;
			$icon->iconshadowheight = $this->_mp->iconshadowheight;
			$icon->iconanchorx = $this->_mp->iconanchorx;
			$icon->iconanchory = $this->_mp->iconanchory;
			if ($this->_mp->iconimagemap!="")
				$icon->iconimagemap = $this->_mp->iconimagemap;
			else
				$icon->iconimagemap = 	"13,0,15,1,16,2,17,3,18,4,18,5,19,6,19,7,19,8,19,9,19,10,19,11,19,12,19,13,18,14,18,15,17,16,16,17,15,18,14,19,14,20,13,21,13,22,12,23,12,24,12,25,12,26,11,27,11,28,11,29,11,30,11,31,11,32,11,33,8,33,8,32,8,31,8,30,8,29,8,28,8,27,8,26,7,25,7,24,7,23,6,22,6,21,5,20,5,19,4,18,3,17,2,16,1,15,1,14,0,13,0,12,0,11,0,10,0,9,0,8,0,7,0,6,1,5,1,4,2,3,3,2,4,1,6,0,13,0";
	
			$this->_mp->markericon = array($icon);
			$this->_mp->icontype ="A";
		} else
			$this->_mp->icontype ="";

		unset($icon, $this->_mp->icon, $this->_mp->iconwidth, $this->_mp->iconheight, $this->_mp->iconshadow, $this->_mp->iconshadowwidth, $this->_mp->iconshadowheight, $this->_mp->iconanchorx, $this->_mp->iconanchory, $this->_mp->iconimagemap, $this->_mp->iconshadowanchorx, $this->_mp->iconshadowanchory, $this->_mp->iconshadowanchorx, $this->_mp->iconshadowanchory, $this->_mp->iconinfoanchorx, $this->_mp->iconinfoanchory, $this->_mp->icontransparent);
		
		return $code;
	}
	
	function _processMapv3_streetview() {
		if ($this->_mp->sv!='none'&&$this->_mp->animdir=='0') {
			if ($this->_mp->sv=='top'||$this->_mp->sv=='bottom')
				$this->_mp->sv = "svpanorama".$this->_mp->mapnm;
				
			$this->_mp->svopt = new stdClass();
			if ($this->_mp->svyaw!='0')
				$this->_mp->svopt->heading = (int) $this->_mp->svyaw;
			else
				$this->_mp->svopt->heading = 0;
			if ($this->_mp->svpitch!='0')
				$this->_mp->svopt->pitch = (int) $this->_mp->svpitch;
			else
				$this->_mp->svopt->pitch = 0;
			if ($this->_mp->svzoom!='')
				$this->_mp->svopt->zoom = (int) $this->_mp->svzoom;
			else
				$this->_mp->svopt->zoom = 1;
				
			if ($this->_mp->svaddress=='0')
				$this->_mp->svaddress = false;
			else
				$this->_mp->svaddress = true;
		}		
		
		unset($this->_mp->svyaw,$this->_mp->svpitch,$this->_mp->svzoom);
	}

	function _processMapv3_kml() {
		// Change kml url if proxy is used
		if ($this->_mp->proxy=='1') {
			foreach ($this->_mp->kml as $idx=>$val) {
				$this->_mp->kml[$idx] = $this->_make_absolute($val);
			}
		}

		// Rename parameter so they can be used by geoxml
		$this->_mp->geoxmloptions = new stdClass();
		if ($this->_mp->kmlsidebar=="left"||$this->_mp->kmlsidebar=="right") {
			$this->_mp->geoxmloptions->sidebarid = 'kmlsidebar'.$this->_mp->mapnm;
		} else {
			if ($this->_mp->kmlsidebar!="none")
				$this->_mp->geoxmloptions->sidebarid = $this->_mp->kmlsidebar;
		}
		
		if ($this->_mp->kmlmessshow=='0') {
			$this->_mp->geoxmloptions->veryquiet = true;
			$this->_mp->geoxmloptions->quiet = true;
		}
	
		if ($this->_inline_coords==1)
			$this->_mp->geoxmloptions->nozoom = true;

		if ($this->_mp->dir!='0')
			$this->_mp->geoxmloptions->directions = true;
			
		if ($this->_mp->kmlfoldersopen!='0')
			$this->_mp->geoxmloptions->allfoldersopen = true;
			
		if ($this->_mp->kmlhide!='0')
			$this->_mp->geoxmloptions->hideall = true;

		if ($this->_mp->kmlscale!='0')
			$this->_mp->geoxmloptions->scale=  true;

		if ($this->_mp->kmlopenmethod!='0')
			$this->_mp->geoxmloptions->iwmethod = $this->_mp->kmlopenmethod;
		
		if ($this->_mp->kmlsbsort=='asc') {
			$this->_mp->geoxmloptions->sortbyname = 'asc';
		}elseif ($this->_mp->kmlsbsort=='desc') {
			$this->_mp->geoxmloptions->sortbyname= 'desc';
		} else 	
			$this->_mp->geoxmloptions->sortbyname = null;

		if ($this->_mp->kmlclickablemarkers!='1') {
			$this->_mp->geoxmloptions->clickablemarkers = false;
			$this->_mp->geoxmloptions->clickablelines = false;
			$this->_mp->geoxmloptions->dohilite = false;
		}
			
		if ($this->_mp->kmlzoommarkers!='0')
			$this->_mp->geoxmloptions->zoommarkers = $this->_mp->kmlzoommarkers;

		if ($this->_mp->kmlopendivmarkers!='')
			$this->_mp->geoxmloptions->opendivmarkers = $this->_mp->kmlopendivmarkers;

		if ($this->_mp->kmlcontentlinkmarkers!='0')
			$this->_mp->geoxmloptions->extcontentmarkers = true;

		if ($this->_mp->kmllinkablemarkers!='0')
			$this->_mp->geoxmloptions->contentlinkmarkers = true;

		if ($this->_mp->kmllinktarget!='')
			$this->_mp->geoxmloptions->linktarget = $this->_mp->kmllinktarget;

		if ($this->_mp->kmllinkmethod!='')
			$this->_mp->geoxmloptions->linkmethod = $this->_mp->kmllinkmethod;

		if (($this->_mp->kmlpolylabel!=""&&$this->_mp->kmlpolylabelclass!="")) {
			$this->_mp->geoxmloptions->polylabelopacity = $this->_mp->kmlpolylabel;
			$this->_mp->geoxmloptions->polylabelclass = $this->_mp->kmlpolylabelclass;
		}
		if (($this->_mp->kmlmarkerlabel!=""&&$this->_mp->kmlmarkerlabelclass!="")) {
			$this->_mp->geoxmloptions->pointlabelopacity = $this->_mp->kmlmarkerlabel;
			$this->_mp->geoxmloptions->pointlabelclass = $this->_mp->kmlmarkerlabelclass;
		}
		if ($this->_mp->icon!='')
			$this->_mp->geoxmloptions->baseicon = "A";

		if ($this->_mp->maxcluster!=''&&$this->_mp->gridsize!='') {
			$clusteroptions = array();
			if ($this->_mp->maxcluster!='')
				$clusteroptions[] ="maxVisibleMarkers : ".$this->_mp->maxcluster;
			if ($this->_mp->gridsize!='')
				$clusteroptions[] ="gridSize : ".$this->_mp->gridsize;
			if ($this->_mp->minmarkerscluster!='')
				$clusteroptions[] ="minMarkersPerCluster : ".$this->_mp->minmarkerscluster;
			if ($this->_mp->maxlinesinfocluster!='')
				$clusteroptions[] ="maxLinesPerInfoBox : ".$this->_mp->maxlinesinfocluster;
			if ($this->_mp->clusterinfowindow!='')
				$clusteroptions[] ="ClusterInfoWindow : '".$this->_mp->clusterinfowindow."'" ;
			if ($this->_mp->clusterzoom!='')
				$clusteroptions[] ="ClusterZoom : '".$this->_mp->clusterzoom."'" ;
			if ($this->_mp->clustermarkerzoom!='')
				$clusteroptions[] ="ClusterMarkerZoom : ".$this->_mp->clustermarkerzoom;
			if ($this->_mp->icon!='')
				$clusteroptions[] ="Icon : markericon".$this->_mp->mapnm;

			$this->_mp->geoxmloptions->clustering = $clusteroptions;
		}
		
		unset($this->_mp->kmlmessshow, $this->_mp->kmlfoldersopen, $this->_mp->kmlhide, $this->_mp->kmlscale, $this->_mp->kmlopenmethod, $this->_mp->kmlsbsort, $this->_mp->kmlsbsort, $this->_mp->kmlclickablemarkers, $this->_mp->kmlzoommarkers, $this->_mp->kmlopendivmarkers, $this->_mp->kmlcontentlinkmarkers, $this->_mp->kmllinkablemarkers, $this->_mp->kmllinktarget, $this->_mp->kmllinkmethod, $this->_mp->kmlpolylabel, $this->_mp->kmlpolylabelclass, $this->_mp->kmlmarkerlabel, $this->_mp->kmlmarkerlabelclass, $this->_mp->maxcluster, $this->_mp->gridsize, $this->_mp->maxcluster, $this->_mp->minmarkerscluster, $this->_mp->maxlinesinfocluster, $this->_mp->clusterinfowindow, $this->_mp->clusterzoom, $this->_mp->clustermarkerzoom, $clusteroptions, $idx, $val);
	}
	
	function _processMapv3_template() {
		$code = "";
		$lbcode = "";

		$code.= "<!-- fail nicely if the browser has no Javascript -->
				<noscript><blockquote class='warning'><p>".$this->no_javascript."</p></blockquote></noscript>";			

		if ($this->_mp->align!='none')
			$code.="<div id='mapbody".$this->_mp->mapnm."' style=\"display: none; text-align:".$this->_mp->align."\">";
		else
			$code.="<div id='mapbody".$this->_mp->mapnm."' style=\"display: none;\">";
			
		if ($this->_mp->lightbox=='1') {
			$lboptions = array();
			if ($this->_mp->lbxzoom!="")
				$lboptions[] = "zoom : ".$this->_mp->lbxzoom;
			if ($this->_mp->lbxcenterlat!=""&&$this->_mp->lbxcenterlon!="")
				$lboptions[] = "mapcenter : \"".$this->_mp->lbxcenterlat." ".$this->_mp->lbxcenterlon."\"";
				
			$this->_lbxwidthorig = (is_numeric($this->_lbxwidthorig)?(($this->_mp->kmlsidebar=="left"||$this->_mp->kmlsidebar=="right")?$this->_lbxwidthorig+$this->_kmlsbwidthorig+5:$this->_lbxwidthorig)."px":$this->_lbxwidthorig);
			$lbname = (($this->_mp->gotoaddr=='1'||(($this->_mp->kmlrenderer=="google"&&count($this->_mp->kmlsb)!=0)||($this->_mp->kmlrenderer=="geoxml"&&(count($this->_mp->kml)!=0||count($this->_mp->kmlsb)!=0))&&($this->_mp->kmlsidebar=="left"||$this->_mp->kmlsidebar=="right"))||$this->_mp->animdir!='0'||$this->_mp->sv=='top'||$this->_mp->sv=='bottom'||$this->_mp->searchlist=='div'||$this->_mp->dir=='5'||($this->_mp->formaddress==1&&$this->_mp->animdir==0))?"lightbox":"googlemap");
			
			if ($this->_mp->show==1) {
				$code.="<a href='javascript:void(0)' onclick='javascript:MOOdalBox.open(\"".$lbname.$this->_mp->mapnm."\", \"".$this->_mp->lbxcaption."\", \"".$this->_lbxwidthorig." ".$this->_mp->lbxheight."\", googlemap".$this->_mp->mapnm.".map, {".implode(",",$lboptions)."});return false;' class='lightboxlink'>".html_entity_decode($this->_mp->txtlightbox)."</a>";
				$code .= "<div id='lightbox".$this->_mp->mapnm."'>";
			} else {
				$lbcode.="<a href='javascript:void(0)' onclick='javascript:MOOdalBox.open(\"".$lbname.$this->_mp->mapnm."\", \"".$this->_mp->lbxcaption."\", \"".$this->_lbxwidthorig." ".$this->_mp->lbxheight."\", googlemap".$this->_mp->mapnm.".map, {".implode(",",$lboptions)."});return false;' class='lightboxlink'>".html_entity_decode($this->_mp->txtlightbox)."</a>";
				$code .= "<div id='lightbox".$this->_mp->mapnm."' style='display:none'>";
			}
		}
		
		if ($this->_mp->gotoaddr=='1')	{
			$code.="<form id=\"gotoaddress".$this->_mp->mapnm."\" class=\"gotoaddress\" onSubmit=\"javascript:googlemap".$this->_mp->mapnm.".gotoAddress();return false;\">";
			$code.="	<input id=\"txtAddress".$this->_mp->mapnm."\" name=\"txtAddress".$this->_mp->mapnm."\" type=\"text\" size=\"25\" value=\"\">";
			$code.="	<input name=\"goto\" type=\"button\" class=\"button\" onClick=\"javascript:googlemap".$this->_mp->mapnm.".gotoAddress();return false;\" value=\"Goto\">";
			$code.="</form>";
		}

		if ($this->_mp->latitudeform=='1')	{
			$code.="<form id=\"latitudeform".$this->_mp->mapnm."\" class=\"latitudefrom\" onSubmit=\"javascript:googlemap".$this->_mp->mapnm.".showLatitude();return false;\">";
			$code.="	<input id=\"latitudeid".$this->_mp->mapnm."\" name=\"latitudeid".$this->_mp->mapnm."\" type=\"text\" size=\"25\" value=\"\">";
			$code.="	<input name=\"show\" type=\"button\" class=\"button\" onClick=\"javascript:googlemap".$this->_mp->mapnm.".showLatitude();return false;\" value=\"Show latitude location\">";
			$code.="</form>";
		}

		if ($this->_mp->formaddress==1)
			$code.=$this->_processMapv3_templatedirform('Form');
			
		if ((($this->_mp->kmlrenderer=="google"&&count($this->_mp->kmlsb)!=0)||($this->_mp->kmlrenderer=="geoxml"&&(count($this->_mp->kml)!=0||count($this->_mp->kmlsb)!=0)))&&($this->_mp->kmlsidebar=="left"||$this->_mp->kmlsidebar=="right"))
			$code.="<table style=\"width:100%;border-spacing:0px;\">
					<tr>";

		if ((($this->_mp->kmlrenderer=="google"&&count($this->_mp->kmlsb)!=0)||($this->_mp->kmlrenderer=="geoxml"&&(count($this->_mp->kml)!=0||count($this->_mp->kmlsb)!=0)))&&$this->_mp->kmlsidebar=="left")
			$code.="<td style=\"width:".$this->_mp->kmlsbwidth.";height:".$this->_mp->height.";vertical-align:top;\"><div id=\"kmlsidebar".$this->_mp->mapnm."\" class=\"kmlsidebar\" style=\"align:left;width:".$this->_mp->kmlsbwidth.";height:".$this->_mp->height.";overflow:auto;\"></div></td>";

		if ((($this->_mp->kmlrenderer=="google"&&count($this->_mp->kmlsb)!=0)||($this->_mp->kmlrenderer=="geoxml"&&(count($this->_mp->kml)!=0||count($this->_mp->kmlsb)!=0)))&&($this->_mp->kmlsidebar=="left"||$this->_mp->kmlsidebar=="right"))
			$code.="<td>";
			
		if ($this->_mp->sv=='top'||($this->_mp->animdir!='0'&&$this->_mp->animdir!='3')) {
			$code.="<div id='svpanel".$this->_mp->mapnm."' class='svPanel' style='" . ($this->_mp->align != 'none' ? ($this->_mp->align == 'center' || $this->_mp->align == 'left' ? 'margin-right: auto; ' : '') . ($this->_mp->align == 'center' || $this->_mp->align == 'right' ? 'margin-left: auto; ' : '') : '') . "width:".$this->_mp->svwidth."; height:".$this->_mp->svheight."'><div id='svpanorama".$this->_mp->mapnm."' class='streetview' style='width:".$this->_mp->svwidth."; height:".$this->_mp->svheight.(($this->_mp->kmlsidebar=="right")?"float:left;":"").";'></div>";
			$code.="<div style=\"clear: both;\"></div>";
			$code.="</div>";
		}
			
		$code.="<div id=\"googlemap".$this->_mp->mapnm."\" ".((!empty($this->_mp->mapclass))?"class=\"".$this->_mp->mapclass."\"" :"class=\"map\"")." style=\"" . ($this->_mp->align != 'none' ? ($this->_mp->align == 'center' || $this->_mp->align == 'left' ? 'margin-right: auto; ' : '') . ($this->_mp->align == 'center' || $this->_mp->align == 'right' ? 'margin-left: auto; ' : '') : '') . "width:".$this->_mp->width."; height:".$this->_mp->height.";".(($this->_mp->show==0&&$this->_mp->lightbox==0)?"display:none;":"").(((($this->_mp->kmlrenderer=="google"&&count($this->_mp->kmlsb)!=0)||($this->_mp->kmlrenderer=="geoxml"&&(count($this->_mp->kml)!=0||count($this->_mp->kmlsb)!=0))&&$this->_mp->kmlsidebar=="right")||$this->_mp->animdir=='2')?"float:left;":"")."\"></div>";

		if ($this->_mp->sv=='bottom'||$this->_mp->animdir=="3") {
			$code.="<div style=\"clear: both;\"></div>";
			$code.="</div>";
			$code.="<div id='svpanel".$this->_mp->mapnm."' class='svPanel' style='" . ($this->_mp->align != 'none' ? ($this->_mp->align == 'center' || $this->_mp->align == 'left' ? 'margin-right: auto; ' : '') . ($this->_mp->align == 'center' || $this->_mp->align == 'right' ? 'margin-left: auto; ' : '') : '') . "width:".$this->_mp->svwidth."; height:".$this->_mp->svheight."'><div id='svpanorama".$this->_mp->mapnm."' class='streetview' style='width:".$this->_mp->svwidth."; height:".$this->_mp->svheight.(($this->_mp->kmlsidebar=="right")?"float:left;":"").";'></div>";
		}

		if ((($this->_mp->kmlrenderer=="google"&&count($this->_mp->kmlsb)!=0)||($this->_mp->kmlrenderer=="geoxml"&&(count($this->_mp->kml)!=0||count($this->_mp->kmlsb)!=0)))&&($this->_mp->kmlsidebar=="left"||$this->_mp->kmlsidebar=="right"))
			$code.="</td>";
		
		if ((($this->_mp->kmlrenderer=="google"&&count($this->_mp->kmlsb)!=0)||($this->_mp->kmlrenderer=="geoxml"&&(count($this->_mp->kml)!=0||count($this->_mp->kmlsb)!=0)))&&$this->_mp->kmlsidebar=="right")
			$code.="<td style=\"width:".$this->_mp->kmlsbwidth.";height:".$this->_mp->height.";vertical-align:top;\"><div id=\"kmlsidebar".$this->_mp->mapnm."\"  class=\"kmlsidebar\" style=\"align:left;width:".$this->_mp->kmlsbwidth.";height:".$this->_mp->height.";overflow:auto;\"></div></td>";
			
		if ((($this->_mp->kmlrenderer=="google"&&count($this->_mp->kmlsb)!=0)||($this->_mp->kmlrenderer=="geoxml"&&(count($this->_mp->kml)!=0||count($this->_mp->kmlsb)!=0)))&&($this->_mp->kmlsidebar=="left"||$this->_mp->kmlsidebar=="right"))
			$code.="</tr>
					</table>";

		if (((!empty($this->_mp->tolat)&&!empty($this->_mp->tolon))||!empty($this->_mp->address)||($this->_mp->dir=='5'))&&($this->_mp->animdir!='2'||($this->_mp->animdir=='2'&&$this->_mp->showdir=='0')))
			$code.= "<div id=\"dirsidebar".$this->_mp->mapnm."\" class='directions' ".(($this->_mp->showdir=='0')?"style='display:none'":"")."></div>";

		if ($this->_mp->lightbox=='1')
			$code .= "</div>";

		// Close of mapbody div
		$code.="</div>";
		
		return array($code, $lbcode);
	}
	
	function _processMapv3_templatedirform($type) {
		$dirform="";
		$dirform="<form id='directionform".$this->_mp->mapnm."' action='".$this->protocol.$this->googlewebsite."/maps' method='get' target='_blank' onsubmit='javascript:googlemap".$this->_mp->mapnm.".DirectionMarkersubmit(this);return false;' class='mapdirform'>";
		
		$dirform.=$this->_mp->txtdir;
		
		if ($type=='Marker') {
			$dirform.="<input ".(($this->_mp->txtto=='')?"type='hidden' ":"type='radio' ")." ".(($this->_mp->dirdefault=='0')?"checked='checked'":"")." name='dir' value='to'>".(($this->_mp->txtto!='')?$this->_mp->txtto."&nbsp;":"")."<input ".(($this->_mp->txtfrom=='')?"type='hidden' ":"type='radio' ").(($this->_mp->dirdefault=='1')?"checked='checked'":"")." name='dir' value='from'>".(($this->_mp->txtfrom!='')?$this->_mp->txtfrom:"");
			$dirform.="<br />".$this->_mp->txtdiraddr."<input type='text' class='inputbox' size='20' name='saddr' id='saddr' value='' />";
			
			if (!empty($this->_mp->address))
				$dirform.="<input type='hidden' name='daddr' value='".$this->_mp->address." (".(($this->_mp->latitude!='')?$this->_mp->latitude:$this->_mp->deflatitude).", ".(($this->_mp->longitude!='')?$this->_mp->longitude:$this->_mp->deflongitude).")'/>";
			else
				$dirform.="<input type='hidden' name='daddr' value='".(($this->_mp->latitude!='')?$this->_mp->latitude:$this->_mp->deflatitude).", ".(($this->_mp->longitude!='')?$this->_mp->longitude:$this->_mp->deflongitude)."'/>";
		}
		
		if ($type=='Form') {
			$dirform.=(($this->_mp->txtfrom=='')?"":"<br />").$this->_mp->txtfrom."<input ".(($this->_mp->txtfrom=='')?"type='hidden' ":"type='text'")." class='inputbox' size='20' name='saddr' id='saddr' value='".(($this->_mp->formdir=='1')?$this->_mp->address:(($this->_mp->formdir=='2')?$this->_mp->toaddress:""))."' />";
			$dirform.=(($this->_mp->txtto=='')?"":"<br />").$this->_mp->txtto."<input ".(($this->_mp->txtto=='')?"type='hidden' ":"type='text'")." class='inputbox' size='20' name='daddr' id='daddr' value='".(($this->_mp->formdir=='1')?$this->_mp->toaddress:(($this->_mp->formdir=='2')?$this->_mp->address:""))."' />";
		}
		
		if ($this->_mp->txt_driving!=''||$this->_mp->txt_avhighways!=''||$this->_mp->txt_walking!='')
			$dirform.="<br />";	

		if ($this->_mp->txt_driving!=''||$this->_mp->dirtype=="D")
			$dirform.="<input ".(($this->_mp->txt_driving=='')?"type='hidden' ":"type='radio' ")."class='radio' name='dirflg' value='' ".(($this->_mp->dirtype=="D")?"checked='checked'":"")." />".$this->_mp->txt_driving.(($this->_mp->txt_driving!='')?"&nbsp;":"");
		if ($this->_mp->txt_avhighways!=''||$this->_mp->dirtype=="1")
			$dirform.="<input ".(($this->_mp->txt_avhighways=='')?"type='hidden' ":"type='radio' ")."class='radio' name='dirflg' value='h' ".(($this->_mp->avoidhighways=='1')?"checked='checked'":"")." />".$this->_mp->txt_avhighways.(($this->_mp->txt_avhighways!='')?"&nbsp;":"");
		if ($this->_mp->txt_walking!=''||$this->_mp->dirtype=="W")
			$dirform.="<input ".(($this->_mp->txt_walking=='')?"type='hidden' ":"type='radio' ")."class='radio' name='dirflg' value='w' ".(($this->_mp->dirtype=="W")?"checked='checked'":"")." />".$this->_mp->txt_walking.(($this->_mp->txt_walking!='')?"&nbsp;":"");
			
		$dirform.=(($this->_mp->txt_optimize!='')?"<br/>":"")."<input ".(($this->_mp->txt_optimize=='')?"type='hidden' ":"type='checkbox' ")."class='checkbox' name='diroptimize' value='1' ".(($this->_mp->diroptimize=='1')?"checked='checked'":"")." />".$this->_mp->txt_optimize;
		$dirform.=(($this->_mp->txt_alternatives!='')?"<br/>":"")."<input ".(($this->_mp->txt_alternatives=='')?"type='hidden' ":"type='checkbox' ")."class='checkbox' name='diralternatives' value='1' ".(($this->_mp->diralternatives=='1')?"checked='checked'":"")." />".$this->_mp->txt_alternatives;
			
		$dirform.="<br/><input value='".$this->_mp->txtgetdir."' class='button' type='submit' style='margin-top: 2px;'>";
		
		if ($this->_mp->dir=='2')
			$dirform.= "<input type='hidden' name='pw' value='2'/>";

		if ($this->_mp->lang!='') 
			$dirform.= "<input type='hidden' name='hl' value='".$this->_mp->lang."'/>";

		$dirform.="</form>";

		return $dirform;
	}
	
	function _getInitialParams() {
		jimport( 'joomla.utilities.simplexml' );
		$xml	= new JSimpleXML;
		if (substr($this->jversion,0,3)=="1.5")
			$filename = JPATH_SITE.DS."/plugins/system/plugin_googlemap2.xml";
		else
			$filename = JPATH_SITE.DS."/plugins/system/plugin_googlemap2/plugin_googlemap2.xml";
		
		if ($xml->loadFile($filename)) {
			if (substr($this->jversion,0,3)=="1.5")
				$root =& $xml->document;
			else if (isset($xml->document->config[0]->fields[0]))
				$root = $xml->document->config[0]->fields[0];
			else
				$root =& $xml->document;
			
			foreach ($root->children() as $params) {
				foreach($params->children() as $param) {
					if ($param->attributes('export')=='1') {
						$name = $param->attributes('name');
						if ($name=='lat') {
							$this->initparams->deflatitude = $this->params->get($name, $param->attributes('default'));
						} elseif ($name=='lon') {
							$this->initparams->deflongitude = $this->params->get($name, $param->attributes('default'));
						} elseif (substr($name,0,3)=='txt') {
							$nm = strtolower($name);
							$this->initparams->$nm = $this->params->get($name, '');
						} else {
							$nm = strtolower($name);
							$this->initparams->$nm = $this->params->get($name, $param->attributes('default'));
						}
					}
				}
			}
		}
		
		// Clean up generated variables
		unset($filename, $xml, $root, $params, $param, $name, $nm);
	}
	
	function _getURL($url) {
		$ok = false;
		$getpage = "";
		if (ini_get('allow_url_fopen')) { 
			if (file_exists($url)) {
				$getpage = file_get_contents($url);
				$ok = true;
			}
		} 
		
		if (!$ok) { 
			$this->_debug_log("URI couldn't be opened probably ALLOW_URL_FOPEN off");
			if (function_exists('curl_init')) {
				$this->_debug_log("curl_init does exists");
				$ch = curl_init();
				$timeout = 5; // set to zero for no timeout
				curl_setopt ($ch, CURLOPT_URL, $url);
				curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
				$getpage = curl_exec($ch);
				curl_close($ch);
			} else
				$this->_debug_log("curl_init doesn't exists");
		}
		$this->_debug_log("Returned page: ".htmlentities($getpage));
		
		// Clean up generated variables
		unset($ok, $ch, $timeout);
		
		return $getpage;
	}

	function get_geo($address)
	{
		$this->_debug_log("get_geo(".$address.")");
	
		$coords = '';
		$getpage='';
		$replace = array("\n", "\r", "&lt;br/&gt;", "&lt;br /&gt;", "&lt;br&gt;", "<br>", "<br />", "<br/>");
		$address = str_replace($replace, '', $address);

		// Convert address to utf-8 encoding
		if (function_exists('mb_detect_encoding')) {
			$enc = mb_detect_encoding($address);
			if (!empty($enc))
				$address = mb_convert_encoding($address, "utf-8", $enc);
			else
				$address = mb_convert_encoding($address, "utf-8");
		}

		$this->_debug_log("Address: ".$address);
		
		$uri = $this->protocol.$this->googlewebsite."/maps/geo?q=".urlencode($address)."&output=xml&key=".$this->googlekey;
		$this->_debug_log("get_geo(".$uri.")");
		$getpage = $this->_getURL($uri);

		if (function_exists('mb_detect_encoding')) {
			$enc = mb_detect_encoding($getpage);
			if (!empty($enc))
				$getpage = mb_convert_encoding($getpage, "utf-8", $enc);
		}

		if ($getpage <>'') {
			$expr = '/xmlns/';
			$getpage = preg_replace($expr, 'id', $getpage);
			$xml = new SimpleXMLElement($getpage);
			foreach($xml->xpath('//coordinates') as $coordinates) {
				$coords = $coordinates;
				break;
			}
			if ($coords=='') {
				$this->_debug_log("Coordinates: null");
			} else
				$this->_debug_log("Coordinates: ".join(", ", explode(",", $coords)));
		} else
			$this->_debug_log("get_geo totally wrong end!");
	
		// Clean up variables
		unset($coord, $getpage, $replace, $enc, $uri, $ok, $ch, $timeout, $expr, $xml, $coordinates);
		
		return $coords;
	}
	
	function _debug_log($text)
	{
		if ($this->debug_plugin =='1')
			$this->debug_text .= "\n// ".$text." (".round($this->_memory_get_usage()/1024)." KB)";
	
		return;
	}
	
	function _get_index($string)
	{
		if ($this->brackets=='{') {
			$string = preg_replace("/^(.*?)\[/", '', $string);
			$string = preg_replace("/\](.*?)$/", '', $string);
			
		} else {
			$string = preg_replace("/^.*\(/", '', $string);
			$string = preg_replace("/\).*$/", '', $string);
		}
		
		return $string;
	}
	
    function _memory_get_usage()
    {
		if ( function_exists( 'memory_get_usage' ) )
			return memory_get_usage(); 
		else
			return 0;
    }

	function _get_API_key () {
		$url = trim($this->urlsetting);
		$replace = array('http://', 'https://');
		$url = str_replace($replace, '', $url);


		$url = (($this->protocol=='https://')?$this->protocol:'').$url;
		$this->_debug_log("url: ".$url);
		$key = '';
		$multikey = trim($this->params->get( 'Google_Multi_API_key', '' ));
		if ($multikey!='') {
			$this->_debug_log("multikey: ".$multikey);
			$replace = array("\n", "\r", "<br/>", "<br />", "<br>");
			$sites = preg_split("/[\n\r]+/", $multikey);
			foreach($sites as $site)
			{
				$values = explode(";",$site, 2);
				if (count($values)>1) {
					$values[0] = trim(str_replace($replace, '', $values[0]));
					$values[1] = str_replace($replace, '', $values[1]);
					$this->_debug_log("values[0]: ".$values[0]);
					$this->_debug_log("values[1]: ".$values[1]);
					if ($url==$values[0])
					{
						$key = trim($values[1]);
						break;
					}
				}
			}
		}
		if ($key=='')
			$key = trim($this->params->get( 'Google_API_key', '' ));

		// Clean up variables
		unset($url, $replace, $multikey, $sites, $site, $values);
		$this->_debug_log("key: ".$key);
		return $key;
	}
	
	function _randomkeys($length)
	{
		$key = "";
		$pattern = "1234567890abcdefghijklmnopqrstuvwxyz";
		for($i=0;$i<$length;$i++)
		{
			$key .= $pattern{rand(0,35)};
		}
		
		// Clean up variables
		unset($i, $pattern);
		return $key;
	}

	function _translate($orgtext, $lang) {
		$langtexts = preg_split("/[\n\r]+/", $orgtext);
		$text = "";

		if (is_array($langtexts)) {
			$replace = array("\n", "\r", "<br/>", "<br />", "<br>");
			$firsttext = "";
			foreach($langtexts as $langtext) {
				$values = explode(";",$langtext, 2);
				if (count($values)>1) {
					$values[0] = trim(str_replace($replace, '', $values[0]));
					if ($firsttext == "")
						$firsttext = $values[1];
						
					if (trim($lang)==$values[0])
					{
						$text = $values[1];
						break;
					}
				}
			}
			// Not found
			if ($text=="")
				$text = $firsttext;
		}	
		
		if ($text=="")
			$text = $orgtext;
	
		$text = htmlspecialchars_decode($text, ENT_NOQUOTES);
	
		// Clean up variables
		unset($langtexts, $replace, $langtext, $values);
		return $text;
	}
	
	function _getlang() {
		$this->_debug_log("langtype: ".$this->langtype);

		if ($this->langtype == 'site') {
			$lang = $this->lang->getTag();
			$this->_debug_log("Joomla lang: ".$lang);
			// Chinese and portugal use full iso code to indicate language
			if (!($lang=='zh'||$lang=='pt')) {
				$locale_parts = explode('-', $this->lang->getTag());
				$lang = $locale_parts[0];
			}
			$this->_debug_log("site lang: ".$lang);
		} else if ($this->langtype == 'config') {
			$lang = $this->params->get( 'lang', '' );
			$this->_debug_log("config lang: ".$lang);
		} else if ($this->langtype == 'joomfish'&&isset($_COOKIE['jfcookie'])) {
			$lang = $_COOKIE['jfcookie']['lang']; 
			$this->_debug_log("Joomfish lang: ".$lang);
		} else {
			$lang = '';
			$this->_debug_log("No language: ".$lang);
		} 
		
		// Clean up variables
		unset($locale_parts);
		return $lang;
	}
	
	function _remove_html_tags($text) {
		$reg[] = "/<span[^>]*?>/si";
		$repl[] = '';
		$reg[] = "/<\/span>/si";
		$repl[] = '';
		$text = preg_replace( $reg, $repl, $text );
		
		// Clean up variables
		unset($reg, $repl);
		return $text;
	}
	
	function _make_absolute($link) {
		if(substr($link,0, 7)!='http://'&&substr($link,0, 7)!='https://') {
			if(substr($link,0,1)=='/') {
				return $this->url.$link;
			} else {
				return $this->url.'/'.$link;
			}
		}
		return $link;
	}
	
	function _addscript($url) {
		// The method depends on event type. onAfterRender is complex and others are simple based on framework
		if ($this->event!='onAfterRender')
			$this->document->addScript($url);
		else {
			// Get header
			$reg = "/(<HEAD[^>]*>)(.*?)(<\/HEAD>)(.*)/si";
			$count = preg_match_all($reg,$this->_text,$html);	
			if ($count>0) {
				$head=$html[2][0];
			} else {
				$head='';
			}
			// clean browser if statements
			$reg = "/<!--\[if(.*?)<!\[endif\]-->/si";
			$head = preg_replace($reg, '', $head);

			// define scripts regex
			$reg = '/<script.*src=[\'\"](.*?)[\'\"][^>]*[^<]*(<\/script>)?/i';
			$found = false;
			
			$count = preg_match_all($reg,$head,$scripts,PREG_OFFSET_CAPTURE | PREG_PATTERN_ORDER);	

			if ($count>0)
				foreach ($scripts[1] as $script) {
					if ($script[0]==$url) {
						$found = true;
						break;
					}
				}
				
			if (!$found) {
				$script = "\n<script type='text/javascript' src='".$url."'></script>\n";
				if ($count==0) {
					// No scripts then just add it before </head>
					$this->_text = preg_replace("/<head(| .*?)>(.*?)<\/head>/is", "<head$1>$2".$script."</head>", $this->_text);
				} else {
					//add script after the last script
					// position last script and add length
					$pos = strpos($this->_text, trim($scripts[0][$count-1][0]))+strlen(trim($scripts[0][$count-1][0]));
					$this->_text = substr($this->_text,0, $pos).$script.substr($this->_text,$pos);
				}
			}
			
			// Clean up variables
			unset($reg, $count, $head, $found, $scripts, $script, $pos);
		}
	}
	
	function _addstylesheet($url) {
		// The method depends on event type. onAfterRender is complex and others are simple based on framework
		if ($this->event!='onAfterRender')
			$this->document->addStyleSheet($url);
		else {
			// Get header
			$reg = "/(<HEAD[^>]*>)(.*?)(<\/HEAD>)(.*)/si";
			$count = preg_match_all($reg,$this->_text,$html);	
			if ($count>0) {
				$head=$html[2][0];
			} else {
				$head='';
			}
			
			// clean browser if statements
			$reg = "/<!--\[if(.*?)<!\[endif\]-->/si";
			$head = preg_replace($reg, '', $head);

			// define scripts regex
			$reg = '/<link.*href=[\'\"](.*?)[\'\"][^>]*[^<]*(<\/link>)?/i';
			$found = false;
			
			$count = preg_match_all($reg,$head,$styles,PREG_OFFSET_CAPTURE | PREG_PATTERN_ORDER);	
			if ($count>0)
				foreach ($styles[1] as $style) {
					if ($style[0]==$url) {
						$found = true;
						break;
					}
				}
				
			if (!$found) {
				$style = "\n<link href='".$url."' rel='stylesheet' type='text/css' />\n";
				if ($count==0) {
					// No styles then just add it before </head>
					$this->_text = preg_replace("/<head(| .*?)>(.*?)<\/head>/is", "<head$1>$2".$style."</head>", $this->_text);
				} else {
					//add style after the last style
					// position last style and add length
					$pos = strpos($this->_text, trim($styles[0][$count-1][0]))+strlen(trim($styles[0][$count-1][0]));
					$this->_text = substr($this->_text,0, $pos).$style.substr($this->_text,$pos);
				}
			}
			
			// Clean up variables
			unset($reg, $count, $head, $found, $styles, $style, $pos);
		}
	}
	function _addstyledeclaration($source) {
		// The method depends on event type. onAfterRender is complex and others are simple based on framework
		if ($this->event!='onAfterRender')
			$this->document->addStyleDeclaration($source);
		else {
			// Get header
			$reg = "/(<HEAD[^>]*>)(.*?)(<\/HEAD>)(.*)/si";
			$count = preg_match_all($reg,$this->_text,$html);	
			if ($count>0) {
				$head=$html[2][0];
			} else {
				$head='';
			}
			
			// clean browser if statements
			$reg = "/<!--\[if(.*?)<!\[endif\]-->/si";
			$head = preg_replace($reg, '', $head);

			// define scripts regex
			$reg = '/<style[^>]*>(.*?)<\/style>/si';
			$found = false;
			
			$count = preg_match_all($reg,$head,$styles,PREG_OFFSET_CAPTURE | PREG_PATTERN_ORDER);	
			if ($count>0)
				foreach ($styles[1] as $style) {
					if ($style[0]==$source) {
						$found = true;
						break;
					}
				}
				
			if (!$found) {
				$source = "\n<style type='text/css'>\n".$source."\n</style>\n";
				if ($count==0) {
					// No styles then just add it before </head>
					$this->_text = preg_replace("/<head(| .*?)>(.*?)<\/head>/is", "<head$1>$2".$source."</head>", $this->_text);
				} else {
					//add style after the last style
					// position last style and add length
					$pos = strpos($this->_text, trim($styles[0][$count-1][0]))+strlen(trim($styles[0][$count-1][0]));
					$this->_text = substr($this->_text,0, $pos).$source.substr($this->_text,$pos);
				}
			}
			
			// Clean up variables
			unset($reg, $count, $head, $found, $styles, $style, $pos);
		}
	}
	

	function _is_utf8($string) { // v1.01
	//	define('_is_utf8_split',5000);
	//	if (strlen($string) > _is_utf8_split) {
		if (strlen($string) > 5000) {
			// Based on: http://mobile-website.mobi/php-utf8-vs-iso-8859-1-59
			for ($i=0,$s=_is_utf8_split,$j=ceil(strlen($string)/_is_utf8_split);$i < $j;$i++,$s+=_is_utf8_split) {
				if (is_utf8(substr($string,$s,_is_utf8_split)))
					return true;
			}
			return false;
		} else {
			// From http://w3.org/International/questions/qa-forms-utf-8.html
			return preg_match('%^(?:
					[\x09\x0A\x0D\x20-\x7E]            # ASCII
				| [\xC2-\xDF][\x80-\xBF]             # non-overlong 2-byte
				|  \xE0[\xA0-\xBF][\x80-\xBF]        # excluding overlongs
				| [\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}  # straight 3-byte
				|  \xED[\x80-\x9F][\x80-\xBF]        # excluding surrogates
				|  \xF0[\x90-\xBF][\x80-\xBF]{2}     # planes 1-3
				| [\xF1-\xF3][\x80-\xBF]{3}          # planes 4-15
				|  \xF4[\x80-\x8F][\x80-\xBF]{2}     # plane 16
			)*$%xs', $string);
		}
	} 
}

?>