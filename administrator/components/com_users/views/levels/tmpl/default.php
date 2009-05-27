<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	com_users
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @copyright	Copyright (C) 2008 - 2009 JXtended, LLC. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
 */

defined('_JEXEC') or die;

// Include the component HTML helpers.
JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');

// Load the tooltip behavior.
JHtml::_('behavior.tooltip');

// Load the default stylesheet.
JHtml::stylesheet('default.css', 'administrator/components/com_users/media/css/');
?>
<form action="<?php echo JRoute::_('index.php?option=com_users&view=levels');?>" method="post" name="adminForm">
	<fieldset class="filter clearfix">
		<div class="left">
			<label for="search"><?php echo JText::sprintf('JSearch_Label', 'Levels'); ?></label>
			<input type="text" name="filter_search" id="search" value="<?php echo $this->state->get('filter.search'); ?>" size="30" title="<?php echo JText::sprintf('JSearch_Title', 'Levels'); ?>" />
			<button type="submit"><?php echo JText::_('JSearch_Submit'); ?></button>
			<button type="button" onclick="$('search').value='';this.form.submit();"><?php echo JText::_('JSearch_Reset'); ?></button>
		</div>
		<div class="right">
			<ol>
				<li>
					<label for="filter_group_id">
						<?php echo JText::_('Users_Filter_Section'); ?>
					</label>
					<?php echo JHtml::_('access.section', 'filter_section_id', $this->state->get('filter.section_id'), 'onchange="this.form.submit()"'); ?>
				</li>
			</ol>
		</div>
	</fieldset>
	<table class="adminlist">
		<thead>
			<tr>
				<th width="20">
					<input type="checkbox" name="toggle" value="" onclick="checkAll(this)" />
				</th>
				<th class="left">
					<?php echo JText::_('Users_Heading_Level_Name'); ?>
				</th>
				<th width="10%">
					<?php echo JText::_('Users_Heading_Section'); ?>
				</th>
				<th width="30%">
					<?php echo JText::_('Users_Heading_Level_User_Groups'); ?>
				</th>
				<th width="30%">
					&nbsp;
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
		<?php
			$i = 0;
			foreach ($this->items as $item) : ?>
			<tr class="row<?php echo $i++ % 2; ?>">
				<td style="text-align:center">
					<?php echo JHtml::_('grid.id', $item->id, $item->id); ?>
				</td>
				<td>
					<a href="<?php echo JRoute::_('index.php?option=com_users&task=level.edit&cid[]='.$item->id);?>">
						<?php echo $item->title; ?></a>
				</td>
				<td>
					<?php echo $item->section_title; ?>
				</td>
				<td>
					<?php echo nl2br(implode("\n", explode(',', $item->user_groups))); ?>
				</td>
				<td>
					&nbsp;
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
