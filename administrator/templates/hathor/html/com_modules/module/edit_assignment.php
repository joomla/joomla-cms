<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	templates.hathor
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 * @since		1.6
 */

// No direct access.
defined('_JEXEC') or die;

// Initiasile related data.
require_once JPATH_ADMINISTRATOR.'/components/com_menus/helpers/menus.php';
$menuTypes = MenusHelper::getMenuLinks();
?>
		<fieldset class="adminform">
			<legend><?php echo JText::_('COM_MODULES_MENU_ASSIGNMENT'); ?></legend>

				<label id="jform_menus-lbl" class="hasTip" for="jform_menus"><?php echo JText::_('COM_MODULES_MODULE_ASSIGN'); ?>:</label>
				<select id="jform_menus" name="jform[assignment]">
					<?php echo JHtml::_('select.options', ModulesHelper::getAssignmentOptions($this->item->client_id), 'value', 'text', $this->item->assignment, true);?>
				</select>

				<span id="jform_menuselect-lbl" class="hasTip faux-label"><?php echo JText::_('JGLOBAL_MENU_SELECTION'); ?>:</span>

				<div class="clr"></div>

				<button type="button" class="button2" onclick="$$('.chk-menulink').each(function(el) { el.checked = !el.checked; });">
					<?php echo JText::_('JGLOBAL_SELECTION_INVERT'); ?>
				</button>

				<div id="menu-assignment">

				<?php foreach ($menuTypes as &$type) : ?>
					<h3><?php echo $type->title ? $type->title : $type->menutype; ?></h3>
					<ul class="menu-links">
						<?php
						foreach ($type->links as $link) :
							if ($this->item->assignment < 0) :
								$checked = in_array(-$link->value, $this->item->assigned) ? ' checked="checked"' : '';
							else :
								$checked = in_array($link->value, $this->item->assigned) ? ' checked="checked"' : '';
							endif;
						?>
						<li class="menu-link">
							<input type="checkbox" class="chk-menulink" name="jform[assigned][]" value="<?php echo (int) $link->value;?>" id="link<?php echo (int) $link->value;?>"<?php echo $checked;?>/>
							<label for="link<?php echo (int) $link->value;?>">
								<?php echo $link->text; ?>
							</label>
						</li>
						<?php endforeach; ?>
					</ul>
				<?php endforeach; ?>
				</div>

		</fieldset>