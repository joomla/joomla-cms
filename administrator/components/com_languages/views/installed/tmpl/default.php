<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	com_languages
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Add specific helper files for html generation
JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');
$user	= JFactory::getUser();
$userId	= $user->get('id');
?>
<form action="<?php echo JRoute::_('index.php?option=com_languages&view=installed'); ?>" method="post" id="adminForm" name="adminForm">

	<?php if ($this->ftp): ?>
		<?php echo $this->loadTemplate('ftp');?>
	<?php endif; ?>

	<fieldset id="filter-bar">
		<div class="filter-select fltrt">
			<label for="filter_client_id">
				<?php echo JText::_('COM_LANGUAGES_FILTER_CLIENT_LABEL'); ?>
			</label>
			<select id="filter_client_id" name="filter_client_id" class="inputbox" onchange="this.form.submit()">
				<?php echo JHtml::_('select.options', JHtml::_('languages.clients'), 'value', 'text', $this->state->get('filter.client_id'));?>
			</select>
		</div>
	</fieldset>

	<table class="adminlist">
		<thead>
			<tr>
				<th width="20">
					<?php echo JText::_('COM_LANGUAGES_HEADING_NUM'); ?>
				</th>
				<th width="30">
					&#160;
				</th>
				<th width="25%" class="title">
					<?php echo JText::_('COM_LANGUAGES_HEADING_LANGUAGE'); ?>
				</th>
				<th width="5%">
					<?php echo JText::_('COM_LANGUAGES_HEADING_DEFAULT'); ?>
				</th>
				<th width="10%">
					<?php echo JText::_('JVERSION'); ?>
				</th>
				<th width="10%">
					<?php echo JText::_('JDATE'); ?>
				</th>
				<th width="20%">
					<?php echo JText::_('JAUTHOR'); ?>
				</th>
				<th width="25%">
					<?php echo JText::_('COM_LANGUAGES_HEADING_AUTHOR_EMAIL'); ?>
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
		<?php foreach ($this->rows as $i => $row) :
			$canCreate	= $user->authorise('core.create',		'com_languages');
			$canEdit	= $user->authorise('core.edit',			'com_languages');
			$canChange	= $user->authorise('core.edit.state',	'com_languages');
		?>
			<tr class="row<?php echo $i % 2; ?>">
				<td width="20">
					<?php echo $this->pagination->getRowOffset($i); ?>
				</td>
				<td width="20">
					<?php echo JHtml::_('languages.id',$i,$row->language);?>
				</td>
				<td width="25%">
					<?php echo $this->escape($row->name);?>
				</td>
				<td width="5%" align="center">
					<?php echo JHtml::_('jgrid.isdefault', $row->published, $i, 'installed.',  !$row->published && $canChange);?>
				</td>
				<td align="center">
					<?php echo $this->escape($row->version); ?>
				</td>
				<td align="center">
					<?php echo $this->escape($row->creationDate); ?>
				</td>
				<td align="center">
					<?php echo $this->escape($row->author); ?>
				</td>
				<td align="center">
					<?php echo $this->escape($row->authorEmail); ?>
				</td>
			</tr>
		<?php endforeach;?>
		</tbody>
	</table>

	<div>
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>
