<?php
/**
 * @package     corejoomla.administrator
 * @subpackage  com_cjforum
 *
 * @copyright   Copyright (C) 2009 - 2014 corejoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die();

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers');
JHtml::_('behavior.caption');

$layout = $this->params->get('layout', 'default');
?>
<div id="cj-wrapper" class="profile-reputation<?php echo $this->pageclass_sfx;?>">
	<?php if(!empty($this->items)):?>
	<h3 class="cjheader"><?php echo JText::sprintf('COM_CJFORUM_REPUTATION_HEADING', CjLibUtils::formatNumber($this->item->points));?></h3>
	<table class="table table-hover table-striped">
		<?php foreach ($this->items as $item):?>
		<tr>
			<th>
				<div title="<?php echo JHtml::_('date', $item->created, JText::_('DATE_FORMAT_LC2'));?>" data-toggle="tooltip">
					<?php echo CjLibDateUtils::getShortDate($item->created);?>
				</div>
			</th>
			<td>
				<span class="label label-<?php echo $item->points > 0 ? 'success' : 'danger';?>"><?php echo $item->points;?></span>
			</td>
			<td>
				<?php echo $item->title;;?>
			</td>
		</tr>
		<?php endforeach;?>
	</table>
	
	<?php if (!empty($this->items)) : ?>
	<?php if (($this->params->def('show_pagination', 2) == 1  || ($this->params->get('show_pagination') == 2)) && ($this->pagination->pagesTotal > 1)) : ?>
		<form action="<?php echo htmlspecialchars(JUri::getInstance()->toString()); ?>" method="post" name="adminForm" id="adminForm">
			<div class="pagination">
				<?php if ($this->params->def('show_pagination_results', 1)) : ?>
					<p class="counter pull-right">
						<?php echo $this->pagination->getPagesCounter(); ?>
					</p>
				<?php endif; ?>
		
				<?php echo $this->pagination->getPagesLinks(); ?>
			</div>
		</form>
	<?php endif; ?>
	<?php  endif; ?>
	
	<?php else :?>
	<div class="alert alert-info"><i class="fa fa-info-circle"></i> <?php echo JText::_('COM_CJFORUM_NO_RESULTS_FOUND')?></div>
	<?php endif;?>
</div>