<?php
/**
* Detect visitor information on joomla components
* Embedd a spam report on Joomla! components
* @author: Alikon
* @version: 2.0.0
* @release: 09/04/2011 9.28
* @package: Alikonweb.detector
* @copyright: (C) 2007-2011 Alikonweb.it
* @license: http://www.gnu.org/copyleft/gpl.html GNU/GPL
*
*
*
**/

#----NO DIRECT ACCESS--------------------------------------------------------------------------------#
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.plugin.plugin' );
jimport( 'joomla.html.parameter' );			
$app = JFactory::getApplication();

$lang = JFactory :: getLanguage();

$lang->load('plg_alikonweb_detector', JPATH_ADMINISTRATOR);
$app->registerEvent( 'onDetect', 'plgDetector' );
$app->registerEvent( 'onHoney', 'plgHoneypot2ip' );
$app->registerEvent( 'onBotscout', 'plgBotscout2ip' );
$app->registerEvent( 'onStopforumspam', 'plgStopforumspam2ip' );
$app->registerEvent( 'onSpamtrap', 'plgTrap4Honeypot' );
$app->registerEvent( 'onLocalize', 'plgLocalizeIp' );
$app->registerEvent( 'onAkismet', 'plgAkismet' );
$app->registerEvent( 'onDefensio', 'plgDefensio' );
$app->registerEvent( 'onFspamlist', 'plgFspamlist' );

	function getIpAddr(){
		if (!empty($_SERVER['HTTP_CLIENT_IP'])) //check ip from share internet
		{
			$ip=$_SERVER['HTTP_CLIENT_IP'];
		}
		elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) //to check ip is pass from proxy
		{
			$ip=$_SERVER['HTTP_X_FORWARDED_FOR'];
		}
		else
		{
			$ip=$_SERVER['REMOTE_ADDR'];
		}
		//echo 'IP:'.$ip;
		return $ip;
}	
function is_connected() 
{ 
    //check to see if the local machine is connected to the web 
    //uses sockets to open a connection to google.com 
    $connected = @fsockopen("www.google.com", 80); 
    if ($connected){ 
        $is_conn = true; 
        fclose($connected); 
    }else{ 
        $is_conn = false; 
    } 
    return $is_conn; 
    
}//end is_connected function 	
function plgDetector( $mode,$ip=null,$email,$name,$body,$website){
global $mainframe;
$plugin =& JPluginHelper::getPlugin('alikonweb', 'alikonweb.detector');	
$botParams = new JParameter( $plugin->params );
//$ip='84.0.164.93';
//$ip='91.211.144.1';
		if($ip==null) {
			$ip=getIpAddr();
		}
	
$info_localize	= array('status' => 'ko', 'latitude' => '0', 'longitude' => '0', 'zippostalcode' => '', 'city' => 'unknown', 'region_name' => '', 'country_name' => 'Unknown', 'country_code' => 'UN', 'ip' => $ip);		   ;
$info_spam      = array('status' => 'ko','text' => 'Off-line', 'score' => 0);		

if (is_connected()){
  switch ($mode):	 
      case 0:
       $info_spam = plgHoneypot2ip($ip );		   
	     if ($info_spam['score'] < 5 ) {		
	       $info_spam = plgStopforumspam2ip($ip,$email);
       }     
	     if ($info_spam['score'] < 5 ) {		
	       $info_spam = plgBotscout2ip($ip,$email,$name);
       }	 			
	     if ($info_spam['score'] < 5 ) {		
	       $info_spam = plgFspamlist($ip,$email);
	     }	  	     		   
       if ($info_spam['score'] < 5 ) {			     
	       $info_spam = plgSpamhaus($ip);
	     }		   
	     if ($info_spam['score'] < 5 ) {		
	          $info_spam = plgSpamcop($ip);
	     }
	     if ($info_spam['score'] < 5 ) {		
	          $info_spam = plgSorbs($ip);
	     }
	     if ($info_spam['score'] < 5 ) {		
	          $info_spam = plgMollom($email,$name,$body,$website);
	     }
       $info_localize = plgLocalizeIp($ip );	
	   break;
      case 1: 
         $info_localize = plgLocalizeIp($ip );			
	       $info_spam = plgHoneypot2ip($ip );	
	       if ($info_spam['score'] < 5 ) {		
	          $info_spam = plgStopforumspam2ip($ip,$email);
         }
	       if ($info_spam['score'] < 5 ) {		
	          $info_spam = plgBotscout2ip($ip,$email,$name);
         }	      
	       if ($info_spam['score'] < 5 ) {		
	          $info_spam = plgFspamlist($ip,$email);
	       }		   
	       if ($info_spam['score'] < 5 ) {			                             
	          $info_spam = plgAkismet($name,$email,$website,$body);
	       }	
	       if ($info_spam['score'] < 5 ) {			                             
	          $info_spam = plgDefensio($name,$email,$website,$body);
	       }
         break;  		   
      case 2:
         $info_localize = plgLocalizeIp($ip );			
	     $info_spam = plgAkismet($name,$email,$website,$body);	   	  
	     if ($info_spam['score'] < 5 ) {			                             
	        $info_spam = plgDefensio($name,$email,$website,$body);
	     }	
		 break;  
      case 3:
         $info_spam = plgHoneypot2ip($ip );	
	       if ($info_spam['score'] < 5 ) {		
	          $info_spam = plgStopforumspam2ip($ip,$email);
         }
	       if ($info_spam['score'] < 5 ) {		
	          $info_spam = plgBotscout2ip($ip,$email,$name);
         }	      
	       if ($info_spam['score'] < 5 ) {		
	          $info_spam = plgFspamlist($ip,$email);
	       }		   
	       if ($info_spam['score'] < 5 ) {			                             
	          $info_spam = plgAkismet($name,$email,$website,$body);
	       }	
	       if ($info_spam['score'] < 5 ) {			                             
	          $info_spam = plgDefensio($name,$email,$website,$body);
	       }	
		break;   
      case 4:
       $info_spam = plgHoneypot2ip($ip );		   
	     if ($info_spam['score'] < 5 ) {		
	       $info_spam = plgStopforumspam2ip($ip,$email);
       }     
	     if ($info_spam['score'] < 5 ) {		
	       $info_spam = plgBotscout2ip($ip,$email,$name);
       }	 			
	     if ($info_spam['score'] < 5 ) {		
	       $info_spam = plgFspamlist($ip,$email);
	     }	  
       if ($info_spam['score'] < 5 ) {			     
	       $info_spam = plgSpamhaus($ip);
	   }		   
       //$info_localize = plgLocalizeIp($ip );	
	   break;
       case 5:	   
	     
	     $info_localize = plgLocalizeIp($ip );	
		// jexit('ip'.$info_localize['ip']);
		 break;
  endswitch;

  }   
return array_merge( $info_spam,$info_localize);
		
}	

