<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_languages
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');
$client			= $this->state->get('filter.client') == 'site' ? JText::_('JSITE') : JText::_('JADMINISTRATOR');
$language		= $this->state->get('filter.language');
$listOrder	= $this->escape($this->state->get('list.ordering'));
$listDirn		= $this->escape($this->state->get('list.direction')); ?>
<form action="<?php echo JRoute::_('index.php?option=com_languages&view=overrides'); ?>" method="post" name="adminForm" id="adminForm">
<?php if (!empty( $this->sidebar)) : ?>
	<div id="j-sidebar-container" class="span2">
		<?php echo $this->sidebar; ?>
	</div>
	<div id="j-main-container" class="span10">
<?php else : ?>
	<div id="j-main-container">
<?php endif;?>
	<fieldset id="filter-bar">
		<div class="filter-search fltlft">
			<label class="filter-search-lbl" for="filter_search"><?php echo JText::_('JSEARCH_FILTER_LABEL'); ?></label>
			<input type="text" name="filter_search" id="filter_search" value="<?php echo $this->escape($this->state->get('filter.search')); ?>" title="<?php echo JText::_('COM_LANGUAGES_VIEW_OVERRIDES_FILTER_SEARCH_DESC'); ?>" />

			<button type="submit" class="btn"><?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?></button>
			<button type="button" onclick="document.id('filter_search').value='';this.form.submit();"><?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?></button>
		</div>
		<div class="filter-select fltrt">
			<select name="filter_language_client" class="inputbox" onchange="this.form.submit()">
				<?php echo JHtml::_('select.options', $this->languages, null, 'text', $this->state->get('filter.language_client')); ?>
			</select>
		</div>
	</fieldset>

	<div class="clr"></div>

	<table class="adminlist">
		<thead>
			<tr>
				<th width="1%">
					<input type="checkbox" name="checkall-toggle" value="" onclick="Joomla.checkAll(this)" />
				</th>
				<th width="30%" class="left">
					<?php echo JHtml::_('grid.sort', 'COM_LANGUAGES_VIEW_OVERRIDES_KEY', 'key', $listDirn, $listOrder); ?>
				</th>
				<th class="left">
					<?php echo JHtml::_('grid.sort', 'COM_LANGUAGES_VIEW_OVERRIDES_TEXT', 'text', $listDirn, $listOrder); ?>
				</th>
				<th class="nowrap">
					<?php echo JText::_('COM_LANGUAGES_FIELD_LANG_TAG_LABEL'); ?>
				</th>
				<th>
					<?php echo JText::_('JCLIENT'); ?>
				</th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<td colspan="5">
					<?php echo $this->pagination->getListFooter(); ?>
				</td>
			</tr>
		</tfoot>
		<tbody>
		<?php $canEdit = JFactory::getUser()->authorise('core.edit', 'com_languages');
		$i = 0;
		foreach ($this->items as $key => $text) : ?>
			<tr class="row<?php echo $i % 2; ?>" id="overriderrow<?php echo $i; ?>">
				<td class="center">
					<?php echo JHtml::_('grid.id', $i, $key); ?>
				</td>
				<td>
					<?php if ($canEdit) : ?>
						<a id="key[<?php	echo $this->escape($key); ?>]" href="<?php echo JRoute::_('index.php?option=com_languages&task=override.edit&id='.$key); ?>"><?php echo $this->escape($key); ?></a>
					<?php else: ?>
						<?php echo $this->escape($key); ?>
					<?php endif; ?>
				</td>
				<td>
					<span id="string[<?php	echo $this->escape($key); ?>]"><?php echo $this->escape($text); ?></span>
				</td>
				<td class="center">
					<?php echo $language; ?>
				</td>
				<td class="center">
					<?php echo $client; ?>
				</td>
			</tr>
			<?php $i++;
		endforeach; ?>
		</tbody>
	</table>
	<div>
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
		<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</div>
</form>
