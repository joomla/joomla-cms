<?php
/**
* version $Id$
* @package Joomla
* @subpackage Newsfeeds
* @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
* @license GNU/GPL, see LICENSE.php
*
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

/**
 * HTML View class for the Newsfeeds component
 *
 * @static
 * @package Joomla
 * @subpackage Newsfeeds
 * @since 1.0
 */
class NewsfeedsViewCategory
{
	function show( &$rows, $catid, $category, &$params, &$pagination ) {
		require(dirname(__FILE__).DS.'tmpl'.DS.'table.php');		
	}

	/**
	* Display Table of items
	*/
	function showItems( &$params, &$rows, $catid, &$pagination )
	{
		global $Itemid;
	
		$k = 0;		
		for($i = 0; $i <  count($rows); $i++) 
		{
			$rows[$i]->link =  sefRelToAbs('index.php?option=com_newsfeeds&amp;task=view&amp;feedid='. $rows[$i]->id .'&amp;Itemid='. $Itemid);
			
			$rows[$i]->odd   = $k;
			$rows[$i]->count = $i;
			$k = 1 - $k;
		}
		
		require(dirname(__FILE__).DS.'tmpl'.DS.'table_items.php');		
	}
}
?>