function plgFspamlist( $ip,$email){
	$plugin =& JPluginHelper::getPlugin('alikonweb', 'alikonweb.detector');	
	$botParams = new JParameter( $plugin->params );
	$fspamlist_use	= $botParams->get('fspamlist_use', 0);
	$fspamlist_api_key	= $botParams->get('fspamlist_api_key', '');
  if (!$fspamlist_use){
	    return array('status' => 'ko', 'text' => JText::_( 'Fspamlist disabled' ), 'score' => 0);
  }	
  if ($fspamlist_api_key ==''){
	    return  array('status' => 'ko','text' => JText::_( 'FspamList No API Key' ),'score' => 0);
  }
  $response = array('status' => 'ok','text' => JText::_( 'FspamList not found' ),'score' => 0);
  $fname='name';
	$xml_string =file_get_contents('http://www.fspamlist.com/api.php?key='.$fspamlist_api_key.'&spammer='.$email.','.$fname.','.$ip);
	if ($xml_string === false) {
	      return  array('status' => 'ko','text' => JText::_( 'FspamList no connection' ),'score' => 0);
	}
	if ( $xml_string == 'Invalid API Key' ){
	    		 	    	          	       
	    return  array('status' => 'ko','text' => JText::_( 'FspamList Invalid API Key' ),'score' => 0);		
	}else {  
		  $response = array('status' => 'ok','text' => JText::_( 'FspamList no spam' ),'score' => 0);		
		  $xml = new SimpleXMLElement($xml_string);        	   
	    foreach ($xml->children() as $node) {
	    		$arr = $node->attributes();   // returns an array
	       	if( $node->isspammer == 'true' ){
	       		if( $node->spammer == $ip ){
	    		    //return array('status' => 'ok','text' => JText::_( 'FspamList In database for ip ' ).$node->timesreported.JText::_( ' times' ),'score' => 10);
	    		   // echo '10'; return;
				     $response=array('status' => 'ok','text' => JText::_( 'FspamList In database for ip ' ).$node->timesreported.JText::_( ' times' ),'score' => 10);
					 break;
					 
	    		  }else{
	    		   // return array('status' => 'ok','text' => JText::_( 'FspamList In database for email ' ).$node->timesreported.JText::_( ' times' ),'score' => 11);
				    $response= array('status' => 'ok','text' => JText::_( 'FspamList In database for email ' ).$node->timesreported.JText::_( ' times' ),'score' => 11);
					break;
	    		  //	echo '11'; return;
	    		  }  
	      	}
	    }	
	}   
	/* 
	if (!$fspamlist_api_key ==''){
		//Fspamlist		
      $fname='name';
	    $xml_string =file_get_contents('http://www.fspamlist.com/api.php?key='.$fspamlist_api_key.'&spammer='.$mail.','.$fname.','.$ip);
	    if ($xml_string === false) return = array('status' => 'ko','text' => JText::_( 'FspamList wrong Apikey' ),'score' => 0);
	    if( $xml_string != '' )&&( $xml_string == 'Invalid API Key' ){
	        $response = array('status' => 'ok','text' => JText::_( 'FspamList No spam' ),'score' => 1); 	
	    //	Jexit('fs:'.$xml_string);
	    	 $xml = new SimpleXMLElement($xml_string);
        	   
	    	 foreach ($xml->children() as $node) {
	    
	    		$arr = $node->attributes();   // returns an array
	    			 	 
	       	if( $node->isspammer == 'true' ){
	       		if( $node->spammer == $ip ){
	    		    $response = array('status' => 'ok','text' => JText::_( 'FspamList In database for ip ' ).$node->timesreported.JText::_( ' times' ),'score' => 10);
	    		  }else{
	    		  	$response = array('status' => 'ok','text' => JText::_( 'FspamList In database for email ' ).$node->timesreported.JText::_( ' times' ),'score' => 11);
	    		  }  
	      	}
	      	
	       }	      
	    }else {     			
	       $response = array('status' => 'ko','text' => JText::_( 'FspamList No connection or APIkey invalid' ),'score' => 1); 	      
	    }		      	   		
   }
  */ 
 return $response;	

}	

