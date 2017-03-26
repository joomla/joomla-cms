<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_languages
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;


JHtml::_('bootstrap.tooltip');

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');
$client    = $this->state->get('filter.client') == '0' ? JText::_('JSITE') : JText::_('JADMINISTRATOR');
$language  = $this->state->get('filter.language');
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));

$opposite_client   = $this->state->get('filter.client') == '1' ? JText::_('JSITE') : JText::_('JADMINISTRATOR');
$opposite_filename = constant('JPATH_' . strtoupper(1 - $this->state->get('filter.client')? 'administrator' : 'site'))
	. '/language/overrides/' . $this->state->get('filter.language', 'en-GB') . '.override.ini';
$opposite_strings  = LanguagesHelper::parseFile($opposite_filename);
?>

<form action="<?php echo JRoute::_('index.php?option=com_languages&view=overrides'); ?>" method="post" name="adminForm" id="adminForm">
	<div class="row">
		<div id="j-sidebar-container" class="col-md-2">
			<?php echo $this->sidebar; ?>
		</div>
		<div class="col-md-10">
			<div id="j-main-container" class="j-main-container">
				<div id="filter-bar" class="btn-toolbar clearfix">
					<div class="filter-search btn-group float-left">
						<div class="input-group">
							<input type="text" name="filter_search" id="filter_search" placeholder="<?php echo JText::_('JSEARCH_FILTER'); ?>" value="<?php echo $this->escape($this->state->get('filter.search')); ?>" class="form-control hasTooltip" title="<?php echo JHtml::tooltipText('COM_LANGUAGES_VIEW_OVERRIDES_FILTER_SEARCH_DESC'); ?>">
							<div class="input-group-btn">
								<button type="submit" class="btn btn-secondary hasTooltip" title="<?php echo JHtml::_('tooltipText', 'JSEARCH_FILTER_SUBMIT'); ?>"><span class="icon-search"></span></button>
								<button type="button" class="btn btn-secondary hasTooltip" title="<?php echo JHtml::_('tooltipText', 'JSEARCH_FILTER_CLEAR'); ?>" onclick="document.getElementById('filter_search').value='';this.form.submit();"><span class="icon-remove"></span></button>
							</div>
						</div>
					</div>
					<div class="btn-group float-right hidden-sm-down">
						<label for="limit" class="element-invisible"><?php echo JText::_('JFIELD_PLG_SEARCH_SEARCHLIMIT_DESC'); ?></label>
						<?php echo $this->pagination->getLimitBox(); ?>
					</div>
				</div>
				<?php if (empty($this->items)) : ?>
					<div class="alert alert-warning alert-no-items">
						<?php echo JText::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
					</div>
				<?php else : ?>
					<table class="table table-striped" id="overrideList">
						<thead>
							<tr>
								<th width="1%" class="text-center">
									<?php echo JHtml::_('grid.checkall'); ?>
								</th>
								<th width="30%">
									<?php echo JHtml::_('grid.sort', 'COM_LANGUAGES_VIEW_OVERRIDES_KEY', 'key', $listDirn, $listOrder); ?>
								</th>
								<th class="hidden-sm-down">
									<?php echo JHtml::_('grid.sort', 'COM_LANGUAGES_VIEW_OVERRIDES_TEXT', 'text', $listDirn, $listOrder); ?>
								</th>
								<th class="nowrap hidden-sm-down">
									<?php echo JText::_('COM_LANGUAGES_FIELD_LANG_TAG_LABEL'); ?>
								</th>
								<th class="hidden-sm-down">
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
						<?php $canEdit = JFactory::getUser()->authorise('core.edit', 'com_languages'); ?>
						<?php $i = 0; ?>
						<?php foreach ($this->items as $key => $text) : ?>
							<tr class="row<?php echo $i % 2; ?>" id="overriderrow<?php echo $i; ?>">
								<td class="text-center">
									<?php echo JHtml::_('grid.id', $i, $key); ?>
								</td>
								<td>
									<?php if ($canEdit) : ?>
										<a id="key[<?php echo $this->escape($key); ?>]" href="<?php echo JRoute::_('index.php?option=com_languages&task=override.edit&id=' . $key); ?>"><?php echo $this->escape($key); ?></a>
									<?php else : ?>
										<?php echo $this->escape($key); ?>
									<?php endif; ?>
								</td>
								<td class="hidden-sm-down">
									<span id="string[<?php echo $this->escape($key); ?>]"><?php echo $this->escape($text); ?></span>
								</td>
								<td class="hidden-sm-down">
									<?php echo $language; ?>
								</td>
								<td class="hidden-sm-down">
									<?php echo $client; ?>
									<?php
									if (isset($opposite_strings[$key]) && ($opposite_strings[$key] == $text))
									{
										echo '/' . $opposite_client;
									}
									?>
								</td>
							</tr>
						<?php $i++; ?>
						<?php endforeach; ?>
						</tbody>
					</table>
				<?php endif; ?>

				<input type="hidden" name="task" value="">
				<input type="hidden" name="boxchecked" value="0">
				<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>">
				<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>">
				<?php echo JHtml::_('form.token'); ?>
			</div>
		</div>
	</div>
</form>
