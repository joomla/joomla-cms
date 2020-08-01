<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_contenthistory
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;

Session::checkToken('get') or die(Text::_('JINVALID_TOKEN'));

?>
<div role="main">
	<h1>
		<?php echo Text::sprintf('COM_CONTENTHISTORY_PREVIEW_SUBTITLE_DATE', $this->item->save_date); ?>
	</h1>
	<?php if ($this->item->version_note) : ?>
		<h2>
			<?php echo Text::sprintf('COM_CONTENTHISTORY_PREVIEW_SUBTITLE', $this->item->version_note); ?>
		</h2>
	<?php endif; ?>

	<table class="table">
		<caption id="captionTable" class="sr-only">
			<?php echo Text::_('COM_CONTENTHISTORY_PREVIEW_CAPTION'); ?>
		</caption>
		<thead>
			<tr>
				<th class="w-25"><?php echo Text::_('COM_CONTENTHISTORY_PREVIEW_FIELD'); ?></th>
				<th><?php echo Text::_('COM_CONTENTHISTORY_PREVIEW_VALUE'); ?></th>
			</tr>
		</thead>
		<tbody>
		<?php foreach ($this->item->data as $name => $value) : ?>
			<?php if (is_object($value->value)) : ?>
				<tr>
					<td colspan="2">
						<?php echo $value->label; ?>
					</td>
				</tr>
				<?php foreach ($value->value as $subName => $subValue) : ?>
					<?php if ($subValue) : ?>
						<tr>
							<th scope="row"><em>&nbsp;&nbsp;<?php echo $subValue->label; ?></em></th>
							<td><?php echo $subValue->value; ?></td>
						</tr>
					<?php endif; ?>
				<?php endforeach; ?>
			<?php else : ?>
				<tr>
					<th scope="row"><?php echo $value->label; ?></th>
					<td><?php echo $value->value; ?></td>
				</tr>
			<?php endif; ?>
		<?php endforeach; ?>
		</tbody>
	</table>
</div>
