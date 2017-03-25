<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_contenthistory
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JSession::checkToken('get') or die(JText::_('JINVALID_TOKEN'));

$version2 = $this->items[0];
$version1 = $this->items[1];
$object1  = $version1->data;
$object2  = $version2->data;

JHtml::_('script', 'vendor/diff/diff.min.js', array('version' => 'auto', 'relative' => true));
JHtml::_('script', 'com_contenthistory/admin-compare-compare.min.js', array('version' => 'auto', 'relative' => true));
?>
<fieldset>

	<h2 class="mb-3"><?php echo JText::sprintf('COM_CONTENTHISTORY_COMPARE_TITLE'); ?></h2>

	<table id="diff" class="table table-striped table-sm">
		<thead>
			<tr>
				<th width="25%"><?php echo JText::_('COM_CONTENTHISTORY_PREVIEW_FIELD'); ?></th>
				<th><?php echo JText::_('COM_CONTENTHISTORY_COMPARE_OLD'); ?></th>
				<th><?php echo JText::_('COM_CONTENTHISTORY_COMPARE_NEW'); ?></th>
				<th><?php echo JText::_('COM_CONTENTHISTORY_COMPARE_DIFF'); ?></th>
			</tr>
		</thead>
		<tbody>
		<?php foreach ($object1 as $name => $value) : ?>
			<?php if ($value->value != $object2->$name->value) : ?>
			<tr>
				<?php if (is_object($value->value)) : ?>
					<td>
						<b><?php echo $value->label; ?></b>
					</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<?php foreach ($value->value as $subName => $subValue) : ?>
						<?php $newSubValue = isset($object2->$name->value->$subName->value) ? $object2->$name->value->$subName->value : ''; ?>
						<?php if ($subValue->value || $newSubValue) : ?>
							<?php if ($subValue->value != $newSubValue) : ?>
								<tr>
									<td><em>&nbsp;&nbsp;<?php echo $subValue->label; ?></em></td>
									<td class="original"><?php echo htmlspecialchars($subValue->value, ENT_COMPAT, 'UTF-8'); ?></td>
									<td class="changed" ><?php echo htmlspecialchars($newSubValue, ENT_COMPAT, 'UTF-8'); ?></td>
									<td class="diff">&nbsp;</td>
								</tr>
							<?php endif; ?>
						<?php endif; ?>
					<?php endforeach; ?>
				<?php else : ?>
					<td>
						<b><?php echo $value->label; ?></b>
					</td>
					<td class="original"><?php echo htmlspecialchars($value->value); ?></td>
					<?php $object2->$name->value = is_object($object2->$name->value) ? json_encode($object2->$name->value) : $object2->$name->value; ?>
					<td class="changed"><?php echo htmlspecialchars($object2->$name->value, ENT_COMPAT, 'UTF-8'); ?></td>
					<td class="diff">&nbsp;</td>
				<?php endif; ?>
			</tr>
		<?php endif; ?>
		<?php endforeach; ?>
		</tbody>
	</table>

</fieldset>
