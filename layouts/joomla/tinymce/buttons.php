<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$buttons = $displayData;

// Load modal popup behavior
JHtml::_('behavior.modal', 'a.modal-button');
?>
<div id="editor-xtd-buttons" class="btn-toolbar pull-left">
	<?php if ($buttons) : ?>
		<?php foreach ($buttons as $button) : ?>
			<?php echo JLayoutHelper::render('joomla.tinymce.buttons.button', $button); ?>
		<?php endforeach; ?>
	<?php endif; ?>
</div>