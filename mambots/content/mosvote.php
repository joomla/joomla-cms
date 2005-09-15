<?php
/**
* @version $Id: mosvote.php 137 2005-09-12 10:21:17Z eddieajau $
* @package Joomla
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software and parts of it may contain or be derived from the
* GNU General Public License or other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_VALID_MOS' ) or die( 'Restricted access' );

$_MAMBOTS->registerFunction( 'onBeforeDisplayContent', 'botVoting' );

function botVoting( &$row, &$params, $page=0 ) {
	global $mosConfig_live_site, $mosConfig_absolute_path, $cur_template;
	global $Itemid, $_LANG;

	$id = $row->id;
	$option = 'com_content';
	$task = mosGetParam( $_REQUEST, 'task', '' );

	$html = '';
	if ($params->get( 'rating' ) && !$params->get( 'popup' )){
		$html .= '<form method="post" action="' . sefRelToAbs( 'index.php' ) . '">';
		$img = '';

		// look for images in template if available
		$starImageOn = mosAdminMenus::ImageCheck( 'rating_star.png', '/images/M_images/' );
		$starImageOff = mosAdminMenus::ImageCheck( 'rating_star_blank.png', '/images/M_images/' );

		if ($row->rating_count > 0) {
			$rating = round( $row->rating_sum / $row->rating_count );
		} else {
			$rating = 0;
		}
		for ($i=0; $i < $rating; $i++) {
			$img .= $starImageOn;
		}
		for ($i=$rating; $i < 5; $i++) {
			$img .= $starImageOff;
		}
		$html .= '<span class="content_rating">';
		$html .= $_LANG->_( 'USER_RATING' ) . ':' . $img . '&nbsp;/&nbsp;';
		$html .= intval( $row->rating_count );
		$html .= "</span>\n<br />\n";
		$url = @$_SERVER['REQUEST_URI'];
		$url = ampReplace( $url );

		if (!$params->get( 'intro_only' ) && $task != "blogsection") {
			$html .= '<span class="content_vote">';
			$html .= $_LANG->_( 'VOTE_POOR' );
			$html .= '<input type="radio" alt="vote 1 star" name="user_rating" value="1" />';
			$html .= '<input type="radio" alt="vote 2 star" name="user_rating" value="2" />';
			$html .= '<input type="radio" alt="vote 3 star" name="user_rating" value="3" />';
			$html .= '<input type="radio" alt="vote 4 star" name="user_rating" value="4" />';
			$html .= '<input type="radio" alt="vote 5 star" name="user_rating" value="5" checked="checked" />';
			$html .= $_LANG->_( 'VOTE_BEST' );
			$html .= '&nbsp;<input class="button" type="submit" name="submit_vote" value="'. $_LANG->_( 'RATE_BUTTON' ) .'" />';
			$html .= '<input type="hidden" name="task" value="vote" />';
			$html .= '<input type="hidden" name="pop" value="0" />';
			$html .= '<input type="hidden" name="option" value="com_content" />';
			$html .= '<input type="hidden" name="Itemid" value="'. $Itemid .'" />';
			$html .= '<input type="hidden" name="cid" value="'. $id .'" />';
			$html .= '<input type="hidden" name="url" value="'. $url .'" />';
			$html .= '</span>';
		}
		$html .= "</form>\n";
	}
	return $html;
}
?>
