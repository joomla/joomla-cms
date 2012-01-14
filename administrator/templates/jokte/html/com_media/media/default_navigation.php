<?php
/**
 * @version		$Id: default_navigation.php 18340 2010-08-06 06:48:12Z infograf768 $
 * @package		Joomla.Administrator
 * @subpackage	com_media
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;
$app	= JFactory::getApplication();
$style = $app->getUserStateFromRequest('media.list.layout', 'layout', 'thumbs', 'word');
?>
<div id="submenu-box">		
		<div class="submenu-box">
			<div class="submenu-pad">
				<ul id="submenu" class="media">
					<li><a id="thumbs" onclick="MediaManager.setViewType('thumbs')" class="<?php echo ($style == "thumbs") ? 'active' : '';?>">
					<?php echo JText::_('COM_MEDIA_THUMBNAIL_VIEW'); ?></a></li>
					<li><a id="details" onclick="MediaManager.setViewType('details')" class="<?php echo ($style == "details") ? 'active' : '';?>">
					<?php echo JText::_('COM_MEDIA_DETAIL_VIEW'); ?></a></li>
				</ul>
				<div class="clr"></div>
			</div>
		</div>
		<div class="clr"></div>	
</div>