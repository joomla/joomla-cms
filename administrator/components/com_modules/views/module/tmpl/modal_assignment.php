<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	com_modules
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

// Initiasile related data.
require_once JPATH_ADMINISTRATOR.'/components/com_menus/helpers/menus.php';
$menuTypes = MenusHelper::getMenuLinks();
?>

		<script>
			window.addEvent('domready', function(){
				validate();
				document.getElements('select').addEvent('change', function(e){validate();});
			});
			function validate(){
				var value	= document.id('jform_assignment').value;
				var button 	= document.id('jform_toggle');
				var list	= document.id('menu-assignment');
				if(value == '-' || value == '0'){
					button.setProperty('disabled', true);
					list.getElements('input').each(function(el){
						el.setProperty('disabled', true);
						if (value == '-'){
							el.setProperty('checked', false);
						} else {
							el.setProperty('checked', true);
						}
					});
				} else {
					button.setProperty('disabled', false);
					list.getElements('input').each(function(el){
						el.setProperty('disabled', false);
					});
				}
			}
		</script>

		<fieldset class="adminform">
			<legend><?php echo JText::_('COM_MODULES_MENU_ASSIGNMENT'); ?></legend>
				<label id="jform_menus-lbl" class="hasTip" for="jform_assignment"><?php echo JText::_('COM_MODULES_MODULE_ASSIGN'); ?></label>
				<select name="jform[assignment]"  id="jform_assignment">
					<?php echo JHtml::_('select.options', ModulesHelper::getAssignmentOptions($this->item->client_id), 'value', 'text', $this->item->assignment, true);?>
				</select>

				<button type="button" id="jform_toggle" class="jform-rightbtn" onclick="$$('.chk-menulink').each(function(el) { el.checked = !el.checked; });">
					<?php echo JText::_('JGLOBAL_SELECTION_INVERT'); ?>
				</button>

				<div class="clr"></div>
				<div id="menu-assignment" style="height: 300px; overflow: auto;">

				<?php foreach ($menuTypes as &$type) : ?>
					<h3><?php echo $type->title ? $type->title : $type->menutype; ?></h3>
					<ul class="menu-links">
						<?php
						foreach ($type->links as $link) :
							if (trim($this->item->assignment) == '-'):
								$checked = '';
							elseif ($this->item->assignment == 0):
								$checked = ' checked="checked"';
							elseif ($this->item->assignment < 0):
								$checked = in_array(-$link->value, $this->item->assigned) ? ' checked="checked"' : '';
							elseif ($this->item->assignment > 0) :
								$checked = in_array($link->value, $this->item->assigned) ? ' checked="checked"' : '';
							endif;
						?>
						<li class="menu-link">
							<input type="checkbox" class="chk-menulink" name="jform[assigned][]" value="<?php echo (int) $link->value;?>" id="link<?php echo (int) $link->value;?>"<?php echo $checked;?>/>
							<label for="link<?php echo (int) $link->value;?>">
								<?php echo $link->text; ?>
							</label>
						</li>
						<div class="clr"></div>
						<?php endforeach; ?>
					</ul>
				<?php endforeach; ?>
				</div>

		</fieldset>
