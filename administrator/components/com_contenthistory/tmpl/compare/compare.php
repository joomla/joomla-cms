<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_contenthistory
 *
 * @copyright   (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;

Session::checkToken('get') or die(Text::_('JINVALID_TOKEN'));

$version2 = $this->items[0];
$version1 = $this->items[1];
$object1  = $version1->data;
$object2  = $version2->data;

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->document->getWebAssetManager();
$wa->useScript('com_contenthistory.admin-compare-compare');

?>
<div role="main">
	<h1 class="mb-3"><?php echo Text::_('COM_CONTENTHISTORY_COMPARE_TITLE'); ?></h1>

	<table id="diff" class="table">
		<caption class="visually-hidden">
			<?php echo Text::_('COM_CONTENTHISTORY_COMPARE_CAPTION'); ?>
		</caption>
		<thead>
			<tr>
				<th scope="col" class="w-25"><?php echo Text::_('COM_CONTENTHISTORY_PREVIEW_FIELD'); ?></th>
				<th scope="col"><?php echo Text::_('COM_CONTENTHISTORY_COMPARE_OLD'); ?></th>
				<th scope="col"><?php echo Text::_('COM_CONTENTHISTORY_COMPARE_NEW'); ?></th>
				<th scope="col"><?php echo Text::_('COM_CONTENTHISTORY_COMPARE_DIFF'); ?></th>
			</tr>
		</thead>
		<tbody>
		<?php foreach ($object1 as $name => $value) : ?>
			<?php if (isset($value->value) && isset($object2->$name->value) && $value->value != $object2->$name->value) : ?>
				<?php if (is_object($value->value)) : ?>
					<tr>
						<td colspan="4">
							<strong><?php echo $value->label; ?></strong>
						</td>
					</tr>
					<?php foreach ($value->value as $subName => $subValue) : ?>
						<?php $newSubValue = $object2->$name->value->$subName->value ?? ''; ?>
						<?php if ($subValue->value || $newSubValue) : ?>
							<?php if ($subValue->value != $newSubValue) : ?>
								<tr>
									<th scope="row"><em>&nbsp;&nbsp;<?php echo $subValue->label; ?></em></th>
									<td class="original"><?php echo htmlspecialchars($subValue->value, ENT_COMPAT, 'UTF-8'); ?></td>
									<td class="changed" ><?php echo htmlspecialchars($newSubValue, ENT_COMPAT, 'UTF-8'); ?></td>
									<td class="diff">&nbsp;</td>
								</tr>
							<?php endif; ?>
						<?php endif; ?>
					<?php endforeach; ?>
				<?php else : ?>
					<tr>
						<th scope="row">
							<?php echo $value->label; ?>
						</th>
						<td class="original"><?php echo htmlspecialchars($value->value); ?></td>
						<?php $object2->$name->value = is_object($object2->$name->value) ? json_encode($object2->$name->value) : $object2->$name->value; ?>
						<td class="changed"><?php echo htmlspecialchars($object2->$name->value, ENT_COMPAT, 'UTF-8'); ?></td>
						<td class="diff">&nbsp;</td>
					</tr>
				<?php endif; ?>
		<?php endif; ?>
		<?php endforeach; ?>
		</tbody>
	</table>
</div>
