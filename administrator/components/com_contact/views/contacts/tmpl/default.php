<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_contact
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');

JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.multiselect');
JHtml::_('formbehavior.chosen', 'select');

$user      = JFactory::getUser();
$userId    = $user->get('id');
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
$saveOrder = $listOrder == 'a.ordering';
$assoc     = JLanguageAssociations::isEnabled();

if ($saveOrder)
{
	$saveOrderingUrl = 'index.php?option=com_contact&task=contacts.saveOrderAjax&tmpl=component';
	JHtml::_('sortablelist.sortable', 'contactList', 'adminForm', strtolower($listDirn), $saveOrderingUrl);
}
?>
<form action="<?php echo JRoute::_('index.php?option=com_contact'); ?>" method="post" name="adminForm" id="adminForm">
	<?php if (!empty($this->sidebar)) : ?>
	<div id="j-sidebar-container" class="span2">
		<?php echo $this->sidebar; ?>
	</div>
	<div id="j-main-container" class="span10">
	<?php else : ?>
	<div id="j-main-container">
	<?php endif; ?>
		<?php echo JLayoutHelper::render('joomla.searchtools.default', array('view' => $this)); ?>
		<div class="clearfix"></div>
		<?php if (empty($this->items)) : ?>
		<div class="alert alert-no-items">
			<?php echo JText::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
		</div>
		<?php else : ?>
			<table class="table table-striped" id="contactList">
				<thead>
					<tr>
						<th width="1%" class="nowrap center hidden-phone">
							<?php echo JHtml::_('searchtools.sort', '', 'a.ordering', $listDirn, $listOrder, null, 'asc', 'JGRID_HEADING_ORDERING', 'icon-menu-2'); ?>
						</th>
						<th width="1%" class="nowrap center">
							<?php echo JHtml::_('grid.checkall'); ?>
						</th>
						<th width="1%" style="min-width:55px" class="nowrap center">
							<?php echo JHtml::_('searchtools.sort', 'JSTATUS', 'a.published', $listDirn, $listOrder); ?>
						</th>
						<th class="nowrap">
							<?php echo JHtml::_('searchtools.sort', 'JGLOBAL_TITLE', 'a.name', $listDirn, $listOrder); ?>
						</th>
						<th class="nowrap hidden-phone hidden-tablet">
							<?php echo JHtml::_('searchtools.sort', 'COM_CONTACT_FIELD_LINKED_USER_LABEL', 'ul.name', $listDirn, $listOrder); ?>
						</th>
						<th width="10%" class="nowrap hidden-phone">
							<?php echo JHtml::_('searchtools.sort', 'JGRID_HEADING_ACCESS', 'access_level', $listDirn, $listOrder); ?>
						</th>
						<?php if ($assoc) : ?>
						<th width="5%" class="nowrap hidden-phone hidden-tablet">
							<?php echo JHtml::_('searchtools.sort', 'COM_CONTACT_HEADING_ASSOCIATION', 'association', $listDirn, $listOrder); ?>
						</th>
						<?php endif; ?>
						<th width="15%" class="nowrap hidden-phone">
							<?php echo JHtml::_('searchtools.sort', 'JGRID_HEADING_LANGUAGE', 'language_title', $listDirn, $listOrder); ?>
						</th>
						<th width="1%" class="nowrap hidden-phone">
							<?php echo JHtml::_('searchtools.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
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
				$n = count($this->items);
				foreach ($this->items as $i => $item) :
					$canCreate  = $user->authorise('core.create',     'com_contact.category.' . $item->catid);
					$canEdit    = $user->authorise('core.edit',       'com_contact.category.' . $item->catid);
					$canCheckin = $user->authorise('core.manage',     'com_checkin') || $item->checked_out == $userId || $item->checked_out == 0;
					$canEditOwn = $user->authorise('core.edit.own',   'com_contact.category.' . $item->catid) && $item->created_by == $userId;
					$canChange  = $user->authorise('core.edit.state', 'com_contact.category.' . $item->catid) && $canCheckin;

					$item->cat_link = JRoute::_('index.php?option=com_categories&extension=com_contact&task=edit&type=other&id=' . $item->catid);
					?>
					<tr class="row<?php echo $i % 2; ?>" sortable-group-id="<?php echo $item->catid; ?>">
						<td class="order nowrap center hidden-phone">
							<?php
							$iconClass = '';
							if (!$canChange)
							{
								$iconClass = ' inactive';
							}
							elseif (!$saveOrder)
							{
								$iconClass = ' inactive tip-top hasTooltip" title="' . JHtml::tooltipText('JORDERINGDISABLED');
							}
							?>
							<span class="sortable-handler<?php echo $iconClass; ?>">
								<span class="icon-menu"></span>
							</span>
							<?php if ($canChange && $saveOrder) : ?>
								<input type="text" style="display:none" name="order[]" size="5"
									value="<?php echo $item->ordering; ?>" class="width-20 text-area-order " />
							<?php endif; ?>
						</td>
						<td class="center">
							<?php echo JHtml::_('grid.id', $i, $item->id); ?>
						</td>
						<td class="center">
							<div class="btn-group">
								<?php echo JHtml::_('jgrid.published', $item->published, $i, 'contacts.', $canChange, 'cb', $item->publish_up, $item->publish_down); ?>
								<?php echo JHtml::_('contact.featured', $item->featured, $i, $canChange); ?>
								<?php // Create dropdown items and render the dropdown list.
								if ($canChange)
								{
									JHtml::_('actionsdropdown.' . ((int) $item->published === 2 ? 'un' : '') . 'archive', 'cb' . $i, 'contacts');
									JHtml::_('actionsdropdown.' . ((int) $item->published === -2 ? 'un' : '') . 'trash', 'cb' . $i, 'contacts');
									echo JHtml::_('actionsdropdown.render', $this->escape($item->name));
								}
								?>
							</div>
						</td>
						<td class="nowrap has-context">
							<div class="pull-left">
								<?php if ($item->checked_out) : ?>
									<?php echo JHtml::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, 'contacts.', $canCheckin); ?>
								<?php endif; ?>
								<?php if ($canEdit || $canEditOwn) : ?>
									<a href="<?php echo JRoute::_('index.php?option=com_contact&task=contact.edit&id=' . (int) $item->id); ?>"><?php echo $this->escape($item->name); ?></a>
								<?php else : ?>
									<?php echo $this->escape($item->name); ?>
								<?php endif; ?>
								<span class="small">
									<?php echo JText::sprintf('JGLOBAL_LIST_ALIAS', $this->escape($item->alias)); ?>
								</span>
								<div class="small">
									<?php echo JText::_('JCATEGORY') . ': ' . $this->escape($item->category_title); ?>
								</div>
							</div>
						</td>
						<td class="small hidden-phone hidden-tablet">
							<?php if (!empty($item->linked_user)) : ?>
								<a href="<?php echo JRoute::_('index.php?option=com_users&task=user.edit&id=' . $item->user_id); ?>"><?php echo $item->linked_user; ?></a>
								<div class="small"><?php echo $item->email; ?></div>
							<?php endif; ?>
						</td>
						<td class="small hidden-phone">
							<?php echo $item->access_level; ?>
						</td>
						<?php if ($assoc) : ?>
						<td class="hidden-phone hidden-tablet">
							<?php if ($item->association) : ?>
								<?php echo JHtml::_('contact.association', $item->id); ?>
							<?php endif; ?>
						</td>
						<?php endif; ?>
						<td class="small hidden-phone">
							<?php echo JLayoutHelper::render('joomla.content.language', $item); ?>
						</td>
						<td class="hidden-phone">
							<?php echo $item->id; ?>
						</td>
					</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
			<?php // Load the batch processing form. ?>
			<?php if ($user->authorise('core.create', 'com_contact')
				&& $user->authorise('core.edit', 'com_contact')
				&& $user->authorise('core.edit.state', 'com_contact')) : ?>
				<?php echo JHtml::_(
					'bootstrap.renderModal',
					'collapseModal',
					array(
						'title'  => JText::_('COM_CONTACT_BATCH_OPTIONS'),
						'footer' => $this->loadTemplate('batch_footer'),
					),
					$this->loadTemplate('batch_body')
				); ?>
			<?php endif; ?>
		<?php endif; ?>
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>