#---Defensio Spamtrap begin -----------------------------------------------------------------------------------#
function plgDefensio( $author,$email,$website,$body){
	//JPlugin::loadLanguage( 'plg_alikonweb_alikonweb.detector',JPATH_ADMINISTRATOR );
	$plugin =& JPluginHelper::getPlugin('alikonweb', 'alikonweb.detector');	
	$botParams = new JParameter( $plugin->params );
	$response='No Defensio Api key';	 
	$document = array();
//	$defensio_api_key	= $botParams->set('defensio_api_key', 'yourapikey');
$defensio_api_key	= $botParams->get('defensio_api_key', '');
$defensio_use	= $botParams->get('defensio_use', 0);
if ($defensio_use==0){
	   return array('status' => 'ko', 'text' => 'Defensio disabled', 'score' => 0);
}		      
If (!$defensio_api_key ==''){
	require_once(dirname(__FILE__).DS.'alikonweb_plgDetector'.DS.'Defensio.php');
	$defensio = new Defensio($defensio_api_key);
	if (array_shift($defensio->getUser()) == 200){
  	    $document = array(
		    'type' => 'comment', 
			  'content' => $body, 
			  'platform' => 'alikonweb_joomla', 
			  'client' => 'Defensio-PHP Example | 0.1 | alikon | info@alikonweb.it', 
			  'async' => 'true'
		);
		$post_result = $defensio->postDocument($document);
		$doc1_signature = $post_result[1]->signature;
    $get_result  = $defensio->getDocument($doc1_signature);
		if ($get_result[1]->status=='success'){
		   switch ($get_result[1]->classification) {
		      case 'legitimate':
		      $response=array('status' => 'ko', 'text' => 'Defensio: legitimate', 'score' => 0); 
               break;			  
			    case 'innocent':
			    $response=array('status' => 'ko', 'text' => 'Defensio: innocent', 'score' => 0); 
			    break;
			    case 'malicious':
			    $response=array('status' => 'ko', 'text' => 'Defensio: malicious', 'score' => 13); 
			    break;
			    case 'spam':
			    $response=array('status' => 'ko', 'text' => 'Defensio: spam', 'score' => 14); 
			    break;
		   }
		
		}
	}else{        
		$response=array('status' => 'ko', 'text' => 'Defensio:Api key is invalid', 'score' => 0); 
	}
}else{        
   $response=array('status' => 'ko', 'text' => 'Defensio: no api key', 'score' => 0); 	
}         
    
     
return $response;	
}	
#---Akismet Spamtrap begin -----------------------------------------------------------------------------------#
function plgAkismet( $author,$email,$website,$body){
//	JPlugin::loadLanguage( 'plg_alikonweb_alikonweb.detector',JPATH_ADMINISTRATOR );
	$plugin =& JPluginHelper::getPlugin('alikonweb', 'alikonweb.detector');	
	$botParams = new JParameter( $plugin->params );
	$akismet_api_key = $botParams->get('akismet_api_key', '');
	$akismet_use	 = $botParams->get('akismet_use', 0);
	if ($akismet_use==0){
	   return array('status' => 'ko', 'text' => 'Akismet disabled', 'score' => 0);
	}	
	if (!$akismet_api_key ==''){
	    $comment = array(
            'author'    => $author,
            'email'     => $email,
            'website'   => $website,
            'body'      => $body,
            'permalink' =>  JURI::base(),
         );     
        require_once(dirname(__FILE__).DS.'alikonweb_plgDetector'.DS.'Akismet.class.php');
        //$akismet = new Akismet('http://www.yourdomain.com/', 'YOUR_WORDPRESS_API_KEY', $comment);
        $akismet = new Akismet( JURI::base(), $akismet_api_key, $comment);
        if($akismet->errorsExist()) {		            
          $response= array('status' => 'ko', 'text' => 'Not connected to Akismet server!', 'score' => 0);   
        } else {
          if($akismet->isSpam()) {
		     $response= array('status' => 'ko', 'text' => 'Akismet:Spam detected', 'score' => 12);   
          } else {
		     $response= array('status' => 'ko', 'text' => 'Akismet:No spam!', 'score' => 0);                 
          }
      }
	} else { 
	  $response= array('status' => 'ko', 'text' => 'Akismet no api key', 'score' => 0);
    }
return $response;	
}	
#----Spamtrap end-------------------------------------------------------------------------------------#
#---Honeypot quicklink begin -----------------------------------------------------------------------------------#
function plgTrap4Honeypot( ){
	JPlugin::loadLanguage( 'plg_alikonweb_alikonweb.detector',JPATH_ADMINISTRATOR );
	$plugin =& JPluginHelper::getPlugin('alikonweb', 'alikonweb.detector');	
	$botParams = new JParameter( $plugin->params );
	//$honey_api_key	= $botParams->set('honey_api_key', '');
	$honey_quicklink	= $botParams->get('honey_quicklink', '');
	//$scout_api_key	= $botParams->set('scout_api_key', '');
	echo $honey_quicklink;
return;	
}	
#----Spamtrap end-------------------------------------------------------------------------------------#
#----geolocation begin--------------------------------------------------------------------------------#
function plgLocalizeIp( $ip){
	//JPlugin::loadLanguage( 'plg_alikonweb_alikonweb.detector',JPATH_ADMINISTRATOR );
	$plugin =& JPluginHelper::getPlugin('alikonweb', 'alikonweb.detector');
	$botParams = new JParameter( $plugin->params );
	  
	$ipinfodb_api_use	= $botParams->get('ipinfodb_api_use', 0);	
	$ipinfodb_api_key	= $botParams->get('ipinfodb_api_key', '');	


	$response	= array('status' => 'ko', 'latitude' => '0', 'longitude' => '0', 'zippostalcode' => '', 'city' => 'unknown', 'region_name' => '', 'country_name' => 'Unknown', 'country_code' => 'UN', 'ip' => $ip);		   ;
   
	if (($ipinfodb_api_use)&&($ipinfodb_api_key!='')) {		
    	require_once(dirname(__FILE__).DS.'alikonweb_plgDetector'.DS.'infoDB.function.php');       
	    $response	= ipLocation( $ip,$ipinfodb_api_key );
	}
/*
	echo 'DEBUG<br />';
	echo 'ip [' . $ip . ']<br />';
	echo 'response:<br />';
	print_r( $response );
	die( 'userbreak - mic' );
*/

	return $response;
}


