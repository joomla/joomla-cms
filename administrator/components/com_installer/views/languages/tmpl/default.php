<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_installer
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::_('behavior.multiselect');

$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
?>
<form
	action="<?php echo JRoute::_('index.php?option=com_installer&view=languages');?>"
	method="post" name="adminForm" id="adminForm">
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
			</div>
		</div>
		<!-- End Sidebar -->
		<!-- Begin Content -->
		<div class="span10">
			<?php if (count($this->items) || $this->escape($this->state->get('filter.search'))) : ?>
				<?php echo $this->loadTemplate('filter'); ?>
				<table class="table table-striped">
					<thead>
						<tr>
							<th width="20" class="hidden-phone">
								<input type="checkbox" name="checkall-toggle" value="" title="<?php echo JText::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)" />
							</th>
							<th class="nowrap">
								<?php echo JHtml::_('grid.sort', 'COM_INSTALLER_HEADING_NAME', 'name', $listDirn, $listOrder); ?>
							</th>
							<th width="10%" class="center">
								<?php echo JText::_('JVERSION'); ?>
							</th>
							<th class="hidden-phone">
								<?php echo JText::_('COM_INSTALLER_HEADING_TYPE'); ?>
							</th>
							<th width="35%" class="hidden-phone">
								<?php echo JText::_('COM_INSTALLER_HEADING_DETAILS_URL'); ?>
							</th>
							<th width="30" class="hidden-phone">
								<?php echo JHtml::_('grid.sort', 'COM_INSTALLER_HEADING_ID', 'update_id', $listDirn, $listOrder); ?>
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
						<?php foreach ($this->items as $i => $language) :
						?>
						<tr class="row<?php echo $i % 2; ?>">
							<td class="hidden-phone">
								<?php echo JHtml::_('grid.id', $i, $language->update_id, false, 'cid'); ?>
							</td>
							<td>
								<?php echo $language->name; ?>
							</td>
							<td class="center small">
								<?php echo $language->version; ?>
							</td>
							<td class="center small hidden-phone">
								<?php echo $language->type; ?>
							</td>
							<td class="small hidden-phone">
								<?php echo $language->detailsurl; ?>
							</td>
							<td class="small hidden-phone">
								<?php echo $language->update_id; ?>
							</td>
						</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			<?php else : ?>
				<div class="alert"><?php echo JText::_('COM_INSTALLER_MSG_LANGUAGES_NOLANGUAGES'); ?></div>
			<?php endif; ?>
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
