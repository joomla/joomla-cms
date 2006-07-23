<?php
/**
* @version $Id$
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

// Include the syndicate functions only once
require_once (dirname(__FILE__).DS.'helper.php');

$link 	 = $params->get( 'link' );
		
$folder  = modRandomImageHelper::getFolder($params);	
$images  = modRandomImageHelper::getImages($params, $folder);
		
if (!count($images)) {
	echo JText::_( 'No images ');
	return;
} 
		
$image = modRandomImageHelper::getRandomImage($params, $images);
			
?><div align="center"><?php

if ($link) : ?>
	<a href="<?php echo $link; ?>" target="_self">
<?php endif; ?>

<img src="<?php echo $image->folder.'/'.$image->name; ?>" border="0" width="<?php echo $image->width; ?>" height="<?php echo $image->height; ?>" alt="<?php echo $image->name; ?>" /><br />

<?php if ($link) : ?>
	</a>
<?php endif; ?>

</div>
