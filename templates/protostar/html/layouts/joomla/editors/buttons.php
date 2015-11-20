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

// Override jModalClose and SqueezeBox.close for B/C
JFactory::getDocument()->addScriptDeclaration(
	"
		if (jModalClose === undefined && typeof(jModalClose) != 'function') {
			var jModalClose;
			jModalClose = function() {
				jQuery('.modal.in ').modal('hide');
			}
		} else {
			var oldClose = jModalClose;
			jModalClose = function() {
				oldClose.apply(this, arguments);
				jQuery('.modal.in ').modal('hide');
			};
		}
		if (SqueezeBox != undefined) {
			var oldSqueezeBox = SqueezeBox.close;
			SqueezeBox.close = function() {
				oldSqueezeBox.apply(this, arguments);
				jQuery('.modal.in ').modal('hide');
			}
		} else {
			var SqueezeBox = {};
			SqueezeBox.close = function() {
				jQuery('.modal.in ').modal('hide');
			}
		}
	"
);

?>
<div id="editor-xtd-buttons" class="btn-toolbar pull-left">
	<?php if ($buttons) : ?>
		<?php foreach ($buttons as $button) : ?>
			<?php echo JLayoutHelper::render('joomla.editors.buttons.button', $button); ?>
		<?php endforeach; ?>
		<?php foreach ($buttons as $button) : ?>
			<?php echo JLayoutHelper::render('joomla.editors.buttons.modal', $button); ?>
		<?php endforeach; ?>
	<?php endif; ?>
</div>