<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_templates
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\Component\Menus\Administrator\Helper\MenusHelper;

// Initialise related data.
$menuTypes = MenusHelper::getMenuLinks();
$user      = Factory::getUser();

HTMLHelper::_('script', 'com_templates/admin-template-toggle-assignment.js', ['version' => 'auto', 'relative' => true]);
?>
<label id="jform_menuselect-lbl" for="jform_menuselect"><?php echo Text::_('JGLOBAL_MENU_SELECTION'); ?></label>
<div class="btn-toolbar">
	<button class="btn btn-sm btn-secondary jform-rightbtn" type="button" onclick="Joomla.toggleAll()">
		<span class="fas fa-square" aria-hidden="true"></span> <?php echo Text::_('JGLOBAL_SELECTION_INVERT_ALL'); ?>
	</button>
</div>
<div id="menu-assignment" class="menu-assignment">
	<ul class="menu-links">

		<?php foreach ($menuTypes as &$type) : ?>
			<li>
				<div class="menu-links-block">
					<button class="btn btn-sm btn-secondary jform-rightbtn mb-2" type="button" onclick='Joomla.toggleMenutype("<?php echo $type->menutype; ?>")'>
						<span class="fas fa-square" aria-hidden="true"></span> <?php echo Text::_('JGLOBAL_SELECTION_INVERT'); ?>
					</button>
					<h5><?php echo $type->title ?: $type->menutype; ?></h5>

					<?php foreach ($type->links as $link) : ?>
						<label class="checkbox small" for="link<?php echo (int) $link->value; ?>" >
						<input type="checkbox" name="jform[assigned][]" value="<?php echo (int) $link->value; ?>" id="link<?php echo (int) $link->value; ?>"<?php if ($link->template_style_id == $this->item->id) : ?> checked="checked"<?php endif; ?><?php if ($link->checked_out && $link->checked_out != $user->id) : ?> disabled="disabled"<?php else : ?> class="chk-menulink menutype-<?php echo $type->menutype; ?>"<?php endif; ?> />
						<?php echo LayoutHelper::render('joomla.html.treeprefix', array('level' => $link->level)) . $link->text; ?>
						</label>
					<?php endforeach; ?>

				</div>
			</li>
		<?php endforeach; ?>

	</ul>
</div>
