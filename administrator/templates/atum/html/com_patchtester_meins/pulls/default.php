<?php
/**
 * Patch testing component for the Joomla! CMS
 *
 * @copyright  Copyright (C) 2011 - 2012 Ian MacLennan, Copyright (C) 2013 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later
 */

/** @var  \PatchTester\View\Pulls\PullsHtmlView  $this */

$searchToolsOptions = array(
	'filtersHidden'       => true,
	'filterButton'        => true,
	'defaultLimit'        => JFactory::getApplication()->get('list_limit', 20),
	'searchFieldSelector' => '#filter_search',
	'selectorFieldName'   => 'client_id',
	'showSelector'        => false,
	'orderFieldSelector'  => '#sortTable',
	'showNoResults'       => false,
	'noResultsText'       => '',
	'formSelector'        => '#adminForm',
);

\JHtml::_('behavior.core');
\JHtml::_('bootstrap.tooltip');
\JHtml::_('searchtools.form', '#adminForm', $searchToolsOptions);
\JHtml::_('stylesheet', 'com_patchtester/octicons.css', array('version' => 'auto', 'relative' => true));
\JHtml::_('script', 'com_patchtester/patchtester.js', array('version' => 'auto', 'relative' => true));

$listOrder     = $this->escape($this->state->get('list.ordering'));
$listDirn      = $this->escape($this->state->get('list.direction', 'desc'));
$filterApplied = $this->escape($this->state->get('filter.applied'));
$filterBranch  = $this->escape($this->state->get('filter.branch'));
$filterRtc     = $this->escape($this->state->get('filter.rtc'));
$colSpan       = $this->trackerAlias !== false ? 8 : 7;
?>
<form action="<?php echo \JRoute::_('index.php?option=com_patchtester&view=pulls'); ?>" method="post" name="adminForm" id="adminForm" data-order="<?php echo $listOrder; ?>">
	<div class="row">
		<div class="col-md-12">
			<div id="j-main-container" class="j-main-container">
				<div class="js-stools" role="search">
					<div class="js-stools-container-bar">
						<label for="filter_search" class="sr-only">
							<?php echo \JText::_('COM_PATCHTESTER_FILTER_SEARCH_DESCRIPTION'); ?>
						</label>
						<div class="btn-toolbar">
							<div class="btn-group mr-2">
								<div class="input-group">
									<input type="text" name="filter_search" id="filter_search" value="<?php echo $this->escape($this->state->get('filter.search')); ?>" class="form-control" title="<?php echo \JText::_('COM_PATCHTESTER_FILTER_SEARCH_DESCRIPTION'); ?>" placeholder="<?php echo \JText::_('JSEARCH_FILTER'); ?>">
									<span class="input-group-append">
										<button type="submit" class="btn btn-primary hasTooltip" title="<?php echo \JHtml::_('tooltipText', 'JSEARCH_FILTER_SUBMIT'); ?>" aria-label="<?php echo \JText::_('JSEARCH_FILTER_SUBMIT'); ?>">
											<span class="fa fa-search" aria-hidden="true"></span>
										</button>
									</span>
								</div>
							</div>
							<button type="button" class="btn btn-primary hasTooltip js-stools-btn-clear mr-2" title="<?php echo \JHtml::_('tooltipText', 'JSEARCH_FILTER_CLEAR'); ?>">
								<?php echo \JText::_('JSEARCH_FILTER_CLEAR'); ?>
							</button>
							<div class="btn-group">
								<button type="button" class="btn btn-primary hasTooltip js-stools-btn-filter">
									<?php echo \JText::_('JTABLE_OPTIONS'); ?>
									<span class="fa fa-caret-down" aria-hidden="true"></span>
								</button>
							</div>
						</div>
					</div>
					<!-- Filters div -->
					<div class="js-stools-container-filters clearfix">
						<div class="ordering-select">
							<div class="js-stools-field-list">
								<select name="sortTable" id="sortTable" class="custom-select" onchange="PatchTester.orderTable()">
									<option value=""><?php echo \JText::_('JGLOBAL_SORT_BY'); ?></option>
									<?php echo \JHtml::_('select.options', $this->getSortFields(), 'value', 'text', $listOrder); ?>
								</select>
							</div>
							<div class="js-stools-field-list">
								<select name="directionTable" id="directionTable" class="custom-select" onchange="PatchTester.orderTable()">
									<option value=""><?php echo \JText::_('JFIELD_ORDERING_DESC');?></option>
									<option value="asc"<?php if (strtolower($listDirn) === 'asc') echo ' selected="selected"'; ?>><?php echo \JText::_('JGLOBAL_ORDER_ASCENDING'); ?></option>
									<option value="desc"<?php if (strtolower($listDirn) === 'desc') echo ' selected="selected"'; ?>><?php echo \JText::_('JGLOBAL_ORDER_DESCENDING'); ?></option>
								</select>
							</div>
						</div>
						<div class="js-stools-field-filter">
							<select name="filter_applied" class="custom-select" onchange="this.form.submit();">
								<option value=""><?php echo \JText::_('COM_PATCHTESTER_FILTER_APPLIED_PATCHES'); ?></option>
								<option value="yes"<?php if ($filterApplied == 'yes') echo ' selected="selected"'; ?>><?php echo \JText::_('COM_PATCHTESTER_APPLIED'); ?></option>
								<option value="no"<?php if ($filterApplied == 'no') echo ' selected="selected"'; ?>><?php echo \JText::_('COM_PATCHTESTER_NOT_APPLIED'); ?></option>
							</select>
						</div>
						<div class="js-stools-field-filter">
							<select name="filter_rtc" class="custom-select" onchange="this.form.submit();">
								<option value=""><?php echo \JText::_('COM_PATCHTESTER_FILTER_RTC_PATCHES'); ?></option>
								<option value="yes"<?php if ($filterRtc == 'yes') echo ' selected="selected"'; ?>><?php echo \JText::_('COM_PATCHTESTER_RTC'); ?></option>
								<option value="no"<?php if ($filterRtc == 'no') echo ' selected="selected"'; ?>><?php echo \JText::_('COM_PATCHTESTER_NOT_RTC'); ?></option>
							</select>
						</div>
						<div class="js-stools-field-filter">
							<select name="filter_branch" class="custom-select" onchange="this.form.submit();">
								<option value=""><?php echo \JText::_('COM_PATCHTESTER_FILTER_BRANCH'); ?></option>
								<?php echo \JHtml::_('select.options', $this->branches, 'text', 'text', $filterBranch, false); ?>
							</select>
						</div>
					</div>
				</div>
				<div id="j-main-container" class="j-main-container">
					<?php if (empty($this->items)) : ?>
						<div class="alert alert-warning alert-no-items">
							<?php echo \JText::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
						</div>
					<?php else : ?>
						<table class="table">
							<thead>
								<tr>
									<th width="5%" class="nowrap text-center">
										<?php echo \JText::_('COM_PATCHTESTER_PULL_ID'); ?>
									</th>
									<th class="nowrap">
										<?php echo \JText::_('JGLOBAL_TITLE'); ?>
									</th>
									<th width="8%" class="nowrap text-center">
										<?php echo \JText::_('COM_PATCHTESTER_BRANCH'); ?>
									</th>
									<th width="8%" class="nowrap text-center">
										<?php echo \JText::_('COM_PATCHTESTER_READY_TO_COMMIT'); ?>
									</th>
									<th width="8%" class="nowrap text-center">
										<?php echo \JText::_('COM_PATCHTESTER_GITHUB'); ?>
									</th>
									<?php if ($this->trackerAlias !== false) : ?>
										<th width="8%" class="nowrap text-center">
											<?php echo \JText::_('COM_PATCHTESTER_JISSUES'); ?>
										</th>
									<?php endif; ?>
									<th width="10%" class="nowrap text-center">
										<?php echo \JText::_('JSTATUS'); ?>
									</th>
									<th width="15%" class="nowrap text-center">
										<?php echo \JText::_('COM_PATCHTESTER_TEST_THIS_PATCH'); ?>
									</th>
								</tr>
							</thead>
							<tbody>
								<?php echo $this->loadTemplate('items'); ?>
							</tbody>
						</table>
					<?php endif; ?>

					<?php echo $this->pagination->getListFooter(); ?>

					<input type="hidden" name="task" value="" />
					<input type="hidden" name="boxchecked" value="0" />
					<input type="hidden" name="pull_id" id="pull_id" value="" />
					<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
					<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
					<?php echo \JHtml::_('form.token'); ?>
				</div>
			</div>
		</div>
	</div>
</form>
