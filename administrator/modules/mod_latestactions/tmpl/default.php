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

HTMLHelper::_('bootstrap.tooltip');
?>
<div class="row-striped">
	<?php if (count($list)) : ?>
		<?php foreach ($list as $i => $item) : ?>
			<div class="row-fluid">
				<div class="span8 truncate">
					<?php echo $item->message; ?>
				</div>
				<div class="span4">
					<div class="small pull-right hasTooltip" title="<?php echo HTMLHelper::_('tooltipText', 'JGLOBAL_FIELD_CREATED_LABEL'); ?>">
						<span class="icon-calendar" aria-hidden="true"></span> <?php echo HTMLHelper::_('date', $item->log_date, JText::_('DATE_FORMAT_LC5')); ?>
					</div>
				</div>
			</div>
		<?php endforeach; ?>
	<?php else : ?>
		<div class="row-fluid">
			<div class="span12">
				<div class="alert"><?php echo Text::_('MOD_LATEST_ACTIONS_NO_MATCHING_RESULTS'); ?></div>
			</div>
		</div>
	<?php endif; ?>
</div>
