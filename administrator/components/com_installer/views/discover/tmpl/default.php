<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_installer
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.multiselect');

$listOrder	= $this->escape($this->state->get('list.ordering'));
$listDirn	= $this->escape($this->state->get('list.direction'));
?>
<div id="installer-discover">
	<form action="<?php echo JRoute::_('index.php?option=com_installer&view=discover');?>" method="post" name="adminForm" id="adminForm">

	<?php if (!empty( $this->sidebar)) : ?>
    <div id="j-sidebar-container" class="span2">
      <?php echo $this->sidebar; ?>
    </div>
    <div id="j-main-container" class="span10">
  <?php else : ?>
    <div id="j-main-container">
  <?php endif;?>

  	<?php if ($this->showMessage) : ?>
  		<?php echo $this->loadTemplate('message'); ?>
  	<?php endif; ?>

  	<?php if ($this->ftp) : ?>
  		<?php echo $this->loadTemplate('ftp'); ?>
  	<?php endif; ?>

  	<!-- Begin Content -->
  		<?php if (count($this->items)) : ?>
  		<table class="table table-striped">
  			<thead>
  				<tr>
  					<th width="20"><?php echo JHtml::_('grid.checkall'); ?></th>
  					<th class="nowrap"><?php echo JHtml::_('grid.sort', 'COM_INSTALLER_HEADING_NAME', 'name', $listDirn, $listOrder); ?></th>
  					<th class="center"><?php echo JHtml::_('grid.sort', 'COM_INSTALLER_HEADING_TYPE', 'type', $listDirn, $listOrder); ?></th>
  					<th width="10%" class="center"><?php echo JText::_('JVERSION'); ?></th>
  					<th width="10%" class="center"><?php echo JText::_('JDATE'); ?></th>
  					<th><?php echo JHtml::_('grid.sort', 'COM_INSTALLER_HEADING_FOLDER', 'folder', $listDirn, $listOrder); ?></th>
  					<th><?php echo JHtml::_('grid.sort', 'COM_INSTALLER_HEADING_CLIENT', 'client_id', $listDirn, $listOrder); ?></th>
  					<th width="15%" class="center"><?php echo JText::_('JAUTHOR'); ?></th>
  					<th width="10"><?php echo JHtml::_('grid.sort', 'COM_INSTALLER_HEADING_ID', 'extension_id', $listDirn, $listOrder); ?></th>
  				</tr>
  			</thead>
  			<tfoot><tr><td colspan="10"><?php echo $this->pagination->getListFooter(); ?></td></tr>
  			</tfoot>
  			<tbody>
  			<?php foreach ($this->items as $i => $item) : ?>
  				<tr class="row<?php echo $i % 2;?>">
  					<td><?php echo JHtml::_('grid.id', $i, $item->extension_id); ?></td>
  					<td><span class="bold hasTooltip" title="<?php echo JHtml::tooltipText($item->name, $item->description, 0); ?>"><?php echo $item->name; ?></span></td>
  					<td class="center"><?php echo JText::_('COM_INSTALLER_TYPE_' . $item->type); ?></td>
  					<td class="center"><?php echo @$item->version != '' ? $item->version : '&#160;'; ?></td>
  					<td class="center"><?php echo @$item->creationDate != '' ? $item->creationDate : '&#160;'; ?></td>
  					<td class="center"><?php echo @$item->folder != '' ? $item->folder : JText::_('COM_INSTALLER_TYPE_NONAPPLICABLE'); ?></td>
  					<td class="center"><?php echo $item->client; ?></td>
  					<td class="center">
  						<span class="editlinktip hasTooltip" title="<?php echo JHtml::tooltipText(JText::_('COM_INSTALLER_AUTHOR_INFORMATION'), $item->author_info, 0); ?>">
  							<?php echo @$item->author != '' ? $item->author : '&#160;'; ?>
  						</span>
  					</td>
  					<td><?php echo $item->extension_id ?></td>
  				</tr>
  			<?php endforeach; ?>
  			</tbody>
  		</table>
  		<?php echo JText::_('COM_INSTALLER_MSG_DISCOVER_DESCRIPTION'); ?>
  		<?php else : ?>
  			<p>
  				<?php echo JText::_('COM_INSTALLER_MSG_DISCOVER_DESCRIPTION'); ?>
  			</p>
  			<div class="alert">
  				<?php echo JText::_('COM_INSTALLER_MSG_DISCOVER_NOEXTENSION'); ?>
  			</div>
  		<?php endif; ?>

  		<input type="hidden" name="task" value="" />
  		<input type="hidden" name="boxchecked" value="0" />
  		<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
  		<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
  		<?php echo JHtml::_('form.token'); ?>
		</div>
	</form>
</div>
