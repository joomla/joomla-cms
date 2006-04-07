<?php
/**
* @version $Id$
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

$type 			= $params->get( 'type', 'jpg' );
$folder 		= $params->get( 'folder' );
$link 			= $params->get( 'link' );
$width 			= $params->get( 'width' );
$height 		= $params->get( 'height' );
$LiveSite 		= $mainframe->getCfg('live_site');
$the_array 		= array();
$the_image 		= array();

// if folder includes livesite info, remove
if ( JString::strpos($folder, $LiveSite) === 0 ) {
	$folder = JString::str_replace( $LiveSite, '', $folder );
}
// if folder includes absolute path, remove
if ( JString::strpos($folder, JPATH_SITE) === 0 ) {
	$folder= JString::str_replace( JPATH_SITE, '', $folder );
}
$folder = str_replace('\\',DS,$folder);
$folder = str_replace('/',DS,$folder);
// if folder doesnt contain slash to start, add
if ( strpos($folder, DS) !== 0 ) {	
	$folder_path = DS . $folder;
} else {
	$folder_path = $folder;
}
// construct absolute path to directory
$abspath_folder = JPATH_SITE . $folder_path;

// check if directory exists
if (is_dir($abspath_folder)) {
	if ($handle = opendir($abspath_folder)) {
		while (false !== ($file = readdir($handle))) {
			if ($file != '.' && $file != '..' && $file != 'CVS' && $file != 'index.html' ) {
				$the_array[] = $file;
			}
		}
	}
	closedir($handle);

	foreach ($the_array as $img) {
		if (!is_dir($abspath_folder .'/'. $img)) {
			if (eregi($type, $img)) {
				$the_image[] = $img;
			}
		}
	}

	if (!$the_image) {
		echo JText::_( 'No images ');
	} else {
		$i 				= count($the_image);
		$random 		= mt_rand(0, $i - 1);
		$image_name 	= $the_image[$random];
		$abspath_image	= $abspath_folder . '/'. $image_name;
		$size 			= getimagesize ($abspath_image);
		
		if ($width == '') {
			($size[0] > 100 ? $width = 100 : $width = $size[0]);
		}
		if ($height == '') {
			$coeff 	= $size[0]/$size[1];
			$height = (int) ($width/$coeff);
		}
		
		$folder = str_replace( '\\', '/', $folder );
	  	$image 	= $LiveSite . $folder .'/'. $image_name;	
	  	?>
	 	<div align="center">
		 	<?php
		  	if ($link) {
		  		?>
		  		<a href="<?php echo $link; ?>" target="_self">
		  		<?php
		  	}
		  	?>
		 	<img src="<?php echo $image; ?>" border="0" width="<?php echo $width; ?>" height="<?php echo $height; ?>" alt="<?php echo $image_name; ?>" /><br />
		 	<?php
		  	if ($link) {
		  		?>
		  		</a>
		  		<?php
		  	}
		  	?>
	 	</div>
  	<?php
	}
}
?>
