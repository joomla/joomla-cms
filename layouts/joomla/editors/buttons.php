<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$buttons = $displayData;

?>
<div id="editor-xtd-buttons" class="btn-toolbar pull-left">
	<?php if ($buttons) : ?>
		<?php foreach ($buttons as $button) : ?>
				<?php
				// Some checks for new layouts
				$isNewLayout = 0;
				if (isset($button->plugin)){
					$named = strtolower(str_replace(' ', '', $button->plugin));
					$mpath = '/layouts/joomla/editors-xtd/' . $named . '/' . $named . '.php';

					if (is_file(JPATH_THEMES . '/' . JFactory::getApplication()->getTemplate() . '/html' . $mpath)
						|| is_file(JPATH_ROOT . $mpath))
					{
						$isNewLayout = 1;
					}
				}
				?>
				<?php if ($isNewLayout) : ?>
				<?php echo JLayoutHelper::render('joomla.editors-xtd.' . $named . '.' . $named, $button); ?>
				<?php endif; ?>
				<?php if (!$isNewLayout) : ?>
				<?php echo JLayoutHelper::render('joomla.editors.buttons.button', $button); ?>
				<?php endif; ?>

		<?php endforeach; ?>
	<?php endif; ?>
</div>