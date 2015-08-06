<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_media
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$user = JFactory::getUser();

$item = $displayData['item'];

$link = 'index.php?option=com_media&amp;tmpl=index&amp;';
$link .= '&amp;task=' . $displayData['task'];
$link .= '&amp;folder=' . $displayData['folder'];
$link .= '&amp;' . JSession::getFormToken() . '=1';
$link .= '&amp;rm[]='. $item->name;
?>
<div class="small height-20">
	<input class="pull-left" type="checkbox" name="rm[]" id="<?php echo $item->title; ?>" value="<?php echo $item->name; ?>" />
	<?php if ($user->authorise('core.delete', 'com_media')): ?>
		<a class="pull-right close delete-item" target="_top" href="<?php echo $link; ?>" rel="<?php echo $item->name; ?>" title="<?php echo JText::_('JACTION_DELETE'); ?>">
			<span class="icon-delete" style="font-size: x-small; color: #CB0B0B;"></span>
		</a>
	<?php endif; ?>
</div>

