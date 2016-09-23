<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_templates
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Initialise related data.
JLoader::register('MenusHelper', JPATH_ADMINISTRATOR . '/components/com_menus/helpers/menus.php');
$menuTypes = MenusHelper::getMenuLinks();
$user = JFactory::getUser();

?>
<fieldset class="adminform">
	<legend><?php echo JText::_('COM_TEMPLATES_MENUS_ASSIGNMENT'); ?></legend>
		<label id="jform_menuselect-lbl" for="jform_menuselect"><?php echo JText::_('JGLOBAL_MENU_SELECTION'); ?></label>

		<button type="button" class="jform-rightbtn" onclick="$$('.chk-menulink').each(function(el) { el.checked = !el.checked; });">
			<?php echo JText::_('JGLOBAL_SELECTION_INVERT_ALL'); ?>
		</button>
		<div class="clr"></div>
		<div id="menu-assignment">

		<?php foreach ($menuTypes as &$type) : ?>
			<ul class="menu-links">
				<button type="button" class="jform-rightbtn" onclick="$$('.<?php echo $type->menutype; ?>').each(function(el) { el.checked = !el.checked; });">
					<?php echo JText::_('JGLOBAL_SELECTION_INVERT'); ?>
				</button>
				<div class="clr"></div>
				<h3><?php echo $type->title ? $type->title : $type->menutype; ?></h3>

				<?php foreach ($type->links as $link) : ?>
					<li class="menu-link">
						<input type="checkbox" name="jform[assigned][]" value="<?php echo (int) $link->value;?>" id="link<?php echo (int) $link->value;?>"<?php if ($link->template_style_id == $this->item->id):?> checked="checked"<?php endif;?><?php if ($link->checked_out && $link->checked_out != $user->id):?> disabled="disabled"<?php else:?> class="chk-menulink <?php echo $type->menutype; ?>"<?php endif;?> />
						<label for="link<?php echo (int) $link->value;?>" >
							<?php echo $link->text; ?>
						</label>
					</li>
				<?php endforeach; ?>
			</ul>
		<?php endforeach; ?>

		</div>
</fieldset>
