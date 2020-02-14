<?php 
/**
 * @version $Id$
 * @package DJ-ImageSlider
 * @subpackage DJ-ImageSlider Component
 * @copyright Copyright (C) 2017 DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 * @developer Szymon Woronowski - szymon.woronowski@design-joomla.eu
 *
 *
 * DJ-ImageSlider is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * DJ-ImageSlider is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with DJ-ImageSlider. If not, see <http://www.gnu.org/licenses/>.
 *
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Session\Session;

JHTML::_('behavior.tooltip');
JHtml::_('formbehavior.chosen', 'select');

$user		= JFactory::getUser();
$userId		= $user->get('id');
$listOrder	= $this->state->get('list.ordering');
$listDirn	= $this->state->get('list.direction');
$canOrder	= $user->authorise('core.edit.state', 'com_djimageslider.category');
$saveOrder	= $listOrder == 'a.ordering';

$joomla4 = version_compare(JVERSION, '4', '>=') ? true : false;

if($saveOrder) {
	if($joomla4) {
		$saveOrderingUrl = 'index.php?option=com_djimageslider&task=items.saveOrderAjax&tmpl=component&' . Session::getFormToken() . '=1';
		HTMLHelper::_('draggablelist.draggable');
	} else {
		$saveOrderingUrl = 'index.php?option=com_djimageslider&task=items.saveOrderAjax&tmpl=component';
		JHtml::_('sortablelist.sortable', 'itemsList', 'adminForm', strtolower($listDirn), $saveOrderingUrl);
	}
}
?>

<div class="<?php echo $this->classes->row ?>">

<?php if(!empty( $this->sidebar)): ?>
<div id="j-sidebar-container" class="<?php echo $this->classes->col ?>2">
	<?php echo $this->sidebar; ?>
</div>
<div id="j-main-container" class="<?php echo $this->classes->col ?>10">
<?php else: ?>
<div id="j-main-container">
<?php endif;?>
	
<form action="<?php echo JRoute::_('index.php?option=com_djimageslider&view=items'); ?>" method="post" name="adminForm" id="adminForm">
	<div id="filter-bar" class="btn-toolbar">
		<div class="filter-search fltlft btn-group pull-left">
			<label class="filter-search-lbl element-invisible" for="filter_search"><?php echo JText::_('JSEARCH_FILTER_LABEL'); ?></label>
			<input type="text" name="filter_search" id="filter_search" value="<?php echo $this->escape($this->state->get('filter.search')); ?>" placeholder="<?php echo JText::_('COM_DJIMAGESLIDER_SEARCH_IN_TITLE'); ?>" />
		</div>
		<div class="filter-search fltlft btn-group pull-left">
			<button type="submit" class="btn"><?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?></button>
			<button type="button" class="btn" onclick="document.id('filter_search').value='';this.form.submit();"><?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?></button>
		</div>
		<div class="btn-group pull-right hidden-phone">
			<?php echo $this->pagination->getLimitBox(); ?>
		</div>
		<div class="filter-select fltrt btn-group pull-right">
			<select name="filter_published" class="inputbox input-medium" onchange="this.form.submit()">
				<option value=""><?php echo JText::_('JOPTION_SELECT_PUBLISHED');?></option>
				<?php echo JHtml::_('select.options', array(JHtml::_('select.option', '1', 'JPUBLISHED'),JHtml::_('select.option', '0', 'JUNPUBLISHED')), 'value', 'text', $this->state->get('filter.published'), true);?>
			</select>
		</div>
		<div class="filter-select fltrt btn-group pull-right">
			<select name="filter_category" class="inputbox" onchange="this.form.submit()">
				<option value=""><?php echo JText::_('JOPTION_SELECT_CATEGORY');?></option>
				<?php echo JHtml::_('select.options', JHtml::_('category.options', 'com_djimageslider'), 'value', 'text', $this->state->get('filter.category'));?>
			</select>
		</div>
	</div>
	<div class="clr"> </div>
	
	<table class="adminlist table table-striped" id="itemsList">
		<thead>
			<tr>
				<th width="1%" class="nowrap center hidden-phone">
					<?php echo JHtml::_('grid.sort', '<i class="icon-menu-2"></i>', 'a.ordering', $listDirn, $listOrder, null, 'asc', 'JGRID_HEADING_ORDERING'); ?>
				</th>
				<th width="1%">
					<input type="checkbox" name="checkall-toggle" value="" onclick="checkAll(this)" />
				</th>
				<th width="8%">
					<?php echo JText::_('COM_DJIMAGESLIDER_IMAGE'); ?>
				</th>
				<th>
					<?php echo JHtml::_('grid.sort',  'JGLOBAL_TITLE', 'a.title', $listDirn, $listOrder); ?>
				</th>				
				<th width="5%">
					<?php echo JHtml::_('grid.sort', 'JPUBLISHED', 'a.published', $listDirn, $listOrder); ?>
				</th>
				<th width="10%">
					<?php echo JHtml::_('grid.sort', 'JCATEGORY', 'category_title', $listDirn, $listOrder); ?>
				</th>
				<th width="1%">
					<?php echo JHtml::_('grid.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
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
		<tbody <?php if ($saveOrder && $joomla4) :?> class="js-draggable" data-url="<?php echo $saveOrderingUrl; ?>" data-direction="<?php echo strtolower($listDirn); ?>" data-nested="false"<?php endif; ?>>
		<?php 
		$n = count($this->items);
		foreach ($this->items as $i => $item) :
			$ordering	= ($listOrder == 'a.ordering');
			$canCreate	= $user->authorise('core.create',		'com_djimageslider.category.'.$item->catid);
			$canEdit	= $user->authorise('core.edit',			'com_djimageslider.category.'.$item->catid);
			$canCheckin	= $user->authorise('core.manage',		'com_checkin') || $item->checked_out == $userId || $item->checked_out == 0;
			$canEditOwn	= true; //$user->authorise('core.edit.own',		'com_djimageslider.category.'.$item->catid) && $item->created_by == $userId;
			$canChange	= $user->authorise('core.edit.state',	'com_djimageslider.category.'.$item->catid) && $canCheckin;

			?>
			<tr class="row<?php echo $i % 2; ?>" sortable-group-id="<?php echo $item->catid ?>" data-dragable-group="<?php echo $item->catid ?>">
			
				<td class="order nowrap center hidden-phone">
					<?php $iconClass = '';
					if (!$canChange) {
						$iconClass = ' inactive';
					} elseif (!$saveOrder) {
						$iconClass = ' inactive tip-top hasTooltip" title="' . JHtml::tooltipText('JORDERINGDISABLED');
					} ?>
					<span class="sortable-handler<?php echo $iconClass ?>">
						<i class="icon-move"></i>
					</span>
					<?php if ($canChange && $saveOrder) : ?>
						<input type="text" style="display:none" name="order[]" size="5" value="<?php echo $item->ordering; ?>" class="width-20 text-area-order " />
					<?php endif; ?>
				</td>
				<td class="center">
					<?php echo JHtml::_('grid.id', $i, $item->id); ?>
				</td>
				<td align="center">
					<?php if ($item->image) : ?>
						<a class="mf-popup" href="<?php echo $item->image; ?>"><img src="<?php echo $item->image; ?>" alt="<?php echo $this->escape($item->title); ?>" style="border: 1px solid #ccc; padding: 1px; max-height: 40px; max-width: 60px;" /></a>
					<?php endif; ?>
				</td>
				<td>
					<?php if ($item->checked_out) : ?>
						<?php echo JHtml::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, 'items.', $canCheckin); ?>
					<?php endif; ?>
					<?php if ($canEdit || $canEditOwn) : ?>
						<a href="<?php echo JRoute::_('index.php?option=com_djimageslider&task=item.edit&id='.(int) $item->id); ?>">
							<?php echo $this->escape($item->title); ?></a>
					<?php else : ?>
						<?php echo $this->escape($item->title); ?>
					<?php endif; ?>
					<div class="smallsub small">
						<?php 
						$desc = strip_tags($item->description);
						if(function_exists('mb_substr')) {
							echo mb_substr($desc,0,120); if(strlen($desc) > 120) echo '...';
						} else {
							echo substr($desc,0,120); if(strlen($desc) > 120) echo '...';
						} ?>
					</div>
				</td>
				<td class="center">
					<?php echo JHtml::_('jgrid.published', $item->published, $i, 'items.', true, 'cb'	); ?>
				</td>
			
				<td align="center">
					<?php echo $item->category_title; ?>
				</td>
				<td align="center">
					<?php echo $item->id; ?>
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
</div>

</div>

<div class="clr" style="clear: both"></div>
<?php echo DJIMAGESLIDERFOOTER; ?>