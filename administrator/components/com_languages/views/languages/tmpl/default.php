<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_languages
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.multiselect');

$user		= JFactory::getUser();
$userId		= $user->get('id');
$n			= count($this->items);
$listOrder	= $this->escape($this->state->get('list.ordering'));
$listDirn	= $this->escape($this->state->get('list.direction'));
$canOrder	= $user->authorise('core.edit.state', 'com_languages');
$saveOrder	= $listOrder == 'a.ordering';
?>

<form action="<?php echo JRoute::_('index.php?option=com_languages&view=languages'); ?>" method="post" name="adminForm" id="adminForm">
	<div class="row-fluid">
	<!-- Begin Sidebar -->
	<div id="sidebar" class="span2">
		<div class="sidebar-nav">
			<?php
				// Display the submenu position modules
				$this->modules = JModuleHelper::getModules('submenu');
				foreach ($this->modules as $module) {
					$output = JModuleHelper::renderModule($module);
					$params = new JRegistry;
					$params->loadString($module->params);
					echo $output;
				}
			?>
			<hr />
			<div class="filter-select">
				<h4 class="page-header"><?php echo JText::_('JSEARCH_FILTER_LABEL');?></h4>
				<select name="filter_published" class="span12 small" onchange="this.form.submit()">
					<option value=""><?php echo JText::_('JOPTION_SELECT_PUBLISHED');?></option>
					<?php echo JHtml::_('select.options', JHtml::_('languages.publishedOptions'), 'value', 'text', $this->state->get('filter.published'), true);?>
				</select>
				<hr class="hr-condensed" />
				<select name="filter_access" class="span12 small" onchange="this.form.submit()">
					<option value=""><?php echo JText::_('JOPTION_SELECT_ACCESS');?></option>
				<?php echo JHtml::_('select.options', JHtml::_('access.assetgroups'), 'value', 'text', $this->state->get('filter.access'));?>
				</select>
			</div>
		</div>
	</div>
	<!-- End Sidebar -->
	<!-- Begin Content -->
	<div class="span10">
		<div id="filter-bar" class="btn-toolbar">
			<div class="filter-search btn-group pull-left">
				<input type="text" name="filter_search" id="filter_search" placeholder="<?php echo JText::_('COM_LANGUAGES_SEARCH_IN_TITLE'); ?>" value="<?php echo $this->escape($this->state->get('filter.search')); ?>" title="<?php echo JText::_('COM_LANGUAGES_SEARCH_IN_TITLE'); ?>" />
			</div>
			<div class="btn-group pull-left">
				<button class="btn" rel="tooltip" type="submit" title="<?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?>"><i class="icon-search"></i></button>
				<button class="btn" rel="tooltip" type="button" title="<?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?>" onclick="document.id('filter_search').value='';this.form.submit();"><i class="icon-remove"></i></button>
			</div>
		</div>
		<div class="clearfix"> </div>
		<table class="table table-striped">
			<thead>
				<tr>
					<th width="5">
						<?php echo JText::_('JGRID_HEADING_ROW_NUMBER'); ?>
					</th>
					<th width="20">
						<input type="checkbox" name="checkall-toggle" value="" title="<?php echo JText::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)" />
					</th>
					<th class="title">
						<?php echo JHtml::_('grid.sort',  'JGLOBAL_TITLE', 'a.title', $listDirn, $listOrder); ?>
					</th>
					<th class="title">
						<?php echo JHtml::_('grid.sort',  'COM_LANGUAGES_HEADING_TITLE_NATIVE', 'a.title_native', $listDirn, $listOrder); ?>
					</th>
					<th width="5%" class="nowrap">
						<?php echo JHtml::_('grid.sort',  'COM_LANGUAGES_FIELD_LANG_TAG_LABEL', 'a.lang_code', $listDirn, $listOrder); ?>
					</th>
					<th width="5%" class="nowrap">
						<?php echo JHtml::_('grid.sort',  'COM_LANGUAGES_FIELD_LANG_CODE_LABEL', 'a.sef', $listDirn, $listOrder); ?>
					</th>
					<th width="5%" class="nowrap">
						<?php echo JHtml::_('grid.sort',  'COM_LANGUAGES_HEADING_LANG_IMAGE', 'a.image', $listDirn, $listOrder); ?>
					</th>
					<th width="10%">
						<?php echo JHtml::_('grid.sort', 'JGRID_HEADING_ORDERING', 'a.ordering', $listDirn, $listOrder); ?>
						<?php if ($canOrder && $saveOrder) :?>
							<?php echo JHtml::_('grid.order',  $this->items, 'filesave.png', 'languages.saveorder'); ?>
						<?php endif; ?>
					</th>
					<th width="5%">
						<?php echo JHtml::_('grid.sort', 'JGRID_HEADING_ACCESS', 'a.access', $listDirn, $listOrder); ?>
					</th>
					<th width="5%" class="nowrap">
						<?php echo JHtml::_('grid.sort',  'COM_LANGUAGES_HOMEPAGE', '', $listDirn, $listOrder); ?>
					</th>
					<th width="1%" class="nowrap">
						<?php echo JHtml::_('grid.sort',  'JGRID_HEADING_ID', 'a.lang_id', $listDirn, $listOrder); ?>
					</th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<td colspan="11">
						<?php echo $this->pagination->getListFooter(); ?>
					</td>
				</tr>
			</tfoot>
			<tbody>
			<?php
			foreach ($this->items as $i => $item) :
				$ordering	= ($listOrder == 'a.ordering');
				$canCreate	= $user->authorise('core.create',		'com_languages');
				$canEdit	= $user->authorise('core.edit',			'com_languages');
				$canChange	= $user->authorise('core.edit.state',	'com_languages');
			?>
				<tr class="row<?php echo $i % 2; ?>">
					<td>
						<?php echo $this->pagination->getRowOffset($i); ?>
					</td>
					<td>
						<?php echo JHtml::_('grid.id', $i, $item->lang_id); ?>
					</td>
					<td>
						<?php echo JHtml::_('jgrid.published', $item->published, $i, 'languages.', $canChange);?>
						<span class="editlinktip hasTip" title="<?php echo JText::_('JGLOBAL_EDIT_ITEM');?>::<?php echo $this->escape($item->title); ?>">
						<?php if ($canEdit) : ?>
							<a href="<?php echo JRoute::_('index.php?option=com_languages&task=language.edit&lang_id='.(int) $item->lang_id); ?>">
								<?php echo $this->escape($item->title); ?></a>
						<?php else : ?>
								<?php echo $this->escape($item->title); ?>
						<?php endif; ?>
						</span>
					</td>
					<td class="small">
						<?php echo $this->escape($item->title_native); ?>
					</td>
					<td class="small">
						<?php echo $this->escape($item->lang_code); ?>
					</td>
					<td class="small">
						<?php echo $this->escape($item->sef); ?>
					</td>
					<td class="small">
						<?php echo $this->escape($item->image); ?>
					</td>
					<td class="order">
						<?php if ($canChange) : ?>
							<div class="input-prepend">
								<?php if ($saveOrder) :?>
									<?php if ($listDirn == 'asc') : ?>
										<span class="add-on"><?php echo $this->pagination->orderUpIcon($i, true, 'languages.orderup', 'JLIB_HTML_MOVE_UP', $ordering); ?></span><span class="add-on"><?php echo $this->pagination->orderDownIcon($i, $this->pagination->total, true, 'languages.orderdown', 'JLIB_HTML_MOVE_DOWN', $ordering); ?></span>
									<?php elseif ($listDirn == 'desc') : ?>
										<span class="add-on"><?php echo $this->pagination->orderUpIcon($i, true, 'languages.orderdown', 'JLIB_HTML_MOVE_UP', $ordering); ?></span><span class="add-on"><?php echo $this->pagination->orderDownIcon($i, $this->pagination->total, true, 'languages.orderup', 'JLIB_HTML_MOVE_DOWN', $ordering); ?></span>
									<?php endif; ?>
								<?php endif; ?>
								<?php $disabled = $saveOrder ?  '' : 'disabled="disabled"'; ?>
								<?php if(!$disabled = $saveOrder) : echo "<span class=\"add-on tip\" title=\"".JText::_('JDISABLED')."\"><i class=\"icon-ban-circle\"></i></span>"; endif;?><input type="text" name="order[]" size="5" value="<?php echo $item->ordering;?>" <?php echo $disabled ?> class="width-20 text-area-order" />
							</div>
						<?php else : ?>
							<?php echo $item->ordering; ?>
						<?php endif; ?>
					</td>
					<td class="center">
						<?php echo $this->escape($item->access_level); ?>
					</td>
					<td class="small">
						<?php if ($item->home == '1') : ?>
							<?php echo JText::_('JYES');?>
						<?php else:?>
							<?php echo JText::_('JNO');?>
						<?php endif;?>
					</td>
					<td class="center">
						<?php echo $this->escape($item->lang_id); ?>
					</td>
				</tr>
				<?php endforeach; ?>
			</tbody>
		</table>

		<div>
			<input type="hidden" name="task" value="" />
			<input type="hidden" name="boxchecked" value="0" />
			<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
			<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
			<?php echo JHtml::_('form.token'); ?>
		</div>
		</div>
		<!-- End Content -->
	</div>
</form>
