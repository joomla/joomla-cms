<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_menus
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

use Joomla\Registry\Registry;

// Initialise related data.
$menuLinks = MenusHelper::getMenuLinks('main');

JHtml::_('stylesheet', 'com_menus/admin-item-edit_container.css', array('version' => 'auto', 'relative' => true));
JHtml::_('script', 'com_menus/admin-item-edit_container.min.js', array('version' => 'auto', 'relative' => true));
JHtml::_('script', 'system/treeselectmenu.min.js', array('version' => 'auto', 'relative' => true));
?>

<div id="menuselect-group" class="control-group">
	<div class="control-label"><?php echo $this->form->getLabel('hideitems', 'params'); ?></div>

	<div id="jform_params_hideitems" class="controls">
		<?php if (!empty($menuLinks)) : ?>
		<?php $id = 'jform_params_hideitems'; ?>

		<div class="form-inline">
			<span class="small mr-2"><?php echo JText::_('COM_MENUS_ACTION_EXPAND'); ?>:
				<a id="treeExpandAll" href="javascript://"><?php echo JText::_('JALL'); ?></a>,
				<a id="treeCollapseAll" href="javascript://"><?php echo JText::_('JNONE'); ?></a> |
				<?php echo JText::_('JSHOW'); ?>:
				<a id="treeUncheckAll" href="javascript://"><?php echo JText::_('JALL'); ?></a>,
				<a id="treeCheckAll" href="javascript://"><?php echo JText::_('JNONE'); ?></a>
			</span>
			<input type="text" id="treeselectfilter" name="treeselectfilter" class="form-control search-query"
				autocomplete="off" placeholder="<?php echo JText::_('JSEARCH_FILTER'); ?>" aria-invalid="false" tabindex="-1">
		</div>

		<hr>

		<ul class="treeselect">
			<?php if (count($menuLinks)) : ?>
				<?php $prevlevel = 0; ?>
				<joomla-alert type="info"><?php echo JText::_('COM_MENUS_ITEM_FIELD_COMPONENTS_CONTAINER_HIDE_ITEMS_DESC'); ?></joomla-alert>
				<li>
				<?php
				$params      = new Registry($this->item->params);
				$hiddenLinks = (array) $params->get('hideitems');

				foreach ($menuLinks as $i => $link) : ?>
					<?php
					if ($extension = $link->element):
						$lang->load("$extension.sys", JPATH_ADMINISTRATOR, null, false, true)
						|| $lang->load("$extension.sys", JPATH_ADMINISTRATOR . '/components/' . $extension, null, false, true);
					endif;

					if ($prevlevel < $link->level)
					{
						echo '<ul class="treeselect-sub">';
					}
					elseif ($prevlevel > $link->level)
					{
						echo str_repeat('</li></ul>', $prevlevel - $link->level);
					}
					else
					{
						echo '</li>';
					}

					$selected = in_array($link->value, $hiddenLinks) ? 1 : 0;
					?>
						<li>
							<div class="treeselect-item">
								<input type="checkbox" <?php echo $link->value > 1 ? ' name="jform[params][hideitems][]" ' : ''; ?>
									   id="<?php echo $id . $link->value; ?>" value="<?php echo (int) $link->value; ?>" class="novalidate checkbox-toggle"
									<?php echo $selected ? ' checked="checked"' : ''; ?>>

								<?php if ($link->value == 1): ?>
									<label for="<?php echo $id . $link->value; ?>" class="btn btn-sm btn-info"><?php echo JText::_('JALL') ?></label>
								<?php else: ?>
									<label for="<?php echo $id . $link->value; ?>" class="btn btn-sm btn-danger btn-hide"><?php echo JText::_('JHIDE') ?></label>
									<label for="<?php echo $id . $link->value; ?>" class="btn btn-sm btn-success btn-show"><?php echo JText::_('JSHOW') ?></label>
									<label for="<?php echo $id . $link->value; ?>"><?php echo JText::_($link->text); ?></label>
								<?php endif; ?>
							</div>
					<?php

					if (!isset($menuLinks[$i + 1]))
					{
						echo str_repeat('</li></ul>', $link->level);
					}
					$prevlevel = $link->level;
					?>
					<?php endforeach; ?>
				</li>
				<?php endif; ?>
		</ul>
		<joomla-alert id="noresultsfound" type="warning" style="display:none;"><?php echo JText::_('JGLOBAL_NO_MATCHING_RESULTS'); ?></joomla-alert>
		<?php endif; ?>
	</div>
</div>
