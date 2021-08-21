<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_latestactions
 *
 * @copyright   (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

?>
<table class="table" id="<?php echo str_replace(' ', '', $module->title) . $module->id; ?>">
	<caption class="visually-hidden"><?php echo $module->title; ?></caption>
	<thead>
		<tr>
			<th scope="col" class="w-70"><?php echo Text::_('MOD_LATESTACTIONS_ACTION'); ?></th>
			<th scope="col" class="w-30"><?php echo Text::_('JDATE'); ?></th>
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
				<?php echo HTMLHelper::_('date.relative', $item->log_date); ?>
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
