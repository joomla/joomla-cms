<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_modules
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Initialise related data.
JLoader::register('MenusHelper', JPATH_ADMINISTRATOR . '/components/com_menus/helpers/menus.php');
$menuTypes = MenusHelper::getMenuLinks();

JHtml::_('script', 'jui/treeselectmenu.jquery.min.js', array('version' => 'auto', 'relative' => true));

$script = "
	jQuery(document).ready(function()
	{
		menuHide(jQuery('#jform_assignment').val());
		jQuery('#jform_assignment').change(function()
		{
			menuHide(jQuery(this).val());
		})
	});
	function menuHide(val)
	{
		if (val == 0 || val == '-')
		{
			jQuery('#menuselect-group').hide();
		}
		else
		{
			jQuery('#menuselect-group').show();
		}
	}
";

// Add the script to the document head
JFactory::getDocument()->addScriptDeclaration($script);
?>
<div class="control-group">
	<label id="jform_menus-lbl" class="control-label" for="jform_menus"><?php echo JText::_('COM_MODULES_MODULE_ASSIGN'); ?></label>
	<div id="jform_menus" class="controls">
		<select class="custom-select" name="jform[assignment]" id="jform_assignment">
			<?php echo JHtml::_('select.options', ModulesHelper::getAssignmentOptions($this->item->client_id), 'value', 'text', $this->item->assignment, true); ?>
		</select>
	</div>
</div>
<div id="menuselect-group" class="control-group">
	<label id="jform_menuselect-lbl" for="jform_menuselect"><?php echo JText::_('JGLOBAL_MENU_SELECTION'); ?></label>

	<div id="jform_menuselect">
		<?php if (!empty($menuTypes)) : ?>
		<?php $id = 'jform_menuselect'; ?>

		<div class="well well-small">
			<div class="form-inline">
				<span class="small"><?php echo JText::_('JSELECT'); ?>:
					<a id="treeCheckAll" href="javascript://"><?php echo JText::_('JALL'); ?></a>,
					<a id="treeUncheckAll" href="javascript://"><?php echo JText::_('JNONE'); ?></a>
				</span>
				<span class="width-20">|</span>
				<span class="small"><?php echo JText::_('COM_MODULES_EXPAND'); ?>:
					<a id="treeExpandAll" href="javascript://"><?php echo JText::_('JALL'); ?></a>,
					<a id="treeCollapseAll" href="javascript://"><?php echo JText::_('JNONE'); ?></a>
				</span>
				<input type="text" id="treeselectfilter" name="treeselectfilter" class="form-control search-query float-right" size="16"
					autocomplete="off" placeholder="<?php echo JText::_('JSEARCH_FILTER'); ?>" aria-invalid="false" tabindex="-1">
			</div>

			<hr>

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
									$uselessMenuItem = (in_array($link->type, array('separator', 'heading', 'alias', 'url')));
									?>
									<input type="checkbox" class="float-left novalidate" name="jform[assigned][]" id="<?php echo $id . $link->value; ?>" value="<?php echo (int) $link->value; ?>"<?php echo $selected ? ' checked="checked"' : ''; echo $uselessMenuItem ? ' disabled="disabled"' : ''; ?> />
									<label for="<?php echo $id . $link->value; ?>" class="float-left">
										<?php echo $link->text; ?> <span class="small"><?php echo JText::sprintf('JGLOBAL_LIST_ALIAS', $this->escape($link->alias)); ?></span>
										<?php if (JLanguageMultilang::isEnabled() && $link->language != '' && $link->language != '*') : ?>
											<?php if ($link->language_image) : ?>
												<?php echo JHtml::_('image', 'mod_languages/' . $link->language_image . '.gif', $link->language_title, array('title' => $link->language_title), true); ?>
											<?php else : ?>
												<?php echo '<span class="label" title="' . $link->language_title . '">' . $link->language_sef . '</span>'; ?>
											<?php endif; ?>
										<?php endif; ?>
										<?php if ($link->published == 0) : ?>
											<?php echo ' <span class="label">' . JText::_('JUNPUBLISHED') . '</span>'; ?>
										<?php endif; ?>
										<?php if ($uselessMenuItem) : ?>
											<?php echo ' <span class="label">' . JText::_('COM_MODULES_MENU_ITEM_' . strtoupper($link->type)) . '</span>'; ?>
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
			<div id="noresultsfound" style="display:none;" class="alert alert-warning alert-no-items">
				<?php echo JText::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
			</div>
			<div style="display:none" id="treeselectmenu">
				<div class="nav-hover treeselect-menu">
					<div class="dropdown">
						<a href="#" data-toggle="dropdown" class="dropdown-toggle btn btn-xs btn-secondary">
							<span class="caret"></span>
						</a>
						<div class="dropdown-menu">
							<h5 class="dropdown-header"><?php echo JText::_('COM_MODULES_SUBITEMS'); ?></h5>
							<div class="dropdown-divider"></div>
							<a class="dropdown-item checkall" href="javascript://"><span class="icon-checkbox"></span> <?php echo JText::_('JSELECT'); ?></a>
							<a class="dropdown-item uncheckall" href="javascript://"><span class="icon-checkbox-unchecked"></span> <?php echo JText::_('COM_MODULES_DESELECT'); ?></a>
							<div class="treeselect-menu-expand">
								<div class="dropdown-divider"></div>
								<a class="dropdown-item expandall" href="javascript://"><span class="icon-plus"></span> <?php echo JText::_('COM_MODULES_EXPAND'); ?></a>
								<a class="dropdown-item collapseall" href="javascript://"><span class="icon-minus"></span> <?php echo JText::_('COM_MODULES_COLLAPSE'); ?></a>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<?php endif; ?>
	</div>
</div>
