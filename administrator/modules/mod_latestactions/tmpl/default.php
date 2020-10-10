<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_latestactions
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

HTMLHelper::_('bootstrap.framework');

?>
<table class="table" id="<?php echo str_replace(' ', '', $module->title) . $module->id; ?>">
	<caption class="sr-only"><?php echo $module->title; ?></caption>
	<thead>
		<tr>
			<th scope="col" class="w-80"><?php echo Text::_('MOD_LATESTACTIONS_ACTION'); ?></th>
			<th scope="col" class="w-20"><?php echo Text::_('JDATE'); ?></th>
		</tr>
	</thead>
	<tbody>
		<?php if (count($list)) : ?>
		<?php foreach ($list as $i => $item) : ?>
		<tr>
			<td>
				<?php echo $item->message; ?>
			</td>
			<td>
				<?php echo HTMLHelper::_('date', $item->log_date, Text::_('DATE_FORMAT_LC5')); ?>
			</td>
		</tr>
		<?php endforeach; ?>
		<?php else : ?>
		<tr>
			<td colspan="2">
				<?php echo Text::_('MOD_LATESTACTIONS_NO_MATCHING_RESULTS'); ?>
			</td>
		</tr>
		<?php endif; ?>
	</tbody>
</table>
