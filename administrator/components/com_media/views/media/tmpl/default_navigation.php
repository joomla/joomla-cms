<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	com_media
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;
?>
<div id="submenu-box">
	<div class="t">
		<div class="t">
			<div class="t"></div>
 		</div>
	</div>
	<div class="m">
		<div class="submenu-box">
			<div class="submenu-pad">
				<ul id="submenu" class="media">
					<li><a id="thumbs" onclick="MediaManager.setViewType('thumbs')"><?php echo JText::_('THUMBNAIL_VIEW'); ?></a></li>
					<li><a id="details" onclick="MediaManager.setViewType('details')"><?php echo JText::_('DETAIL_VIEW'); ?></a></li>
				</ul>
				<div class="clr"></div>
			</div>
		</div>
		<div class="clr"></div>
	</div>
	<div class="b">
		<div class="b">
 			<div class="b"></div>
		</div>
	</div>
</div>