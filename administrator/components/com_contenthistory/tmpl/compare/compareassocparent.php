<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_contenthistory
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;

Session::checkToken('get') or die(Text::_('JINVALID_TOKEN'));

$version2 = $this->items[0];
$version1 = $this->items[1];
$object1  = $version1->data;
$object2  = $version2->data;

$objLabel = '';
if (array_key_exists('title', $object2))
{
	$objLabel = $object2->title;
}
elseif (array_key_exists('name', $object2))
{
	$objLabel = $object2->name;
}

HTMLHelper::_('script', 'vendor/diff/diff.min.js', array('version' => 'auto', 'relative' => true));
HTMLHelper::_('script', 'com_associations/admin-compare-assoc-parent.min.js', array('version' => 'auto', 'relative' => true));
HTMLHelper::_('stylesheet', 'com_associations/sidebyside.css', ['version' => 'auto', 'relative' => true]);
?>

<div class="m-t-2 m-b-3">
	<div class="control-group">
		<div class="control-label">
			<?php echo $objLabel->label ?>
		</div>
		<div class="controls">
			<input type="text" value="<?php echo $objLabel->value ?>" class="form-control" disabled>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo $version2->data->alias->label ?>
		</div>
		<div class="controls">
			<input type="text" value="<?php echo $version2->data->alias->value ?>" class="form-control" disabled>
		</div>
	</div>
</div>
<table id="diff" class="table table-sm">
	<thead>
	<tr>
		<th style="width:25%"><?php echo Text::_('COM_CONTENTHISTORY_PREVIEW_FIELD'); ?></th>
		<th><?php echo Text::_('COM_CONTENTHISTORY_COMPARE_DIFF'); ?></th>
	</tr>
	</thead>
	<tbody>
	<?php foreach ($object1 as $name => $value) : ?>
		<?php if ($value->value != $object2->$name->value) : ?>
			<tr>
			<?php if (is_object($value->value)) : ?>
				<td>
					<strong><?php echo $value->label; ?></strong>
				</td>
				<?php foreach ($value->value as $subName => $subValue) : ?>
					<?php $newSubValue = $object2->$name->value->$subName->value ?? ''; ?>
					<?php if ($subValue->value || $newSubValue) : ?>
						<?php if ($subValue->value != $newSubValue) : ?>
							<tr>
								<td><em><?php echo $subValue->label; ?></em></td>
								<td class="original hidden"><?php echo htmlspecialchars($subValue->value, ENT_COMPAT, 'UTF-8'); ?></td>
								<td class="changed hidden"><?php echo htmlspecialchars($newSubValue, ENT_COMPAT, 'UTF-8'); ?></td>
								<td class="diff"></td>
							</tr>
						<?php endif; ?>
					<?php endif; ?>
				<?php endforeach; ?>
			<?php else : ?>
				<td>
					<strong><?php echo $value->label; ?></strong>
				</td>
				<td class="original hidden"><?php echo htmlspecialchars($value->value); ?></td>
				<?php $object2->$name->value = is_object($object2->$name->value) ? json_encode($object2->$name->value) : $object2->$name->value; ?>
				<td class="changed hidden"><?php echo htmlspecialchars($object2->$name->value, ENT_COMPAT, 'UTF-8'); ?></td>
				<td class="diff"></td>
			<?php endif; ?>
			</tr>
		<?php endif; ?>
	<?php endforeach; ?>
	</tbody>
</table>
