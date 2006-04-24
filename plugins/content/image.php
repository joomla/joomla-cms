<?php
/**
* @version $Id: mosimage.php 2412 2006-02-16 17:24:10Z stingrey $
* @package Joomla
* @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

$mainframe->registerEvent( 'onPrepareContent', 'pluginImage' );

/**
*/
function pluginImage( &$row, &$params, $page=0 )
{
	global $database;

 	// simple performance check to determine whether bot should process further
	if ( JString::strpos( $row->text, '{image' ) === false ) {
		return true;
	}

	// expression to search for
	$regex = '/{image\s*.*?}/i';

	$plugin =& JPluginHelper::getPlugin('content', 'image');

	// check whether images have been disabled for page
	// check whether plugin has been unpublished
	if (!$plugin->published || !$params->get( 'image' )) {
		$row->text = preg_replace( $regex, '', $row->text );
		return true;
	}

	//count how many {jcp:image} are in introtext if it is set to hidden.
	$introCount=0;
	if ( ! $params->get( 'introtext' ) & ! $params->get( 'intro_only') )
	{
		preg_match_all( $regex, $row->introtext, $matches );
		$introCount = count ( $matches[0] );
	}

	// find all instances of plugin and put in $matches
	preg_match_all( $regex, $row->text, $matches );

 	// Number of plugins
	$count = count( $matches[0] );

 	// plugin only processes if there are any instances of the plugin in the text
 	if ( $count ) {
		// load plugin params info
	 	$pluginParams = new JParameter( $plugin->params );

	 	$pluginParams->def( 'padding' );
	 	$pluginParams->def( 'margin' );
	 	$pluginParams->def( 'link', 0 );

		$images 	= processImages( $row, $pluginParams, $introCount );

		// store some vars in globals to access from the replacer
		$GLOBALS['botMosImageCount'] 	= 0;
		$GLOBALS['botMosImageParams'] 	=& $pluginParams;
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

function processImages ( &$row, &$params, &$introCount )
{
	global $mainframe;

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
				$attrib[3] = 0;
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
			$image = '<img src="images/stories/'. $attrib[0] .'"'. $size;
			// no aligment variable - if caption detected
			if ( !$attrib[4] ) {
				$image .= $attrib[1] ? ' align="'. $attrib[1] .'"' : '';
			}
			$image .=' alt="'. $attrib[2] .'" title="'. $attrib[2] .'" border="'. $border .'" />';

			// assemble caption - if caption detected
			$caption = '';
			if ( $attrib[4] ) {
				$caption = '<div class="mosimage_caption"';
				if ( $attrib[6] ) {
					$caption .= ' style="text-align: '. $attrib[6] .';"';
					$caption .= ' align="'. $attrib[6] .'"';
				}
				$caption .= '>';
				$caption .= $attrib[4];
				$caption .= '</div>';
			}

			// final output
			if ( $attrib[4] ) {
				// initialize variables
				$margin  	= '';
				$padding 	= '';
				$float		= '';
				$style		= '';
				if ( $params->def( 'margin' ) ) {
					$margin 		= ' margin: '. $params->def( 'margin' ).'px;';
				}
				if ( $params->def( 'padding' ) ) {
					$padding 		= ' padding: '. $params->def( 'padding' ).'px;';
				}
				if ( $attrib[1] ) {
					$float 			= ' float: '. $attrib[1] .';';
				}
				if ( $attrib[3] ) {
					$border_width	= ' border-width: '. $attrib[3] .'px;';
				}

				if ( $params->def( 'margin' ) || $params->def( 'padding' ) || $attrib[1] || $attrib[3] ) {
					$style = ' style="'. $border_width . $float . $margin . $padding . $width .'"';
				}

				$img = '<div class="mosimage" '. $style .' align="center">';

				// display caption in top position
				if ( $attrib[5] == 'top' && $caption ) {
					$img .= $caption;
				}

				$img .= $image;

				// display caption in bottom position
				if ( $attrib[5] == 'bottom' && $caption ) {
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
function botMosImage_replacer( &$matches )
{
	$i = $GLOBALS['botMosImageCount']++;

	return @$GLOBALS['botMosImageArray'][$i];
}
?>