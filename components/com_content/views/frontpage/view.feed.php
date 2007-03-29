<?php
/**
 * @version		$Id$
 * @package		Joomla
 * @subpackage	Content
 * @copyright	Copyright (C) 2005 - 2007 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport( 'joomla.application.component.view');

/**
 * Frontpage View class
 *
 * @static
 * @package		Joomla
 * @subpackage	Content
 * @since 1.5
 */
class ContentViewFrontpage extends JView
{
	function display($tpl = null)
	{
		global $mainframe;

		// parameters
		$db			=& JFactory::getDBO();
		$document	=& JFactory::getDocument();
		$limit		= '10';
		$document->link = JRoute::_(JURI::base().'index.php?option=com_content&view=frontpage');

		JRequest::setVar('limit', $limit);
		$rows = $this->get('data');

		foreach ( $rows as $row )
		{
			// strip html from feed item title
			$title = htmlspecialchars( $row->title );
			$title = html_entity_decode( $title );

			// url link to article
			// & used instead of &amp; as this is converted by feed creator
			$link = JRoute::_( JURI::base().'index.php?option=com_content&view=article&id='. $row->id );

			// strip html from feed item description text
			$description	= $row->introtext;
			$author			= $row->author;
			@$date			= ( $row->created ? date( 'r', strtotime($row->created) ) : '' );

			// load individual item creator class
			$item = new JFeedItem();
			$item->title 		= $title;
			$item->link 		= $link;
			$item->description 	= $description;
			$item->date			= $date;
			$item->author		= $author;
			$item->category   	= 'frontpage';

			// loads item info into rss array
			$document->addItem( $item );
		}
	}
}
?>