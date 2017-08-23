<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_contact
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::_('bootstrap.tooltip', '.hasTooltip', array('placement' => 'bottom'));

// @deprecated 4.0 the function parameter, the inline js and the buttons are not needed since 3.7.0.
$function  = JFactory::getApplication()->input->getCmd('function', 'jEditContact_' . (int) $this->item->id);

// Function to update input title when changed
JFactory::getDocument()->addScriptDeclaration('
	function jEditContactModal() {
		if (window.parent && document.formvalidator.isValid(document.getElementById("contact-form"))) {
			return window.parent.' . $this->escape($function) . '(document.getElementById("jform_name").value);
		}
	}
');
?>
<button id="applyBtn" type="button" class="hidden" onclick="Joomla.submitbutton('contact.apply'); jEditContactModal();"></button>
<button id="saveBtn" type="button" class="hidden" onclick="Joomla.submitbutton('contact.save'); jEditContactModal();"></button>
<button id="closeBtn" type="button" class="hidden" onclick="Joomla.submitbutton('contact.cancel');"></button>

<div class="container-popup">
	<?php $this->setLayout('edit'); ?>
	<?php echo $this->loadTemplate(); ?>
</div>
