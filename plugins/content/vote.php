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

$mainframe->registerEvent( 'onBeforeDisplayContent', 'plgContentVote' );

function plgContentVote( &$row, &$params, $page=0 )
{
	$uri = & JFactory::getURI();

	$id 	= $row->id;
	$html 	= '';

	if (isset($row->rating_count) && $params->get( 'show_vote' ) && !$params->get( 'popup' ))
	{
		JPlugin::loadLanguage( 'plg_content_vote' );
		$html .= '<form method="post" action="' . $uri->toString( ) . '">';
		$img = '';

		// look for images in template if available
		$starImageOn 	= JHtml::_('image.site',  'rating_star.png', '/images/M_images/' );
		$starImageOff 	= JHtml::_('image.site',  'rating_star_blank.png', '/images/M_images/' );

		for ($i=0; $i < $row->rating; $i++) {
			$img .= $starImageOn;
		}
		for ($i=$row->rating; $i < 5; $i++) {
			$img .= $starImageOff;
		}
		$html .= '<span class="content_rating">';
		$html .= JText::_( 'User Rating' ) .':'. $img .'&nbsp;/&nbsp;';
		$html .= intval( $row->rating_count );
		$html .= "</span>\n<br />\n";

		if (!$params->get( 'intro_only' ))
		{
			$html .= '<span class="content_vote">';
			$html .= JText::_( 'Poor' );
			$html .= '<input type="radio" alt="vote 1 star" name="user_rating" value="1" />';
			$html .= '<input type="radio" alt="vote 2 star" name="user_rating" value="2" />';
			$html .= '<input type="radio" alt="vote 3 star" name="user_rating" value="3" />';
			$html .= '<input type="radio" alt="vote 4 star" name="user_rating" value="4" />';
			$html .= '<input type="radio" alt="vote 5 star" name="user_rating" value="5" checked="checked" />';
			$html .= JText::_( 'Best' );
			$html .= '&nbsp;<input class="button" type="submit" name="submit_vote" value="'. JText::_( 'Rate' ) .'" />';
			$html .= '<input type="hidden" name="task" value="vote" />';
			$html .= '<input type="hidden" name="option" value="com_content" />';
			$html .= '<input type="hidden" name="cid" value="'. $id .'" />';
			$html .= '<input type="hidden" name="url" value="'.  $uri->toString( ) .'" />';
			$html .= '</span>';
		}
		$html .= '</form>';
	}
	return $html;
}