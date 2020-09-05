<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_admin
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

/** @var \Joomla\Component\Admin\Administrator\View\Sysinfo\HtmlView $this */
?>
<div class="sysinfo">
	<table class="table">
		<caption class="sr-only">
			<?php echo Text::_('COM_ADMIN_CONFIGURATION_FILE'); ?>
		</caption>
		<thead>
			<tr>
				<th scope="col" class="w-30">
					<?php echo Text::_('COM_ADMIN_SETTING'); ?>
				</th>
				<th scope="col">
					<?php echo Text::_('COM_ADMIN_VALUE'); ?>
				</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($this->config as $key => $value) : ?>
				<tr>
					<th scope="row">
						<?php echo $key; ?>
					</th>
					<td>
						<?php if (is_bool($value)) : ?>
							<?php $value = $value === true ? 'true' : 'false'; ?>
						<?php endif; ?>
						<?php echo htmlspecialchars($value, ENT_QUOTES); ?>
					</td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
</div>
