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
require_once JPATH_ADMINISTRATOR.'/components/com_menus/helpers/menus.php';
$menuTypes = MenusHelper::getMenuLinks();
?>

		<div class="control-group">
			<label id="jform_menus-lbl" class="control-label" for="jform_menus"><?php echo JText::_('COM_MODULES_MODULE_ASSIGN'); ?></label>
			<div id="jform_menus" class="controls">
				<select name="jform[assignment]" id="jform_assignment">
					<?php echo JHtml::_('select.options', ModulesHelper::getAssignmentOptions($this->item->client_id), 'value', 'text', $this->item->assignment, true);?>
				</select>
			</div>
		</div>
		<div class="control-group">
			<label id="jform_menuselect-lbl" class="control-label" for="jform_menuselect"><?php echo JText::_('JGLOBAL_MENU_SELECTION'); ?></label>
			<div class="controls">
				<select id="menu-assignment" name="jform[assigned][]" multiple="multiple">
					<?php foreach ($menuTypes as &$type) :
						$count 	= count($type->links);
						$i		= 0;
						if ($count) :
						?>
				  <optgroup label="<?php echo $type->title;?>">
					  	<?php
						foreach ($type->links as $link) :
							if (trim($this->item->assignment) == '-'):
								$selected = '';
							elseif ($this->item->assignment == 0):
								$selected = ' selected="selected"';
							elseif ($this->item->assignment < 0):
								$selected = in_array(-$link->value, $this->item->assigned) ? ' selected="selected"' : '';
							elseif ($this->item->assignment > 0) :
								$selected = in_array($link->value, $this->item->assigned) ? ' selected="selected"' : '';
							endif;
						?>
				    		<option value="<?php echo (int) $link->value;?>" <?php echo $selected;?>><?php echo $link->text; ?></option>
				    <?php endforeach; ?>
				  </optgroup>
				 	 <?php endif; ?>
				  <?php endforeach; ?>
				</select>
			</div>
		</div>
