<?php
/**
 * @version		$Id: form.php 11476 2009-01-25 06:58:51Z eddieajau $
 * @package		Joomla.Administrator
 * @subpackage	Weblinks
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License, see LICENSE.php
 */

defined('_JEXEC') or die('Restricted access');

JHtml::addIncludePath(JPATH_COMPONENT.DS.'helpers'.DS.'html');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
?>
<script type="text/javascript">
<!--
	function submitbutton(task)
	{
		if (task == 'weblink.cancel' || document.formvalidator.isValid($('weblink-form'))) {
			submitform(task);
		}
	}
// -->
</script>

<form action="<?php JRoute::_('index.php?option=com_weblinks'); ?>" method="post" name="adminForm" id="weblink-form">
	<fieldset style="width:45%; float:left;">
		<legend><?php echo empty($this->item->id) ? JText::_('Weblinks_New_Weblink') : JText::sprintf('Weblinks_Edit_Weblink', $this->item->id); ?></legend>

		<ol>
			<li>
				<?php echo $this->form->getLabel('title'); ?><br />
				<?php echo $this->form->getInput('title'); ?>
			</li>
			<li>
				<?php echo $this->form->getLabel('alias'); ?><br />
				<?php echo $this->form->getInput('alias'); ?>
			</li>
			<li>
				<?php echo $this->form->getLabel('url'); ?><br />
				<?php echo $this->form->getInput('url'); ?>
			</li>
			<li>
				<?php echo $this->form->getLabel('state'); ?><br />
				<?php echo $this->form->getInput('state'); ?>
			</li>
			<li>
				<?php echo $this->form->getLabel('catid'); ?><br />
				<?php echo $this->form->getInput('catid'); ?>
			</li>
			<li>
				<?php echo $this->form->getLabel('ordering'); ?><br />
				<?php echo $this->form->getInput('ordering'); ?>
			</li>
		</ol>
	</fieldset>

	<fieldset style="width:45%; float:right;">
		<legend><?php echo JText::_('Weblinks_Options'); ?></legend>

		<?php foreach($this->form->getFields('params') as $field): ?>
			<?php if ($field->hidden): ?>
				<?php echo $field->input; ?>
			<?php else: ?>
				<tr>
					<td class="paramlist_key" width="40%">
						<?php echo $field->label; ?>
					</td>
					<td class="paramlist_value">
						<?php echo $field->input; ?>
					</td>
				</tr>
			<?php endif; ?>
		<?php endforeach; ?>

	</fieldset>

	<input type="hidden" name="task" value="" />
	<?php echo JHtml::_('form.token'); ?>
</form>

<script type="text/javascript">
// Attach the onblur event to auto-create the alias
e = document.getElementById('jform_title');
e.onblur = function(){
	title = document.getElementById('jform_title');
	alias = document.getElementById('jform_alias');
	if (alias.value=='') {
		alias.value = title.value.replace(/[\s\-]+/g,'-').replace(/&/g,'and').replace(/[^A-Z0-9\-\_]/ig,'').toLowerCase();
	}
}
</script>
