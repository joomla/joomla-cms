<?php
/**
 * @package     corejoomla.administrator
 * @subpackage  com_cjforum
 *
 * @copyright   Copyright (C) 2009 - 2014 corejoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die();

$rowClass = $this->params->get('layout', 'default') != 'bs3' ? 'row-fluid' : 'row';
?>
<div class="<?php echo $rowClass;?>">
	<div class="<?php echo $this->params->get('layout', 'default') != 'bs3' ? 'span6' : 'col-lg-6 col-md-6 col-sm-6';?>">
		<h3 class="cjheader"><?php echo JText::_('COM_CJFORUM_TOPICS');?></h3>
		<?php if(!empty($this->summary->topics)):?>
		<ul class="list-summary">
			<?php foreach ($this->summary->topics as $item):?>
			<li><?php echo JHtml::link(CjForumHelperRoute::getTopicRoute($item->slug, $item->catid, $item->language), $this->escape($item->title), array('title'=>$this->escape($item->title)));?></li>
			<?php endforeach;?>
		</ul>
		<?php else:?>
		<p><?php echo JText::_('COM_CJFORUM_NO_RESULTS_FOUND');?></p>
		<?php endif;?>
	</div>
	<div class="<?php echo $this->params->get('layout', 'default') != 'bs3' ? 'span6' : 'col-lg-6 col-md-6 col-sm-6';?>">
		<h3 class="cjheader"><?php echo JText::_('COM_CJFORUM_DISCUSSIONS');?></h3>
		<?php if(!empty($this->summary->replies)):?>
		<ul class="list-summary">
			<?php foreach ($this->summary->replies as $item):?>
			<li>
				<?php echo JHtml::link(CjForumHelperRoute::getTopicRoute($item->slug, $item->catid, $item->language).'#p'.$item->id, $this->escape($item->title), array('title'=>$this->escape($item->title)));?>
			</li>
			<?php endforeach;?>
		</ul>
		<?php else:?>
		<p><?php echo JText::_('COM_CJFORUM_NO_RESULTS_FOUND');?></p>
		<?php endif;?>
	</div>
</div>
<div class="<?php echo $rowClass;?>">
	<div class="<?php echo $this->params->get('layout', 'default') != 'bs3' ? 'span6' : 'col-lg-6 col-md-6 col-sm-6';?> ">
		<h3 class="cjheader"><?php echo JText::_('COM_CJFORUM_REPUTATION');?></h3>
		<?php if(!empty($this->summary->reputation)):?>
		<ul class="list-summary">
			<?php foreach ($this->summary->reputation as $item):?>
			<li title="<?php echo strip_tags($item->title);?>">
				<span class="label label-<?php echo $item->points > 0 ? 'success' : 'danger';?>"><?php echo $item->points;?></span> <?php echo $item->title;?>
			</li>
			<?php endforeach;?>
		</ul>
		<?php else :?>
		<p><?php echo JText::_('COM_CJFORUM_NO_RESULTS_FOUND');?></p>
		<?php endif;?>
	</div>
	<div class="<?php echo $this->params->get('layout', 'default') != 'bs3' ? 'span6' : 'col-lg-6 col-md-6 col-sm-6';?>">
		<h3 class="cjheader"><?php echo JText::_('COM_CJFORUM_FAVORITES');?></h3>
		<?php if(!empty($this->summary->favorites)):?>
		<ul class="list-summary">
			<?php foreach ($this->summary->favorites as $item):?>
			<li><?php echo JHtml::link(CjForumHelperRoute::getTopicRoute($item->slug, $item->catid, $item->language), $this->escape($item->title), array('title'=>$this->escape($item->title)));?></li>
			<?php endforeach;?>
		</ul>
		<?php else :?>
		<p><?php echo JText::_('COM_CJFORUM_NO_RESULTS_FOUND');?></p>
		<?php endif;?>
	</div>
</div>
<div class="<?php echo $rowClass;?>">
	<div class="<?php echo $this->params->get('layout', 'default') != 'bs3' ? 'span12' : 'col-lg-12 col-md-12 col-sm-12';?>">
		<h3 class="cjheader"><?php echo JText::_('COM_CJFORUM_ACTIVITY');?></h3>
		<?php if(!empty($this->summary->activities)):?>
		<table class="table table-hover table-striped">
			<?php foreach ($this->summary->activities as $item):?>
			<tr>
				<th>
					<div title="<?php echo JHtml::_('date', $item->created, JText::_('DATE_FORMAT_LC2'));?>" data-toggle="tooltip">
						<?php echo CjLibDateUtils::getShortDate($item->created);?>
					</div>
				</th>
				<td><?php echo $item->title;?></td>
			</tr>
			<?php endforeach;?>
		</table>
		<?php else :?>
		<p><?php echo JText::_('COM_CJFORUM_NO_RESULTS_FOUND');?></p>
		<?php endif;?>
	</div>
</div>