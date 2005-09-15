<?php
/**
* @version $Id: mosimage.php 137 2005-09-12 10:21:17Z eddieajau $
* @package Mambo
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software and parts of it may contain or be derived from the
* GNU General Public License or other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_VALID_MOS' ) or die( 'Restricted access' );

$_MAMBOTS->registerFunction( 'onPrepareContent', 'botMosImage' );

/**
*/
function botMosImage( $published, &$row, &$params, $page=0 ) {
	global $database;

 	// expression to search for
	$regex = '/{mosimage\s*.*?}/i';

	// check whether mosimage has been disabled for page
	if (!$published || !$params->get( 'image' )) {
	    $row->text = str_replace( '{mosimage}', '', $row->text );
	    return true;
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
	 	$mambot = new mosMambot( $database );
	  	$mambot->load( $id );
	 	$mparams = new mosParameters( $mambot->params );

	 	$mparams->def( 'padding' );
	 	$mparams->def( 'margin' );
	 	$mparams->def( 'link', 0 );

 		$images 	= processImages( $row, $mparams );

		$start = 0;
		// needed to stopping loading of images for the introtext, when it is set to hidden
		if ( !$params->get( 'introtext' ) ) {
			// find all instances of mambot in intro text and put in $matches
			preg_match_all( $regex, $row->introtext, $matches_intro );
		 	// Number of mambots
			$start 		= count( $matches_intro[0] );
		}

		// store some vars in globals to access from the replacer
		$GLOBALS['botMosImageCount'] 	= $start;
		$GLOBALS['botMosImageParams'] 	=& $params;
		$GLOBALS['botMosImageArray'] 	=& $images;

		// perform the replacement
		$row->text = preg_replace_callback( $regex, 'botMosImage_replacer', $row->text );

		// clean up globals
		unset( $GLOBALS['botMosImageCount'] );
		unset( $GLOBALS['botMosImageMask'] );
		unset( $GLOBALS['botMosImageArray'] );

		return true;
	}
}

function processImages ( &$row, &$mparams ) {
	global $mosConfig_absolute_path, $mosConfig_live_site;

	$images 		= array();

	// split on \n the images fields into an array
	$row->images 	= explode( "\n", $row->images );

	$start = 0;
	$total = count( $row->images );

	for ( $i = $start; $i < $total; $i++ ) {
		$img = trim( $row->images[$i] );

		// split on pipe the attributes of the image
		if ( $img ) {
			$attrib = explode( '|', trim( $img ) );
			// $attrib[0] image name and path from /images/stories

			// $attrib[1] alignment
			if ( !isset($attrib[1]) || !$attrib[1] ) {
				$attrib[1] = 'left';
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
				$width 		= ' width: '. $attrib[7] .'px';
			}

			// $attrib[8] link
			if ( !isset($attrib[8]) || !$attrib[8] ) {
				$attrib[8] 	= '';
				$link 		= '';
			} else {
				$link 		= $attrib[8];
				// adds 'http://' if none is set
				if ( !strstr( $link, 'http' ) && !strstr( $link, 'https' ) ) {
					$link = 'http://'. $link;
				}
			}

			// $attrib[9] link target
			if ( !isset($attrib[9]) || !$attrib[9] ) {
				$attrib[9] 	= '';
				$target 	= '_blank';
			} else {
				$target 	= $attrib[9];
			}

			// image size attibutes
			$size = '';
			if ( function_exists( 'getimagesize' ) ) {
				$size 	= @getimagesize( $mosConfig_absolute_path .'/images/stories/'. $attrib[0] );
				if (is_array( $size )) {
					$size = ' width="'. $size[0] .'" height="'. $size[1] .'"';
				}
			}

			// assemble the <image> tag
			$image = '';
			if ( $link ) {
				// link
				$image .= '<a href="'. $link .'" target="'. $target .'" style="display: block;">';
			}
			$image .= '<img src="'. $mosConfig_live_site .'/images/stories/'. $attrib[0] .'" '. $size;
			// no aligment variable - if caption detected
			if ( !$attrib[4] ) {
				$image .= $attrib[1] ? ' align="'. $attrib[1] .'"' : '';
			}
			$image .=' hspace="6" alt="'. $attrib[2] .'" title="'. $attrib[2] .'" border="'. $border .'" />';
			if ( $link ) {
				// link
				$image .= '</a>';
			}

			// assemble caption - if caption detected
			if ( $attrib[4] ) {
				$caption = '<div class="mosimage_caption" style="width: '. $width .'; text-align: '. $attrib[6] .';" align="'. $attrib[6] .'">';
				if ( $link ) {
					// link
					$caption .= '<a href="'. $link .'" target="'. $target .'" style="display: block;">';
				}
				$caption .= $attrib[4];
				if ( $link ) {
					// link
					$caption .= '</a>';
				}
				$caption .='</div>';
			}

			// final output
			$img = '';
			if ( $attrib[4] ) {
				// surrounding div
				$img .= '<div class="mosimage" style="border-width: '. $attrib[3] .'px; float: '. $attrib[1] .'; margin: '. $mparams->def( 'margin' ) .'px; padding: '. $mparams->def( 'padding' ) .'px;'. $width .'" align="center">';

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