#----geolocation end--------------------------------------------------------------------------------#

#----Honeypot report  begin------------------------------------------------------------------------------#
function plgHoneypot2ip($ip){
//	JPlugin::loadLanguage( 'plg_alikonweb_alikonweb.detector',JPATH_ADMINISTRATOR );
	$plugin =& JPluginHelper::getPlugin('alikonweb', 'alikonweb.detector');
	//include_once('getrealip.php');
    $status='ok';
	$botParams = new JParameter( $plugin->params );
	$honey_api_key	= $botParams->get('honey_api_key', '');	
	$honey_api_use	= $botParams->get('honey_api_use', 0);	
	$response		= '';

	// new mic: check for keys to avoid unnesserary loads!
	if( !$honey_api_key  ) {
		return  array('status' => 'ko', 'text' => 'HoneyPot no api key', 'score' => 0);
	}
	if( !$honey_api_use  ) {
		return  array('status' => 'ko', 'text' => 'HoneyPot disabled', 'score' => 0);
	} 
	 
  //require_once(dirname(__FILE__).DS.'alikonweb_plgDetector'.DS.'Honeypot.class.php');     

	$h = new http_bl( $honey_api_key );
	
	$r				= $h->query($ip);

	if($r==2){
		$honey_report	= JText::sprintf( 'HoneyPot Found a %s (%s) with a score of %s. last seen since %s days', $h->type_txt, $h->type_num, $h->score, $h->days );
	}elseif($r==1){
		$honey_report	= JText::sprintf( 'HoneyPot Found a Search Engine (%s)', $h->engine_num );
	}else{
		$honey_report	= JText::_( 'HoneyPot Not found' );
	}

	$response= array('status' => $status, 'text' => $honey_report, 'score' => $h->type_num);
	  
	return $response;
}
#----Honeypot report  begin------------------------------------------------------------------------------#
function plgBotscout2ip($ip,$email,$name){
	//JPlugin::loadLanguage( 'plg_alikonweb_alikonweb.detector',JPATH_ADMINISTRATOR );
	$plugin =& JPluginHelper::getPlugin('alikonweb', 'alikonweb.detector');


	$botParams = new JParameter( $plugin->params );
	
	$scout_api_use	= $botParams->get('scout_api_use', 0);	
	$scout_api_key	= $botParams->get('scout_api_key', '');	
	$response		= '';
		//jexit('JINVALIDuse:'.$scout_api_use);
	if( !$scout_api_use ) {
		return array('status' => 'ko','text' => 'BotScout disabled', 'score' => 0);
	}
    
	// new mic: check for keys to avoid unnesserary loads!
	if( !$scout_api_key ) {
		return array('status' => 'ko','text' => 'BotScout no api key', 'score' => 0);
	}
  require_once(dirname(__FILE__).DS.'alikonweb_plgDetector'.DS.'Botscout.function.php');     
	
	$botscout_report	= http_botscout( $ip, $scout_api_key ,$email,$name);
	
   
	return $botscout_report;
}
#----StopForumSpam report  begin------------------------------------------------------------------------------#
function plgStopforumspam2ip($ip,$email){
	//JPlugin::loadLanguage( 'plg_alikonweb_alikonweb.detector',JPATH_ADMINISTRATOR );
	$plugin =& JPluginHelper::getPlugin('alikonweb', 'alikonweb.detector');
    $status='ok';
	$botParams = new JParameter( $plugin->params );	
	$stopforumspam_api_key	= $botParams->get('stopforumspam_api_key', '');	
	$stopforumspam_use	= $botParams->get('stopforumspam_use', 0);	
	$response		= '';

	if( !$stopforumspam_use ) {
		return array('status' => 'ko','text' => 'StopForumSpam disabled', 'score' => 0);
  	}  
	// new mic: check for keys to avoid unnesserary loads!
	//if( !$stopforumspam_api_key ) {
	//		return array('status' => 'ko','text' => 'StopForumSpam no api key', 'score' => 0);
  //	}
    require_once(dirname(__FILE__).DS.'alikonweb_plgDetector'.DS.'Stopforumspam.function.php');     	
	$spambot_report		= checkSpambots( $email, $ip );	   
	return $spambot_report;
}

