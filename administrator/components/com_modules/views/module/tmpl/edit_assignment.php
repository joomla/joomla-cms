<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_modules
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Initiasile related data.
require_once JPATH_ADMINISTRATOR . '/components/com_menus/helpers/menus.php';
$menuTypes = MenusHelper::getMenuLinks();

?>
<script type="text/javascript">
	jQuery(function($)
	{
		var treeselectmenu = $('div#treeselectmenu').html();

		$('.treeselect li').each(function()
		{
			$li = $(this);
			$div = $li.find('div.treeselect-item:first');

			// Add icons
			$li.prepend('<i class="pull-left icon-"></i>');

			// Append clearfix
			$div.after('<div class="clearfix"></div>');

			if ($li.find('ul').length > 1 || $li.find('ul:first').children('li').length > 1) {
				// Add classes to Expand/Collapse icons
				$li.find('i').addClass('treeselect-toggle icon-minus');

				// Append drop down menu in nodes
				$div.find('label:first').after(treeselectmenu);

				// Add mouse actions for showing drop down menu
				$div
					.mouseenter(function()
					{
						$(this).find('.btn-group').removeClass('open').css('visibility', 'visible');
					})
					.mouseleave(function()
					{
						$(this).find('.btn-group').removeClass('open').css('visibility', 'hidden');
					});
			}
		});

		// Takes care of the Expand/Collapse of a node
		$('i.treeselect-toggle').click(function()
		{
			$i = $(this);
			$ulvisible = $i.parent().find('ul').is(':visible');

			// Take care of parent UL
			$i.removeClass('icon-plus icon-minus').addClass($ulvisible ? 'icon-plus' : 'icon-minus').parent().find('ul').toggle();

			// Take care of children image folders
			$i.parent().find('ul i.treeselect-toggle').removeClass('icon-plus icon-minus').addClass($ulvisible ? 'icon-plus' : 'icon-minus');

			// Take care of children ULs
			if ($ulvisible) {
				$i.parent()
					.find('ul')
					.hide();
			} else {
				$i.parent()
					.find('ul')
					.show();
			}
		});

		// Takes care of the filtering
		$('#treeselectfilter').keyup(function()
		{
			var text = $(this).val().toLowerCase();
			$('.treeselect li').each(function()
			{
				if ($(this).text().toLowerCase().indexOf(text) == -1) {
					$(this).hide();
				}
				else {
					$(this).show();
				}
			});
		});

		// Checks all checkboxes the tree
		$('#treeCheckAll').click(function()
		{
			$('.treeselect input').attr('checked', 'checked');
		});

		// Unchecks all checkboxes the tree
		$('#treeUncheckAll').click(function()
		{
			$('.treeselect input').attr('checked', false);
		});

		// Checks all checkboxes the tree
		$('#treeExpandAll').click(function()
		{
			$('ul.treeselect ul').show();
			$('ul.treeselect i.treeselect-toggle').removeClass('icon-plus').addClass('icon-minus');
		});

		// Unchecks all checkboxes the tree
		$('#treeCollapseAll').click(function()
		{
			$('ul.treeselect ul').hide();
			$('ul.treeselect i.treeselect-toggle').removeClass('icon-minus').addClass('icon-plus');
		});

		// Take care of children check/uncheck all
		$('a.checkall').click(function()
		{
			$(this).parent().parent().parent().parent().parent().parent().find('ul input').attr('checked', 'checked');
		});
		$('a.uncheckall').click(function()
		{
			$(this).parent().parent().parent().parent().parent().parent().find('ul input').attr('checked', false);
		});

		// Take care of children toggle all
		$('a.expandall').click(function()
		{
			$(this).parent().parent().parent().parent().parent().parent().find('li > ul').show();
		});
		$('a.collapseall').click(function()
		{
			$(this).parent().parent().parent().parent().parent().parent().find('li > ul').hide();
		});
	});
</script>

