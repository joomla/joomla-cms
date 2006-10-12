<script language="javascript" type="text/javascript">
<!--
	function submitbutton(frm) {
		// do field validation
		if (frm.mailto.value == "" || frm.from.value == "") {
			alert( '<?php echo JText::_('EMAIL_ERR_NOINFO'); ?>' );
			return false;
		}
		return true;
	}
-->
</script>

<form action="index2.php?option=com_mailto&amp;task=send" name="mailtoform" method="post" onSubmit="return submitbutton();">

<div style="padding: 10px;">
	<div style="text-align:right">
		<a href="javascript: void window.close()">
			<?php echo JText::_('close window'); ?> <img src="components/com_mailto/assets/close-x.png" border="0" alt="" title="" />
		</a>
	</div>

	<div class="componentheading">
		<?php echo JText::_('Email this link to a friend.'); ?>
	</div>

	<p>
		<?php echo JText::_('Email to'); ?>:
		<br/>
		<input type="text" name="mailto" class="inputbox" size="25" />
	</p>

	<p>
		<?php echo JText::_('Sender'); ?>:
		<br/>
		<input type="text" name="sender" class="inputbox" value="<?php echo $this->data->sender ?>" size="25" />
	</p>

	<p>
		<?php echo JText::_('Your email'); ?>:
		<br/>
		<input type="text" name="from" class="inputbox" value="<?php echo $this->data->from ?>" size="25" />
	</p>

	<p>
		<?php echo JText::_('Subject'); ?>:
		<br/>
		<input type="text" name="subject" class="inputbox" value="" size="25" />
	</p>

	<p>
		<button class="button" onclick="return submitbutton(this.form);">
			<?php echo JText::_('Send'); ?>
		</button>
		<button class="button" onclick="window.close();return false;">
			<?php echo JText::_('Cancel'); ?>
		</button>
	</p>
</div>

	<input type="hidden" name="option" value="com_mailto" />
	<input type="hidden" name="task" value="send" />
	<input type="hidden" name="link" value="<?php echo $this->data->link; ?>" />
	<input type="hidden" name="token" value="<?php echo JUtility::getToken(); ?>" />
</form>