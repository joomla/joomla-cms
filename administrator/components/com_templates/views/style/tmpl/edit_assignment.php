<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_templates
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Initiasile related data.
require_once JPATH_ADMINISTRATOR . '/components/com_menus/helpers/menus.php';
$menuTypes = MenusHelper::getMenuLinks();
$user = JFactory::getUser();
?>
<label id="jform_menuselect-lbl" for="jform_menuselect"><?php echo JText::_('JGLOBAL_MENU_SELECTION'); ?></label>
<div class="btn-toolbar">
	<button class="btn" type="button" class="jform-rightbtn" onclick="jQuery('.chk-menulink').attr('checked', !jQuery('.chk-menulink').attr('checked'));">
		<span class="icon-checkbox-partial"></span> <?php echo JText::_('JGLOBAL_SELECTION_INVERT_ALL'); ?>
	</button>
</div>
<div id="menu-assignment">
	<ul class="menu-links thumbnails">

		<?php foreach ($menuTypes as &$type) : ?>
			<li class="span3">
				<div class="thumbnail">
				<button class="btn" type="button" class="jform-rightbtn" onclick="jQuery('.<?php echo $type->menutype; ?>').attr('checked', !jQuery('.<?php echo $type->menutype; ?>').attr('checked'));">
					<span class="icon-checkbox-partial"></span> <?php echo JText::_('JGLOBAL_SELECTION_INVERT'); ?>
				</button>
				<h5><?php echo $type->title ? $type->title : $type->menutype; ?></h5>

				<?php foreach ($type->links as $link) : ?>
					<label class="checkbox small" for="link<?php echo (int) $link->value;?>" >
					<input type="checkbox" name="jform[assigned][]" value="<?php echo (int) $link->value;?>" id="link<?php echo (int) $link->value;?>"<?php if ($link->template_style_id == $this->item->id):?> checked="checked"<?php endif;?><?php if ($link->checked_out && $link->checked_out != $user->id):?> disabled="disabled"<?php else:?> class="chk-menulink <?php echo $type->menutype; ?>"<?php endif;?> />
					<?php echo $link->text; ?>
					</label>
				<?php endforeach; ?>
				</div>
			</li>
		<?php endforeach; ?>
	</ul>
</div>
