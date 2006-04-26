<?php
/**
 * @version $Id: blog.php 3152 2006-04-19 14:28:35Z Jinx $
 * @package Joomla
 * @subpackage Content
 * @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

// require the content html view
require_once (JApplicationHelper::getPath('front_html', 'com_content'));

/**
 * RSS Blog View class for the Frontpage component
 *
 * @static
 * @package Joomla
 * @subpackage Content
 * @since 1.5
 */
class JViewBlog
{
	function show(&$model, &$access, &$menu)
	{
		global $mainframe, $Itemid;

		// parameters
		$params   =& $model->getMenuParams();
		$db       =& $mainframe->getDBO();
		$document =& $mainframe->getDocument('rss');

		$link       = $mainframe->getBaseURL() .'index.php?option=com_content&task=view&id=';
		$format		= 'RSS2.0';
		$limit		= '10';

		JRequest::setVar('limit', $limit);
		$rows = $model->getContentData();

		$count = count( $rows );
		for ( $i=0; $i < $count; $i++ )
		{
			$Itemid = $mainframe->getItemid( $rows[$i]->id );
			$rows[$i]->link = $link .$rows[$i]->id .'&Itemid='. $Itemid;
			$rows[$i]->date = $rows[$i]->created;
			$rows[$i]->description = $rows[$i]->introtext;
			
		}

		$document->createFeed( $rows, $format, $menu->name, $params );
	}

	
}
?>