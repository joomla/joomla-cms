<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_modules
 *
 * @copyright   (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
use Joomla\Component\Menus\Administrator\Helper\MenusHelper;
use Joomla\Component\Modules\Administrator\Helper\ModulesHelper;

// Initialise related data.
$menuTypes = MenusHelper::getMenuLinks();

$this->document->getWebAssetManager()
	->useScript('joomla.treeselectmenu')
	->useScript('com_modules.admin-module-edit-assignment');

?>
<div class="control-group">
	<label id="jform_menus-lbl" class="control-label" for="jform_assignment"><?php echo Text::_('COM_MODULES_MODULE_ASSIGN'); ?></label>
	<div id="jform_menus" class="controls">
		<select class="form-select" name="jform[assignment]" id="jform_assignment">
			<?php echo HTMLHelper::_('select.options', ModulesHelper::getAssignmentOptions($this->item->client_id), 'value', 'text', $this->item->assignment, true); ?>
		</select>
	</div>
</div>
<div id="menuselect-group" class="control-group">
	<label id="jform_menuselect-lbl" class="control-label" for="jform_menuselect"><?php echo Text::_('JGLOBAL_MENU_SELECTION'); ?></label>
	<div id="jform_menuselect" class="controls">
		<?php if (!empty($menuTypes)) : ?>

		<div class="card-header">
			<section class="d-flex align-items-center flex-wrap w-100" aria-label="<?php echo Text::_('COM_MODULES_GLOBAL'); ?>">
				<div class="d-flex align-items-center flex-fill mb-1" role="group" aria-label="<?php echo Text::_('COM_MODULES_GLOBAL_ASSIGN'); ?>"><?php echo Text::_('COM_MODULES_GLOBAL_ASSIGN'); ?>
					<button id="treeCheckAll" class="btn btn-secondary btn-sm mx-1" type="button">
						<?php echo Text::_('JALL'); ?>
					</button>
					<button id="treeUncheckAll" class="btn btn-secondary btn-sm mx-1" type="button">
						<?php echo Text::_('JNONE'); ?>
					</button>
				</div>
				<div class="d-flex align-items-center mb-1 flex-fill" role="group" aria-label="<?php echo Text::_('COM_MODULES_GLOBAL_TREE_EXPAND'); ?>"><?php echo Text::_('COM_MODULES_GLOBAL_TREE_EXPAND'); ?>
					<button id="treeExpandAll" class="btn btn-secondary btn-sm mx-1" type="button">
						<?php echo Text::_('JALL'); ?>
					</button>
					<button id="treeCollapseAll" class="btn btn-secondary btn-sm mx-1" type="button">
						<?php echo Text::_('JNONE'); ?>
					</button>
				</div>
				<div role="search" class="flex-grow-1">
					<label for="treeselectfilter" class="visually-hidden"><?php echo Text::_('COM_MODULES_SEARCH_MENUITEM'); ?></label>
					<input type="text" id="treeselectfilter" name="treeselectfilter" class="form-control search-query" autocomplete="off" placeholder="<?php echo Text::_('JSEARCH_FILTER'); ?>">
				</div>
			</section>
		</div>
		<div class="card-body">
			<ul class="treeselect">
				<?php foreach ($menuTypes as &$type) : ?>
				<?php if (count($type->links)) : ?>
					<?php $prevlevel = 0; ?>
					<li>
						<div class="treeselect-item treeselect-header">
							<label class="nav-header"><?php echo $type->title; ?></label></div>
					<?php foreach ($type->links as $i => $link) : ?>
						<?php
						if ($prevlevel < $link->level)
						{
							echo '<ul class="treeselect-sub">';
						} elseif ($prevlevel > $link->level)
						{
							echo str_repeat('</li></ul>', $prevlevel - $link->level);
						} else {
							echo '</li>';
						}
						$selected = 0;
						if ($this->item->assignment == 0)
						{
							$selected = 1;
						} elseif ($this->item->assignment < 0)
						{
							$selected = in_array(-$link->value, $this->item->assigned);
						} elseif ($this->item->assignment > 0)
						{
							$selected = in_array($link->value, $this->item->assigned);
						}
						?>
							<li>
								<div class="treeselect-item">
									<?php
									$uselessMenuItem = in_array($link->type, array('separator', 'heading', 'alias', 'url'));
									$id = 'jform_menuselect';
									?>
									<input type="checkbox" class="novalidate form-check-input" name="jform[assigned][]" id="<?php echo $id . $link->value; ?>" value="<?php echo (int) $link->value; ?>"<?php echo $selected ? ' checked="checked"' : ''; echo $uselessMenuItem ? ' disabled="disabled"' : ''; ?>>
									<label for="<?php echo $id . $link->value; ?>" class="">
										<?php echo $link->text; ?>
										<?php if (Multilanguage::isEnabled() && $link->language != '' && $link->language != '*') : ?>
											<?php if ($link->language_image) : ?>
												<?php echo HTMLHelper::_('image', 'mod_languages/' . $link->language_image . '.gif', $link->language_title, array('title' => $link->language_title), true); ?>
											<?php else : ?>
												<?php echo '<span class="badge bg-secondary" title="' . $link->language_title . '">' . $link->language_sef . '</span>'; ?>
											<?php endif; ?>
										<?php endif; ?>
										<?php if ($link->published == 0) : ?>
											<?php echo ' <span class="badge bg-secondary">' . Text::_('JUNPUBLISHED') . '</span>'; ?>
										<?php endif; ?>
										<?php if ($uselessMenuItem) : ?>
											<?php echo ' <span class="badge bg-secondary">' . Text::_('COM_MODULES_MENU_ITEM_' . strtoupper($link->type)) . '</span>'; ?>
										<?php endif; ?>
									</label>
								</div>
						<?php

						if (!isset($type->links[$i + 1]))
						{
							echo str_repeat('</li></ul>', $link->level);
						}
						$prevlevel = $link->level;
						?>
						<?php endforeach; ?>
					</li>
					<?php endif; ?>
				<?php endforeach; ?>
			</ul>
			<joomla-alert id="noresultsfound" type="warning" style="display:none"><?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?></joomla-alert>
			<div class="hidden" id="treeselectmenu">
				<div class="nav-hover treeselect-menu">
					<div class="dropdown">
						<button type="button" data-bs-toggle="dropdown" class="dropdown-toggle btn btn-sm btn-light">
							<span class="caret"></span>
							<span class="visually-hidden"><?php echo Text::sprintf('JGLOBAL_TOGGLE_DROPDOWN'); ?></span>
						</button>
						<div class="dropdown-menu">
							<h1 class="dropdown-header"><?php echo Text::_('COM_MODULES_SUBITEMS'); ?></h1>
							<div class="dropdown-divider"></div>
							<a class="dropdown-item checkall" href="javascript://"><span class="icon-check-square" aria-hidden="true"></span> <?php echo Text::_('JSELECT'); ?></a>
							<a class="dropdown-item uncheckall" href="javascript://"><span class="icon-square" aria-hidden="true"></span> <?php echo Text::_('COM_MODULES_DESELECT'); ?></a>
							<div class="treeselect-menu-expand">
								<div class="dropdown-divider"></div>
								<a class="dropdown-item expandall" href="javascript://"><span class="icon-plus" aria-hidden="true"></span> <?php echo Text::_('COM_MODULES_EXPAND'); ?></a>
								<a class="dropdown-item collapseall" href="javascript://"><span class="icon-minus" aria-hidden="true"></span> <?php echo Text::_('COM_MODULES_COLLAPSE'); ?></a>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<?php endif; ?>
	</div>
</div>
