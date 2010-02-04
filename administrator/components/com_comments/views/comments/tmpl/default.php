<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	com_comments
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

// Include the component HTML helpers.
JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');
JHtml::_('behavior.tooltip');
?>

<form action="<?php echo JRoute::_('index.php?option=com_comments&view=comments');?>" method="post" name="adminForm">
	<fieldset id="filter-bar">
		<div class="filter-search fltlft">
			<label class="filter-search-lbl" for="filter_search"><?php echo JText::_('JSearch_Filter_Label'); ?></label>
			<input type="text" name="filter_search" id="filter_search" value="<?php echo $this->state->get('filter.search'); ?>" title="<?php echo JText::_('Comments_Search_in_name'); ?>" />
			<button type="submit"><?php echo JText::_('JSearch_Filter_Submit'); ?></button>
			<button type="button" onclick="document.id('filter_search').value='';this.form.submit();"><?php echo JText::_('JSearch_Filter_Clear'); ?></button>
		</div>
		<div class="filter-select fltrt">
			<select name="filter_state" id="published" class="inputbox" onchange="this.form.submit()">
				<?php echo JHtml::_('comments.commentStateOptions', $this->state->get('filter.state')); ?>
			</select>

			<select name="filter_context" id="context" class="inputbox" onchange="this.form.submit()">
				<?php echo JHtml::_('comments.commentContextOptions', $this->state->get('filter.context')); ?>
			</select>
		</div>
	</fieldset>
	<div class="clr"> </div>

	<table class="adminlist">
		<thead>
			<tr>
				<th width="15%">
					<?php echo JHtml::_('grid.sort', 'COMMENTS_DATE', 'a.created_date', $this->state->get('list.direction'), $this->state->get('list.ordering')); ?>
					,
					<?php echo JHtml::_('grid.sort', 'COMMENTS_AUTHOR', 'a.name', $this->state->get('list.direction'), $this->state->get('list.ordering')); ?>
				</th>
				<th width="15%">
					<?php echo JHtml::_('grid.sort', 'COMMENTS_EMAIL', 'a.email', $this->state->get('list.direction'), $this->state->get('list.ordering')); ?>
					,
					<?php echo JHtml::_('grid.sort', 'COMMENTS_URL', 'a.url', $this->state->get('list.direction'), $this->state->get('list.ordering')); ?>
					,
					<?php echo JHtml::_('grid.sort', 'COMMENTS_IP_ADDRESS', 'a.address', $this->state->get('list.direction'), $this->state->get('list.ordering')); ?>
				</th>
				<th align="center">
					<?php echo JHtml::_('grid.sort', 'COMMENTS_SUBJECT', 'a.subject', $this->state->get('list.direction'), $this->state->get('list.ordering')); ?>
					,
					<?php echo JText::_('COMMENTS_BODY'); ?>
				</th>
				<th nowrap="nowrap" width="12%">
					<?php echo JText::_('COMMENTS_ACTION'); ?>
				</th>
				<th width="1%" nowrap="nowrap">
					<?php echo JHtml::_('grid.sort', 'JGrid_Heading_ID', 'a.id', $this->state->get('list.direction'), $this->state->get('list.ordering')); ?>
				</th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<td colspan="15">
					<?php echo $this->pagination->getListFooter(); ?>
				</td>
			</tr>
		</tfoot>
		<tbody>
		<?php foreach ($this->items as $i => &$item) : ?>
			<tr class="row<?php echo $i % 2; ?>">
				<td align="center">
					<a href="<?php echo JRoute::_('index.php?option=com_comments&view=comment&id='.$item->id); ?>">
						<?php echo JText::sprintf('COMMENTS_SUBMITTED_AUTHOR_DATE', $item->name, JHtml::date($item->created_date, JText::_('DATE_FORMAT_LC2'))); ?></a>
				</td>
				<td valign="top">
					<ul class="comment-author-data">
						<li class="email" title="<?php echo JText::_('COMMENTS_EMAIL'); ?>">
							<?php echo ($item->email) ? $item->email : '- N/A -'; ?></li>
						<li class="url" title="<?php echo JText::_('COMMENTS_WEBSITE_URL'); ?>">
							<?php echo ($item->url) ? $item->url : JText::_('COMMENTS_NOT_AVAILABLE'); ?></li>
						<li class="ip" title="<?php echo JText::_('COMMENTS_IP_ADDRESS'); ?>">
							<?php echo ($item->address) ? $item->address : JText::_('COMMENTS_NOT_AVAILABLE'); ?> <a href="index.php?option=com_comments&amp;task=config.block&amp;block=address&amp;cid[]=<?php echo $item->id;?>">[ <?php echo JText::_('COMMENTS_BLOCK');?> ]</a></li>
					</ul>
				</td>
				<td valign="top">
					<ul class="comment-data">
						<li class="thread">
							<a href="<?php echo $this->getContentRoute($item->page_route); ?>" target="_blank">
								<?php
								$subject = JText::sprintf('COMMENTS_RE', !empty($item->subject) ? $item->subject : $item->page_title);
								$subject = htmlspecialchars($subject, ENT_QUOTES, 'UTF-8');
								echo $subject;
								?></a>
						</li>
						<li class="body">
							<?php echo JHtml::_('string.truncate', JHtml::_('bbcode.render', $item->body), 430); ?>
						</li>
					</ul>
				</td>
				<td>
					<?php echo JHtml::_('commentmoderation.action', $item); ?>
				</td>
				<td>
					<?php echo (int) $item->id; ?>
				</td>
			</tr>
			<?php endforeach; ?>
		</tbody>
	</table>

	<input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="filter_order" value="<?php echo $this->state->get('list.ordering'); ?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo $this->state->get('list.direction'); ?>" />
	<?php echo JHtml::_('form.token'); ?>
</form>
