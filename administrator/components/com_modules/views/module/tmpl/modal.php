<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_modules
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JFactory::getDocument()->addScriptDeclaration('
	window.parent.jQuery(".modal").on("hidden", function () { Joomla.submitbutton("module.cancel"); });
');
?>
<div class="btn-toolbar">
	<div class="btn-group">
		<button type="button" class="btn btn-primary" onclick="Joomla.submitbutton('module.save');">
		<?php echo JText::_('JSAVE');?></button>
	</div>
	<div class="btn-group">
		<button type="button" class="btn" onclick="Joomla.submitbutton('module.cancel'); window.parent.jQuery('.modal').modal('hide');">
		<?php echo JText::_('JCANCEL');?></button>
	</div>
	<div class="clearfix"></div>
</div>

<?php
$this->setLayout('edit');
echo $this->loadTemplate();
