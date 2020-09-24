<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_newsfeeds
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::_('bootstrap.tooltip', '.hasTooltip', array('placement' => 'bottom'));

// @deprecated 4.0 the function parameter, the inline js and the buttons are not needed since 3.7.0.
$function  = JFactory::getApplication()->input->getCmd('function', 'jEditNewsfeed_' . (int) $this->item->id);

// Function to update input title when changed
JFactory::getDocument()->addScriptDeclaration('
	function jEditNewsfeedModal() {
		if (window.parent && document.formvalidator.isValid(document.getElementById("newsfeed-form"))) {
			return window.parent.' . $this->escape($function) . '(document.getElementById("jform_name").value);
		}
	}
');
?>
<button id="applyBtn" type="button" class="hidden" onclick="Joomla.submitbutton('newsfeed.apply'); jEditNewsfeedModal();"></button>
<button id="saveBtn" type="button" class="hidden" onclick="Joomla.submitbutton('newsfeed.save'); jEditNewsfeedModal();"></button>
<button id="closeBtn" type="button" class="hidden" onclick="Joomla.submitbutton('newsfeed.cancel');"></button>

<div class="container-popup">
	<?php $this->setLayout('edit'); ?>
	<?php echo $this->loadTemplate(); ?>
</div>