function getClientIP() {

		if ($_SERVER["HTTP_X_FORWARDED_FOR"]) return $_SERVER["HTTP_X_FORWARDED_FOR"];
		if ($_ENV["HTTP_CLIENT_IP"]) return $_ENV["HTTP_CLIENT_IP"];
		if ($_SERVER["REMOTE_ADDR"]) return $_SERVER["REMOTE_ADDR"];
		return '0.0.0.0';

//return '95.71.71.99';
	}
	#----spamreport  end------------------------------------------------------------------------------#


/*
Project Honey Pot Http BlackList
http://www.projecthoneypot.org/httpbl_configure.php
version 0.1

- 2008-01-18 version 0.1 by Francois Dechery, www.440net.net

This php class is distribured under the GNU Public License ("GPL") version 2.
http://www.gnu.org/licenses/gpl.txt

--------------
Usage Example:

$h=new http_bl('pqtxmwvztziq'); // put your access key here
$ip='89.149.254.13'; // replace with the ip to query
$r=$h->query($ip);

echo $ip.": ";
if($r==2){
echo "Found a " . $h->type_txt ." (".$h->type_num .") with a score of ". $h->score . ", last seen since ". $h->days . " days";
}
elseif($r==1){
echo "Found a Search engine (". $h->engine_num . ")";
}
else{
echo "Not Found";
}
*/
class http_bl
{
	var $access_key		= '';
	var $domain			= 'dnsbl.httpbl.org';
	var $answer_codes	= array();
	//var $engine_codes=array();

