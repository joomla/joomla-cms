<?php
/**
 * @version	
 * @package		Joomla.Administrator
 * @subpackage	Modules
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */
?>
<?php defined('_JEXEC') or die; ?>

<?php

	JHtml::_('behavior.tooltip');
?>
<script type="text/javascript">
<!--
	function submitbutton(task)
	{
		if (task == 'item.cancel' || document.formvalidator.isValid(document.id('item-form'))) {
			submitform(task);
		}
	}
// -->
</script>
<div class="">
<form action="<?php echo JRoute::_('index.php');?>" method="post" name="adminForm">

	
	<fieldset class="adminform">
		<legend><?php echo JText::_('Module_Menu_Assignment_Legend'); ?></legend>
	<div class="fltrt">
			<button type="button" onclick="Joomla.submitform('module.save', this.form);window.top.setTimeout('window.parent.SqueezeBox.close()', 700);">
				<?php echo JText::_('Save');?></button>
			<button type="button" onclick="window.parent.SqueezeBox.close();">
				<?php echo JText::_('Cancel');?></button>
		</div>
		<script type="text/javascript">
			function allselections() {
				var e = document.getElementById('selections');
					e.disabled = true;
				var i = 0;
				var n = e.options.length;
				for (i = 0; i < n; i++) {
					e.options[i].disabled = true;
					e.options[i].selected = true;
				}
			}
			function disableselections() {
				var e = document.getElementById('selections');
					e.disabled = true;
				var i = 0;
				var n = e.options.length;
				for (i = 0; i < n; i++) {
					e.options[i].disabled = true;
					e.options[i].selected = false;
				}
			}
			function enableselections() {
				var e = document.getElementById('selections');
					e.disabled = false;
				var i = 0;
				var n = e.options.length;
				for (i = 0; i < n; i++) {
					e.options[i].disabled = false;
					
				}
			}
		</script>
	<!-- TO DO: Need to rework UI for this section -->
			<label id="jform_menus-lbl" class="hasTip" for="jform_menus"><?php echo JText::_('Menus'); ?>:</label>
				<?php if ($this->row->client_id != 1) : ?>
				
			<fieldset id="jform_menus" class="radio">
				<label id="jform_menus-all-lbl" for="menus-all"><?php echo JText::_('All'); ?></label>
				<input id="menus-all" type="radio" name="menus" value="all" onclick="allselections();" <?php
						echo ($this->row->pages == 'all') ? 'checked="checked"' : ''; ?> />
			
				<label id="jform_menus-none-lbl" for="menus-none"><?php echo JText::_('None'); ?></label>	
				<input id="menus-none" type="radio" name="menus" value="none" onclick="disableselections();" <?php
						echo ($this->row->pages == 'none') ? 'checked="checked"' : ''; ?> />
			
				<label id="jform_menus-select-lbl" for="menus-select"><?php echo JText::_('Select From List'); ?></label>	
				<input id="menus-select" type="radio" name="menus" value="select" onclick="enableselections();" <?php
						echo ($this->row->pages == 'select') ? 'checked="checked"' : ''; ?> />
						
				<label id="jform_menus-deselect-lbl" for="menus-deselect"><?php echo JText::_('Deselect From List'); ?></label>
				<input id="menus-deselect" type="radio" name="menus" value="deselect" onclick="enableselections();" <?php
						echo ($this->row->pages == 'deselect') ? 'checked="checked"' : ''; ?> />
			</fieldset>	
				<?php endif; ?>
				
			<label id="jform_menuselect-lbl" class="hasTip" for="jform_menuselect"><?php echo JText::_('Menu Selection'); ?>:</label>
					<?php echo $this->lists['selections']; ?>
			
		<?php if ($this->row->client_id != 1) : ?>
			<?php if ($this->row->pages == 'all') : ?>
			<script type="text/javascript">allselections();</script>
			<?php elseif ($this->row->pages == 'none') : ?>
			<script type="text/javascript">disableselections();</script>
			<?php endif; ?>
		<?php endif; ?>
	</fieldset>



	


	<input type="hidden" name="option" value="com_modules" />
	<input type="hidden" name="jform[id]" value="<?php echo $this->row->id; ?>" />
	<input type="hidden" name="cid[]" value="<?php echo $this->row->id; ?>" />
	<input type="hidden" name="original" value="<?php echo $this->row->ordering; ?>" />
	<input type="hidden" name="jform[module]" value="<?php echo $this->row->module; ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="jform[client_id]" value="<?php echo $this->client->id ?>" />
	<?php echo JHtml::_('form.token'); ?>
</form>
</div>