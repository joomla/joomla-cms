<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_content
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

JFactory::getDocument()->addScriptDeclaration("
	jQuery('#exampleModal').on('hide.bs.modal', function (e) {
		document.getElementById('batch-category-id').value = '';
		document.getElementById('batch-access').value = '';
		document.getElementById('batch-language-id').value = '';
		document.getElementById('batch-user-id').value = '';
		document.getElementById('batch-tag-id').value = '';
	});
");

?>
<a class="btn btn-secondary" type="button" data-dismiss="modal">
	<?php echo JText::_('JCANCEL'); ?>
</a>
<button class="btn btn-success" type="submit" onclick="Joomla.submitbutton('article.batch');">
	<?php echo JText::_('JGLOBAL_BATCH_PROCESS'); ?>
</button>
