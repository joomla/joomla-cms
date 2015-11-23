<?php
/**
 * @version		$Id: controller.php 01 2012-08-13 11:37:09Z maverick $
 * @package		CoreJoomla.Framework
 * @subpackage	Components
 * @copyright	Copyright (C) 2009 - 2012 corejoomla.com. All rights reserved.
 * @author		Maverick
 * @link		http://www.corejoomla.com/
 * @license		License GNU General Public License version 2 or later
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

jimport( 'joomla.application.component.controller' );

class CjLibController extends JControllerLegacy {
	
    function __construct() {
    	
    	JRequest::setVar('view', 'default');
        parent::__construct();
    }
}
?>