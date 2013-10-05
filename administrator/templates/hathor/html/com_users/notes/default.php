<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  Template.hathor
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$user = JFactory::getUser();
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn = $this->escape($this->state->get('list.direction'));
$canEdit = $user->authorise('core.edit', 'com_users');
?>
<form action="<?php echo JRoute::_('index.php?option=com_users&view=notes');?>" method="post" name="adminForm" id="adminForm">
<?php if (!empty( $this->sidebar)) : ?>
	<div id="j-sidebar-container" class="span2">
		<?php echo $this->sidebar; ?>
	</div>
	<div id="j-main-container" class="span10">
<?php else : ?>
	<div id="j-main-container">
<?php endif;?>
	<fieldset id="filter-bar">
	<legend class="element-invisible"><?php echo JText::_('COM_USERS_SEARCH_IN_NOTE_TITLE'); ?></legend>
		<div class="filter-search">
			<label class="filter-search-lbl" for="filter_search"><?php echo JText::_('JSEARCH_FILTER_LABEL'); ?></label>
			<input type="text" name="filter_search" id="filter_search" value="<?php echo $this->escape($this->state->get('filter.search')); ?>" title="<?php echo JText::_('COM_USERS_SEARCH_IN_NOTE_TITLE'); ?>" />
			<button type="submit"><?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?></button>
			<button type="button" onclick="document.id('filter_search').value='';this.form.submit();"><?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?></button>
		</div>

		<div class="filter-select">
			<span class="faux-label"><?php echo JText::_('COM_USERS_FILTER_LABEL'); ?></span>

			<label class="selectlabel" for="filter_category_id">
				<?php echo JText::_('JOPTION_SELECT_CATEGORY'); ?>
			</label>
			<select name="filter_category_id" class="inputbox" id="filter_category_id" >
				<option value=""><?php echo JText::_('JOPTION_SELECT_CATEGORY');?></option>
				<?php
				echo JHtml::_(
					'select.options', JHtml::_('category.options', 'com_users.notes'),
					'value', 'text', $this->state->get('filter.category_id')
				); ?>
			</select>

			<label class="selectlabel" for="filter_published">
				<?php echo JText::_('JOPTION_SELECT_PUBLISHED'); ?>
			</label>
			<select name="filter_published" class="inputbox" id="filter_published">
				<option value=""><?php echo JText::_('JOPTION_SELECT_PUBLISHED');?></option>
				<?php
				echo JHtml::_(
					'select.options', JHtml::_('jgrid.publishedOptions'),
					'value', 'text', $this->state->get('filter.state'), true
				); ?>
			</select>

			<button type="submit" id="filter-go">
				<?php echo JText::_('JSUBMIT'); ?></button>
		</div>
	</fieldset>

	<table class="adminlist">
		<thead>
			<tr>
				<th class="checkmark-col">
					<input type="checkbox" name="toggle" value="" class="checklist-toggle" title="<?php echo JText::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)" />
				</th>
				<th class="width-15">
					<?php echo JHtml::_('grid.sort', 'COM_USERS_USER_HEADING', 'u.name', $listDirn, $listOrder); ?>
				</th>
				<th  class="title">
					<?php echo JHtml::_('grid.sort', 'COM_USERS_SUBJECT_HEADING', 'a.subject', $listDirn, $listOrder); ?>
				</th>
				<th class="width-20">
					<?php echo JHtml::_('grid.sort', 'COM_USERS_CATEGORY_HEADING', 'c.title', $listDirn, $listOrder); ?>
				</th>
				<th class="width-5">
					<?php echo JHtml::_('grid.sort', 'JSTATUS', 'a.state', $listDirn, $listOrder); ?>
				</th>
				<th class="width-10">
					<?php echo JHtml::_('grid.sort', 'COM_USERS_REVIEW_HEADING', 'a.review_time', $listDirn, $listOrder); ?>
				</th>
				<th class="nowrap id-col">
					<?php echo JHtml::_('grid.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
				</th>
			</tr>
		</thead>
		<tbody>
		<?php foreach ($this->items as $i => $item) : ?>
			<?php $canChange	= $user->authorise('core.edit.state',	'com_users'); ?>
			<tr class="row<?php echo $i % 2; ?>">
				<td class="center checklist">
					<?php echo JHtml::_('grid.id', $i, $item->id); ?>
				</td>
				<td>
					<?php if ($item->checked_out) : ?>
						<?php echo JHtml::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time); ?>
					<?php endif; ?>
					<?php if ($canEdit) : ?>
						<a href="<?php echo JRoute::_('index.php?option=com_users&task=note.edit&id='.$item->id);?>">
							<?php echo $this->escape($item->user_name); ?></a>
					<?php else : ?>
						<?php echo $this->escape($item->user_name); ?>
					<?php endif; ?>
				</td>
				<td>
					<?php if ($item->subject) : ?>
						<?php echo $this->escape($item->subject); ?>
					<?php else : ?>
						<?php echo JText::_('COM_USERS_EMPTY_SUBJECT'); ?>
					<?php endif; ?>
				</td>
				<td class="center">
					<?php if ($item->catid && $item->cparams->get('image')) : ?>
					<?php echo JHtml::_('users.image', $item->cparams->get('image')); ?>
					<?php endif; ?>
					<?php echo $this->escape($item->category_title); ?>
				</td>
				<td class="center">
					<?php echo JHtml::_('jgrid.published', $item->state, $i, 'notes.', $canChange, 'cb', $item->publish_up, $item->publish_down); ?>
				</td>
				<td class="center">
					<?php if ((int) $item->review_time) : ?>
						<?php echo $this->escape($item->review_time); ?>
					<?php else : ?>
						<?php echo JText::_('COM_USERS_EMPTY_REVIEW'); ?>
					<?php endif; ?>
				</td>
				<td class="center">
					<?php echo (int) $item->id; ?>
				</td>
			</tr>
			<?php endforeach; ?>
		</tbody>
	</table>

	<?php echo $this->pagination->getListFooter(); ?>

	<div>
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
		<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</div>
</form>
