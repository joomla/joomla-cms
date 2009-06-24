<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	com_installer
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

$lists = $this->lists;
?>
<form action="index.php" method="post" name="adminForm">
	<?php if ($this->showMessage) : ?>
		<?php echo $this->loadTemplate('message'); ?>
	<?php endif; ?>

	<?php if ($this->ftp) : ?>
		<?php echo $this->loadTemplate('ftp'); ?>
	<?php endif; ?>

	<!-- TODO: connect me to something -->
	<table>
		<tr>
			<td width="100%">
				<?php echo JText::_('Filter'); ?>:
				<input type="text" name="filter" id="filter" value="<?php echo $lists['filter'];?>" class="text_area" title="<?php echo JText::_('Filter by name, element or enter extension ID');?>"/>
				<button onclick="this.form.submit();"><?php echo JText::_('Go'); ?></button>
				<!--<button onclick="document.getElementById('search').value='';this.form.getElementById('filter_sectionid').value='-1';this.form.getElementById('catid').value='0';this.form.getElementById('filter_authorid').value='0';this.form.getElementById('filter_state').value='';this.form.submit();"><?php echo JText::_('Reset'); ?></button>-->
				<input type="checkbox" name="hideprotected" <?php if ($lists['hideprotected']) echo 'CHECKED'; ?>/>Hide Protected Extensions
			</td>
			<td nowrap="nowrap">
				<?php
				echo $lists['type'];
				echo $lists['folder'];// group?
				echo $lists['clientid'];
				//echo $lists['state'];
				?>
			</td>

		</tr>
	</table>

	<?php if (count($this->items)) : ?>
	<table class="adminlist" cellspacing="1">
		<thead>
			<tr>
				<th class="title" width="10px"><?php echo JText::_('Num'); ?></th>
				<th class="title" nowrap="nowrap"><?php echo JText::_('Extension'); ?></th>
				<th class="title"><?php echo JText::_('Type') ?></th>
				<th class="title" width="5%" align="center"><?php echo JText::_('Enabled'); ?></th>
				<th class="title" width="10%" align="center"><?php echo JText::_('Version'); ?></th>
				<th class="title" width="10%"><?php echo JText::_('Date'); ?></th>
				<th class="title" ><?php echo JText::_('Folder') ?></th>
				<th class="title" ><?php echo JText::_('Client') ?></th>
				<th class="title" width="15%"><?php echo JText::_('Author'); ?></th>
				<th class="title" width="10px"><?php echo JText::_('Id') ?></th>
			</tr>
		</thead>
		<tfoot>
			<tr>
			<td colspan="10"><?php echo $this->pagination->getListFooter(); ?></td>
			</tr>
		</tfoot>
		<tbody>
		<?php for ($i=0, $n=count($this->items), $rc=0; $i < $n; $i++, $rc = 1 - $rc) : ?>
			<?php
				$this->loadItem($i);
				echo $this->loadTemplate('item');
			?>
		<?php endfor; ?>
		</tbody>
	</table>
	<?php else : ?>
		<?php echo JText::_('There are no extensions installed matching your query'); ?>
	<?php endif; ?>

	<input type="hidden" name="task" value="manage" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="option" value="com_installer" />
	<input type="hidden" name="type" value="manage" />
	<?php echo JHTML::_('form.token'); ?>
</form>