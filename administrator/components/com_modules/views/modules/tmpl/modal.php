<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_modules
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::_('bootstrap.tooltip');
JHtml::_('formbehavior.chosen', 'select');

$client    = $this->state->get('filter.client_id') ? 'administrator' : 'site';
$user      = JFactory::getUser();
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
$trashed   = $this->state->get('filter.state') == -2 ? true : false;
$canOrder  = $user->authorise('core.edit.state', 'com_modules');
$saveOrder = $listOrder == 'ordering';
$editor    = JFactory::getApplication()->input->get('editor', '', 'cmd');

if ($saveOrder)
{
	$saveOrderingUrl = 'index.php?option=com_modules&task=modules.saveOrderAjax&tmpl=component';
	JHtml::_('sortablelist.sortable', 'moduleList', 'adminForm', strtolower($listDirn), $saveOrderingUrl);
}

$sortFields = $this->getSortFields();

JFactory::getDocument()->addScriptDeclaration('
		Joomla.orderTable = function()
		{
			table = document.getElementById("sortTable");
			direction = document.getElementById("directionTable");
			order = table.options[table.selectedIndex].value;
			if (order != "' . $listOrder . '")
			{
				dirn = "asc";
			}
			else
			{
				dirn = direction.options[direction.selectedIndex].value;
			}
			Joomla.tableOrdering(order, dirn, "");
		};

	        moduleIns = function(type, name) {
	            var extraVal ,fieldExtra = jQuery("#extra_class");
	            extraVal = (fieldExtra.length && fieldExtra.val().length) ? "," + fieldExtra.val() : "";
	            parent.window.jInsertEditorText("{loadmodule " + type + "," + name + extraVal + "}", "' . $editor . '");
	            parent.window.jModalClose();
	        }
	        modulePosIns = function(position) {
	            var extraVal ,fieldExtra = jQuery("#extra_class");
	            extraVal = (fieldExtra.length && fieldExtra.val().length) ? "," + fieldExtra.val() : "";
	            parent.window.jInsertEditorText("{loadposition " + position +  extraVal  + "}", "' . $editor . '");
	            parent.window.jModalClose();
	        }
');
?>
<div style="padding-top: 25px;"></div>
<div class="well">
	<div class="control-group">
		<div class="control-label">
			<label for="extra_class" class="hasTooltip" title="<?php echo JHtml::tooltipText('COM_MODULES_EXTRA_STYLE_DESC'); ?>" aria-invalid="false">
				<?php echo JText::_('COM_MODULES_EXTRA_STYLE_TITLE'); ?>
			</label>
		</div>
		<div class="controls">
			<input type="text" id="extra_class" value="" class="span12" size="45" maxlength="255" aria-invalid="false">
		</div>
	</div>
</div>
<form action="<?php echo JRoute::_('index.php?option=com_modules&view=modules&layout=modal&tmpl=component&' . JSession::getFormToken() . '=1');?>"
	method="post" name="adminForm" id="adminForm">
	<div id="j-main-container">
		<div id="filter-bar" class="btn-toolbar">
			<div class="filter-search btn-group pull-left">
				<label for="filter_search" class="element-invisible"><?php echo JText::_('JSEARCH_FILTER_LABEL');?></label>
				<input type="text" name="filter_search" id="filter_search" placeholder="<?php echo JText::_('JSEARCH_FILTER'); ?>" value="<?php echo $this->escape($this->state->get('filter.search')); ?>" class="hasTooltip" title="<?php echo JHtml::tooltipText('COM_MODULES_MODULES_FILTER_SEARCH_DESC'); ?>" />
			</div>
			<div class="btn-group pull-left">
				<button type="submit" class="btn hasTooltip" title="<?php echo JHtml::tooltipText('JSEARCH_FILTER_SUBMIT'); ?>"><span class="icon-search"></span></button>
				<button type="button" class="btn hasTooltip" title="<?php echo JHtml::tooltipText('JSEARCH_FILTER_CLEAR'); ?>" onclick="document.getElementById('filter_search').value='';this.form.submit();"><span class="icon-remove"></span></button>
			</div>
			<div class="btn-group pull-right hidden-phone">
				<label for="limit" class="element-invisible"><?php echo JText::_('JFIELD_PLG_SEARCH_SEARCHLIMIT_DESC');?></label>
				<?php echo $this->pagination->getLimitBox(); ?>
			</div>
			<div class="btn-group pull-right hidden-phone">
				<label for="directionTable" class="element-invisible"><?php echo JText::_('JFIELD_ORDERING_DESC');?></label>
				<select name="directionTable" id="directionTable" class="input-medium" onchange="Joomla.orderTable()">
					<option value=""><?php echo JText::_('JFIELD_ORDERING_DESC');?></option>
					<option value="asc" <?php if ($listDirn == 'asc') echo 'selected="selected"'; ?>><?php echo JText::_('JGLOBAL_ORDER_ASCENDING');?></option>
					<option value="desc" <?php if ($listDirn == 'desc') echo 'selected="selected"'; ?>><?php echo JText::_('JGLOBAL_ORDER_DESCENDING');?></option>
				</select>
			</div>
			<div class="btn-group pull-right hidden-phone">
				<label for="sortTable" class="element-invisible"><?php echo JText::_('JGLOBAL_SORT_BY');?></label>
				<select name="sortTable" id="sortTable" class="input-medium" onchange="Joomla.orderTable()">
					<option value=""><?php echo JText::_('JGLOBAL_SORT_BY');?></option>
					<?php echo JHtml::_('select.options', $sortFields, 'value', 'text', $listOrder);?>
				</select>
			</div>
		</div>
		<div class="clearfix"> </div>
		<?php if (empty($this->items)) : ?>
			<div class="alert alert-no-items">
				<?php echo JText::_('COM_MODULES_MSG_MANAGE_NO_MODULES'); ?>
			</div>
		<?php else : ?>
			<table class="table table-striped" id="moduleList">
				<thead>
				<tr>
					<th class="title">
						<?php echo JHtml::_('grid.sort', 'JGLOBAL_TITLE', 'a.title', $listDirn, $listOrder); ?>
					</th>
					<th width="15%" class="nowrap hidden-phone">
						<?php echo JHtml::_('grid.sort', 'COM_MODULES_HEADING_POSITION', 'position', $listDirn, $listOrder); ?>
					</th>
					<th width="10%" class="nowrap hidden-phone" >
						<?php echo JHtml::_('grid.sort', 'COM_MODULES_HEADING_MODULE', 'name', $listDirn, $listOrder); ?>
					</th>
					<th width="10%" class="nowrap hidden-phone">
						<?php echo JHtml::_('grid.sort', 'COM_MODULES_HEADING_PAGES', 'pages', $listDirn, $listOrder); ?>
					</th>
					<th width="10%" class="nowrap hidden-phone">
						<?php echo JHtml::_('grid.sort', 'JGRID_HEADING_ACCESS', 'a.access', $listDirn, $listOrder); ?>
					</th>
					<th width="10%" class="nowrap hidden-phone">
						<?php echo JHtml::_('grid.sort', 'JGRID_HEADING_LANGUAGE', 'language_title', $listDirn, $listOrder); ?>
					</th>
					<th width="1%" class="nowrap hidden-phone">
						<?php echo JHtml::_('grid.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
					</th>
				</tr>
				</thead>
				<tfoot>
				<tr>
					<td colspan="10">
						<?php echo $this->pagination->getListFooter(); ?>
					</td>
				</tr>
				</tfoot>
				<tbody>
				<?php foreach ($this->items as $i => $item) :
					$ordering   = ($listOrder == 'ordering');
					?>
					<tr class="row<?php echo $i % 2; ?>" sortable-group-id="<?php echo $item->position?>">
						<td class="has-context">
								<a class="btn btn-small btn-block btn-success" href="#" onclick="moduleIns('<?php echo $this->escape($item->module); ?>', '<?php echo $this->escape($item->title); ?>')">
									<?php echo $this->escape($item->title); ?></a>
						</td>
						<td class="small hidden-phone">
							<?php if ($item->position) : ?>
							<a class="btn btn-small btn-block btn-warning" href="#" onclick="modulePosIns('<?php echo $item->position; ?>')">
								<?php echo $item->position; ?>
							</a>
							<?php else : ?>
								<span class="label">
								<?php echo JText::_('JNONE'); ?>
							</span>
							<?php endif; ?>
						</td>
						<td class="small hidden-phone">
							<?php echo $item->name;?>
						</td>
						<td class="small hidden-phone">
							<?php echo $item->pages; ?>
						</td>

						<td class="small hidden-phone">
							<?php echo $this->escape($item->access_level); ?>
						</td>
						<td class="small hidden-phone">
							<?php if ($item->language == ''):?>
								<?php echo JText::_('JDEFAULT'); ?>
							<?php elseif ($item->language == '*'):?>
								<?php echo JText::alt('JALL', 'language'); ?>
							<?php else:?>
								<?php echo $item->language_title ? JHtml::_('image', 'mod_languages/' . $item->language_image . '.gif', $item->language_title, array('title' => $item->language_title), true) . '&nbsp;' . $this->escape($item->language_title) : JText::_('JUNDEFINED'); ?>
							<?php endif;?>
						</td>
						<td class="hidden-phone">
							<?php echo (int) $item->id; ?>
						</td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
		<?php endif;?>

		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
		<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>
