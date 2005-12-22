<?php
/**
* @version $Id$
* @package Joomla
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

$mainframe->registerEvent( 'onPrepareContent', 'botMosImage' );

/**
*/
function botMosImage( $published, &$row, &$params, $page=0 ) {
	global $database;

 	// expression to search for
	$regex = '/{mosimage\s*.*?}/i';

	// check whether mosimage has been disabled for page
	// check whether mambot has been unpublished
	if (!$published || !$params->get( 'image' )) {
		$row->text = preg_replace( $regex, '', $row->text );
		return true;
	}

	//count how many {mosimage} are in introtext if it is set to hidden.
	$introCount=0;
	if ( ! $params->get( 'introtext' ) & ! $params->get( 'intro_only') )
	{
		preg_match_all( $regex, $row->introtext, $matches );
		$introCount = count ( $matches[0] );
	}

	// find all instances of mambot and put in $matches
	preg_match_all( $regex, $row->text, $matches );

 	// Number of mambots
	$count = count( $matches[0] );

 	// mambot only processes if there are any instances of the mambot in the text
 	if ( $count ) {
		// load mambot params info
		$query = "SELECT id"
		. "\n FROM #__mambots"
		. "\n WHERE element = 'mosimage'"
		. "\n AND folder = 'content'"
		;
		$database->setQuery( $query );
	 	$id 	= $database->loadResult();
	 	$mambot = new JMambotModel( $database );
	  	$mambot->load( $id );
	 	$botParams = new JParameters( $mambot->params );

	 	$botParams->def( 'padding' );
	 	$botParams->def( 'margin' );
	 	$botParams->def( 'link', 0 );

		$images 	= processImages( $row, $botParams, $introCount );

		// store some vars in globals to access from the replacer
		$GLOBALS['botMosImageCount'] 	= 0;
		$GLOBALS['botMosImageParams'] 	=& $botParams;
		$GLOBALS['botMosImageArray'] 	=& $images;
		//$GLOBALS['botMosImageArray'] 	=& $combine;

		// perform the replacement
		$row->text = preg_replace_callback( $regex, 'botMosImage_replacer', $row->text );

		// clean up globals
		unset( $GLOBALS['botMosImageCount'] );
		unset( $GLOBALS['botMosImageMask'] );
		unset( $GLOBALS['botMosImageArray'] );
		unset( $GLOBALS['botJosIntroCount'] );
		return true;
	}
}

function processImages ( &$row, &$params, &$introCount ) {
	$images 		= array();

	// split on \n the images fields into an array
	$row->images 	= explode( "\n", $row->images );
	$total 			= count( $row->images );

	$start = $introCount;
	for ( $i = $start; $i < $total; $i++ ) {
		$img = trim( $row->images[$i] );

		// split on pipe the attributes of the image
		if ( $img ) {
			$attrib = explode( '|', trim( $img ) );
			// $attrib[0] image name and path from /images/stories

			// $attrib[1] alignment
			if ( !isset($attrib[1]) || !$attrib[1] ) {
				$attrib[1] = '';
			}

			// $attrib[2] alt & title
			if ( !isset($attrib[2]) || !$attrib[2] ) {
				$attrib[2] = 'Image';
			} else {
				$attrib[2] = htmlspecialchars( $attrib[2] );
			}

			// $attrib[3] border
			if ( !isset($attrib[3]) || !$attrib[3] ) {
				$attrib[3] = '0';
			}

			// $attrib[4] caption
			if ( !isset($attrib[4]) || !$attrib[4] ) {
				$attrib[4]	= '';
				$border 	= $attrib[3];
			} else {
				$border 	= 0;
			}

			// $attrib[5] caption position
			if ( !isset($attrib[5]) || !$attrib[5] ) {
				$attrib[5] = '';
			}

			// $attrib[6] caption alignment
			if ( !isset($attrib[6]) || !$attrib[6] ) {
				$attrib[6] = '';
			}

			// $attrib[7] width
			if ( !isset($attrib[7]) || !$attrib[7] ) {
				$attrib[7] 	= '';
				$width 		= '';
			} else {
				$width 		= ' width: '. $attrib[7] .'px;';
			}

			// image size attibutes
			$size = '';
			if ( function_exists( 'getimagesize' ) ) {
				$size 	= @getimagesize( JPATH_SITE .'/images/stories/'. $attrib[0] );
				if (is_array( $size )) {
					$size = ' width="'. $size[0] .'" height="'. $size[1] .'"';
				}
			}

			// assemble the <image> tag
			$image = '<img src="'. JURL_SITE .'/images/stories/'. $attrib[0] .'"'. $size;
			// no aligment variable - if caption detected
			if ( !$attrib[4] ) {
				$image .= $attrib[1] ? ' align="'. $attrib[1] .'"' : '';
			}
			$image .=' hspace="6" alt="'. $attrib[2] .'" title="'. $attrib[2] .'" border="'. $border .'" />';

			// assemble caption - if caption detected
			if ( $attrib[4] ) {
				$caption = '<div class="mosimage_caption" style="width: '. $width .'; text-align: '. $attrib[6] .';" align="'. $attrib[6] .'">';
				$caption .= $attrib[4];
				$caption .='</div>';
			}

			// final output
			if ( $attrib[4] ) {
				$img = '<div class="mosimage" style="border-width: '. $attrib[3] .'px; float: '. $attrib[1] .'; margin: '. $params->def( 'margin' ) .'px; padding: '. $params->def( 'padding' ) .'px;'. $width .'" align="center">';

				// display caption in top position
				if ( $attrib[5] == 'top' ) {
					$img .= $caption;
				}

				$img .= $image;

				// display caption in bottom position
				if ( $attrib[5] == 'bottom' ) {
					$img .= $caption;
				}
				$img .='</div>';
			} else {
				$img = $image;
			}


			$images[] = $img;
		}
	}

	return $images;
}

/**
* Replaces the matched tags an image
* @param array An array of matches (see preg_match_all)
* @return string
*/
function botMosImage_replacer( &$matches ) {
	$i = $GLOBALS['botMosImageCount']++;

	return @$GLOBALS['botMosImageArray'][$i];
}
?>