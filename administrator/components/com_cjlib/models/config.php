<?php
/**
 * @version		$Id: config.php 01 2012-01-11 11:37:09Z maverick $
 * @package		CoreJoomla.Cjlib
 * @subpackage	Components.models
 * @copyright	Copyright (C) 2009 - 2012 corejoomla.com, Inc. All rights reserved.
 * @author		Maverick
 * @link		http://www.corejoomla.com/
 * @license		License GNU General Public License version 2 or later
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

// Import Joomla! libraries
jimport('joomla.application.component.model');

class CjLibModelConfig extends JModelLegacy {

    function __construct() {
    	
        parent::__construct();
    }

    function save() {
        
    	$db = JFactory::getDBO();
    	$app = JFactory::getApplication();

    	$manual_cron = $app->input->getInt('manual_cron', 0);
    	$cron_emails = $app->input->getInt('cron_emails', 60);
    	$cron_delay = $app->input->getInt('cron_delay', 10);
    	
        $db->setQuery( $query );
        
        $query = "
        	insert into 
        		#__cjlib_config (config_name, config_value) 
        	values 
        		('manual_cron',".$manual_cron."),
        		('cron_emails',".$cron_emails."),
        		('cron_delay',".$cron_delay.") 
        	on duplicate key 
        		update config_value = values (config_value)";
        
        $db->setQuery($query);

        if(!$db->query()) {
        	
            return false;
        }

        return true;
    }
}
?>