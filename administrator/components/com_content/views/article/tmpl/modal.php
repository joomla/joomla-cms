<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_content
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::_('bootstrap.tooltip', '.hasTooltip', array('placement' => 'bottom'));

// This code is needed for proper check out in case of modal close
JFactory::getDocument()->addScriptDeclaration('
	window.parent.jQuery(".modal").on("hidden", function () {
	if (typeof window.parent.jQuery("#articleEdit' . $this->item->id . 'Modal iframe").contents().find("#closeBtn") !== "undefined") {
		window.parent.jQuery("#articleEdit' . $this->item->id . 'Modal iframe").contents().find("#closeBtn").click();
		}
	});
');
?>
<button id="saveBtn" type="button" class="hidden" onclick="Joomla.submitbutton('article.save');"></button>
<button id="closeBtn" type="button" class="hidden" onclick="Joomla.submitbutton('article.cancel');"></button>

<div class="container-popup">
	<?php $this->setLayout('edit'); ?>
	<?php echo $this->loadTemplate(); ?>
</div>
