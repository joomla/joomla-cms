<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_menus
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Include the component HTML helpers.
JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');

JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.multiselect');
JHtml::_('formbehavior.chosen', 'select');

$uri = JUri::getInstance();
$return = base64_encode($uri);
$user = JFactory::getUser();
$userId = $user->get('id');
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn = $this->escape($this->state->get('list.direction'));
$modMenuId = (int) $this->get('ModMenuId');

JFactory::getDocument()->addScriptDeclaration("
		Joomla.submitbutton = function(task)
		{
			if (task != 'menus.delete' || confirm('" . JText::_('COM_MENUS_MENU_CONFIRM_DELETE', true) . "'))
			{
				Joomla.submitform(task);
			}
		};
");

$script = array();
$script[] = "jQuery(document).ready(function() {";

foreach ($this->items as $item) :
	if ($user->authorise('core.edit', 'com_menus')) :
		$script[] = '	function jSelectPosition_' . $item->id . '(name) {';
		$script[] = '		document.getElementById("' . $item->id . '").value = name;';
		$script[] = '		jQuery(".modal").modal("hide");';
		$script[] = '	};';
	endif;
endforeach;

$script[] = '	jQuery(".modal").on("hidden", function () {';
$script[] = '		setTimeout(function(){';
$script[] = '			window.parent.location.reload();';
$script[] = '		},1000);';
$script[] = '	});';
$script[] = "});";

JFactory::getDocument()->addScriptDeclaration(implode("\n", $script));
?>
<form action="<?php echo JRoute::_('index.php?option=com_menus&view=menus');?>" method="post" name="adminForm" id="adminForm">
<?php if (!empty( $this->sidebar)) : ?>
	<div id="j-sidebar-container" class="span2">
		<?php echo $this->sidebar; ?>
	</div>
	<div id="j-main-container" class="span10">
<?php else : ?>
	<div id="j-main-container">
<?php endif;?>
		<div id="filter-bar" class="btn-toolbar">
			<div class="filter-search btn-group pull-left">
				<label for="filter_search" class="element-invisible"><?php echo JText::_('COM_MENUS_MENU_SEARCH_FILTER');?></label>
				<input type="text" name="filter_search" id="filter_search" placeholder="<?php echo JText::_('JSEARCH_FILTER'); ?>" value="<?php echo $this->escape($this->state->get('filter.search')); ?>" class="hasTooltip" title="<?php echo JHtml::tooltipText('COM_MENUS_ITEMS_SEARCH_FILTER'); ?>" />
			</div>
			<div class="btn-group pull-left">
				<button type="submit" class="btn hasTooltip" title="<?php echo JHtml::tooltipText('JSEARCH_FILTER_SUBMIT'); ?>"><span class="icon-search"></span></button>
				<button type="button" class="btn hasTooltip" title="<?php echo JHtml::tooltipText('JSEARCH_FILTER_CLEAR'); ?>" onclick="document.getElementById('filter_search').value='';this.form.submit();"><span class="icon-remove"></span></button>
			</div>
			<div class="btn-group pull-right hidden-phone">
				<label for="limit" class="element-invisible"><?php echo JText::_('JFIELD_PLG_SEARCH_SEARCHLIMIT_DESC');?></label>
				<?php echo $this->pagination->getLimitBox(); ?>
			</div>
		</div>
		<div class="clearfix"> </div>
		<?php if (empty($this->items)) : ?>
			<div class="alert alert-no-items">
				<?php echo JText::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
			</div>
		<?php else : ?>
			<table class="table table-striped" id="menuList">
				<thead>
					<tr>
						<th width="1%">
							<?php echo JHtml::_('grid.checkall'); ?>
						</th>
						<th>
							<?php echo JHtml::_('grid.sort', 'JGLOBAL_TITLE', 'a.title', $listDirn, $listOrder); ?>
						</th>
						<th width="10%" class="nowrap center">
							<span class="icon-publish"></span>
							<span class="hidden-phone"><?php echo JText::_('COM_MENUS_HEADING_PUBLISHED_ITEMS'); ?></span>
						</th>
						<th width="10%" class="nowrap center">
							<span class="icon-unpublish"></span>
							<span class="hidden-phone"><?php echo JText::_('COM_MENUS_HEADING_UNPUBLISHED_ITEMS'); ?></span>
						</th>
						<th width="10%" class="nowrap center">
							<span class="icon-trash"></span>
							<span class="hidden-phone"><?php echo JText::_('COM_MENUS_HEADING_TRASHED_ITEMS'); ?></span>
						</th>
						<th width="20%" class="nowrap center">
							<span class="icon-cube"></span>
							<span class="hidden-phone"><?php echo JText::_('COM_MENUS_HEADING_LINKED_MODULES'); ?></span>

						</th>
						<th width="1%" class="nowrap">
							<?php echo JHtml::_('grid.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
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
							<a href="<?php echo JRoute::_('index.php?option=com_menus&view=items&menutype=' . $item->menutype) ?> ">
								<?php echo $this->escape($item->title); ?></a>
							<p class="small">(<span><?php echo JText::_('COM_MENUS_MENU_MENUTYPE_LABEL') ?></span>
								<?php if ($canEdit) : ?>
									<?php echo '<a href="' . JRoute::_('index.php?option=com_menus&task=menu.edit&id=' . $item->id) . ' title=' . $this->escape($item->description) . '">' .
									$this->escape($item->menutype) . '</a>'; ?>)
								<?php else : ?>
									<?php echo $this->escape($item->menutype)?>)
								<?php endif; ?>
							</p>
						</td>
						<td class="center btns">
							<a class="badge badge-success" href="<?php echo JRoute::_('index.php?option=com_menus&view=items&menutype=' . $item->menutype . '&filter[published]=1');?>">
								<?php echo $item->count_published; ?></a>
						</td>
						<td class="center btns">
							<a class="badge" href="<?php echo JRoute::_('index.php?option=com_menus&view=items&menutype=' . $item->menutype . '&filter[published]=0');?>">
								<?php echo $item->count_unpublished; ?></a>
						</td>
						<td class="center btns">
							<a class="badge badge-error" href="<?php echo JRoute::_('index.php?option=com_menus&view=items&menutype=' . $item->menutype . '&filter[published]=-2');?>">
								<?php echo $item->count_trashed; ?></a>
						</td>
						<td class="center">
							<?php if (isset($this->modules[$item->menutype])) : ?>
								<div class="btn-group">
									<a href="#" class="btn btn-small dropdown-toggle" data-toggle="dropdown">
										<?php echo JText::_('COM_MENUS_MODULES') ?>
										<span class="caret"></span>
									</a>
									<ul class="dropdown-menu">
										<?php foreach ($this->modules[$item->menutype] as &$module) : ?>
											<li>
												<?php if ($canEdit) : ?>
													<?php $link = JRoute::_('index.php?option=com_modules&task=module.edit&id=' . $module->id . '&return=' . $return . '&tmpl=component&layout=modal'); ?>
													<a href="#module<?php echo $module->id; ?>Modal" role="button" class="button" data-toggle="modal" title="<?php echo JText::_('COM_MENUS_EDIT_MODULE_SETTINGS');?>">
														<?php echo JText::sprintf('COM_MENUS_MODULE_ACCESS_POSITION', $this->escape($module->title), $this->escape($module->access_title), $this->escape($module->position)); ?></a>
												<?php else :?>
													<?php echo JText::sprintf('COM_MENUS_MODULE_ACCESS_POSITION', $this->escape($module->title), $this->escape($module->access_title), $this->escape($module->position)); ?>
												<?php endif; ?>
											</li>
										<?php endforeach; ?>
									</ul>
								 </div>
								<?php foreach ($this->modules[$item->menutype] as &$module) : ?>
									<?php if ($canEdit) : ?>
										<?php $link = JRoute::_('index.php?option=com_modules&task=module.edit&id=' . $module->id . '&return=' . $return . '&tmpl=component&layout=modal'); ?>
										<?php echo JHtml::_(
												'bootstrap.renderModal',
												'module' . $module->id . 'Modal',
												array(
													'url' => $link,
													'title' => JText::_('COM_MENUS_EDIT_MODULE_SETTINGS'),
													'height' => '300px',
													'width' => '800px',
													'footer' => '<button class="btn" data-dismiss="modal" aria-hidden="true">'
														. JText::_("JLIB_HTML_BEHAVIOR_CLOSE") . '</button>'
														. '<button class="btn btn-success" data-dismiss="modal" aria-hidden="true" onclick="jQuery(\'#module'
														. $module->id . 'Modal iframe\').contents().find(\'#saveBtn\').click();">'
														. JText::_("JSAVE") . '</button>'
												)
											); ?>
									<?php endif; ?>
								<?php endforeach; ?>
							<?php elseif ($modMenuId) : ?>
								<?php $link = JRoute::_('index.php?option=com_modules&task=module.add&eid=' . $modMenuId . '&params[menutype]=' . $item->menutype); ?>
								<a href="<?php echo $link; ?>"><?php echo JText::_('COM_MENUS_ADD_MENU_MODULE'); ?></a>
								<?php echo JHtml::_(
										'bootstrap.renderModal',
										'moduleModal',
										array(
											'url' => $link,
											'title' => JText::_('COM_MENUS_EDIT_MODULE_SETTINGS'),
											'height' => '500px',
											'width' => '800px',
											'footer' => '<button class="btn" data-dismiss="modal" aria-hidden="true">'
												. JText::_("JLIB_HTML_BEHAVIOR_CLOSE") . '</button>'
										)
									); ?>
							<?php endif; ?>
						</td>
						<td>
							<?php echo $item->id; ?>
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
