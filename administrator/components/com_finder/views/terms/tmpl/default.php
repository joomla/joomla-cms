<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_finder
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

JHtml::_('bootstrap.tooltip');
JHtml::_('formbehavior.chosen', 'select');
JHtml::_('bootstrap.popover');

$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
?>
<form action="<?php echo JRoute::_('index.php?option=com_finder&view=terms'); ?>" method="post" name="adminForm" id="adminForm">
<?php if (!empty( $this->sidebar)) : ?>
	<div id="j-sidebar-container" class="span2">
		<?php echo $this->sidebar; ?>
	</div>
	<div id="j-main-container" class="span10">
<?php else : ?>
	<div id="j-main-container">
<?php endif;?>
		<?php echo JLayoutHelper::render('joomla.searchtools.default', array('view' => $this)); ?>

		<?php if (empty($this->items)) : ?>
		<div class="alert alert-no-items">
			<?php echo JText::_('COM_FINDER_INDEX_NO_CONTENT'); ?>
		</div>
		<?php else : ?>
		<table class="table table-striped table-condensed">
			<thead>
				<tr>
					<th class="nowrap">
						<?php echo JHtml::_('searchtools.sort', 'COM_FINDER_TERMS_TERM_HEADING', 'term', $listDirn, $listOrder); ?>
					</th>
					<th class="nowrap">
						<?php echo JHtml::_('searchtools.sort', 'COM_FINDER_TERMS_STEM_HEADING', 'stem', $listDirn, $listOrder); ?>
					</th>
					<th class="nowrap">
						<?php echo JHtml::_('searchtools.sort', 'COM_FINDER_TERMS_WEIGHT_HEADING', 'weight', $listDirn, $listOrder); ?>
					</th>
					<th class="nowrap">
						<?php echo JHtml::_('searchtools.sort', 'COM_FINDER_TERMS_SOUNDEX_HEADING', 'soundex', $listDirn, $listOrder); ?>
					</th>
					<th class="nowrap">
						<?php echo JHtml::_('searchtools.sort', 'COM_FINDER_TERMS_LANGUAGE_HEADING', 'language', $listDirn, $listOrder); ?>
					</th>
					<th width="3%" class="nowrap center hidden-phone hidden-tablet">
						<?php echo JHtml::_('searchtools.sort', 'COM_FINDER_TERMS_LINKS_HEADING', 'links', $listDirn, $listOrder); ?>
					</th>
					<th width="1%" class="nowrap center hidden-phone hidden-tablet">
						<?php echo JHtml::_('searchtools.sort', 'COM_FINDER_TERMS_SHARD_HEADING', 'shard', $listDirn, $listOrder); ?>
					</th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<td colspan="7">
						<?php echo $this->pagination->getListFooter(); ?>
					</td>
				</tr>
			</tfoot>
			<tbody>
				<?php foreach ($this->items as $term) : ?>
				<tr>
					<td>
						<?php echo $term->term; ?>
						<?php if ($term->common) : ?>
							<span class="badge badge-info"><?php echo JText::_('COM_FINDER_TERMS_COMMON'); ?></span>
						<?php endif; ?>
					</td>
					<td>
						<?php echo $term->stem; ?>
					</td>
					<td>
						<span><?php echo $term->weight; ?></span>
					</td>
					<td>
						<a href="<?php echo JRoute::_('index.php?option=com_finder&view=terms&filter[soundex]=' . $term->soundex); ?>">
							<span><?php echo $term->soundex; ?></span>
						</a>
					</td>
					<td class="small hidden-phone">
						<?php echo JLayoutHelper::render('joomla.content.language', $term); ?>
					</td>
					<td class="center hidden-phone hidden-tablet">
						<span class="badge badge-info"><?php echo $term->links; ?></span>
					</td>
					<td class="center hidden-phone hidden-tablet">
						<a href="<?php echo JRoute::_('index.php?option=com_finder&view=terms&filter[shard]=' . hexdec($term->shard)); ?>">
							<span class="badge badge-info"><?php echo $term->shard; ?></span>
						</a>
					</td>
				</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
		<?php endif; ?>
		<input type="hidden" name="task" value="display" />
		<input type="hidden" name="boxchecked" value="0" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>

