<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_media
 *
 * @copyright   (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
$app   = JFactory::getApplication();
$style = $app->getUserStateFromRequest('media.list.layout', 'layout', 'thumbs', 'word');
?>

<div class="media btn-group ventral-space">
	<a href="#" id="thumbs" onclick="MediaManager.setViewType('thumbs')" class="btn <?php echo ($style == 'thumbs') ? 'active' : ''; ?>">
	<span class="icon-grid-view-2"></span> <?php echo JText::_('COM_MEDIA_THUMBNAIL_VIEW'); ?></a>
	<a href="#" id="details" onclick="MediaManager.setViewType('details')" class="btn <?php echo ($style == 'details') ? 'active' : ''; ?>">
	<span class="icon-list-view"></span> <?php echo JText::_('COM_MEDIA_DETAIL_VIEW'); ?></a>
</div>
