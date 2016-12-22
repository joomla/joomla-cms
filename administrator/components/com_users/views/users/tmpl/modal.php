<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_users
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');

JHtml::_('bootstrap.tooltip', '.hasTooltip', array('placement' => 'bottom'));

JHtml::_('behavior.multiselect');

// Special case for the search field tooltip.
$searchFilterDesc = $this->filterForm->getFieldAttribute('search', 'description', null, 'filter');
JHtml::_('bootstrap.tooltip', '#filter_search', array('title' => JText::_($searchFilterDesc), 'placement' => 'bottom'));

$app             = JFactory::getApplication();
$input           = $app->input;
$field           = $input->getCmd('field');
$listOrder       = $this->escape($this->state->get('list.ordering'));
$listDirn        = $this->escape($this->state->get('list.direction'));
$enabledStates   = array(0 => 'icon-publish', 1 => 'icon-unpublish');
$activatedStates = array(0 => 'icon-publish', 1 => 'icon-unpublish');
$userRequired    = (int) $input->get('required', 0, 'int');

/**
 * Mootools compatibility
 *
 * There is an extra option passed in the url for the iframe &ismoo=0 for the bootstraped field.
 * By default the value will be 1 or defaults to mootools behaviour using function jSelectUser()
 *
 * This should be removed when mootools won't be shipped by Joomla.
 */
$isMoo      = $input->getInt('ismoo', 1);

if ($isMoo)
{
	$onClick = "window.parent.jSelectUser(this);window.parent.jQuery('.modal.in').modal('hide');";
}

?>
<div class="container-popup">
	<form action="<?php echo JRoute::_('index.php?option=com_users&view=users&layout=modal&tmpl=component&groups=' . $input->get('groups', '', 'BASE64') . '&excluded=' . $input->get('excluded', '', 'BASE64')); ?>" method="post" name="adminForm" id="adminForm">
		<?php if (!$userRequired) : ?>
		<div class="float-xs-left">
			<button type="button" class="btn button-select" data-user-value="0" data-user-name="<?php echo $this->escape(JText::_('JLIB_FORM_SELECT_USER')); ?>"
				data-user-field="<?php echo $this->escape($field); ?>" <?php if ($isMoo) : ?>value="" onclick="window.parent.jSelectUser(this)"<?php endif; ?>><?php echo JText::_('JOPTION_NO_USER'); ?></button>&nbsp;
		</div>
		<?php endif; ?>
		<?php echo JLayoutHelper::render('joomla.searchtools.default', array('view' => $this)); ?>
		<?php if (empty($this->items)) : ?>
		<div class="alert alert-warning alert-no-items">
			<?php echo JText::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
		</div>
		<?php else : ?>
		<table class="table table-striped table-sm">
			<thead>
				<tr>
					<th class="nowrap">
						<?php echo JHtml::_('searchtools.sort', 'COM_USERS_HEADING_NAME', 'a.name', $listDirn, $listOrder); ?>
					</th>
					<th width="25%" class="nowrap">
						<?php echo JHtml::_('searchtools.sort', 'JGLOBAL_USERNAME', 'a.username', $listDirn, $listOrder); ?>
					</th>
					<th width="1%" class="nowrap text-xs-center">
						<?php echo JHtml::_('searchtools.sort', 'COM_USERS_HEADING_ENABLED', 'a.block', $listDirn, $listOrder); ?>
					</th>
					<th width="1%" class="nowrap text-xs-center">
						<?php echo JHtml::_('searchtools.sort', 'COM_USERS_HEADING_ACTIVATED', 'a.activation', $listDirn, $listOrder); ?>
					</th>
					<th width="25%" class="nowrap">
						<?php echo JText::_('COM_USERS_HEADING_GROUPS'); ?>
					</th>
					<th width="1%" class="nowrap">
						<?php echo JHtml::_('searchtools.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
					</th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<td colspan="6">
						<?php echo $this->pagination->getListFooter(); ?>
					</td>
				</tr>
			</tfoot>
			<tbody>
				<?php
				$i = 0;
				foreach ($this->items as $item) : ?>
				<tr class="row<?php echo $i % 2; ?>">
					<td>
						<a class="pointer button-select" href="#" data-user-value="<?php echo $item->id; ?>" data-user-name="<?php echo $this->escape($item->name); ?>"
							data-user-field="<?php echo $this->escape($field); ?>" <?php if ($isMoo) : ?>onclick="<?php echo $onClick; ?>"<?php endif; ?>>
							<?php echo $this->escape($item->name); ?>
						</a>
					</td>
					<td>
						<?php echo $this->escape($item->username); ?>
					</td>
					<td class="text-xs-center">
						<span class="<?php echo $enabledStates[(int) $this->escape($item->block)]; ?>"></span>
					</td>
					<td class="text-xs-center">
						<span class="<?php echo $activatedStates[(int) $this->escape($item->activation)]; ?>"></span>
					</td>
					<td>
						<?php echo nl2br($item->group_names); ?>
					</td>
					<td>
						<?php echo (int) $item->id; ?>
					</td>
				</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
		<?php endif; ?>
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="field" value="<?php echo $this->escape($field); ?>" />
		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="required" value="<?php echo $userRequired; ?>" />
		<input type="hidden" name="ismoo" value="<?php echo $input->get('ismoo', 1, 'int'); ?>" />
		<?php echo JHtml::_('form.token'); ?>
	</form>
</div>
