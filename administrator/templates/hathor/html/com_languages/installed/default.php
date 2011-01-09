<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	templates.hathor
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 * @since		1.6
 */

defined('_JEXEC') or die;

// Add specific helper files for html generation
JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');
$user	= JFactory::getUser();
$userId	= $user->get('id');
?>
<form action="<?php echo JRoute::_('index.php?option=com_languages&view=installed'); ?>" method="post" name="adminForm" id="adminForm">

	<?php if ($this->ftp): ?>
		<?php echo $this->loadTemplate('ftp');?>
	<?php endif; ?>

	<fieldset id="filter-bar">
	<legend class="element-invisible"><?php echo JText::_('JSEARCH_FILTER_LABEL'); ?></legend>
		<div class="filter-select">
			<label class="filter-search-lbl" for="filter_client_id">
				<?php echo JText::_('COM_LANGUAGES_FILTER_CLIENT_LABEL'); ?>
			</label>
			<select id="filter_client_id" name="filter_client_id" class="inputbox">
				<?php echo JHtml::_('select.options', JHtml::_('languages.clients'), 'value', 'text', $this->state->get('filter.client_id'));?>
			</select>

			<button type="button" id="filter-go" onclick="this.form.submit();">
				<?php echo JText::_('JSUBMIT'); ?></button>

		</div>
	</fieldset>

	<table class="adminlist">
		<thead>
			<tr>
				<th class="row-number-col">
					<?php echo JText::_('COM_LANGUAGES_HEADING_NUM'); ?>
				</th>
				<th class="checkmark-col">
					&#160;
				</th>
				<th class="title">
					<?php echo JText::_('COM_LANGUAGES_HEADING_LANGUAGE'); ?>
				</th>
				<th class="width-5">
					<?php echo JText::_('COM_LANGUAGES_HEADING_DEFAULT'); ?>
				</th>
				<th class="width-10">
					<?php echo JText::_('JVERSION'); ?>
				</th>
				<th class="width-10">
					<?php echo JText::_('JDATE'); ?>
				</th>
				<th class="width-20">
					<?php echo JText::_('JAUTHOR'); ?>
				</th>
				<th class="width-25">
					<?php echo JText::_('COM_LANGUAGES_HEADING_AUTHOR_EMAIL'); ?>
				</th>
			</tr>
		</thead>

		<tbody>
		<?php foreach ($this->rows as $i => $row) :
			$canCreate	= $user->authorise('core.create',		'com_languages');
			$canEdit	= $user->authorise('core.edit',			'com_languages');
			$canChange	= $user->authorise('core.edit.state',	'com_languages');
		?>
			<tr class="row<?php echo $i % 2; ?>">
				<th>
					<?php echo $this->pagination->getRowOffset($i); ?>
				</th>
				<td>
					<?php echo JHtml::_('languages.id',$i,$row->language);?>
				</td>
				<td>
					<?php echo $row->name;?>
				</td>
				<td class="center">
					<?php echo JHtml::_('jgrid.isdefault', $row->published, $i, 'installed.',  !$row->published && $canChange);?>
				</td>
				<td class="center">
					<?php echo $row->version; ?>
				</td>
				<td class="center">
					<?php echo $row->creationDate; ?>
				</td>
				<td class="center">
					<?php echo $row->author; ?>
				</td>
				<td class="center">
					<?php echo $row->authorEmail; ?>
				</td>
			</tr>
		<?php endforeach;?>
		</tbody>
	</table>

	<?php echo $this->pagination->getListFooter(); ?>

	<input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked" value="0" />
	<?php echo JHtml::_('form.token'); ?>
</form>
