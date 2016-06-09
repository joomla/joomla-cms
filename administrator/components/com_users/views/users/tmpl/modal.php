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
JHtml::_('formbehavior.chosen', 'select');
JHtml::_('behavior.multiselect');

// Special case for the search field tooltip.
$searchFilterDesc = $this->filterForm->getFieldAttribute('search', 'description', null, 'filter');
JHtml::_('bootstrap.tooltip', '#filter_search', array('title' => JText::_($searchFilterDesc), 'placement' => 'bottom'));

$app              = JFactory::getApplication();
$input            = $app->input;
$field            = $input->getCmd('field');
$basetype         = $input->getCmd('basetype');
$function         = $input->getCmd('function', 'jSelectUser');
$listOrder        = $this->escape($this->state->get('list.ordering'));
$listDirn         = $this->escape($this->state->get('list.direction'));
$enabledStates    = array(0 => 'icon-publish', 1 => 'icon-unpublish');
$activatedStates  = array(0 => 'icon-publish', 1 => 'icon-unpublish');
$userRequired     = (int) $input->get('required', 0, 'int');
?>
<div class="container-popup">
	<form
		id="adminForm"
		name="adminForm"
		action="<?php echo JRoute::_('index.php?option=com_users&view=users&layout=modal&tmpl=component&function=' . $function
			. '&groups=' . $input->get('groups', '', 'BASE64') . '&excluded=' . $input->get('excluded', '', 'BASE64')); ?>"
		method="post"
		>

		<?php if ( ! $userRequired && $basetype != 'modal') : ?>
		<?php // @deprecated  3.6.0  No User button kept for B/C with Mootools modal ?>
			<div class="pull-left">
				<button
					class="btn button-select"
					data-user-field="<?php echo $this->escape($field);?>"
					data-user-name="<?php echo $this->escape(JText::_('JLIB_FORM_SELECT_USER')); ?>"
					data-user-value="0"
					type="button"
					onclick="if (window.parent) window.parent.jSelectUser(this);"
					>
					<?php echo JText::_('JOPTION_NO_USER'); ?>
				</button>
				<span>&nbsp;</span>
			</div>
		<?php endif; ?>

		<?php echo JLayoutHelper::render('joomla.searchtools.default', array('view' => $this)); ?>

		<?php if (empty($this->items)) : ?>
			<div class="alert alert-no-items">
				<?php echo JText::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
			</div>
		<?php else : ?>
			<table class="table table-striped table-condensed">
				<thead>
					<tr>
						<th class="nowrap">
							<?php echo JHtml::_('searchtools.sort', 'COM_USERS_HEADING_NAME', 'a.name', $listDirn, $listOrder); ?>
						</th>
						<th class="nowrap" width="25%">
							<?php echo JHtml::_('searchtools.sort', 'JGLOBAL_USERNAME', 'a.username', $listDirn, $listOrder); ?>
						</th>
						<th class="nowrap center" width="1%">
							<?php echo JHtml::_('searchtools.sort', 'COM_USERS_HEADING_ENABLED', 'a.block', $listDirn, $listOrder); ?>
						</th>
						<th class="nowrap center" width="1%">
							<?php echo JHtml::_('searchtools.sort', 'COM_USERS_HEADING_ACTIVATED', 'a.activation', $listDirn, $listOrder); ?>
						</th>
						<th class="nowrap" width="25%">
							<?php echo JText::_('COM_USERS_HEADING_GROUPS'); ?>
						</th>
						<th class="nowrap" width="1%">
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
					<?php $i = 0; ?>
					<?php foreach ($this->items as $item) : ?>
						<tr class="row<?php echo $i % 2; ?>">
							<td>
								<?php if ($basetype == 'modal') : ?>
									<a
										class="pointer button-select"
										href="javascript:void(0);"
										onclick="if (window.parent) window.parent.<?php echo $this->escape($function); ?>
											('<?php echo $item->id; ?>', '<?php echo $this->escape(addslashes($item->name)); ?>');"
										>
										<?php echo $this->escape($item->name); ?>
									</a>
								<?php else : ?>
									<a
										class="pointer button-select"
										data-user-value="<?php echo $item->id; ?>"
										data-user-name="<?php echo $this->escape($item->name); ?>"
										data-user-field="<?php echo $this->escape($field);?>"
										href="#"
										onclick="if (window.parent) window.parent.jSelectUser(this);"
										>
										<?php echo $this->escape($item->name); ?>
									</a>
								<?php endif; ?>
							</td>
							<td>
								<?php echo $this->escape($item->username); ?>
							</td>
							<td class="center">
								<span class="<?php echo $enabledStates[(int) $this->escape($item->block)]; ?>"></span>
							</td>
							<td class="center">
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

		<input name="task" type="hidden" value="" />
		<input name="field" type="hidden" value="<?php echo $this->escape($field); ?>" />
		<input name="boxchecked" type="hidden" value="0" />
		<input name="required" type="hidden" value="<?php echo $userRequired; ?>" />
		<?php echo JHtml::_('form.token'); ?>
	</form>
</div>
