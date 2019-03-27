<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_privacy_dashboard
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::_('bootstrap.tooltip');

$totalRequest  = 0;
$manageRequest = 0;

?>
<div class="row-striped">
<?php if (count($list)) : ?>
	<?php foreach ($list as $item) : ?>		
		<div class="row-fluid">
			<div class="span4">
			<span class="badge badge-info"><?php echo $item->count; ?></span>
			</div>
			<div class="span4">
				<?php echo JHtml::_('PrivacyHtml.helper.statusLabel', $item->status); ?>
			</div>			
			<div class="span4">
				<a class="hasTooltip" href="<?php echo JRoute::_('index.php?option=com_privacy&view=requests&filter[request_type]=' . $item->request_type . '&filter[status]=' . $item->status); ?>" title="" data-original-title="<?php echo JText::_('MOD_PRIVACY_DASHBOARD_VIEW_REQUESTS'); ?>">
					<strong><?php echo $item->request_type; ?></strong>
				</a>
			</div>
		</div>
		<?php if (($item->status === "0") || ($item->status === "1")) : ?> 
		 	<?php $manageRequest += $item->count; ?>
		<?php endif; ?>
		<?php $totalRequest += $item->count; ?>
	<?php endforeach; ?>
	<div class="row-fluid">
		<div class="span6">
			<span class="btn btn-info"><?php echo $totalRequest; ?></span>&nbsp;<?php echo JText::_('MOD_PRIVACY_DASHBOARD_TOTAL_REQUESTS'); ?>
		</div>
		<div class="span6">
			<span class="btn btn-warning"><?php echo $manageRequest; ?></span>&nbsp;<?php echo JText::_('MOD_PRIVACY_DASHBOARD_MANAGE_REQUESTS'); ?>
		</div>
	</div>
<?php else : ?>
	<div class="row-fluid">
		<div class="span12">
			<div class="alert"><?php echo JText::_('MOD_PRIVACY_DASHBOARD_NO_MATCHING_RESULTS'); ?></div>
		</div>
	</div>
<?php endif; ?>
</div>
