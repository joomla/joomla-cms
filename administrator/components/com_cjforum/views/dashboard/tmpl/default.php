<?php
/**
 * @package     corejoomla.administrator
 * @subpackage  com_cjforum
 *
 * @copyright   Copyright (C) 2009 - 2014 corejoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die();
JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');

JHtml::_('bootstrap.tooltip');
CJLib::behavior('bscore');
CJLib::behavior('fontawesome');

$app		= JFactory::getApplication();
$user		= JFactory::getUser();
$userId		= $user->id;
$span		= !empty( $this->sidebar) ? 'span10' : '';
?>
<div id="cj-wrapper">
	<?php if (!empty( $this->sidebar)) : ?>
	<div id="j-sidebar-container" class="span2">
		<?php echo $this->sidebar; ?>
	</div>
	<?php endif;?>
	<div id="j-main-container" class="<?php echo $span;?>">
		<div class="span8">
			<?php echo $this->loadTemplate('charts');?>
			<?php echo $this->loadTemplate('topics');?>
		</div>
		<div class="span4">
			<div class="panel panel-default">
				<div class="panel-heading">
					<strong><i class="fa fa-bullhorn"></i> <?php echo JText::_('COM_CJFORUM_TITLE_VERSION');?></strong>
				</div>
				<table class="table table-striped">
					<thead>
						<tr>
							<td colspan="2">
								<p>If you use CjForum, please post a rating and a review at the Joomla Extension Directory</p>
								<a class="btn btn-info" href="http://extensions.joomla.org/extensions/extension/communication/forum/cjforum" target="_blank">
									<i class="icon-share icon-white"></i> <span style="color: white">Post Your Review</span>
								</a>
							</td>
						</tr>
					</thead>
					<tbody>
						<tr>
							<th><?php echo JText::_('COM_CJFORUM_INSTALLED_VERSION');?>:</th>
							<td><?php echo CF_CURR_VERSION;?></td>
						<tr>
						<?php if(!empty($this->version)):?>
						<tr>
							<th>Latest Version:</th>
							<td><?php echo $this->version['version'];?></td>
						</tr>
						<tr>
							<th>Latest Version Released On:</th>
							<td><?php echo $this->version['released'];?></td>
						</tr>
						<tr>
							<th>CjLib Version</th>
							<td><?php echo CJLIB_VER;?></td>
						</tr>
						<tr>
							<td colspan="2" style="text-align: center;">
								<?php if($this->version['status'] == 1):?>
								<a href="http://www.corejoomla.com/downloads.html" target="_blank" class="btn btn-danger">
									<i class="icon-download icon-white"></i> <span style="color: white">Please Update</span>
								</a>
								<?php else:?>
								<a href="#" class="btn btn-success"><i class="icon-ok icon-white"></i> <span style="color: white">Up-to date</span></a>
								<?php endif;?>
							</td>
						</tr>
						<?php endif;?>
					</tbody>
				</table>
			</div>
			<div class="panel panel-default">
				<div class="panel-heading">
					<strong><i class="fa fa-group"></i> <?php echo JText::_('COM_CJFORUM_TITLE_TOP_USERS');?></strong>
				</div>
				<?php if(empty($this->topusers)):?>
				<div class="panel-body">
					<?php echo JText::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
				</div>
				<?php else:?>
				<table class="table table-striped table-hover">
					<caption></caption>
					<thead>
						<tr>
							<th><?php echo JText::_('JGLOBAL_TITLE');?></th>
							<th width="20%"><?php echo JText::_('COM_CJFORUM_POSTS_LABEL');?></th>
							<th width="25%" class="nowrap hidden-phone"><?php echo JText::_('COM_CJFORUM_LAST_POST_TIME');?></th>
						</tr>
					</thead>
					<tbody>
					<?php foreach ($this->topusers as $i => $item) :
						?>
						<tr>
							<td><?php echo $this->escape($item->username);?>
							<td><?php echo $item->topics + $item->replies;?></td>
							<td><?php echo JHtml::_('date', $item->last_post_time, JText::_('DATE_FORMAT_LC4')); ?></td>
						</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
				<?php endif;?>
			</div>
			<div class="panel panel-default">
				<div class="panel-heading"><strong>Credits: </strong></div>
				<div class="panel-body">
					<div>CjForum is a free software released under Gnu/GPL license. Copyright© 2009-14 corejoomla.com</div>
					<div>Core Components: Bootstrap, jQuery, FontAwesome and ofcourse Joomla<sup>&reg;</sup>.</div>
				</div>
			</div>
		</div>
	</div>
</div>