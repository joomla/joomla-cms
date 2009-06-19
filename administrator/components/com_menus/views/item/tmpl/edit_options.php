<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	com_menus
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
?>
<table>
<?php foreach($this->form->getFields('params') as $field): ?>
	<?php if ($field->hidden): ?>
		<?php echo $field->input; ?>
	<?php else: ?>
		<tr>
			<td class="paramlist_key" width="40%">
				<?php echo $field->label; ?>
			</td>
			<td class="paramlist_value">
				<?php echo $field->input; ?>
			</td>
		</tr>
	<?php endif; ?>
<?php endforeach; ?>
</table>
