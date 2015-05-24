<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_media
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$title = JText::_('COM_MEDIA_MOVE_MEDIA');
?>
<button data-toggle="modal" data-target="#moveMediaModal" class="btn btn-small">
	<span class="icon-move" title="
	<?php echo $title; ?>"></span> 
	<?php echo $title; ?>
</button>
