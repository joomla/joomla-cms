<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	templates.hathor
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 * @since		1.6
 */

// no direct access
defined('_JEXEC') or die;

// Include the component HTML helpers.
JHtml::addIncludePath(JPATH_COMPONENT.DS.'helpers'.DS.'html');
JHtml::_('behavior.tooltip');

$user	= &JFactory::getUser();
$userId	= $user->get('id');
$listOrder	= $this->state->get('list.ordering');
$listDirn	= $this->state->get('list.direction');


?>
<form action="<?php echo JRoute::_('index.php?option=com_menus&view=items');?>" method="post" name="adminForm">
	<fieldset id="filter-bar">
	<legend class="element-invisible"><?php echo JText::_('JSEARCH_FILTER_LABEL'); ?></legend>
		<div class="filter-search">
			<label class="filter-search-lbl" for="filter_search"><?php echo JText::_('JSEARCH_FILTER_LABEL'); ?>:</label>
			<input type="text" name="filter_search" id="filter_search" value="<?php echo $this->state->get('filter.search'); ?>" title="<?php echo JText::_('COM_MENUS_ITEMS_SEARCH_FILTER'); ?>" />
			<button type="submit"><?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?></button>
			<button type="button" onclick="document.id('filter_search').value='';this.form.submit();"><?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?></button>
		</div>
		<div class="filter-select">
			<label class="selectlabel" for="filter_access">
				<?php echo JText::_('JOPTION_SELECT_ACCESS'); ?>
			</label>
			<select name="filter_access" id="filter_access" class="inputbox">
				<option value=""><?php echo JText::_('JOPTION_SELECT_ACCESS');?></option>
				<?php echo JHtml::_('select.options', JHtml::_('access.assetgroups'), 'value', 'text', $this->state->get('filter.access'));?>
			</select>

			<label class="selectlabel" for="filter_published">
				<?php echo JText::_('JOPTION_SELECT_PUBLISHED'); ?>
			</label>
			<select name="filter_published" id="filter_published" class="inputbox">
				<option value=""><?php echo JText::_('JOPTION_SELECT_PUBLISHED');?></option>
				<?php echo JHtml::_('select.options', JHtml::_('jgrid.publishedOptions'), 'value', 'text', $this->state->get('filter.published'), true);?>
			</select>

			<label class="selectlabel" for="menutype">
				<?php echo JText::_('TPL_HATHOR_COM_MENUS_MENU'); ?>
			</label>
			<select name="menutype" id="menutype" class="inputbox">
				<?php echo JHtml::_('select.options', JHtml::_('menu.menus'), 'value', 'text', $this->state->get('filter.menutype'));?>
			</select>

			<label class="selectlabel" for="filter_level">
				<?php echo JText::_('COM_MENUS_OPTION_SELECT_LEVEL'); ?>
			</label>
			<select name="filter_level" id="filter_level" class="inputbox">
				<option value=""><?php echo JText::_('COM_MENUS_OPTION_SELECT_LEVEL');?></option>
				<?php echo JHtml::_('select.options', $this->f_levels, 'value', 'text', $this->state->get('filter.level'));?>
			</select>

			<button type="button" id="filter-go" onclick="this.form.submit();">
				<?php echo JText::_('GO'); ?></button>

		</div>
	</fieldset>
	<div class="clr"> </div>

	<table class="adminlist">
		<thead>
			<tr>
				<th class="checkmark-col">
					<input type="checkbox" name="toggle" value="" title="<?php echo JText::_('TPL_HATHOR_CHECKMARK_ALL'); ?>" onclick="checkAll(this)" />
				</th>
				<th class="title">
					<?php echo JHtml::_('grid.sort', 'JGLOBAL_TITLE', 'a.title', $listDirn, $listOrder); ?>
				</th>
				<th class="nowrap state-col">
					<?php echo JHtml::_('grid.sort', 'JPUBLISHED', 'a.published', $listDirn, $listOrder); ?>
				</th>
				<th class="nowrap ordering-col">
					<?php echo JHtml::_('grid.sort', 'JGRID_HEADING_ORDERING', 'a.lft', $listDirn, $listOrder); ?>
					<?php echo JHtml::_('grid.order',  $this->items); ?>
				</th>
				<th class="title access-col">
					<?php echo JHtml::_('grid.sort',  'JGRID_HEADING_ACCESS', 'access_level', $listDirn, $listOrder); ?>
				</th>
				<th width="10%">
					<?php echo JText::_('JGRID_HEADING_MENU_ITEM_TYPE'); ?>
				</th>
				<th class="nowrap id-col">
					<?php echo JHtml::_('grid.sort',  'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
				</th>
			</tr>
		</thead>

		<tbody>
		<?php
		foreach ($this->items as $i => $item) :
			// $lang = &JFactory::getLanguage();
			// $lang->load($item->componentname, JPATH_ADMINISTRATOR);
			$ordering = ($listOrder == 'a.lft');
			$orderkey = array_search($item->id, $this->ordering[$item->parent_id]);
			?>
			<tr class="row<?php echo $i % 2; ?>">
				<td class="center">
					<?php echo JHtml::_('grid.id', $i, $item->id); ?>
				</td>
				<td class="indent-<?php echo intval(($item->level-1)*15)+4; ?>">

					<?php if ($item->checked_out) : ?>
						<?php echo JHtml::_('jgrid.checkedout', $item->editor, $item->checked_out_time); ?>
					<?php endif; ?>
					<a href="<?php echo JRoute::_('index.php?option=com_menus&task=item.edit&cid[]='.$item->id);?>">
						<?php echo $this->escape($item->title); ?></a>

					<?php if ($item->home == 1) : ?>
						<span><?php echo JHTML::_('image','menu/icon-16-default.png', JText::_('JDEFAULT'), array( 'title' => JText::_('JDEFAULT')), true); ?></span>
					<?php endif; ?>

					<p class="smallsub" title="<?php echo $this->escape($item->path);?>">
								(<?php echo '<span>'.JText::_('JFIELD_ALIAS_LABEL') . ':</span> ' . $this->escape($item->alias) ;?>)</p>
				</td>
				<td class="center">
					<?php echo JHtml::_('jgrid.published', $item->published, $i, 'items.');?>
				</td>
				<td class="order">
					<span><?php echo $this->pagination->orderUpIcon($i, isset($this->ordering[$item->parent_id][$orderkey - 1]), 'items.orderup', 'JLIB_HTML_MOVE_UP', $ordering); ?></span>
					<span><?php echo $this->pagination->orderDownIcon($i, $this->pagination->total, isset($this->ordering[$item->parent_id][$orderkey + 1]), 'items.orderdown', 'JLIB_HTML_MOVE_DOWN', $ordering); ?></span>
					<?php $disabled = $ordering ?  '' : 'disabled="disabled"'; ?>
					<input type="text" name="order[]" value="<?php echo $orderkey + 1;?>" <?php echo $disabled ?> class="text-area-order" title="<?php echo $item->title; ?> order" />
				</td>
				<td class="center">
					<?php echo $this->escape($item->access_level); ?>
				</td>
				<td class="center nowrap">
					<span title="<?php echo isset($item->item_type_desc) ? htmlspecialchars($this->escape($item->item_type_desc), ENT_COMPAT, 'UTF-8') : ''; ?>">
						<?php echo $this->escape($item->item_type); ?></span>
				</td>
				<td class="center">
					<span title="<?php echo sprintf('%d-%d', $item->lft, $item->rgt);?>">
						<?php echo (int) $item->id; ?></span>
				</td>
			</tr>
			<?php endforeach; ?>
		</tbody>
	</table>

	<?php echo $this->pagination->getListFooter(); ?>
	<div class="clr"> </div>

	<?php echo $this->loadTemplate('batch'); ?>

	<input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
	<?php echo JHtml::_('form.token'); ?>
</form>
