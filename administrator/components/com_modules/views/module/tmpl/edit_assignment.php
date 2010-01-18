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
		<fieldset class="adminform">
			<legend><?php echo JText::_('Modules_Menu_Assignment'); ?></legend>
				<label id="jform_menus-lbl" class="hasTip" for="jform_menus"><?php echo JText::_('Modules_Module_Assign'); ?>:</label>

				<fieldset id="jform_menus" class="radio">
					<select name="jform[assignment]">
						<?php echo JHtml::_('select.options', ModulesHelper::getAssignmentOptions($this->item->client_id), 'value', 'text', $this->item->assignment, true);?>
					</select>

				</fieldset>

				<label id="jform_menuselect-lbl" class="hasTip" for="jform_menuselect"><?php echo JText::_('MENU_SELECTION'); ?>:</label>

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
