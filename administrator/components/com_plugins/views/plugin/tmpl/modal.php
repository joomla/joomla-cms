<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_plugins
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// This code is needed for proper check out in case of modal close
JFactory::getDocument()->addScriptDeclaration('
	window.parent.jQuery(".modal").on("hidden", function () {
	if (typeof window.parent.jQuery("#plugin' . $this->item->extension_id . 'Modal iframe").contents().find("#closeBtn") !== "undefined") {
		window.parent.jQuery("#plugin' . $this->item->extension_id . 'Modal iframe").contents().find("#closeBtn").click();
		}
	});
');
?>
<button id="applyBtn" type="button" class="hidden" onclick="Joomla.submitbutton('plugin.apply');"></button>
<button id="saveBtn" type="button" class="hidden" onclick="Joomla.submitbutton('plugin.save');"></button>
<button id="closeBtn" type="button" class="hidden" onclick="Joomla.submitbutton('plugin.cancel');"></button>

<div class="container-popup">
	<?php $this->setLayout('edit'); ?>
	<?php echo $this->loadTemplate(); ?>
</div>

