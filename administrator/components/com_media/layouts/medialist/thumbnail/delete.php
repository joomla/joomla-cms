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
$id = md5(var_export($item, true));
?>
<div class="small height-20">
	<input class="pull-left" type="checkbox" id="<?php echo $id; ?>" name="rm[]" value="<?php echo $item->name; ?>" onclick="Joomla.isChecked(this.checked);" />

	<?php if ($user->authorise('core.delete', 'com_media')): ?>
		<a class="pull-right close delete-item" target="_top" href="javascript://" onclick="listItemTask('<?php echo $id; ?>', 'folder.delete');"  rel="<?php echo $item->name; ?>" title="<?php echo JText::_('JACTION_DELETE'); ?>">
			<span class="icon-delete" style="font-size: x-small; color: #CB0B0B;"></span>
		</a>
	<?php endif; ?>
</div>

