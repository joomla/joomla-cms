<?php
/**
* @version $Id: view.php 4854 2006-08-31 11:29:11Z Jinx $
* @package Joomla
* @subpackage Weblinks
* @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
* @license GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

jimport( 'joomla.application.component.view');

/**
 * HTML View class for the WebLinks component
 *
 * @static
 * @package Joomla
 * @subpackage Weblinks
 * @since 1.0
 */
class WeblinksViewCategory extends JView
{
	function display($tpl = null)
	{
		global $mainframe, $Itemid, $option;

		$document =& JFactory::getDocument();

		$limit = '10';
		JRequest::setVar('limit', $limit);

		// Get some data from the model
		$items    = & $this->get( 'data' );
		$category = & $this->get( 'Category' );

		foreach ( $items as $item )
		{
			// strip html from feed item title
			$title = htmlspecialchars( $item->title );
			$title = html_entity_decode( $title );

			// url link to article
			// & used instead of &amp; as this is converted by feed creator
			$link = 'index.php?option=com_weblinks&view=weblink&id='. $item->id . '&catid='.$item->catid.$Itemid;
			$link = sefRelToAbs( $link );

			// strip html from feed item description text
			$description = $item->description;
			$date = ( $item->date ? date( 'r', (int)$item->date ) : '' );

			// load individual item creator class
			$feeditem = new JFeedItem();
			$feeditem->title 		= $title;
			$feeditem->link 		= $link;
			$feeditem->description 	= $description;
			$feeditem->date			= $date;
			$feeditem->category   	= 'Weblinks';

			// loads item info into rss array
			$document->addItem( $feeditem );
		}
	}
}
?>