<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_templates
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;

HTMLHelper::_('behavior.multiselect');

$user      = Factory::getUser();
$clientId = (int) $this->state->get('client_id', 0);
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa    = $this->document->getWebAssetManager();
$wa->addInlineStyle(
<<<CSS
.subhead joomla-toolbar-button {
-webkit-margin-start: 0;
margin-inline-start: 0;
}
.subhead .btn, .subhead .btn-group {
-webkit-margin-start: .75rem;
margin-inline-start: .75rem;
}

.subhead .btn > span {
-webkit-margin-end: .5rem;
margin-inline-end: .5rem;
}
CSS
);

$wa->registerAndUseScript('com_templates.styles_default_esm', 'com_templates/admin-styles-default.es6.js', ['version' => 'auto', 'relative' => true], ['type' => 'module', 'defer' => true]);
$wa->registerAndUseScript('com_templates.styles_default', 'com_templates/admin-styles-default.js', ['version' => 'auto', 'relative' => true], ['nomodule' => '', 'defer' => true]);
?>
<form action="<?php echo Route::_('index.php?option=com_templates&view=styles'); ?>" method="post" name="adminForm" id="adminForm">
	<div class="row">
		<div class="col-md-12">
			<div id="j-main-container" class="j-main-container">
				<?php echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this, 'options' => array('selectorFieldName' => 'client_id'))); ?>
				<?php if ($this->total > 0) : ?>
					<table class="table" id="styleList">
						<caption id="captionTable" class="sr-only">
							<?php echo Text::_('COM_TEMPLATES_STYLES_TABLE_CAPTION'); ?>,
							<span id="orderedBy"><?php echo Text::_('JGLOBAL_SORTED_BY'); ?> </span>,
							<span id="filteredBy"><?php echo Text::_('JGLOBAL_FILTERED_BY'); ?></span>
						</caption>
						<thead>
							<tr>
								<td class="w-1 text-center">
<!--									--><?php //echo HTMLHelper::_('grid.checkall'); ?>
								</td>
								<th scope="col">
									<?php echo HTMLHelper::_('searchtools.sort', 'COM_TEMPLATES_HEADING_STYLE', 'a.title', $listDirn, $listOrder); ?>
								</th>
								<th scope="col" class="w-5 text-center">
									<?php echo Text::_('COM_TEMPLATES_TEMPLATE_PREVIEW'); ?>
								</th>
								<th scope="col" class="w-12 text-center">
									<?php echo HTMLHelper::_('searchtools.sort', 'COM_TEMPLATES_HEADING_DEFAULT', 'a.home', $listDirn, $listOrder); ?>
								</th>
								<?php if ($clientId === 0) : ?>
									<th scope="col" class="w-12 d-none d-md-table-cell">
										<?php echo Text::_('COM_TEMPLATES_HEADING_PAGES'); ?>
									</th>
								<?php endif; ?>
								<th scope="col" class="w-12 d-none d-md-table-cell">
									<?php echo HTMLHelper::_('searchtools.sort', 'COM_TEMPLATES_HEADING_TEMPLATE', 'a.template', $listDirn, $listOrder); ?>
								</th>
								<th scope="col" class="w-5 d-none d-md-table-cell">
									<?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
								</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ($this->items as $i => $item) :
								$canCreate = $user->authorise('core.create',     'com_templates');
								$canEdit   = $user->authorise('core.edit',       'com_templates');
								$canChange = $user->authorise('core.edit.state', 'com_templates');
								$isParent  = $item->inheritable ? 'true' : 'false';
								$canEdit   = $canChange ? 'true' : 'false';
								$isHome    = $item->home == '0' ? 'false' : 'true';
								$isChild   = !empty($item->parent) ? 'true' : 'false';
							?>
							<tr class="row<?php echo $i % 2; ?>">
								<td class="w-1 text-center">
									<?php echo '<label for="cb' . $i . '"><span class="sr-only">' . Text::_('JSELECT')
											. ' ' . htmlspecialchars($item->title, ENT_COMPAT, 'UTF-8') . '</span></label>'
											. '<input type="radio" id="cb' . $i . '" name="cid[]" value="' . $item->id
											. '" onclick="Joomla.isChecked(this.checked);" data-is-parent="' . $isParent
											. '" data-can-edit="' . $canEdit . '" data-is-home="' . $isHome . '" data-is-child="' . $isChild . '">';
									?>
								</td>
								<th scope="row">
									<?php if ($canEdit) : ?>
										<a href="<?php echo Route::_('index.php?option=com_templates&task=style.edit&id=' . (int) $item->id); ?>" title="<?php echo Text::_('JACTION_EDIT'); ?> <?php echo $this->escape($item->title); ?>">
											<?php echo $this->escape($item->title); ?></a>
									<?php else : ?>
										<?php echo $this->escape($item->title); ?>
									<?php endif; ?>
								</th>
								<td class="text-center">
									<?php if ($this->preview) : ?>
										<?php $client = (int) $item->client_id === 1 ? 'administrator' : 'site'; ?>
										<!-- external link icon generated by css -->
										<a href="<?php echo Route::link($client, 'index.php?tp=1&templateStyle=' . (int) $item->id); ?>" target="_blank" class="jgrid"
											title="<?php echo Text::sprintf('JBROWSERTARGET_NEW_TITLE', $item->title); ?>">
											<span class="sr-only"><?php echo Text::_('COM_TEMPLATES_TEMPLATE_PREVIEW'); ?></span>
										</a>
									<?php else : ?>
										<span class="fas fa-eye-slash" aria-hidden="true" title="<?php echo Text::_('COM_TEMPLATES_TEMPLATE_NO_PREVIEW'); ?>"></span>
										<span class="sr-only"><?php echo Text::_('COM_TEMPLATES_TEMPLATE_NO_PREVIEW'); ?></span>
									<?php endif; ?>
								</td>
								<td class="text-center">
									<?php if ($item->home == '0' || $item->home == '1') : ?>
										<?php echo HTMLHelper::_('jgrid.isdefault', $item->home != '0', $i, 'styles.', $canChange && $item->home != '1'); ?>
									<?php elseif ($canChange):?>
										<a href="<?php echo Route::_('index.php?option=com_templates&task=styles.unsetDefault&cid[]=' . $item->id . '&' . Session::getFormToken() . '=1'); ?>">
											<?php if ($item->image) : ?>
												<?php echo HTMLHelper::_('image', 'mod_languages/' . $item->image . '.gif', $item->language_title, array('title' => Text::sprintf('COM_TEMPLATES_GRID_UNSET_LANGUAGE', $item->language_title)), true); ?>
											<?php else : ?>
												<span class="badge badge-secondary" title="<?php echo Text::sprintf('COM_TEMPLATES_GRID_UNSET_LANGUAGE', $item->language_title); ?>"><?php echo $item->home; ?></span>
											<?php endif; ?>
										</a>
									<?php else : ?>
										<?php if ($item->image) : ?>
											<?php echo HTMLHelper::_('image', 'mod_languages/' . $item->image . '.gif', $item->language_title, array('title' => $item->language_title), true); ?>
										<?php else : ?>
											<span class="badge badge-secondary" title="<?php echo $item->language_title; ?>"><?php echo $item->home; ?></span>
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
								<td class="small d-none d-md-table-cell">
									<a href="<?php echo Route::_('index.php?option=com_templates&view=template&id=' . (int) $item->e_id); ?>">
										<?php echo ucfirst($this->escape($item->template)); ?>
									</a>
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
