<?php /** $Id$ */ defined('_JEXEC') or die();

	JHtml::addIncludePath(JPATH_COMPONENT.DS.'helpers'.DS.'html');
	JHtml::_('behavior.tooltip');
	JHtml::_('behavior.formvalidation');
?>
<style type="text/css">
/* @TODO Mode to stylesheet */
label.block {
	padding-bottom: 4px;
	display:block;
}

.readonly {
	border: 0;
}

/* Special checklists */

ul.checklist {
	list-style: none;
	padding: 0;
}
ul.checklist li {
	padding: 0;
	border-bottom: 1px solid #eee;
}
ul.checklist li:hover {
	background: #eee;
}

.scroll {
	overflow: auto;
}
</style>

<script language="javascript" type="text/javascript">
<!--
	function submitbutton(task)
	{
		var form = document.adminForm;
		if (task == 'acl.cancel' || document.formvalidator.isValid(document.adminForm)) {
			submitform(task);
		}
	}
-->
</script>
<form action="<?php echo JRoute::_('index.php?option=com_acl'); ?>" method="post" name="adminForm" class="form-validate">
	<fieldset>
		<?php if ($this->item->id) : ?>
		<legend><?php echo JText::sprintf('Record #%d', $this->item->id); ?></legend>
		<?php endif; ?>

		<table class="adminform">
			<tbody>
				<tr>
					<td width="33%">
						<label for="note" class="block">
							<?php echo JText::_('ACL Note'); ?>
						</label>
						<input type="text" name="note" id="note" value="<?php echo $this->item->note; ?>" class="inputbox required"/>
					</td>
					<td width="33%">
						<label for="allow" class="block">
							<?php echo JText::_('ACL Allow'); ?>
						</label>
						<?php echo JHtml::_('select.booleanlist',  'allow', '', (int) $this->item->get('allow', 1)); ?>
					</td>
					<td width="33%">
						<label for="section_value" class="block">
							<?php echo JText::_('ACL Section'); ?>
						</label>
						<input type="text" name="section_value" id="section_value" value="<?php echo $this->item->get('section_value', 'user'); ?>" class="readonly" readonly="readonly" />
					</td>
				</tr>
				<tr>
					<td>
						<label for="return_value" class="block">
							<?php echo JText::_('ACL Return Value'); ?>
						</label>
						<input type="text" name="return_value" id="return_value" value="<?php echo $this->item->return_value; ?>" class="inputbox"/>
					</td>
					<td>
						<label for="enabled" class="block">
							<?php echo JText::_('ACL Enabled'); ?>
						</label>
						<?php echo JHtml::_('select.booleanlist',  'enabled', '', (int) $this->item->get('enabled', 1)); ?>
					</td>
					<td>
						<label for="updated_date" class="block">
							<?php echo JText::_('ACL Updated Date'); ?>
						</label>
						<input type="text" value="<?php echo $this->item->updated_date; ?>" id="updated_date" class="readonly" readonly="readonly" />
					</td>
				</tr>
			</tbody>
		</table>

		<?php echo $this->loadTemplate('type'.$this->item->acl_type); ?>
	</fieldset>

	<input type="hidden" name="id" value="<?php echo $this->item->id;?>" />
	<input type="hidden" name="acl_type" value="<?php echo $this->item->acl_type;?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="<?php echo JUtility::getToken();?>" value="1" />
</form>
