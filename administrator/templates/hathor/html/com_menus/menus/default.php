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
JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');

JHtml::_('behavior.multiselect');

$uri		= JUri::getInstance();
$return		= base64_encode($uri);
$user		= JFactory::getUser();
$userId		= $user->get('id');
$listOrder	= $this->escape($this->state->get('list.ordering'));
$listDirn	= $this->escape($this->state->get('list.direction'));
?>
<script type="text/javascript">
	Joomla.submitbutton = function(task)
	{
		if (task != 'menus.delete' || confirm('<?php echo JText::_('COM_MENUS_MENU_CONFIRM_DELETE', true);?>'))
		{
			Joomla.submitform(task);
		}
	}
</script>
<form action="<?php echo JRoute::_('index.php?option=com_menus&view=menus');?>" method="post" name="adminForm" id="adminForm">
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
			<label class="filter-search-lbl" for="filter_search"><?php echo JText::_('COM_MENUS_MENU_SEARCH_FILTER'); ?></label>
			<input type="text" name="filter_search" id="filter_search" value="<?php echo $this->escape($this->state->get('filter.search')); ?>" title="<?php echo JText::_('COM_MENUS_ITEMS_SEARCH_FILTER'); ?>" />
			<button type="submit"><?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?></button>
			<button type="button" onclick="document.id('filter_search').value='';this.form.submit();"><?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?></button>
		</div>
	</fieldset>
	<div class="clearfix"> </div>
	<table class="adminlist">
		<thead>
			<tr>
				<th class="checkmark-col" rowspan="2">
					<input type="checkbox" name="checkall-toggle" value="" title="<?php echo JText::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)" />
				</th>
				<th rowspan="2">
					<?php echo JHtml::_('grid.sort', 'JGLOBAL_TITLE', 'a.title', $listDirn, $listOrder); ?>
				</th>
				<th class="width-30" colspan="3">
					<?php echo JText::_('COM_MENUS_HEADING_NUMBER_MENU_ITEMS'); ?>
				</th>
				<th class="width-20" rowspan="2">
					<?php echo JText::_('COM_MENUS_HEADING_LINKED_MODULES'); ?>
				</th>
				<th class="nowrap id-col" rowspan="2">
					<?php echo JHtml::_('grid.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
				</th>
			</tr>
			<tr>
				<th class="width-10">
					<?php echo JText::_('COM_MENUS_HEADING_PUBLISHED_ITEMS'); ?>
				</th>
				<th class="width-10">
					<?php echo JText::_('COM_MENUS_HEADING_UNPUBLISHED_ITEMS'); ?>
				</th>
				<th class="width-10">
					<?php echo JText::_('COM_MENUS_HEADING_TRASHED_ITEMS'); ?>
				</th>
			</tr>
		</thead>

		<tbody>
		<?php foreach ($this->items as $i => $item) :
			$canCreate = $user->authorise('core.create',     'com_menus');
			$canEdit   = $user->authorise('core.edit',       'com_menus');
			$canChange = $user->authorise('core.edit.state', 'com_menus');
		?>
			<tr class="row<?php echo $i % 2; ?>">
				<td class="center">
					<?php echo JHtml::_('grid.id', $i, $item->id); ?>
				</td>
				<td>
					<a href="<?php echo JRoute::_('index.php?option=com_menus&view=items&menutype='.$item->menutype) ?> ">
						<?php echo $this->escape($item->title); ?></a>
					<p class="smallsub">(<span><?php echo JText::_('COM_MENUS_MENU_MENUTYPE_LABEL') ?></span>
						<?php if ($canEdit) : ?>
							<?php echo '<a href="'.JRoute::_('index.php?option=com_menus&task=menu.edit&id='.$item->id).' title='.$this->escape($item->description).'">'.
							$this->escape($item->menutype).'</a>'; ?>)
						<?php else : ?>
							<?php echo $this->escape($item->menutype)?>)
						<?php endif; ?>
					</p>
				</td>
				<td class="center btns">
					<a href="<?php echo JRoute::_('index.php?option=com_menus&view=items&menutype='.$item->menutype.'&filter_published=1');?>">
						<?php echo $item->count_published; ?></a>
				</td>
				<td class="center btns">
					<a href="<?php echo JRoute::_('index.php?option=com_menus&view=items&menutype='.$item->menutype.'&filter_published=0');?>">
						<?php echo $item->count_unpublished; ?></a>
				</td>
				<td class="center btns">
					<a href="<?php echo JRoute::_('index.php?option=com_menus&view=items&menutype='.$item->menutype.'&filter_published=-2');?>">
						<?php echo $item->count_trashed; ?></a>
				</td>
				<td class="left">
				<ul class="menu-module-list">
					<?php
					if (isset($this->modules[$item->menutype])) :
						foreach ($this->modules[$item->menutype] as &$module) :
						?>
						<li>
							<?php if ($canEdit) : ?>
								<a class="modal" href="<?php echo JRoute::_('index.php?option=com_modules&task=module.edit&id='.$module->id.'&return='.$return.'&tmpl=component&layout=modal');?>" rel="{handler: 'iframe', size: {x: 1024, y: 450}}"  title="<?php echo JText::_('COM_MENUS_EDIT_MODULE_SETTINGS');?>">
								<?php echo JText::sprintf('COM_MENUS_MODULE_ACCESS_POSITION', $this->escape($module->title), $this->escape($module->access_title), $this->escape($module->position)); ?></a>
							<?php else :?>
								<?php echo JText::sprintf('COM_MENUS_MODULE_ACCESS_POSITION', $this->escape($module->title), $this->escape($module->access_title), $this->escape($module->position)); ?>
							<?php endif; ?>
						</li>
						<?php
						endforeach;
					endif;
					?>
				</ul>
				</td>
				<td class="center">
					<?php echo $item->id; ?>
				</td>
			</tr>
			<?php endforeach; ?>
		</tbody>
	</table>

	<?php echo $this->pagination->getListFooter(); ?>

	<input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
	<?php echo JHtml::_('form.token'); ?>
	</div>
</form>
