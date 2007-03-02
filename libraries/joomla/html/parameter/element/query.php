<?php

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

/**
 * @version		$Id: filelist.php 6743 2007-02-28 10:07:23Z tcp $
 * @package Joomla
 * @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
 * @license GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

// Check to ensure this file is within the rest of the framework
defined('JPATH_BASE') or die();

/**
 * Renders a results of a query
 *
 * @author 		Toby Patterson <tcp@gmitc.biz>
 * @package 	Joomla.Framework
 * @subpackage 	Parameter
 * @since		1.5
 * 
 * Use in param tag as follows
 * 
 * <pre>
 * <param name="name" type="query" query="SELECT id AS value, name AS text FROM #__table" message="No results were found." default="0" label="Label" description="Your description."/>
 * </pre>
 * 
 * The query must return two columns: value and text.
 * The message is optional.
 * 
 */

class JElementQuery extends JElement
{
   /**
	* Element name
	*
	* @access	protected
	* @var		string
	*/
	var	$_name = 'Query';

	function fetchElement($name, $value, &$node, $control_name)
	{
		// Build the query
		$db			= JFactory::getDBO();
		$sql		= $node->attributes('query');
		$default	= $node->attributes('default');
		
		// Hit the database
		$db->setQuery( $sql );
		$results	=  $db->loadAssocList('value');
		
		// Check for query failure
		if ( $results === false ) {
			return $db->ErrorMsg();
		}
		
		// Check for no results
		if ( empty($results) ) {
			$message	= $node->attributes('message');
			$message	= $message ? $message : 'No options were found.';
			return JText::_($message);
		}
		
		// Build the list of options
		$options		= array();
		
		// Offer an option to not use
		if (!$node->attributes('hide_none'))
		{
			$options[] = JHTMLSelect::option('-1', '- '.JText::_('Do not use').' -');
		}

		// Offer an option to use the default
		if (!$node->attributes('hide_default'))
		{
			$options[] = JHTMLSelect::option($default, '- '.JText::_('Use default').' -');
		}
		
		// Build the list of options
		foreach ( $results as $result ) {
			$options[]	= $result;
		}
		
		// Return a list of Select List
		$selected		= $value ? $value : $default;
		return JHTMLSelect::genericList($options, $control_name.'['.$name.']', 'class="inputbox"', 'value', 'text', $selected, "param$name");
	}
}
?>
