<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_templates
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Include the component HTML helpers.
JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.multiselect');

$user		= JFactory::getUser();
$listOrder	= $this->escape($this->state->get('list.ordering'));
$listDirn	= $this->escape($this->state->get('list.direction'));
?>
<form action="<?php echo JRoute::_('index.php?option=com_templates&view=styles'); ?>" method="post" name="adminForm" id="adminForm">
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
				<select name="filter_template" class="span12 small" onchange="this.form.submit()">
					<option value="0"><?php echo JText::_('COM_TEMPLATES_FILTER_TEMPLATE'); ?></option>
					<?php echo JHtml::_('select.options', TemplatesHelper::getTemplateOptions($this->state->get('filter.client_id')), 'value', 'text', $this->state->get('filter.template'));?>
				</select>
				<hr class="hr-condensed" />
				<select name="filter_client_id" class="span12 small" onchange="this.form.submit()">
					<option value="*"><?php echo JText::_('JGLOBAL_FILTER_CLIENT'); ?></option>
					<?php echo JHtml::_('select.options', TemplatesHelper::getClientOptions(), 'value', 'text', $this->state->get('filter.client_id'));?>
				</select>
			</div>
		</div>
	</div>
	<!-- End Sidebar -->
	<!-- Begin Content -->
	<div class="span10">
		<div id="filter-bar" class="btn-toolbar">
			<div class="filter-search btn-group pull-left">
				<input type="text" name="filter_search" id="filter_search" placeholder="<?php echo JText::_('JSEARCH_FILTER_LABEL'); ?>" value="<?php echo $this->escape($this->state->get('filter.search')); ?>" title="<?php echo JText::_('COM_TEMPLATES_STYLES_FILTER_SEARCH_DESC'); ?>" />
			</div>
			<div class="btn-group pull-left">
				<button class="btn" rel="tooltip" type="submit" title="<?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?>"><i class="icon-search"></i></button>
				<button class="btn" rel="tooltip" type="button" title="<?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?>" onclick="document.id('filter_search').value='';this.form.submit();"><i class="icon-remove"></i></button>
			</div>
		</div>
		<div class="clear"> </div>

		<table class="table table-striped">
			<thead>
				<tr>
					<th width="5">
						&#160;
					</th>
					<th>
						<?php echo JHtml::_('grid.sort', 'COM_TEMPLATES_HEADING_STYLE', 'a.title', $listDirn, $listOrder); ?>
					</th>
					<th width="10%">
						<?php echo JHtml::_('grid.sort', 'JCLIENT', 'a.client_id', $listDirn, $listOrder); ?>
					</th>
					<th>
						<?php echo JHtml::_('grid.sort', 'COM_TEMPLATES_HEADING_TEMPLATE', 'a.template', $listDirn, $listOrder); ?>
					</th>
					<th width="5%">
						<?php echo JHtml::_('grid.sort', 'COM_TEMPLATES_HEADING_DEFAULT', 'a.home', $listDirn, $listOrder); ?>
					</th>
					<th width="5%">
						<?php echo JText::_('COM_TEMPLATES_HEADING_ASSIGNED'); ?>
					</th>
					<th width="1%" class="nowrap">
						<?php echo JHtml::_('grid.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
					</th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<td colspan="8">
						<?php echo $this->pagination->getListFooter(); ?>
					</td>
				</tr>
			</tfoot>
			<tbody>
				<?php foreach ($this->items as $i => $item) :
					$canCreate	= $user->authorise('core.create',		'com_templates');
					$canEdit	= $user->authorise('core.edit',			'com_templates');
					$canChange	= $user->authorise('core.edit.state',	'com_templates');
				?>
				<tr class="row<?php echo $i % 2; ?>">
					<td width="1%" class="center">
						<?php echo JHtml::_('grid.id', $i, $item->id); ?>
					</td>

					<td>
						<?php if ($this->preview && $item->client_id == '0'): ?>
							<a target="_blank"href="<?php echo JURI::root().'index.php?tp=1&templateStyle='.(int) $item->id ?>"  class="jgrid" title="<?php echo  htmlspecialchars(JText::_('COM_TEMPLATES_TEMPLATE_PREVIEW')); ?>::<?php echo htmlspecialchars($item->title);?>" >
							<i class="icon-eye-open tip" rel="tooltip" data-original-title="<?php echo  htmlspecialchars(JText::_('COM_TEMPLATES_TEMPLATE_PREVIEW')); ?>" ></i></a>
						<?php elseif ($item->client_id == '1'): ?>
							<span class="disabled"><i class="icon-eye-close tip" rel="tooltip" data-original-title="<?php echo  htmlspecialchars(JText::_('COM_TEMPLATES_TEMPLATE_NO_PREVIEW_ADMIN')); ?>" ></i></span>
						<?php else: ?>
							<span class="disabled"><i class="icon-eye-close tip" rel="tooltip" data-original-title="<?php echo  htmlspecialchars(JText::_('COM_TEMPLATES_TEMPLATE_NO_PREVIEW')); ?>" ></i></span>
						<?php endif; ?>
						<?php if ($canEdit) : ?>
						<a href="<?php echo JRoute::_('index.php?option=com_templates&task=style.edit&id='.(int) $item->id); ?>">
							<?php echo $this->escape($item->title);?></a>
						<?php else : ?>
							<?php echo $this->escape($item->title);?>
						<?php endif; ?>
					</td>
					<td class="small">
						<?php echo $item->client_id == 0 ? JText::_('JSITE') : JText::_('JADMINISTRATOR'); ?>
					</td>
					<td>
						<label for="cb<?php echo $i;?>" class="small">
							<a href="<?php echo JRoute::_('index.php?option=com_templates&view=template&id='.(int) $item->e_id); ?>  ">
								<?php echo ucfirst($this->escape($item->template));?>
							</a>
						</label>
					</td>
					<td class="center">
						<?php if ($item->home == '0' || $item->home == '1'):?>
							<?php echo JHtml::_('jgrid.isdefault', $item->home != '0', $i, 'styles.', $canChange && $item->home != '1');?>
						<?php elseif ($canChange):?>
							<a href="<?php echo JRoute::_('index.php?option=com_templates&task=styles.unsetDefault&cid[]='.$item->id.'&'.JSession::getFormToken().'=1');?>">
								<?php echo JHtml::_('image', 'mod_languages/'.$item->image.'.gif', $item->language_title, array('title' => JText::sprintf('COM_TEMPLATES_GRID_UNSET_LANGUAGE', $item->language_title)), true);?>
							</a>
						<?php else:?>
							<?php echo JHtml::_('image', 'mod_languages/'.$item->image.'.gif', $item->language_title, array('title' => $item->language_title), true);?>
						<?php endif;?>
					</td>
					<td class="center">
						<?php if ($item->assigned > 0) : ?>
								<i class="icon-ok tip" rel="tooltip" title="<?php echo JText::plural('COM_TEMPLATES_ASSIGNED', $item->assigned)?>"></i>
						<?php else : ?>
								&#160;
						<?php endif; ?>
					</td>
					<td class="center">
						<?php echo (int) $item->id; ?>
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
