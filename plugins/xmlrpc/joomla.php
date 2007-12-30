<?php
/**
* @version		$Id$
* @package		Joomla
* @copyright	Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.plugin.plugin');

/**
 * Joomla! Base XML-RPC Plugin
 *
 * @package XML-RPC
 * @since 1.5
 */
class plgXMLRPCJoomla extends JPlugin
{

	/**
	 * Constructor
	 *
	 * For php4 compatability we must not use the __constructor as a constructor for plugins
	 * because func_get_args ( void ) returns a copy of all passed arguments NOT references.
	 * This causes problems with cross-referencing necessary for the observer design pattern.
	 *
	 * @param object $subject The object to observe
	 * @param object $params  The object that holds the plugin parameters
	 * @since 1.5
	 */
	function plgXMLRPCJoomla(& $subject, $config)
	{
		parent::__construct($subject, $config);
	}

	/**
	 * Get available web services for this plugin
	 *
	 * @access	public
	 * @return	array	Array of web service descriptors
	 * @since	1.5
	 */
	function onGetWebServices()
	{
		global $xmlrpcString;

		// Initialize variables
		$services = array();

		// Site search service
		$services['joomla.searchSite'] = array(
			'function' => 'plgXMLRPCJoomlaServices::searchSite',
			'docstring' => 'Searches a remote site.',
			'signature' => array(array($xmlrpcString, $xmlrpcString, $xmlrpcString))
			);

		return $services;
	}
}

class plgXMLRPCJoomlaServices
{
	/**
	 * Remote Search method
	 *
	 * The sql must return the following fields that are used in a common display
	 * routine: href, title, section, created, text, browsernav
	 *
	 * @param	string	Target search string
	 * @param	string	mathcing option, exact|any|all
	 * @param	string	ordering option, newest|oldest|popular|alpha|category
	 * @return	array	Search Results
	 * @since	1.5
	 */
	function searchSite($searchword, $phrase='', $order='')
	{
		global $mainframe;

		// Initialize variables
		$db		=& JFactory::getDBO();

		// Prepare arguments
		$searchword	= $db->getEscaped( trim( $searchword ) );
		$phrase		= '';
		$ordering	= '';

		// Load search plugins and fire the onSearch event
		JPluginHelper::importPlugin( 'search' );
		$results = $mainframe->triggerEvent( 'onSearch', array( $searchword, $phrase, $ordering ) );

		// Iterate through results building the return array
		require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_search'.DS.'helpers'.DS.'search.php');

		foreach ($results as $i=>$rows)
		{
			foreach ($rows as $j=>$row) {
				$results[$i][$j]->href = eregi('^(http|https)://', $row->href) ? $row->href : JURI::root().'/'.$row->href;
				$results[$i][$j]->text = SearchHelper::prepareSearchContent( $row->text, 200, $searchword);
			}
		}
		return $results;
	}
}