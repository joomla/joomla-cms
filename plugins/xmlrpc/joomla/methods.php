<?php
/**
* @version $Id: joomla.php 3996 2006-06-12 03:44:31Z spacemonkey $
* @package Joomla
* @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
* @license GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

/**
 * Static XML-RPC Services class
 *
 * @static
 * @package	XML-RPC
 * @since	1.5
 */
class JoomlaXMLRPCServices
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
	function searchSite( $searchword, $phrase='', $order='' )
	{
		global $mainframe;

		// Initialize variables
		$db		=& $mainframe->getDBO();
		$url	= $mainframe->getSiteURL();

		// Prepare arguments
		$searchword	= $db->getEscaped( trim( $searchword ) );
		$phrase		= '';
		$ordering	= '';

		// Load search plugins and fire the onSearch event
		JPluginHelper::importPlugin( 'search' );
		$results = $mainframe->triggerEvent( 'onSearch', array( $searchword, $phrase, $ordering ) );

		// Iterate through results building the return array
		foreach ($results as $i=>$rows) {
			foreach ($rows as $j=>$row) {
				$results[$i][$j]->href = $url.'/'.$row->href;
				$results[$i][$j]->text = mosPrepareSearchContent( $row->text, 200, $searchword);
			}
		}
		return $results;
	}
}
?>