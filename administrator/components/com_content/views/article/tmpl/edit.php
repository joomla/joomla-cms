<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	com_content
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Include the component HTML helpers.
JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');

jimport('joomla.html.pane');
$pane = &JPane::getInstance('sliders', array('allowAllClose' => true));

// Load the tooltip behavior.
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
?>

<script type="text/javascript">
<!--
	function submitbutton(task)
	{
		if (task == 'item.cancel' || document.formvalidator.isValid($('item-form'))) {
			<?php //echo $this->form->fields['introtext']->editor->save('jform[introtext]'); ?>
			submitform(task);
		}
	}
// -->
</script>

<form action="<?php JRoute::_('index.php?option=com_content'); ?>" method="post" name="adminForm" id="item-form" class="form-validate">

	<div class="width-60" style="float:left">
		<table style="display:block">
			<tr>
				<td>
					<?php echo $this->form->getLabel('title'); ?><br />
					<?php echo $this->form->getInput('title'); ?>
				</td>
				<td>
					<?php echo $this->form->getLabel('alias'); ?><br />
					<?php echo $this->form->getInput('alias'); ?>
				</td>
				<td>
					<?php echo $this->form->getLabel('catid'); ?><br />
					<?php echo $this->form->getInput('catid'); ?>
				</td>
			</tr>
			<tr>
				<td>
					<?php echo $this->form->getLabel('state'); ?><br />
					<?php echo $this->form->getInput('state'); ?>
				</td>
				<td>
					<?php echo $this->form->getLabel('version'); ?><br />
					<?php echo $this->form->getInput('version'); ?>
				</td>
				<td>
					<?php echo $this->form->getLabel('hits'); ?><br />
					<?php echo $this->form->getInput('hits'); ?>
				</td>
			</tr>
		</table>
		<?php echo $this->form->getLabel('introtext'); ?><br />
		<?php echo $this->form->getInput('introtext'); ?>

		<?php echo $this->form->getLabel('fulltext'); ?><br />
		<?php echo $this->form->getInput('fulltext'); ?>
	</div>

	<div class="width-40" style="float:left">
		<?php echo $pane->startPane('content-pane'); ?>

		<?php echo $pane->startPanel(JText::_('Content_Fieldset_Publishing'), 'publishing-details'); ?>

		<ol>
			<li>
				<?php echo $this->form->getLabel('created_by'); ?><br />
				<?php echo $this->form->getInput('created_by'); ?>
			</li>
			<li>
				<?php echo $this->form->getLabel('created_by_alias'); ?><br />
				<?php echo $this->form->getInput('created_by_alias'); ?>
			</li>
			<li>
				<?php echo $this->form->getLabel('access'); ?><br />
				<?php echo $this->form->getInput('access'); ?>
			</li>
			<li>
				<?php echo $this->form->getLabel('created'); ?><br />
				<?php echo $this->form->getInput('created'); ?>
			</li>
			<li>
				<?php echo $this->form->getLabel('publish_up'); ?><br />
				<?php echo $this->form->getInput('publish_up'); ?>
			</li>
			<li>
				<?php echo $this->form->getLabel('publish_down'); ?><br />
				<?php echo $this->form->getInput('publish_down'); ?>
			</li>
			<li>
				<?php echo $this->form->getLabel('modified'); ?><br />
				<?php echo $this->form->getInput('modified'); ?>
			</li>
		</ol>
		<?php echo $pane->endPanel(); ?>

		<?php echo $pane->startPanel(JText::_('Content_Fieldset_Options'), 'basic-options'); ?>
		<table>
		<?php foreach($this->form->getFields('attribs') as $field): ?>
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
		<?php echo $pane->endPanel(); ?>

		<?php echo $pane->startPanel(JText::_('Content_Fieldset_Metadata'), 'meta-options'); ?>
		<ol>
			<li>
				<?php echo $this->form->getLabel('language'); ?><br />
				<?php echo $this->form->getInput('language'); ?>
			</li>
			<li>
				<?php echo $this->form->getLabel('metadesc'); ?><br />
				<?php echo $this->form->getInput('metadesc'); ?>
			</li>
			<li>
				<?php echo $this->form->getLabel('metakey'); ?><br />
				<?php echo $this->form->getInput('metakey'); ?>
			</li>

			<?php foreach($this->form->getFields('metadata') as $field): ?>
			<li>
				<?php echo $field->label; ?><br />
				<?php echo $field->input; ?>
			</li>
			<?php endforeach; ?>

			<li>
				<?php echo $this->form->getLabel('xreference'); ?><br />
				<?php echo $this->form->getInput('xreference'); ?>
			</li>
		</ol>
		<?php echo $pane->endPanel(); ?>

		<?php echo $pane->endPane(); ?>
	</div>




	<input type="hidden" name="task" value="" />
	<?php echo JHtml::_('form.token'); ?>
</form>
<div class="clr"></div>
