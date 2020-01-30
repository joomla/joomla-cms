<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  Template.hathor
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');

JHtml::_('behavior.multiselect');
JHtml::_('behavior.modal', 'a.modal');

$user      = JFactory::getUser();
$userId    = $user->get('id');
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
?>
<form id="adminForm" name="adminForm" action="<?php echo JRoute::_('index.php?option=com_banners&view=tracks'); ?>" method="post">
<?php if (!empty( $this->sidebar)) : ?>
	<div class="span2" id="j-sidebar-container">
		<?php echo $this->sidebar; ?>
	</div>
	<div class="span10" id="j-main-container">
<?php else : ?>
	<div id="j-main-container">
<?php endif;?>

		<fieldset id="filter-bar">
			<legend class="element-invisible"><?php echo JText::_('COM_BANNERS_BEGIN_LABEL'); ?></legend>
			<div class="filter-search">
				<label class="filter-hide-lbl" for="filter_begin"><?php echo JText::_('COM_BANNERS_BEGIN_LABEL'); ?></label>
				<?php echo JHtml::_('calendar', $this->state->get('filter.begin'), 'filter_begin', 'filter_begin', '%Y-%m-%d', array('size' => 10));?>

				<label class="filter-hide-lbl" for="filter_end"><?php echo JText::_('COM_BANNERS_END_LABEL'); ?></label>
				<?php echo JHtml::_('calendar', $this->state->get('filter.end'), 'filter_end', 'filter_end', '%Y-%m-%d', array('size' => 10));?>
			</div>

			<div class="filter-select">
				<label class="selectlabel" for="filter_client_id">
					<?php echo JText::_('COM_BANNERS_SELECT_CLIENT'); ?>
				</label>
				<select id="filter_client_id" name="filter_client_id">
					<option value=""><?php echo JText::_('COM_BANNERS_SELECT_CLIENT');?></option>
					<?php echo JHtml::_('select.options', BannersHelper::getClientOptions(), 'value', 'text', $this->state->get('filter.client_id'));?>
				</select>

				<label class="selectlabel" for="filter_category_id">
					<?php echo JText::_('JOPTION_SELECT_CATEGORY'); ?>
				</label>
				<?php $category = $this->state->get('filter.category_id');?>
				<select id="filter_category_id" name="filter_category_id">
					<option value=""><?php echo JText::_('JOPTION_SELECT_CATEGORY');?></option>
					<?php echo JHtml::_('select.options', JHtml::_('category.options', 'com_banners'), 'value', 'text', $category);?>
				</select>

				<label class="selectlabel" for="filter_type">
					<?php echo JText::_('COM_BANNERS_SELECT_TYPE'); ?>
				</label>
				<select id="filter_type" name="filter_type">
					<?php echo JHtml::_('select.options', array(JHtml::_('select.option', '0', JText::_('COM_BANNERS_SELECT_TYPE')), JHtml::_('select.option', 1, JText::_('COM_BANNERS_IMPRESSION')), JHtml::_('select.option', 2, JText::_('COM_BANNERS_CLICK'))), 'value', 'text', $this->state->get('filter.type'));?>
				</select>

				<button id="filter-go" type="submit"><?php echo JText::_('JSUBMIT'); ?></button>
			</div>
		</fieldset>

		<div class="clr"> </div>

		<table class="adminlist">
			<thead>
				<tr>
					<th class="title">
						<?php echo JHtml::_('grid.sort', 'COM_BANNERS_HEADING_NAME', 'name', $listDirn, $listOrder); ?>
					</th>
					<th class="nowrap width-20">
						<?php echo JHtml::_('grid.sort', 'COM_BANNERS_HEADING_CLIENT', 'client_name', $listDirn, $listOrder); ?>
					</th>
					<th class="width-20">
						<?php echo JHtml::_('grid.sort', 'JCATEGORY', 'category_title', $listDirn, $listOrder); ?>
					</th>
					<th class="nowrap width-10">
						<?php echo JHtml::_('grid.sort', 'COM_BANNERS_HEADING_TYPE', 'track_type', $listDirn, $listOrder); ?>
					</th>
					<th class="nowrap width-10">
						<?php echo JHtml::_('grid.sort', 'COM_BANNERS_HEADING_COUNT', 'count', $listDirn, $listOrder); ?>
					</th>
					<th class="nowrap width-10">
						<?php echo JHtml::_('grid.sort', 'JDATE', 'track_date', $listDirn, $listOrder); ?>
					</th>
				</tr>
			</thead>

			<tbody>
			<?php foreach ($this->items as $i => $item) :?>
				<tr class="row<?php echo $i % 2; ?>">
					<td>
						<?php echo $item->banner_name;?>
					</td>
					<td>
						<?php echo $item->client_name;?>
					</td>
					<td>
						<?php echo $item->category_title;?>
					</td>
					<td>
						<?php echo $item->track_type == 1 ? JText::_('COM_BANNERS_IMPRESSION'): JText::_('COM_BANNERS_CLICK');?>
					</td>
					<td>
						<?php echo $item->count;?>
					</td>
					<td>
						<?php echo JHtml::_('date', $item->track_date, JText::_('DATE_FORMAT_LC5'));?>
					</td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>

		<?php // Load the export form ?>
		<?php echo JHtml::_(
			'bootstrap.renderModal',
			'downloadModal',
			array(
				'title'       => JText::_('COM_BANNERS_TRACKS_DOWNLOAD'),
				'url'         => JRoute::_('index.php?option=com_banners&amp;view=download&amp;tmpl=component'),
				'width'       => '100%',
				'height'      => '300px',
				'footer'      => '<button type="button" class="btn" data-dismiss="modal"'
						. ' onclick="jQuery(\'#downloadModal iframe\').contents().find(\'#closeBtn\').click();">'
						. JText::_('COM_BANNERS_CANCEL') . '</button>'
						. '<button type="button" class="btn btn-success"'
						. ' onclick="jQuery(\'#downloadModal iframe\').contents().find(\'#exportBtn\').click();">'
						. JText::_('COM_BANNERS_TRACKS_EXPORT') . '</button>',
			)
		); ?>

		<?php echo $this->pagination->getListFooter(); ?>

		<input name="task" type="hidden" value="" />
		<input name="boxchecked" type="hidden" value="0" />
		<input name="filter_order" type="hidden" value="<?php echo $listOrder; ?>" />
		<input name="filter_order_Dir" type="hidden" value="<?php echo $listDirn; ?>" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>
