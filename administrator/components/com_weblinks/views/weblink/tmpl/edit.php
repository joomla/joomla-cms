<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	com_weblinks
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

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
		// @todo Deal with the editor methods
		submitform(task);
	}
// -->
</script>

<form action="<?php JRoute::_('index.php?option=com_weblinks'); ?>" method="post" name="adminForm" id="weblink-form" class="form-validate">
<div class="col width-50">
	<fieldset>
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
		<li>
			<?php echo $this->form->getLabel('description'); ?><br />
			<?php echo $this->form->getInput('description'); ?>
		</li>
	</ol>
	</fieldset>
</div>
<div class="col width-50">
	<fieldset>
		<legend><?php echo JText::_('Weblinks_Options'); ?></legend>

		<table>
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
		</table>

	</fieldset>
</div>
<div class="clr"></div>

	<input type="hidden" name="task" value="" />
	<?php echo JHtml::_('form.token'); ?>
</form>