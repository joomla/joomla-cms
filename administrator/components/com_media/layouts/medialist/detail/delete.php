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
<td>
	<input type="checkbox" id="<?php echo $id; ?>" name="rm[]" value="<?php echo $item->name; ?>" onclick="Joomla.isChecked(this.checked);" />
</td>
<?php if ($user->authorise('core.delete', 'com_media')):?>
	<td>
		<div class="btn-group">
			<button data-toggle="dropdown" class="dropdown-toggle btn btn-micro">
				<span class="caret"></span>
			</button>
			<ul class="dropdown-menu">
				<li>
					<a href = "javascript://" onclick="listItemTask('<?php echo $id; ?>', 'folder.delete')">
						<span class="icon-remove"></span> <?php echo JText::_('JTOOLBAR_DELETE');?>
					</a>
				</li>
			</ul>
		</div>
	</td>
<?php endif;?>
