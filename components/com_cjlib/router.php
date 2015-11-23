<?php
/**
 * @version		$Id: router.php 01 2011-01-11 11:37:09Z maverick $
 * @package		CoreJoomla.Cjlib
 * @subpackage	Components
 * @copyright	Copyright (C) 2009 - 2011 corejoomla.com. All rights reserved.
 * @author		Maverick
 * @link		http://www.corejoomla.com/
 * @license		License GNU General Public License version 2 or later
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

/*
 * Function to convert a system URL to a SEF URL
*/
function CjlibBuildRoute(&$query) {
    static $items;

    $segments	= array();
    if(isset($query['task'])) {
        $segments[] = $query['task'];
        unset($query['task']);
    }
    if(isset($query['id'])) {
        $segments[] = $query['id'];
        unset($query['id']);
    }
	unset($query['view']);
    return $segments;
}
/*
 * Function to convert a SEF URL back to a system URL
*/
function CjlibParseRoute($segments) {
    $vars = array();
    if(count($segments) > 0){
        $vars['task']	= $segments[0];
    }
    if(count($segments) > 1) {
        $vars['id']     = $segments[1];
    }

    return $vars;
}
?>