<div class="control-group">
	<label id="jform_menus-lbl" class="control-label" for="jform_menus"><?php echo JText::_('COM_MODULES_MODULE_ASSIGN'); ?></label>

	<div id="jform_menus" class="controls">
		<select name="jform[assignment]" id="jform_assignment">
			<?php echo JHtml::_('select.options', ModulesHelper::getAssignmentOptions($this->item->client_id), 'value', 'text', $this->item->assignment, true); ?>
		</select>
	</div>
</div>
<div class="control-group">
	<label id="jform_menuselect-lbl" class="control-label" for="jform_menuselect"><?php echo JText::_('JGLOBAL_MENU_SELECTION'); ?></label>

	<div id="jform_menuselect" class="controls">
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
				<input type="text" id="treeselectfilter" name="treeselectfilter" class="input-medium search-query pull-right" size="16"
					autocomplete="off" placeholder="<?php echo JText::_('JSEARCH_FILTER'); ?>" aria-invalid="false" tabindex="-1">
			</div>

			<div class="clearfix"></div>

			<hr class="hr-condensed" />

			<ul class="treeselect">
				<?php foreach ($menuTypes as &$type) : ?>
				<?php if (count($type->links)) : ?>
					<?php $prevlevel = 0; ?>
					<li>
						<div class="treeselect-item pull-left">
							<label class="pull-left nav-header"><?php echo $type->title; ?></label></div>
					<?php foreach ($type->links as $i => $link) : ?>
						<?php
						if ($prevlevel < $link->level) {
							echo '<ul>';
						} else if ($prevlevel > $link->level) {
							echo str_repeat('</li></ul>', $prevlevel - $link->level);
						} else {
							echo '</li>';
						}
						$selected = 0;
						if ($this->item->assignment == 0) {
							$selected = 1;
						} elseif ($this->item->assignment < 0) {
							$selected = in_array(-$link->value, $this->item->assigned);
						} elseif ($this->item->assignment > 0) {
							$selected = in_array($link->value, $this->item->assigned);
						}
						?>
							<li>
								<div class="treeselect-item pull-left">
									<input type="checkbox" class="pull-left" name="jform[assigned][]" id="<?php echo $id . $link->value; ?>" value="<?php echo (int) $link->value; ?>"<?php echo $selected ? ' checked="checked"' : ''; ?> />
									<label for="<?php echo $id . $link->value; ?>" class="pull-left"><?php echo $link->text; ?></label>
								</div>
						<?php

						if (!isset($type->links[$i + 1])) {
							echo str_repeat('</li></ul>', $link->level);
						}
						$prevlevel = $link->level;
						?>
						<?php endforeach; ?>
					</li>
					<?php endif; ?>
				<?php endforeach; ?>
			</ul>
			<div style="display:none;" id="treeselectmenu">
				<div class="pull-left nav-hover treeselect-menu">
					<div class="btn-group" style="visibility: hidden;">
						<a href="#" data-toggle="dropdown" class="dropdown-toggle btn btn-micro">
							<span class="caret"></span>
						</a>
						<ul class="dropdown-menu">
							<li class="nav-header"><?php echo JText::_('COM_MODULES_SUBITEMS'); ?></li>
							<li class="divider"></li>
							<li class=""><a class="checkall" href="javascript://"><i class="icon-checkbox"></i> <?php echo JText::_('JSELECT'); ?></a>
							</li>
							<li><a class="uncheckall" href="javascript://"><i class="icon-checkbox-unchecked"></i> <?php echo JText::_('COM_MODULES_DESELECT'); ?></a>
							</li>
							<li class="divider"></li>
							<li><a class="expandall" href="javascript://"><i class="icon-plus"></i> <?php echo JText::_('COM_MODULES_EXPAND'); ?></a></li>
							<li><a class="collapseall" href="javascript://"><i class="icon-minus"></i> <?php echo JText::_('COM_MODULES_COLLAPSE'); ?></a></li>
						</ul>
					</div>
				</div>
			</div>
		</div>
		<?php endif; ?>
	</div>
</div>
