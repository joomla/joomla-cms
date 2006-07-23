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

/** ensure this file is being included by a parent file */
defined('_JEXEC') or die('Direct Access to this location is not allowed.');

class modSyndicate
{
	function display($params)
	{
		// paramters
		$params->def('text', 'Feed Entries');
		$params->def('format', 'rss');
		
		$link = modSyndicate::getLink($params);
	
		if(is_null($link)) {
			return;
		}
		
		$img = mosAdminMenus::ImageCheck('livemarks.png', '/images/M_images/');
		?>
			<a href="<?php echo $link ?>">
				<?php echo $img ?> <span><?php echo $params->get('text') ?></span>
			</a>
		<?php
	}
	
	function getLink(&$params)
	{
		global $mainframe;
	
		$document =& $mainframe->getDocument();
	
		foreach($document->_links as $link)
		{
			if(strpos($link, 'application/'.$params->get('format').'+xml')) {
				preg_match("#href=\"(.*?)\"#s", $link, $matches);
				return $matches[1];
			}
		}
	
	}
}

?>