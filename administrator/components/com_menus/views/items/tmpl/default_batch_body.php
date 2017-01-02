<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_menus
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

$options = array(
	JHtml::_('select.option', 'c', JText::_('JLIB_HTML_BATCH_COPY')),
	JHtml::_('select.option', 'm', JText::_('JLIB_HTML_BATCH_MOVE'))
);
$published = $this->state->get('filter.published');
$menuType = JFactory::getApplication()->getUserState('com_menus.items.menutype');
?>
<?php if (strlen($menuType) && $menuType != '*') : ?>
<div class="container-fluid">
	<div class="row-fluid">
		<div class="control-group span6">
			<div class="controls">
				<?php echo JHtml::_('batch.language'); ?>
			</div>
		</div>
		<div class="control-group span6">
			<div class="controls">
				<?php echo JHtml::_('batch.access'); ?>
			</div>
		</div>
	</div>
	<div class="row-fluid">
		<?php if ($published >= 0) : ?>
			<div id="batch-choose-action" class="combo control-group">
				<label id="batch-choose-action-lbl" class="control-label" for="batch-choose-action">
					<?php echo JText::_('COM_MENUS_BATCH_MENU_LABEL'); ?>
				</label>
				<div class="controls">
					<select name="batch[menu_id]" id="batch-menu-id">
						<option value=""><?php echo JText::_('JLIB_HTML_BATCH_NO_CATEGORY'); ?></option>
						<?php echo JHtml::_('select.options', JHtml::_('menu.menuitems', array('published' => $published, 'checkacl' => (int) $this->state->get('menutypeid')))); ?>
					</select>
				</div>
			</div>
			<div id="batch-copy-move" class="control-group radio">
				<?php echo JText::_('JLIB_HTML_BATCH_MOVE_QUESTION'); ?>
				<?php echo JHtml::_('select.radiolist', $options, 'batch[move_copy]', '', 'value', 'text', 'm'); ?>
			</div>
		<?php endif; ?>
	</div>
	<?php else : ?>
	<div class="row-fluid">
		<p><?php echo JText::_('COM_MENUS_SELECT_MENU_FIRST'); ?></p>
	</div>
	<?php endif; ?>
</div>
