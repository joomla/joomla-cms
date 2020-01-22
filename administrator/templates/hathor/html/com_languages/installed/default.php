<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  Template.hathor
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtmlBehavior::core();
// Add specific helper files for html generation
JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');
$user     = JFactory::getUser();
$userId   = $user->get('id');
?>

<form action="<?php echo JRoute::_('index.php?option=com_languages&view=installed'); ?>" method="post" id="adminForm" name="adminForm">
<?php if (!empty( $this->sidebar)) : ?>
	<div id="j-sidebar-container" class="span2">
		<?php echo $this->sidebar; ?>
	</div>
	<div id="j-main-container" class="span10">
<?php else : ?>
	<div id="j-main-container">
<?php endif;?>
	<fieldset id="filter-bar">
		<legend class="element-invisible"><?php echo JText::_('JSEARCH_FILTER_LABEL'); ?></legend>
		<div class="filter-select fltrt">
			<label class="selectlabel" for="client_id">
				<?php echo JText::_('COM_CACHE_SELECT_CLIENT'); ?>
			</label>
			<select name="client_id" id="client_id">
			<?php
			$options   = array();
			$options[] = JHtml::_('select.option', '0', JText::_('JSITE'));
			$options[] = JHtml::_('select.option', '1', JText::_('JADMINISTRATOR'));
			echo JHtml::_('select.options', $options, 'value', 'text', $this->state->get('client_id'));
			?>
			</select>

			<button type="submit" id="filter-go">
				<?php echo JText::_('JSUBMIT'); ?></button>
		</div>
	</fieldset>
	<?php if ($this->ftp) : ?>
		<?php echo $this->loadTemplate('ftp');?>
	<?php endif; ?>

	<table class="adminlist">
		<thead>
			<tr>
				<th class="checkmark-col">
					&#160;
				</th>
				<th class="title">
					<?php echo JText::_('COM_LANGUAGES_HEADING_LANGUAGE'); ?>
				</th>
				<th>
					<?php echo JText::_('COM_LANGUAGES_FIELD_LANG_TAG_LABEL'); ?>
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
			$canCreate = $user->authorise('core.create',     'com_languages');
			$canEdit   = $user->authorise('core.edit',       'com_languages');
			$canChange = $user->authorise('core.edit.state', 'com_languages');
		?>
			<tr class="row<?php echo $i % 2; ?>">
				<td>
					<?php echo JHtml::_('languages.id', $i, $row->language);?>
				</td>
				<td>
					<?php echo $this->escape($row->name); ?>
				</td>
				<td align="center">
					<?php echo $this->escape($row->language); ?>
				</td>
				<td class="center">
					<?php echo JHtml::_('jgrid.isdefault', $row->published, $i, 'installed.', !$row->published && $canChange);?>
				</td>
				<td class="center">
					<?php echo $this->escape($row->version); ?>
				</td>
				<td class="center">
					<?php echo $this->escape($row->creationDate); ?>
				</td>
				<td class="center">
					<?php echo $this->escape($row->author); ?>
				</td>
				<td class="center">
					<?php echo JStringPunycode::emailToUTF8($this->escape($row->authorEmail)); ?>
				</td>
			</tr>
		<?php endforeach;?>
		</tbody>
	</table>

	<?php echo $this->pagination->getListFooter(); ?>

	<input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked" value="0" />
	<?php echo JHtml::_('form.token'); ?>
	</div>
</form>
