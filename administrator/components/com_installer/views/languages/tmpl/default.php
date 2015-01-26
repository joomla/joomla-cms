<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_installer
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::_('behavior.multiselect');
JHtml::_('formbehavior.chosen', 'select');
JHtml::_('bootstrap.tooltip');

$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));

$version = new JVersion;

?>
<div id="installer-languages" class="clearfix">
	<form action="<?php echo JRoute::_('index.php?option=com_installer&view=languages');?>" method="post" name="adminForm" id="adminForm">
	<?php if (!empty( $this->sidebar)) : ?>
		<div id="j-sidebar-container" class="span2">
			<?php echo $this->sidebar; ?>
		</div>
		<div id="j-main-container" class="span10">
	<?php else : ?>
		<div id="j-main-container">
	<?php endif;?>
	<?php if (count($this->items) || $this->escape($this->state->get('filter.search'))) : ?>
		<?php echo $this->loadTemplate('filter'); ?>
		<?php if (empty($this->items)) : ?>
			<div class="alert alert-no-items">
				<?php echo JText::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
			</div>
		<?php else : ?>
			<table class="table table-striped">
				<thead>
					<tr>
						<th width="20" class="nowrap center">
							<?php echo JHtml::_('grid.checkall'); ?>
						</th>
						<th class="nowrap">
							<?php echo JHtml::_('grid.sort', 'COM_INSTALLER_HEADING_NAME', 'name', $listDirn, $listOrder); ?>
						</th>
						<th width="10%" class="center">
							<?php echo JText::_('JVERSION'); ?>
						</th>
						<th class="center nowrap hidden-phone">
							<?php echo JText::_('COM_INSTALLER_HEADING_TYPE'); ?>
						</th>
						<th width="35%" class="nowrap hidden-phone">
							<?php echo JText::_('COM_INSTALLER_HEADING_DETAILS_URL'); ?>
						</th>
						<th width="30" class="nowrap hidden-phone">
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
				<?php foreach ($this->items as $i => $language) : ?>
					<tr class="row<?php echo $i % 2; ?>">
						<td class="center">
							<?php echo JHtml::_('grid.id', $i, $language->update_id, false, 'cid'); ?>
						</td>
						<td>
							<label for="cb<?php echo $i; ?>">
								<?php echo $language->name; ?>

								<?php // Display a Note if language pack version is not equal to Joomla version ?>
								<?php if (substr($language->version, 0, 3) != $version->RELEASE
									|| substr($language->version, 0, 5) != $version->RELEASE . "." . $version->DEV_LEVEL) : ?>
									<div class="small"><?php echo JText::_('JGLOBAL_LANGUAGE_VERSION_NOT_PLATFORM'); ?></div>
								<?php endif; ?>
							</label>
						</td>
						<td class="center small">
							<?php echo $language->version; ?>
						</td>
						<td class="center small hidden-phone">
							<?php echo JText::_('COM_INSTALLER_TYPE_' . strtoupper($language->type)); ?>
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
		<?php endif; ?>
	<?php else : ?>
		<div class="alert"><?php echo JText::_('COM_INSTALLER_MSG_LANGUAGES_NOLANGUAGES'); ?></div>
	<?php endif; ?>

			<input type="hidden" name="task" value="" />
			<input type="hidden" name="boxchecked" value="0" />
			<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
			<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
			<?php echo JHtml::_('form.token'); ?>
		</div>
	</form>
</div>
