<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_joomlaupdate
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::_('jquery.framework');

JFactory::getDocument()->addScriptDeclaration("
jQuery(document).ready(function(){
	window.setTimeout(function() {
		document.getElementById('adminForm').submit();
	}, 5000);
});
");
?>

<div class="alert alert-warning">
	<h4 class="alert-heading">
		<?php echo JText::_('COM_JOOMLAUPDATE_VIEW_UPDATE_FINALISE'); ?>
	</h4>
	<p>
		<?php echo JText::_('COM_JOOMLAUPDATE_VIEW_UPDATE_FINALISE_DESC'); ?>
	</p>
</div>

<form action="<?php echo JRoute::_('index.php?option=com_joomlaupdate&' . JFactory::getSession()->getFormToken() . '=1'); ?>" method="post" id="adminForm">
	<fieldset class="cancelform">
		<div class="btn-group">
			<a href="index.php?option=com_joomlaupdate" class="btn btn-danger" tabindex="4">
				<span class="icon-cancel icon-white"></span> <?php echo JText::_('JCANCEL'); ?>
			</a>
		</div>
	</fieldset>
	<input type="hidden" name="method" value="direct" />
	<input type="hidden" name="task" value="update.finalise" />
	<?php echo JHtml::_('form.token'); ?>
</form>
