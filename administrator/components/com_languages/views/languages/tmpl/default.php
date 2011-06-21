<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	com_languages
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.multiselect');

$user		= JFactory::getUser();
$userId		= $user->get('id');
$n			= count($this->items);
$listOrder	= $this->escape($this->state->get('list.ordering'));
$listDirn	= $this->escape($this->state->get('list.direction'));
?>

<form action="<?php echo JRoute::_('index.php?option=com_languages&view=languages'); ?>" method="post" name="adminForm" id="adminForm">
	<fieldset id="filter-bar">

		<div class="filter-search fltlft">
			<label class="filter-search-lbl" for="filter_search"><?php echo JText::_('JSEARCH_FILTER_LABEL'); ?></label>
			<input type="text" name="filter_search" id="filter_search" value="<?php echo $this->escape($this->state->get('filter.search')); ?>" title="<?php echo JText::_('COM_LANGUAGES_SEARCH_IN_TITLE'); ?>" />

			<button type="submit" class="btn"><?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?></button>
			<button type="button" onclick="document.id('filter_search').value='';this.form.submit();"><?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?></button>

		</div>

		<div class="filter-select fltrt">
			<select name="filter_published" class="inputbox" onchange="this.form.submit()">
				<option value=""><?php echo JText::_('JOPTION_SELECT_PUBLISHED');?></option>
				<?php echo JHtml::_('select.options', JHtml::_('languages.publishedOptions'), 'value', 'text', $this->state->get('filter.published'), true);?>
			</select>
		</div>
	</fieldset>

	<table class="adminlist">
		<thead>
			<tr>
				<th width="5">
					<?php echo JText::_('JGRID_HEADING_ROW_NUMBER'); ?>
				</th>
				<th width="20">
					<input type="checkbox" name="checkall-toggle" value="" title="<?php echo JText::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)" />
				</th>
				<th class="title">
					<?php echo JHtml::_('grid.sort',  'JGLOBAL_TITLE', 'a.title', $listDirn, $listOrder); ?>
				</th>
				<th class="title">
					<?php echo JHtml::_('grid.sort',  'COM_LANGUAGES_HEADING_TITLE_NATIVE', 'a.title_native', $listDirn, $listOrder); ?>
				</th>
				<th width="5%" class="nowrap">
					<?php echo JHtml::_('grid.sort',  'COM_LANGUAGES_FIELD_LANG_TAG_LABEL', 'a.lang_code', $listDirn, $listOrder); ?>
				</th>
				<th width="5%" class="nowrap">
					<?php echo JHtml::_('grid.sort',  'COM_LANGUAGES_FIELD_LANG_CODE_LABEL', 'a.sef', $listDirn, $listOrder); ?>
				</th>
				<th width="5%" class="nowrap">
					<?php echo JHtml::_('grid.sort',  'COM_LANGUAGES_HEADING_LANG_IMAGE', 'a.image', $listDirn, $listOrder); ?>
				</th>
				<th width="5%" class="nowrap">
					<?php echo JHtml::_('grid.sort',  'JSTATUS', 'a.published', $listDirn, $listOrder); ?>
				</th>
				<th width="5%" class="nowrap">
					<?php echo JHtml::_('grid.sort',  'COM_LANGUAGES_HOMEPAGE', '', $listDirn, $listOrder); ?>
				</th>
				<th width="1%" class="nowrap">
					<?php echo JHtml::_('grid.sort',  'JGRID_HEADING_ID', 'a.lang_id', $listDirn, $listOrder); ?>
				</th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<td colspan="10">
					<?php echo $this->pagination->getListFooter(); ?>
				</td>
			</tr>
		</tfoot>
		<tbody>
		<?php
		foreach ($this->items as $i => $item) :
			$canCreate	= $user->authorise('core.create',		'com_languages');
			$canEdit	= $user->authorise('core.edit',			'com_languages');
			$canChange	= $user->authorise('core.edit.state',	'com_languages');
		?>
			<tr class="row<?php echo $i % 2; ?>">
				<td>
					<?php echo $this->pagination->getRowOffset($i); ?>
				</td>
				<td>
					<?php echo JHtml::_('grid.id', $i, $item->lang_id); ?>
				</td>
				<td>
					<span class="editlinktip hasTip" title="<?php echo JText::_('JGLOBAL_EDIT_ITEM');?>::<?php echo $this->escape($item->title); ?>">
					<?php if ($canEdit) : ?>
						<a href="<?php echo JRoute::_('index.php?option=com_languages&task=language.edit&lang_id='.(int) $item->lang_id); ?>">
							<?php echo $this->escape($item->title); ?></a>
					<?php else : ?>
							<?php echo $this->escape($item->title); ?>
					<?php endif; ?>
					</span>
				</td>
				<td class="center">
					<?php echo $this->escape($item->title_native); ?>
				</td>
				<td class="center">
					<?php echo $this->escape($item->lang_code); ?>
				</td>
				<td class="center">
					<?php echo $this->escape($item->sef); ?>
				</td>
				<td class="center">
					<?php echo $this->escape($item->image); ?>
				</td>
				<td class="center">
					<?php echo JHtml::_('jgrid.published', $item->published, $i, 'languages.', $canChange);?>
				</td>
				<td class="center">
					<?php if ($item->home == '1') : ?>
						<?php echo JText::_('JYES');?>
					<?php else:?>
						<?php echo JText::_('JNO');?>
					<?php endif;?>
				</td>
				<td class="center">
					<?php echo $this->escape($item->lang_id); ?>
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
</form>
