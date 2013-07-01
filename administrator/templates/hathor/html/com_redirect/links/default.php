<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  Template.hathor
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Include the component HTML helpers.
JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');

JHtml::_('behavior.multiselect');

$user		= JFactory::getUser();
$listOrder	= $this->escape($this->state->get('list.ordering'));
$listDirn	= $this->escape($this->state->get('list.direction'));
?>

<form action="<?php echo JRoute::_('index.php?option=com_redirect&view=links'); ?>" method="post" name="adminForm" id="adminForm">
<?php if (!empty( $this->sidebar)) : ?>
	<div id="j-sidebar-container" class="span2">
		<?php echo $this->sidebar; ?>
	</div>
	<div id="j-main-container" class="span10">
<?php else : ?>
	<div id="j-main-container">
<?php endif;?>
	<fieldset id="filter-bar">
	<legend class="element-invisible"><?php echo JText::_('JSEARCH_FILTER_LABEL'); ?></legend>
		<div class="filter-search">
			<label class="filter-search-lbl" for="filter_search"><?php echo JText::_('JSEARCH_FILTER_LABEL'); ?></label>
			<input type="text" name="filter_search" id="filter_search" value="<?php echo $this->escape($this->state->get('filter.search')); ?>" title="<?php echo JText::_('COM_REDIRECT_SEARCH_LINKS'); ?>" />
			<button type="submit"><?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?></button>
			<button type="button" onclick="document.id('filter_search').value='';this.form.submit();"><?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?></button>
		</div>

		<div class="filter-select">
			<label class="selectlabel" for="filter_published">
				<?php echo JText::_('JOPTION_SELECT_PUBLISHED'); ?>
			</label>
			<select name="filter_state" class="inputbox" id="filter_published">
				<option value=""><?php echo JText::_('JOPTION_SELECT_PUBLISHED');?></option>
				<?php echo JHtml::_('select.options', RedirectHelper::publishedOptions(), 'value', 'text', $this->state->get('filter.state'), true);?>
			</select>

			<button type="submit" id="filter-go">
				<?php echo JText::_('JSUBMIT'); ?></button>
		</div>
	</fieldset>
	<div class="clr"> </div>

	<table class="adminlist">
		<thead>
			<tr>
				<th class="checkmark-col">
					<input type="checkbox" name="checkall-toggle" value="" title="<?php echo JText::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)" />
				</th>
				<th class="title">
					<?php echo JHtml::_('grid.sort', 'COM_REDIRECT_HEADING_OLD_URL', 'a.old_url', $listDirn, $listOrder); ?>
				</th>
				<th class="width-30">
					<?php echo JHtml::_('grid.sort', 'COM_REDIRECT_HEADING_NEW_URL', 'a.new_url', $listDirn, $listOrder); ?>
				</th>
				<th class="width-30">
					<?php echo JHtml::_('grid.sort', 'COM_REDIRECT_HEADING_REFERRER', 'a.referer', $listDirn, $listOrder); ?>
				</th>
				<th class="width-10">
					<?php echo JHtml::_('grid.sort', 'COM_REDIRECT_HEADING_CREATED_DATE', 'a.created_date', $listDirn, $listOrder); ?>
				</th>
				<th class="nowrap state-col">
					<?php echo JHtml::_('grid.sort', 'JSTATUS', 'a.published', $listDirn, $listOrder); ?>
				</th>
				<th class="nowrap id-col">
					<?php echo JHtml::_('grid.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
				</th>
			</tr>
		</thead>

		<tbody>
		<?php foreach ($this->items as $i => $item) :
			$canCreate = $user->authorise('core.create',     'com_redirect');
			$canEdit   = $user->authorise('core.edit',       'com_redirect');
			$canChange = $user->authorise('core.edit.state', 'com_redirect');
			?>
			<tr class="row<?php echo $i % 2; ?>">
				<td class="center">
					<?php echo JHtml::_('grid.id', $i, $item->id); ?>
				</td>
				<td>
					<?php if ($canEdit) : ?>
						<a href="<?php echo JRoute::_('index.php?option=com_redirect&task=link.edit&id='.$item->id);?>" title="<?php echo $this->escape($item->old_url); ?>">
							<?php echo $this->escape(str_replace(JUri::root(), '', $item->old_url)); ?></a>
					<?php else : ?>
							<?php echo $this->escape(str_replace(JUri::root(), '', $item->old_url)); ?>
					<?php endif; ?>
				</td>
				<td>
					<?php echo $this->escape($item->new_url); ?>
				</td>
				<td>
					<?php echo $this->escape($item->referer); ?>
				</td>
				<td class="center">
					<?php echo JHtml::_('date', $item->created_date, JText::_('DATE_FORMAT_LC4')); ?>
				</td>
				<td class="center">
					<?php echo JHtml::_('redirect.published', $item->published, $i); ?>
				</td>
				<td class="center">
					<?php echo (int) $item->id; ?>
				</td>
			</tr>
			<?php endforeach; ?>
		</tbody>
	</table>

	<?php echo $this->pagination->getListFooter(); ?>
	<p class="footer-tip">
		<?php if ($this->enabled) : ?>
			<span class="enabled"><?php echo JText::_('COM_REDIRECT_PLUGIN_ENABLED'); ?></span>
		<?php else : ?>
			<span class="disabled"><?php echo JText::_('COM_REDIRECT_PLUGIN_DISABLED'); ?></span>
		<?php endif; ?>
	</p>
	<div class="clr"></div>

	<?php if (!empty($this->items)) : ?>
		<?php echo $this->loadTemplate('addform'); ?>
	<?php endif; ?>

	<input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
	<?php echo JHtml::_('form.token'); ?>
</div>
</form>
