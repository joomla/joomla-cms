<?php
/**
 * @version
 * @package		Joomla.Administrator
 * @subpackage	Modules
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */
?>
<?php defined('_JEXEC') or die;

// Initiasile related data.
require_once JPATH_ADMINISTRATOR.'/components/com_menus/helpers/menus.php';
$menuTypes = MenusHelper::getMenuLinks();
?>

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
			<button type="button" onclick="Joomla.submitform('module.save', this.form);window.top.setTimeout('window.parent.SqueezeBox.close()', 1400);">
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

			<fieldset id="jform_menus" class="radio">
					<select name="jform[assignment]">
						<?php echo JHtml::_('select.options', ModulesHelper::getAssignmentOptions($this->item->client_id), 'value', 'text', $this->item->assignment, true);?>
					</select>

				</fieldset>

				<label id="jform_menuselect-lbl" class="hasTip" for="jform_menuselect"><?php echo JText::_('Menu Selection'); ?>:</label>

				<div class="clr"></div>

				<img src="" onclick="$$('.chk-menulink').each(function(el) { el.checked = !el.checked; });" alt="<?php echo JText::_('JCheckInvert'); ?>" title="<?php echo JText::_('JCheckInvert'); ?>">

				<div id="menu-assignment" style="height: 300px; overflow: auto;">

				<?php foreach ($menuTypes as &$type) : ?>
					<div class="menu-links">
						<h3><?php echo $type->title ? $type->title : $type->menutype; ?></h3>
						<?php
						foreach ($type->links as $link) :
							if ($this->item->assignment < 0) :
								$checked = in_array(-$link->value, $this->item->assigned) ? ' checked="checked"' : '';
							else :
								$checked = in_array($link->value, $this->item->assigned) ? ' checked="checked"' : '';
							endif;
						?>
						<div class="menu-link">
							<input type="checkbox" class="chk-menulink" name="jform[assigned][]" value="<?php echo (int) $link->value;?>" id="link<?php echo (int) $link->value;?>"<?php echo $checked;?>/>
							<label for="link<?php echo (int) $link->value;?>">
								<?php echo $link->text; ?>
							</label>
						</div>
						<div class="clr"></div>
						<?php endforeach; ?>
					</div>
				<?php endforeach; ?>
				</div>

		</fieldset>

	<input type="hidden" name="option" value="com_modules" />
	<input type="hidden" name="jform[id]" value="<?php echo $this->item->id; ?>" />
	<input type="hidden" name="cid[]" value="<?php echo $this->item->id; ?>" />
	<input type="hidden" name="original" value="<?php echo $this->item->ordering; ?>" />
	<input type="hidden" name="jform[module]" value="<?php echo $this->item->module; ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="jform[client_id]" value="<?php echo $this->item->client_id ?>" />
	<?php echo JHtml::_('form.token'); ?>
</form>
</div>