	var $ip 			= '';
	var $type_txt		= '';
	var $type_num		= 0;
	var $engine_txt		= '';
	var $engine_num		= 0;
	var $days			= 0;
	var $score			= 0;

	// ***********************************************
	function http_bl( $key = '' ) {
		$key && $this->access_key = $key;

		$this->answer_codes = array(
			0 => JText::_( 'Search Engine' ),
			1 => JText::_( 'Suspicious' ),
			2 => JText::_( 'Harvester' ),
			3 => JText::_( 'Suspicious' ) . ' & ' . JText::_( 'Harvester' ),
			4 => JText::_( 'Comment Spammer' ),
			5 => JText::_( 'Suspicious' ) . ' & ' . JText::_( 'Comment Spammer' ),
			6 => JText::_( 'Harvester' ) . ' & ' . JText::_( 'Comment Spammer' ),
			7 => JText::_( 'Suspicious' ) . ' & ' . JText::_( 'Harvester' ) . ' & ' . JText::_( 'Comment Spammer' )
		);
	}

	// return 1 (Search engine) or 2 (Generic) if host is found, else return 0
	function query($ip){
		
		if( !$ip ) {
			return false;
		}
		$this->ip = $ip;
		list( $a, $b, $c, $d ) = explode( '.', $ip );
		$query	= $this->access_key . ".$d.$c.$b.$a." . $this->domain;
		$host	= gethostbyname( $query );
		list( $first, $days, $score, $type ) = explode( '.', $host );

		if( $first == 127 ) {
			//spammer
			$this->days		= $days;
			$this->score	= $score;
			$this->type_num = $type;
			$this->type_txt	= $this->answer_codes[$type];

			// search engine
			if( $type == 0 ) {
				$this->days			= 0;
				$this->score		= 0;
				$this->engine_num	= $score;
				//$this->engine_txt =$this->engine_codes[$score];
				return 1;
			}else{
				return 2;
			}
		}
		return 0;
	}
}
///
function plgSpamhaus($ip){
global $mainframe;
	$plugin =& JPluginHelper::getPlugin('alikonweb', 'alikonweb.detector');	
	$botParams = new JParameter( $plugin->params );
	$spamhaus_use	= $botParams->get('spamhaus_use', 0);
  if (!$spamhaus_use){
	    return array('status' => 'ko', 'text' => JText::_( 'Spamhaus disabled' ), 'score' => 0);
  }	
  
  $address = $ip;
  $rev = implode('.',array_reverse(explode('.', $address)));
  
  //
  // Check the IP against Spamhaus
  //			
  $spamhausspambot = false;
  $lookup = $rev.'.zen.spamhaus.org.';
			// Spamhaus returns codes based on which blacklist the IP is in;
			//
			// 127.0.0.2		= SBL (Direct UBE sources, verified spam services and ROKSO spammers)
			// 127.0.0.3		= Not used
			// 127.0.0.4-8		= XBL (Illegal 3rd party exploits, including proxies, worms and trojan exploits)
			//	- 4		= CBL
			//	- 5		= NJABL Proxies (customized)
			// 127.0.0.9		= Not used
			// 127.0.0.10-11	= PBL (IP ranges which should not be delivering unauthenticated SMTP email)
			//	- 10		= ISP Maintained
			//	- 11		= Spamhaus Maintained
			//
			// We don't flag the CBL or PBL here.
  $spamhaustemp = gethostbyname($lookup);			
  switch ($spamhaustemp){
				case "127.0.0.2":
					$sSHDB = "(SBL) ";
					$spamhausspambot = true;
					break;
				case "127.0.0.4": // We don't flag those in the CBL
					$sSHDB = "(CBL) ";
					$spamhausspambot = false;
					break;
				case "127.0.0.5":
					$sSHDB = "(NJABL) ";
					$spamhausspambot = true;
					break;
				case "127.0.0.6":
					$sSHDB = "(XBL) ";
					$spamhausspambot = true;
					break;
				case "127.0.0.7":
					$sSHDB = "(XBL) ";
					$spamhausspambot = true;
					break;
				case "127.0.0.8":
					$sSHDB = "(XBL) ";
					$spamhausspambot = true;
					break;
				case "127.0.0.10": // We don't flag those in the PBL
					$sSHDB = "(PBL - ISP Maintained) ";
					$spamhausspambot = false;
					break;
				case "127.0.0.11": // We don't flag those in the PBL
					$sSHDB = "(PBL - Spamhaus Maintained) ";
					$spamhausspambot = false;
					break;
				default: // We only flag valid responses
					$sSHDB = "";
					$spamhausspambot = false;
					break;
			} // End switch
		if($spamhausspambot == true){			
			$response = array('status' => 'ok','text' => JText::_( 'Spamhaus found ' ).$sSHDB,'score' => 14);	
		}else{		
		   	$response = array('status' => 'ok','text' => JText::_( 'Spamhaus not found' ),'score' => 0);
		} // End if
		
 return $response;	

}	
///
function plgSpamcop($ip){
global $mainframe;
	$plugin =& JPluginHelper::getPlugin('alikonweb', 'alikonweb.detector');	
	$botParams = new JParameter( $plugin->params );
	$spamhaus_use	= $botParams->get('spamcop_use', 0);
  if (!$spamhaus_use){
	    return array('status' => 'ko', 'text' => JText::_( 'SpamCop disabled' ), 'score' => 0);
  }	
  
  $address = $ip;
  $rev = implode('.',array_reverse(explode('.', $address)));
  
  //
  // Check the IP against Spamhaus
  //			
  $spamcopresult = false;
  $lookup = $rev.'.bl.spamcop.net.';
			// The response code from the SpamCop server to indicate a queried IP is listed is 127.0.0.2
			
  $lookupResult = gethostbyname($lookup);			
  if ($lookupResult == '127.0.0.2')
			{
  
					$sSHDB = 'SpamCop (RawResponse=' .$lookupResult .')';
					$spamcopresult = true;
	
			} // End switch
		if($spamcopresult == true){			
			$response = array('status' => 'ok','text' => JText::_( 'SpamCop found ' ).$sSHDB,'score' => 147);	
		}else{		
		   	$response = array('status' => 'ok','text' => JText::_( 'SpamCop not found' ),'score' => 0);
		} // End if
		
 return $response;	

}	
function plgSorbs($ip){
global $mainframe;
	$plugin =& JPluginHelper::getPlugin('alikonweb', 'alikonweb.detector');	
	$botParams = new JParameter( $plugin->params );
	$spamhaus_use	= $botParams->get('sorbs_use', 0);
  if (!$spamhaus_use){
	    return array('status' => 'ko', 'text' => JText::_( 'Sorbs disabled' ), 'score' => 0);
  }	
  
  $address = $ip;
  $rev = implode('.',array_reverse(explode('.', $address)));
  
  //
  // Check the IP against Sorbs
  //			
  $sorbsresult = false;
  $lookup = $rev.'.l1.spews.dnsbl.sorbs.net.';
			// The response code from the SpamCop server to indicate a queried IP is listed is 127.0.0.2
			
  $lookupResult = gethostbyname($lookup);			
  if ($lookup != $lookupResult)
			{
  
					$sSHDB = 'Sorbs (RawResponse=' .$lookupResult .')';
					$sorbsresult = true;
	
			} // End switch
		if($sorbsresult == true){			
			$response = array('status' => 'ok','text' => JText::_( 'Sorbs found ' ).$sSHDB,'score' => 157);	
		}else{		
		   	$response = array('status' => 'ok','text' => JText::_( 'Sorbs not found' ),'score' => 0);
		} // End if
	

 return $response;	

}	
function plgMollom($email,$name,$comment,$url){
global $mainframe;
	$plugin =& JPluginHelper::getPlugin('alikonweb', 'alikonweb.detector');	
	$botParams = new JParameter( $plugin->params );
	$mollom_use	= $botParams->get('mollom_use', 0);
	$mollom_publickey	= $botParams->get('mollom_publickey', 0);
	$mollom_privatekey	= $botParams->get('mollom_privatekey', 0);
  if (!$mollom_use){
	    return array('status' => 'ko', 'text' => JText::_( 'Mollom disabled' ), 'score' => 0);
  }	
  
  require_once(dirname(__FILE__).DS.'alikonweb_plgDetector'.DS.'mollom.php');     	
            Mollom::setPublicKey($mollom_publickey);
            Mollom::setPrivateKey($mollom_privatekey);

            $servers = Mollom::getServerList();
/*
            $name = '';
            $email = '';
            $url = '';
            $comment = '';
  */
  if(Mollom::verifyKey()){
     $mollomresult = Mollom::checkContent(null, null, $comment, $name, $url, $email);
     //jexit(var_dump($mollomresult));
		if($mollomresult['spam'] == 'spam'){			
			$response = array('status' => 'ok','text' => JText::_( 'Mollom found ' ).$sSHDB,'score' => 177);	
		}else{		
		   	$response = array('status' => 'ok','text' => JText::_( 'Mollom not found' ),'score' => 0);
		} // End if
 }else{		
 	$response = array('status' => 'ok','text' => JText::_( 'Mollom Verify Key' ),'score' => 0);
 }
 return $response;	

}	
?>			