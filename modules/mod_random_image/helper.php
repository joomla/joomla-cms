<?php
/**
* @version $Id: mod_random_image.php 3996 2006-06-12 03:44:31Z spacemonkey $
* @package Joomla
* @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
* @license GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );


class modRandomImage
{
	function display(&$params)
	{	
		$link 	 = $params->get( 'link' );
		
		$folder  = modRandomImage::getFolder($params);	
		$images  = modRandomImage::getImages($params, $folder);
		
		if (!count($images)) {
			echo JText::_( 'No images ');
			return;
		} 
		
		$image = modRandomImage::getRandomImage($params, $images);
			
		?>
		<div align="center">
		<?php
		if ($link) : ?>
			<a href="<?php echo $link; ?>" target="_self">
		<?php endif; ?>
			<img src="<?php echo $image->folder.'/'.$image->name; ?>" border="0" width="<?php echo $image->width; ?>" height="<?php echo $image->height; ?>" alt="<?php echo $image->name; ?>" /><br />
		<?php
		if ($link) : ?>
			</a>
		<?php endif; ?>
		</div>
		<?php
	}
	
	function getRandomImage(&$params, $images)
	{
		$width 		= $params->get( 'width' );
		$height 	= $params->get( 'height' );
		
		$i 				= count($images);
		$random 		= mt_rand(0, $i - 1);
		$image 			= $images[$random];
		$size 			= getimagesize (JPATH_BASE.DS.$image->folder .DS. $image->name);
		

		if ($width == '') {
			($size[0] > 100 ? $width = 100 : $width = $size[0]);
		}
		if ($height == '') {
			$coeff 	= $size[0]/$size[1];
			$height = (int) ($width/$coeff);
		}
		
		$image->width 	= $width;
		$image->height  = $height;
		
		return $image;
	}
	
	function getImages(&$params, $folder)
	{
		$type 		= $params->get( 'type', 'jpg' );
		
		$files	= array();
		$imags 	= array();
		
		$dir = JPATH_BASE.DS.$folder;
		
		// check if directory exists
		if (is_dir($dir)) 
		{
			if ($handle = opendir($dir)) {
				while (false !== ($file = readdir($handle))) {
					if ($file != '.' && $file != '..' && $file != 'CVS' && $file != 'index.html' ) {
						$files[] = $file;
					}
				}
			}
			closedir($handle);
		
			$i = 0;
			foreach ($files as $img)
			{
				if (!is_dir($dir .DS. $img)) 
				{
					if (eregi($type, $img)) {
						$images[$i]->name 	= $img;
						$images[$i]->folder	= $folder;
						++$i;
					}
				}
			}
		}
		
		return $images;
	}
	
	function getFolder(&$params)
	{
		global $mainframe;
		
		$folder 	= $params->get( 'folder' );
		
		$LiveSite 	= $mainframe->getCfg('live_site');
		
		// if folder includes livesite info, remove
		if ( JString::strpos($folder, $LiveSite) === 0 ) {
			$folder = JString::str_replace( $LiveSite, '', $folder );
		}
		// if folder includes absolute path, remove
		if ( JString::strpos($folder, JPATH_SITE) === 0 ) {
			$folder= JString::str_replace( JPATH_BASE, '', $folder );
		}
		$folder = str_replace('\\',DS,$folder);
		$folder = str_replace('/',DS,$folder);
		
		return $folder;
	}
}

