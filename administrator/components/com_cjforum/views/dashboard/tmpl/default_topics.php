<?php
/**
 * @package     corejoomla.administrator
 * @subpackage  com_cjforum
 *
 * @copyright   Copyright (C) 2009 - 2014 corejoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die();

$app		= JFactory::getApplication();
$user		= JFactory::getUser();
$userId		= $user->id;
?>
<div role="tabpanel">
	<ul class="nav nav-tabs" role="tablist">
		<li role="presentation" class="active">
			<a href="#trending" aria-controls="trending" role="tab" data-toggle="tab">
				<i class="fa fa-leaf"></i> <?php echo JText::_('COM_CJFORUM_TRENDING_TOPICS');?>
			</a>
		</li>
		<li role="presentation">
			<a href="#recent" aria-controls="recent" role="tab" data-toggle="tab">
				<i class="fa fa-refresh"></i> <?php echo JText::_('COM_CJFORUM_RECENT_TOPICS');?>
			</a>
		</li>
	</ul>
	<div class="tab-content">
		<div role="tabpanel" class="tab-pane active" id="trending">
			<div class="panel panel-default">
				<?php if(!$this->trending):?>
				<div class="panel-body">
					<?php echo JText::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
				</div>
				<?php else:?>
				<table class="table table-striped table-hover">
					<thead>
						<tr>
							<th><?php echo JText::_('JGLOBAL_TITLE');?></th>
							<th width="10%" class="nowrap hidden-phone"><?php echo JText::_('JAUTHOR');?></th>
							<th width="5%" class="nowrap hidden-phone"><?php echo JText::_('JGRID_HEADING_LANGUAGE');?></th>
							<th width="10%" class="nowrap hidden-phone"><?php echo JText::_('JDATE');?></th>
							<th width="10%"><?php echo JText::_('COM_CJFORUM_REPLIES_LABEL');?></th>
							<th width="1%" class="nowrap hidden-phone"><?php echo JText::_('JGRID_HEADING_ID');?></th>
						</tr>
					</thead>
					<tbody>
					<?php foreach ($this->trending as $i => $item) :
						$canEdit    = $user->authorise('core.edit',       'com_cjforum.topic.'.$item->id);
						$canCheckin = $user->authorise('core.manage',     'com_checkin') || $item->checked_out == $userId || $item->checked_out == 0;
						$canEditOwn = $user->authorise('core.edit.own',   'com_cjforum.topic.'.$item->id) && $item->created_by == $userId;
						$canChange  = $user->authorise('core.edit.state', 'com_cjforum.topic.'.$item->id) && $canCheckin;
						?>
						<tr>
							<td class="has-context">
								<div>
									<?php if ($item->checked_out) : ?>
										<?php echo JHtml::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, 'topics.', $canCheckin); ?>
									<?php endif; ?>
									<?php if ($item->language == '*'):?>
										<?php $language = JText::alt('JALL', 'language'); ?>
									<?php else:?>
										<?php $language = $item->language_title ? $this->escape($item->language_title) : JText::_('JUNDEFINED'); ?>
									<?php endif;?>
									<?php if ($canEdit || $canEditOwn) : ?>
										<a href="<?php echo JRoute::_('index.php?option=com_cjforum&task=topic.edit&id=' . $item->id); ?>" title="<?php echo JText::_('JACTION_EDIT'); ?>">
											<?php echo $this->escape($item->title); ?></a>
									<?php else : ?>
										<span title="<?php echo JText::sprintf('JFIELD_ALIAS_LABEL', $this->escape($item->alias)); ?>"><?php echo $this->escape($item->title); ?></span>
									<?php endif; ?>
									<div class="small">
										<?php echo JText::_('JCATEGORY') . ": " . $this->escape($item->category_title); ?>
									</div>
								</div>
							</td>
							<td class="small hidden-phone">
								<?php if ($item->created_by_alias) : ?>
									<a href="<?php echo JRoute::_('index.php?option=com_users&task=user.edit&id='.(int) $item->created_by); ?>" title="<?php echo JText::_('JAUTHOR'); ?>">
									<?php echo $this->escape($item->author_name); ?></a>
									<p class="smallsub"> <?php echo JText::sprintf('JGLOBAL_LIST_ALIAS', $this->escape($item->created_by_alias)); ?></p>
								<?php else : ?>
									<a href="<?php echo JRoute::_('index.php?option=com_users&task=user.edit&id='.(int) $item->created_by); ?>" title="<?php echo JText::_('JAUTHOR'); ?>">
									<?php echo $this->escape($item->author_name); ?></a>
								<?php endif; ?>
							</td>
							<td class="small hidden-phone">
								<?php if ($item->language == '*'):?>
									<?php echo JText::alt('JALL', 'language'); ?>
								<?php else:?>
									<?php echo $item->language_title ? $this->escape($item->language_title) : JText::_('JUNDEFINED'); ?>
								<?php endif;?>
							</td>
							<td class="nowrap small hidden-phone">
								<?php echo JHtml::_('date', $item->created, JText::_('DATE_FORMAT_LC4')); ?>
							</td>
							<td class="center">
								<span class="badge badge-success"><?php echo (int) $item->replies; ?></span>
							</td>
							<td class="center hidden-phone">
								<?php echo (int) $item->id; ?>
							</td>
						</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
				<?php endif;?>
			</div>
		</div>
		<div role="tabpanel" class="tab-pane" id="recent">
			<div class="panel panel-default">
				<?php if(!$this->recent):?>
				<div class="panel-body">
					<?php echo JText::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
				</div>
				<?php else:?>
				<table class="table table-striped table-hover">
					<thead>
						<tr>
							<th><?php echo JText::_('JGLOBAL_TITLE');?></th>
							<th width="10%" class="nowrap hidden-phone"><?php echo JText::_('JAUTHOR');?></th>
							<th width="5%" class="nowrap hidden-phone"><?php echo JText::_('JGRID_HEADING_LANGUAGE');?></th>
							<th width="10%" class="nowrap hidden-phone"><?php echo JText::_('JDATE');?></th>
							<th width="10%"><?php echo JText::_('COM_CJFORUM_REPLIES_LABEL');?></th>
							<th width="1%" class="nowrap hidden-phone"><?php echo JText::_('JGRID_HEADING_ID');?></th>
						</tr>
					</thead>
					<tbody>
					<?php foreach ($this->recent as $i => $item) :
						$canEdit    = $user->authorise('core.edit',       'com_cjforum.topic.'.$item->id);
						$canCheckin = $user->authorise('core.manage',     'com_checkin') || $item->checked_out == $userId || $item->checked_out == 0;
						$canEditOwn = $user->authorise('core.edit.own',   'com_cjforum.topic.'.$item->id) && $item->created_by == $userId;
						$canChange  = $user->authorise('core.edit.state', 'com_cjforum.topic.'.$item->id) && $canCheckin;
						?>
						<tr>
							<td class="has-context">
								<div>
									<?php if ($item->checked_out) : ?>
										<?php echo JHtml::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, 'topics.', $canCheckin); ?>
									<?php endif; ?>
									<?php if ($item->language == '*'):?>
										<?php $language = JText::alt('JALL', 'language'); ?>
									<?php else:?>
										<?php $language = $item->language_title ? $this->escape($item->language_title) : JText::_('JUNDEFINED'); ?>
									<?php endif;?>
									<?php if ($canEdit || $canEditOwn) : ?>
										<a href="<?php echo JRoute::_('index.php?option=com_cjforum&task=topic.edit&id=' . $item->id); ?>" title="<?php echo JText::_('JACTION_EDIT'); ?>">
											<?php echo $this->escape($item->title); ?></a>
									<?php else : ?>
										<span title="<?php echo JText::sprintf('JFIELD_ALIAS_LABEL', $this->escape($item->alias)); ?>"><?php echo $this->escape($item->title); ?></span>
									<?php endif; ?>
									<div class="small">
										<?php echo JText::_('JCATEGORY') . ": " . $this->escape($item->category_title); ?>
									</div>
								</div>
							</td>
							<td class="small hidden-phone">
								<?php if ($item->created_by_alias) : ?>
									<a href="<?php echo JRoute::_('index.php?option=com_users&task=user.edit&id='.(int) $item->created_by); ?>" title="<?php echo JText::_('JAUTHOR'); ?>">
									<?php echo $this->escape($item->author_name); ?></a>
									<p class="smallsub"> <?php echo JText::sprintf('JGLOBAL_LIST_ALIAS', $this->escape($item->created_by_alias)); ?></p>
								<?php else : ?>
									<a href="<?php echo JRoute::_('index.php?option=com_users&task=user.edit&id='.(int) $item->created_by); ?>" title="<?php echo JText::_('JAUTHOR'); ?>">
									<?php echo $this->escape($item->author_name); ?></a>
								<?php endif; ?>
							</td>
							<td class="small hidden-phone">
								<?php if ($item->language == '*'):?>
									<?php echo JText::alt('JALL', 'language'); ?>
								<?php else:?>
									<?php echo $item->language_title ? $this->escape($item->language_title) : JText::_('JUNDEFINED'); ?>
								<?php endif;?>
							</td>
							<td class="nowrap small hidden-phone">
								<?php echo JHtml::_('date', $item->created, JText::_('DATE_FORMAT_LC4')); ?>
							</td>
							<td class="center">
								<span class="badge badge-success"><?php echo (int) $item->replies; ?></span>
							</td>
							<td class="center hidden-phone">
								<?php echo (int) $item->id; ?>
							</td>
						</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
				<?php endif;?>
			</div>
		</div>
	</div>
</div>