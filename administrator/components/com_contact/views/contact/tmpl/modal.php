<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_contact
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::_('bootstrap.tooltip', '.hasTooltip', array('placement' => 'bottom'));

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
