<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	com_redirect
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Invalid Request');

// Load the tooltip behavior.
JHtml::_('behavior.tooltip');


// Build the toolbar.
$this->buildDefaultToolBar();
?>
<form action="index.php?option=com_redirect&amp;view=links" method="post" name="adminForm">
	<fieldset id="filter-bar">
		<div class="filter-search fltlft">
			<label class="filter-search-lbl" for="search"><?php echo JText::_('Filter'); ?>:</label>
			<input type="text" name="filter_search" id="search" value="<?php echo $this->state->get('filter.search'); ?>" title="<?php echo JText::_('Search in title'); ?>" />
			<button type="submit"><?php echo JText::_('Go'); ?></button>
			<button type="button" onclick="document.id('search').value='';document.id('published').value=0;this.form.submit();"><?php echo JText::_('Clear'); ?></button>
		</div>
		<div class="filter-select fltrt">
			<label for="published"><?php echo JText::_('REDIRECT_SHOW_BY_STATE'); ?></label>
			<select name="filter_state" id="published" class="inputbox" onchange="this.form.submit()">
				<?php
				echo JHtml::_('select.options', $this->filter_state, 'value', 'text', $this->state->get('filter.state'));
				?>
			</select>
		</div>
	</fieldset>
	<div class="clr"> </div>

<table class="adminlist">
	<thead>
		<tr>
			<th width="20">
				<input type="checkbox" name="toggle" value="" class="checklist-toggle" />
			</th>
			<th class="left">
				<?php echo JHtml::_('grid.sort', 'REDIRECT_LINK_OLD_URL', 'old_url', $this->state->get('list.direction'), $this->state->get('list.ordering')); ?>
			</th>
			<th class="nowrap" width="30%">
				<?php echo JHtml::_('grid.sort', 'REDIRECT_LINK_NEW_URL', 'new_url', $this->state->get('list.direction'), $this->state->get('list.ordering')); ?>
			</th>
			<th class="nowrap" width="30%">
				<?php echo JHtml::_('grid.sort', 'REDIRECT_LINK_REFERRER', 'referer', $this->state->get('list.direction'), $this->state->get('list.ordering')); ?>
			</th>
			<th class="nowrap" width="10%">
				<?php echo JHtml::_('grid.sort', 'REDIRECT_LINK_CREATED_DATE', 'created_date', $this->state->get('list.direction'), $this->state->get('list.ordering')); ?>
			</th>
			<th class="nowrap" width="5%">
				<?php echo JHtml::_('grid.sort', 'REDIRECT_LINK_STATE', 'published', $this->state->get('list.direction'), $this->state->get('list.ordering')); ?>
			</th>
		</tr>
	</thead>
	<tfoot>
		<tr>
			<td colspan="6">
				<?php echo $this->pagination->getListFooter(); ?>
			</td>
		</tr>
	</tfoot>
	<tbody>
<?php
		$k = 0;
		$i = 0;
		foreach ($this->items as $item) :
?>
		<tr class="row<?php echo $k; ?>">
			<td class="checklist center">
				<?php echo JHtml::_('grid.id', $item->id, $item->id); ?>
			</td>
			<td>
				<a href="<?php echo JRoute::_('index.php?option=com_redirect&task=link.edit&l_id='.$item->id);?>">
					<?php echo JFilterOutput::ampReplace($item->old_url); ?></a>
			</td>
			<td>
				<?php echo JFilterOutput::ampReplace($item->new_url); ?>
			</td>
			<td>
				<?php echo JFilterOutput::ampReplace($item->referer); ?>
			</td>
			<td class="center">
				<?php echo JHtml::_('date', $item->created_date); ?>
			</td>
			<td class="center">
				<?php echo JHtml::_('grid.published', $item, $item->id, 'tick.png', 'publish_x.png', 'link.'); ?>
			</td>
		</tr>
	<?php
		$k = 1 - $k;
		$i++;
		endforeach; ?>
	</tbody>
</table>

<fieldset class="batch">
	<legend><?php echo JText::_('REDIRECT_LINK_ACTIVATION_VALUES'); ?></legend>
	<label for="new_url"><?php echo JText::_('REDIRECT_LINK_NEW_URL'); ?>:</label>
	<input type="text" name="new_url" id="new_url" value="" size="50" title="<?php echo JText::_('REDIRECT_LINK_NEW_URL_DESC'); ?>" />
		
	<label for="comment"><?php echo JText::_('REDIRECT_LINK_COMMENT'); ?>:</label>
	<input type="text" name="comment" id="comment" value="" size="50" title="<?php echo JText::_('REDIRECT_LINK_COMMENT_DESC'); ?>" />
</fieldset>

	<input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="filter_order" value="<?php echo $this->state->get('list.ordering'); ?>" />
	<input type="hidden" name="filter_order_Dir" value="" />
	<?php echo JHtml::_('form.token'); ?>
</form>
