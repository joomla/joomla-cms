<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_templates
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Include the component HTML helpers.
JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');

JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.multiselect');


$user      = JFactory::getUser();
$clientId = (int) $this->state->get('client_id', 0);
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
$colSpan = $clientId === 1 ? 5 : 6;
?>
<form action="<?php echo JRoute::_('index.php?option=com_templates&view=styles'); ?>" method="post" name="adminForm" id="adminForm">
	<div class="row">
		<div id="j-sidebar-container" class="col-md-2">
			<?php echo $this->sidebar; ?>
		</div>
		<div class="col-md-10">
			<div id="j-main-container" class="j-main-container">
				<?php echo JLayoutHelper::render('joomla.searchtools.default', array('view' => $this, 'options' => array('selectorFieldName' => 'client_id'))); ?>
				<?php if ($this->total > 0) : ?>
					<table class="table table-striped" id="styleList">
						<thead>
							<tr>
								<th width="1%" class="nowrap text-center">
									&#160;
								</th>
								<th class="nowrap">
									<?php echo JHtml::_('searchtools.sort', 'COM_TEMPLATES_HEADING_STYLE', 'a.title', $listDirn, $listOrder); ?>
								</th>
								<th width="12%" class="nowrap text-center">
									<?php echo JHtml::_('searchtools.sort', 'COM_TEMPLATES_HEADING_DEFAULT', 'a.home', $listDirn, $listOrder); ?>
								</th>
								<?php if ($clientId === 0) : ?>
								<th width="12%" class="nowrap hidden-sm-down text-center">
									<?php echo JText::_('COM_TEMPLATES_HEADING_PAGES'); ?>
								</th>
								<?php endif; ?>
								<th width="12%" class="hidden-sm-down text-center">
									<?php echo JHtml::_('searchtools.sort', 'COM_TEMPLATES_HEADING_TEMPLATE', 'a.template', $listDirn, $listOrder); ?>
								</th>
								<th width="5%" class="nowrap hidden-sm-down text-center">
									<?php echo JHtml::_('searchtools.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
								</th>
							</tr>
						</thead>
						<tfoot>
							<tr>
								<td colspan="<?php echo $colSpan; ?>">
									<?php echo $this->pagination->getListFooter(); ?>
								</td>
							</tr>
						</tfoot>
						<tbody>
							<?php foreach ($this->items as $i => $item) :
								$canCreate = $user->authorise('core.create',     'com_templates');
								$canEdit   = $user->authorise('core.edit',       'com_templates');
								$canChange = $user->authorise('core.edit.state', 'com_templates');
							?>
							<tr class="row<?php echo $i % 2; ?>">
								<td width="1%" class="text-center">
									<?php echo JHtml::_('grid.id', $i, $item->id); ?>
								</td>
								<td>
									<?php if ($this->preview && $item->client_id == '0') : ?>
										<a target="_blank" href="<?php echo JUri::root() . 'index.php?tp=1&templateStyle=' . (int) $item->id ?>" class="jgrid">
										<span class="icon-eye-open hasTooltip" title="<?php echo JHtml::_('tooltipText', JText::_('COM_TEMPLATES_TEMPLATE_PREVIEW'), $item->title, 0); ?>" ></span></a>
									<?php elseif ($item->client_id == '1') : ?>
										<span class="icon-eye-close disabled hasTooltip" title="<?php echo JHtml::_('tooltipText', 'COM_TEMPLATES_TEMPLATE_NO_PREVIEW_ADMIN'); ?>" ></span>
									<?php else: ?>
										<span class="icon-eye-close disabled hasTooltip" title="<?php echo JHtml::_('tooltipText', 'COM_TEMPLATES_TEMPLATE_NO_PREVIEW'); ?>" ></span>
									<?php endif; ?>
									<?php if ($canEdit) : ?>
									<a href="<?php echo JRoute::_('index.php?option=com_templates&task=style.edit&id=' . (int) $item->id); ?>">
										<?php echo $this->escape($item->title); ?></a>
									<?php else : ?>
										<?php echo $this->escape($item->title); ?>
									<?php endif; ?>
								</td>
								<td class="text-center">
									<?php if ($item->home == '0' || $item->home == '1') : ?>
										<?php echo JHtml::_('jgrid.isdefault', $item->home != '0', $i, 'styles.', $canChange && $item->home != '1'); ?>
									<?php elseif ($canChange):?>
										<a href="<?php echo JRoute::_('index.php?option=com_templates&task=styles.unsetDefault&cid[]=' . $item->id . '&' . JSession::getFormToken() . '=1'); ?>">
											<?php if ($item->image) : ?>
												<?php echo JHtml::_('image', 'mod_languages/' . $item->image . '.gif', $item->language_title, array('title' => JText::sprintf('COM_TEMPLATES_GRID_UNSET_LANGUAGE', $item->language_title)), true); ?>
											<?php else : ?>
												<span class="label" title="<?php echo JText::sprintf('COM_TEMPLATES_GRID_UNSET_LANGUAGE', $item->language_title); ?>"><?php echo $item->language_sef; ?></span>
											<?php endif; ?>
										</a>
									<?php else : ?>
										<?php if ($item->image) : ?>
											<?php echo JHtml::_('image', 'mod_languages/' . $item->image . '.gif', $item->language_title, array('title' => $item->language_title), true); ?>
										<?php else : ?>
											<span class="label" title="<?php echo $item->language_title; ?>"><?php echo $item->language_sef; ?></span>
										<?php endif; ?>
									<?php endif; ?>
								</td>
								<?php if ($clientId === 0) : ?>
								<td class="small hidden-sm-down text-center">
									<?php if ($item->home == '1') : ?>
										<?php echo JText::_('COM_TEMPLATES_STYLES_PAGES_ALL'); ?>
									<?php elseif ($item->home != '0' && $item->home != '1') : ?>
										<?php echo JText::sprintf('COM_TEMPLATES_STYLES_PAGES_ALL_LANGUAGE', $this->escape($item->language_title)); ?>
									<?php elseif ($item->assigned > 0) : ?>
										<?php echo JText::sprintf('COM_TEMPLATES_STYLES_PAGES_SELECTED', $this->escape($item->assigned)); ?>
									<?php else : ?>
										<?php echo JText::_('COM_TEMPLATES_STYLES_PAGES_NONE'); ?>
									<?php endif; ?>
								</td>
								<?php endif; ?>
								<td class="hidden-sm-down text-center">
									<label for="cb<?php echo $i; ?>" class="small">
										<a href="<?php echo JRoute::_('index.php?option=com_templates&view=template&id=' . (int) $item->e_id); ?>  ">
											<?php echo ucfirst($this->escape($item->template)); ?>
										</a>
									</label>
								</td>
								<td class="hidden-sm-down text-center">
									<?php echo (int) $item->id; ?>
								</td>
							</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				<?php endif; ?>

				<input type="hidden" name="task" value="" />
				<input type="hidden" name="boxchecked" value="0" />
				<?php echo JHtml::_('form.token'); ?>
			</div>
		</div>
	</div>
</form>
