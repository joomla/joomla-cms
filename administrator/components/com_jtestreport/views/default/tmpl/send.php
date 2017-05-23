<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_joomlaupdate
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

?>
<h1><?php echo JText::_('COM_JTESTREPORT_REPORT_SENT');?></h1>

<h2><?php echo JText::_('COM_JTESTREPORT_SITE_ENV');?></h2>

<table class="table table-striped">
	<thead>
	<tr>
		<th width="25%">
			<?php echo JText::_('COM_JTESTREPORT_NAME');?>
		</th>
		<th>
			<?php echo JText::_('COM_JTESTREPORT_VALUE');?>
		</th>
	</tr>
	</thead>
	<tbody>
	<?php $items = $this->data['env'];?>
	<?php foreach (array_keys($items) as $key) : ?>
		<tr>
			<td>
				<?php echo $key;?>
			</td>
			<td>
				<?php echo $items[$key];?>
			</td>
		</tr>
	<?php endforeach;?>
	</tbody>
</table>

<h2><?php echo JText::_('COM_JTESTREPORT_SITE_USER');?></h2>

<table class="table table-striped">
	<thead>
	<tr>
		<th width="25%">
			<?php echo JText::_('COM_JTESTREPORT_NAME');?>
		</th>
		<th>
			<?php echo JText::_('COM_JTESTREPORT_VALUE');?>
		</th>
	</tr>
	</thead>
	<tbody>
	<?php $items = $this->data['user'];?>
	<?php foreach (array_keys($items) as $key) : ?>
		<tr>
			<td>
				<?php echo $key;?>
			</td>
			<td>
				<?php echo $items[$key];?>
			</td>
		</tr>
	<?php endforeach;?>
	</tbody>
</table>


<h2><?php echo JText::_('COM_JTESTREPORT_ENABLED_EXTENSIONS');?></h2>
	<table class="table table-striped">
		<thead>
		<tr>
			<th width="25%">
				<?php echo JText::_('COM_JTESTREPORT_NAME');?>
			</th>
			<th>
				<?php echo JText::_('COM_JTESTREPORT_TYPE');?>
			</th>
			<th>
				<?php echo JText::_('COM_JTESTREPORT_VERSION');?>
			</th>
			<th>
				<?php echo JText::_('COM_JTESTREPORT_ENABLED');?>
			</th>
			<th>
				<?php echo JText::_('COM_JTESTREPORT_TESTED');?>
			</th>
		</tr>
		</thead>
		<tbody>
		<?php foreach ($this->data['extensions'] as $item) : ?>

			<tr>
				<td>
					<?php echo JText::_($item->name);?>
				</td>
				<td>
					<?php echo $item->type;?>
				</td>
				<td>
					<?php echo $item->version;?>
				</td>
				<td>
					<?php echo $item->enabled == 1 ? JText::_('JYES') : JText::_('JNO');?>
				</td>
				<td>
					<?php echo $item->tested == 1 ? JText::_('JYES') : JText::_('JNO');?>
				</td>
			</tr>

		<?php endforeach;?>
		</tbody>
	</table>

