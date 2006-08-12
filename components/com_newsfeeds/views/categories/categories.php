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
class NewsfeedsViewCategories
{
	function show( &$params, &$categories )
	{
		global $Itemid;
		
		// Define image tag attributes
		if ($params->get('image') != -1)
		{
			$imgAttribs['align'] = '"'. $params->get('image_align').'"';
			$imgAttribs['hspace'] = '"6"';

			// Use the static HTML library to build the image tag
			$image = mosHTML::Image('/images/stories/'.$params->get('image'), JText::_('News Feeds'), $imgAttribs);
		}

		require(dirname(__FILE__).DS.'tmpl'.DS.'list.php');	
	}
}
?>