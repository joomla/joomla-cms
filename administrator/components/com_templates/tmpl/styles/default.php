<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_templates
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Uri\Uri;

HTMLHelper::_('behavior.multiselect');

$user      = Factory::getUser();
$clientId = (int) $this->state->get('client_id', 0);
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
?>
<form action="<?php echo Route::_('index.php?option=com_templates&view=styles'); ?>" method="post" name="adminForm" id="adminForm">
	<div class="row">
		<div class="col-md-12">
			<div id="j-main-container" class="j-main-container">
				<?php echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this, 'options' => array('selectorFieldName' => 'client_id'))); ?>
				<?php if ($this->total > 0) : ?>
					<table class="table" id="styleList">
						<caption id="captionTable" class="sr-only">
							<?php echo Text::_('COM_TEMPLATES_STYLES_TABLE_CAPTION'); ?>, <?php echo Text::_('JGLOBAL_SORTED_BY'); ?>
						</caption>
						<thead>
							<tr>
								<td style="width:1%" class="text-center">
									<?php echo HTMLHelper::_('grid.checkall'); ?>
								</td>
								<th scope="col">
									<?php echo JHtml::_('searchtools.sort', 'COM_TEMPLATES_HEADING_STYLE', 'a.title', $listDirn, $listOrder); ?>
								</th>
								<?php if ($clientId === 0) : ?>
									<th scope="col" style="width:5%" class="text-center">
										<?php echo Text::_('COM_TEMPLATES_TEMPLATE_PREVIEW'); ?>
									</th>
								<?php endif; ?>
								<th scope="col" style="width:12%" class="text-center">
									<?php echo JHtml::_('searchtools.sort', 'COM_TEMPLATES_HEADING_DEFAULT', 'a.home', $listDirn, $listOrder); ?>
								</th>
								<?php if ($clientId === 0) : ?>
									<th scope="col" style="width:12%" class="d-none d-md-table-cell">
										<?php echo JText::_('COM_TEMPLATES_HEADING_PAGES'); ?>
									</th>
								<?php endif; ?>
								<th scope="col" style="width:12%" class="d-none d-md-table-cell">
									<?php echo JHtml::_('searchtools.sort', 'COM_TEMPLATES_HEADING_TEMPLATE', 'a.template', $listDirn, $listOrder); ?>
								</th>
								<th scope="col" style="width:5%" class="d-none d-md-table-cell">
									<?php echo JHtml::_('searchtools.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
								</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ($this->items as $i => $item) :
								$canCreate = $user->authorise('core.create',     'com_templates');
								$canEdit   = $user->authorise('core.edit',       'com_templates');
								$canChange = $user->authorise('core.edit.state', 'com_templates');
							?>
							<tr class="row<?php echo $i % 2; ?>">
								<td style="width:1%" class="text-center">
									<?php echo HTMLHelper::_('grid.id', $i, $item->id); ?>
								</td>
								<th scope="row">
									<?php if ($canEdit) : ?>
										<a href="<?php echo JRoute::_('index.php?option=com_templates&task=style.edit&id=' . (int) $item->id); ?>" title="<?php echo JText::_('JACTION_EDIT'); ?> <?php echo $this->escape(addslashes($item->title)); ?>">
											<span class="fa fa-pen-square mr-2" aria-hidden="true"></span><?php echo $this->escape($item->title); ?></a>
									<?php else : ?>
										<?php echo $this->escape($item->title); ?>
									<?php endif; ?>
								</th>
								<?php if ($clientId === 0) : ?>
									<td class="text-center">
										<?php if ($this->preview && $item->client_id == '0') : ?>
											<a target="_blank" href="<?php echo Uri::root() . 'index.php?tp=1&templateStyle=' . (int) $item->id ?>" class="jgrid">
											<span class="icon-eye-open hasTooltip" aria-hidden="true" title="<?php echo HTMLHelper::_('tooltipText', Text::_('COM_TEMPLATES_TEMPLATE_PREVIEW'), $item->title, 0); ?>"></span>
											<span class="sr-only"><?php echo Text::_('COM_TEMPLATES_TEMPLATE_PREVIEW'); ?></span>
											</a>
										<?php else: ?>
											<span class="icon-eye-close disabled hasTooltip" aria-hidden="true" title="<?php echo HTMLHelper::_('tooltipText', 'COM_TEMPLATES_TEMPLATE_NO_PREVIEW'); ?>"></span>
											<span class="sr-only"><?php echo Text::_('COM_TEMPLATES_TEMPLATE_NO_PREVIEW'); ?></span>
										<?php endif; ?>
									</td>
								<?php endif; ?>
								<td class="text-center">
									<?php if ($item->home == '0' || $item->home == '1') : ?>
										<?php echo HTMLHelper::_('jgrid.isdefault', $item->home != '0', $i, 'styles.', $canChange && $item->home != '1'); ?>
									<?php elseif ($canChange):?>
										<a href="<?php echo Route::_('index.php?option=com_templates&task=styles.unsetDefault&cid[]=' . $item->id . '&' . Session::getFormToken() . '=1'); ?>">
											<?php if ($item->image) : ?>
												<?php echo HTMLHelper::_('image', 'mod_languages/' . $item->image . '.gif', $item->language_title, array('title' => Text::sprintf('COM_TEMPLATES_GRID_UNSET_LANGUAGE', $item->language_title)), true); ?>
											<?php else : ?>
												<span class="badge badge-secondary" title="<?php echo Text::sprintf('COM_TEMPLATES_GRID_UNSET_LANGUAGE', $item->language_title); ?>"><?php echo $item->language_sef; ?></span>
											<?php endif; ?>
										</a>
									<?php else : ?>
										<?php if ($item->image) : ?>
											<?php echo HTMLHelper::_('image', 'mod_languages/' . $item->image . '.gif', $item->language_title, array('title' => $item->language_title), true); ?>
										<?php else : ?>
											<span class="badge badge-secondary" title="<?php echo $item->language_title; ?>"><?php echo $item->language_sef; ?></span>
										<?php endif; ?>
									<?php endif; ?>
								</td>
								<?php if ($clientId === 0) : ?>
								<td class="small d-none d-md-table-cell">
									<?php if ($item->home == '1') : ?>
										<?php echo Text::_('COM_TEMPLATES_STYLES_PAGES_ALL'); ?>
									<?php elseif ($item->home != '0' && $item->home != '1') : ?>
										<?php echo Text::sprintf('COM_TEMPLATES_STYLES_PAGES_ALL_LANGUAGE', $this->escape($item->language_title)); ?>
									<?php elseif ($item->assigned > 0) : ?>
										<?php echo Text::sprintf('COM_TEMPLATES_STYLES_PAGES_SELECTED', $this->escape($item->assigned)); ?>
									<?php else : ?>
										<?php echo Text::_('COM_TEMPLATES_STYLES_PAGES_NONE'); ?>
									<?php endif; ?>
								</td>
								<?php endif; ?>
								<td class="d-none d-md-table-cell">
									<label for="cb<?php echo $i; ?>" class="small">
										<a href="<?php echo Route::_('index.php?option=com_templates&view=template&id=' . (int) $item->e_id); ?>  ">
											<?php echo ucfirst($this->escape($item->template)); ?>
										</a>
									</label>
								</td>
								<td class="d-none d-md-table-cell">
									<?php echo (int) $item->id; ?>
								</td>
							</tr>
							<?php endforeach; ?>
						</tbody>
					</table>

					<?php // load the pagination. ?>
					<?php echo $this->pagination->getListFooter(); ?>

				<?php endif; ?>

				<input type="hidden" name="task" value="">
				<input type="hidden" name="boxchecked" value="0">
				<?php echo HTMLHelper::_('form.token'); ?>
			</div>
		</div>
	</div>
</form>
