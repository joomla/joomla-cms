<?php
/**
 * @version		$Id: cjlib.php 01 2011-01-11 11:37:09Z maverick $
 * @package		CoreJoomla.CJLib
 * @subpackage	Components.framework
 * @copyright	Copyright (C) 2009 - 2010 corejoomla.com. All rights reserved.
 * @author		Maverick
 * @link		http://www.corejoomla.com/
 * @license		License GNU General Public License version 2 or later
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

require_once 'framework.php';
require_once JPATH_COMPONENT.DS.'controller.php';

CJLib::import('corejoomla.framework.core');

$config = CJLib::get_cjconfig();

$task = JRequest::getCmd('task', '');
$secret = JRequest::getCmd('secret', null);
$component = 'com_cjlib';

if($task == 'process' && !empty($secret) && (strcmp($config['cron_secret'], $secret) == 0)){
	
	$emails = (int)$config['cron_emails'];
	$delay = (int)$config['cron_delay'];
	
	$sent = CJFunctions::send_messages_from_queue($emails, $delay, false);
	
	if(!empty($sent)){
		
		echo json_encode($sent);
	}
} else if($task = 'socialcounts'){
	
	require_once CJLIB_PATH.DS.'jquery'.DS.'social'.DS.'socialcounts.php';
	
	$url = base64_decode(JFactory::getApplication()->input->getString('url'));
	
	if( !SocialCount::REQUIRE_LOCAL_URL || SocialCount::isLocalUrl( $url ) ) {
	
		try {
			
			$social = new SocialCount( $url );
	
			$social->addNetwork(new Twitter());
			$social->addNetwork(new Facebook());
			$social->addNetwork(new GooglePlus());
			// $social->addNetwork(new ShareThis());
	
			echo $social->toJSON();
		} catch(Exception $e) {
			
			echo '{"error": "' . $e->getMessage() . '"}';
		}
	} else {
		
		echo '{"error": "URL not authorized."}';
	}
}

jexit();