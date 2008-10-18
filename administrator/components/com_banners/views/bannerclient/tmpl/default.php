<?php defined('_JEXEC') or die('Restricted access'); ?>

<?php JHTML::_('behavior.tooltip'); ?>

<script language="javascript" type="text/javascript">
<!--
function submitbutton(pressbutton)
{
	var form = document.adminForm;
	if (pressbutton == 'cancel')
	{
		submitform( pressbutton );
		return;
	}
	// do field validation
	if (form.name.value == "")
	{
		alert( "<?php echo JText::_( 'Please fill in the Client Name.', true ); ?>" );
	}
	else if (form.contact.value == "")
	{
		alert( "<?php echo JText::_( 'Please fill in the Contact Name.', true ); ?>" );
	}
	else if (form.email.value == "")
	{
		alert( "<?php echo JText::_( 'Please fill in the Contact Email.', true ); ?>" );
	}
	else if (!isEmail( form.email.value ))
	{
		alert( "<?php echo JText::_( 'Please provide a valid Contact Email.', true ); ?>" );
	}
	else
	{
		submitform( pressbutton );
	}
}
//-->
</script>

<form action="<?php echo JRoute::_('index.php'); ?>" method="post" name="adminForm">

<div class="col width-50">
	<fieldset class="adminform">
		<legend><?php echo JText::_( 'Details' ); ?></legend>

		<table class="admintable">
			<tr>
				<td width="20%" nowrap="nowrap">
					<label for="name">
						<?php echo JText::_( 'Client Name' ); ?>:
					</label>
				</td>
				<td>
					<input class="inputbox" type="text" name="name" id="name" size="40" maxlength="60" value="<?php echo $this->row->name; ?>" />
				</td>
			</tr>
			<tr>
				<td nowrap="nowrap">
					<label for="contact">
						<?php echo JText::_( 'Contact Name' ); ?>:
					</label>
				</td>
				<td>
					<input class="inputbox" type="text" name="contact" id="contact" size="40" maxlength="60" value="<?php echo $this->row->contact; ?>" />
				</td>
			</tr>
			<tr>
				<td nowrap="nowrap">
					<label for="email">
						<?php echo JText::_( 'Contact Email' ); ?>:
					</label>
				</td>
				<td>
					<input class="inputbox" type="text" name="email" id="email" size="40" maxlength="60" value="<?php echo $this->row->email; ?>" />
				</td>
			</tr>
			</table>
	</fieldset>
</div>

<div class="col width-50">
	<fieldset class="adminform">
		<legend><?php echo JText::_( 'Extra Info' ); ?></legend>

		<table class="admintable" width="100%">
		<tr>
			<td width="100%" valign="top">
				<textarea class="inputbox" name="extrainfo" id="extrainfo" cols="40" rows="10" style="width:90%"><?php echo str_replace('&','&amp;',$this->row->extrainfo);?></textarea>
			</td>
		</tr>
		</table>
	</fieldset>
</div>
<div class="clr"></div>

<input type="hidden" name="c" value="client" />
<input type="hidden" name="option" value="com_banners" />
<input type="hidden" name="cid" value="<?php echo $this->row->cid; ?>" />
<input type="hidden" name="client_id" value="<?php echo $this->row->cid; ?>" />
<input type="hidden" name="task" value="" />
<?php echo JHTML::_( 'form.token' ); ?>
</form>
