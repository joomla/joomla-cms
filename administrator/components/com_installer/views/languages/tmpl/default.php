<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_installer
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @since       2.5.7
 */

// No direct access
defined('_JEXEC') or die;

JHtml::_('behavior.multiselect');

$listOrder	= $this->escape($this->state->get('list.ordering'));
$listDirn	= $this->escape($this->state->get('list.direction'));
$ver = new JVersion;

?>
<form
	action="<?php echo JRoute::_('index.php?option=com_installer&view=languages');?>"
	method="post" name="adminForm" id="adminForm">

	<?php if (count($this->items) || $this->escape($this->state->get('filter.search'))) : ?>
	<?php echo $this->loadTemplate('filter'); ?>
	<div class="width-100 fltlft">
			<table class="adminlist">
				<thead>
					<tr>
						<th width="20">
							<input type="checkbox" name="checkall-toggle" value="" title="<?php echo JText::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)" />
						</th>
						<th class="nowrap">
							<?php echo JHtml::_('grid.sort', 'COM_INSTALLER_HEADING_NAME', 'name', $listDirn, $listOrder); ?>
						</th>
						<th width="10%" class="center">
							<?php echo JText::_('JVERSION'); ?>
						</th>
						<th>
							<?php echo JText::_('COM_INSTALLER_HEADING_TYPE'); ?>
						</th>
						<th width="35%">
							<?php echo JText::_('COM_INSTALLER_HEADING_DETAILS_URL'); ?>
						</th>
						<th width="30">
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
					<?php if (substr($language->version, 0, 3) == $ver->RELEASE) :
					?>
						<tr class="row<?php echo $i%2; ?>">
							<td>
								<?php echo JHtml::_('grid.id', $i, $language->update_id, false, 'cid'); ?>
							</td>
							<td>
								<?php echo $language->name; ?>
							</td>
							<td class="center">
								<?php echo $language->version; ?>
							</td>
							<td class="center">
								<?php echo JText::_('COM_INSTALLER_TYPE_' . strtoupper($language->type)); ?>
							</td>
							<td>
								<?php echo $language->detailsurl; ?>
							</td>
							<td>
								<?php echo $language->update_id; ?>
							</td>
						</tr>
					<?php endif; ?>
					<?php endforeach; ?>
				</tbody>
			</table>
	</div>
	<?php else : ?>
		<p class="nowarning"><?php echo JText::_('COM_INSTALLER_MSG_LANGUAGES_NOLANGUAGES'); ?></p>
	<?php endif; ?>
	<div>
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
		<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>
