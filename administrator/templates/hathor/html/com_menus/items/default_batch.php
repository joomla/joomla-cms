<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	templates.hathor
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 * @since		1.6
 */

// no direct access
defined('_JEXEC') or die;

$options = array(
	JHtml::_('select.option', 'c', JText::_('JGLOBAL_BATCH_COPY')),
	JHtml::_('select.option', 'm', JText::_('JGLOBAL_BATCH_MOVE'))
);
$published = $this->state->get('filter.published');
?>
<fieldset class="batch">
	<legend><?php echo JText::_('COM_MENUS_BATCH_OPTIONS');?></legend>

			<label id="batch-access-lbl" for="batch-access">
				<?php echo JText::_('JGLOBAL_BATCH_ACCESS_LABEL'); ?>
			</label>
			<?php echo JHtml::_('access.assetgrouplist', 'batch[assetgroup_id]', '', 'class="inputbox"', array('title' => '', 'id' => 'batch-access'));?>

			<?php if ($published >= 0) : ?>
				<label id="batch-choose-action-lbl" for="batch-menu-id">
					<?php echo JText::_('JGLOBAL_BATCH_ACCESS_LABEL'); ?>
				</label>
					<select name="batch[menu_id]" class="inputbox" id="batch-menu-id">
						<option></option>
						<?php echo JHtml::_('select.options', JHtml::_('menu.menuitems', array('published' => $published)));?>
					</select>
					<?php echo JHTML::_( 'select.radiolist', $options, 'batch[move_copy]', '', 'value', 'text', 'm'); ?>
			<?php endif; ?>

		<button type="submit" onclick="Joomla.submitbutton('item.batch');">
			<?php echo JText::_('JGLOBAL_BATCH_PROCESS'); ?>
		</button>
</fieldset>
