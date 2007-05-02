<?php // no direct access
defined('_JEXEC') or die('Restricted access'); ?>
<script language="javascript" type="text/javascript">
<!--
	function submitbutton(frm) {
		// do field validation
		if (frm.mailto.value == "" || frm.from.value == "") {
			alert( '<?php echo JText::_('EMAIL_ERR_NOINFO'); ?>' );
			return false;
		}
		frm.submit();
	}
-->
</script>
<?php
$data	= $this->get('data');
?>

<form action="index.php" name="mailtoform" method="post" onSubmit="return submitbutton();">

<div style="padding: 10px;">
	<div style="text-align:right">
		<a href="javascript: void window.close()">
			<?php echo JText::_('CLOSE_WINDOW'); ?> <img src="components/com_mailto/assets/close-x.png" border="0" alt="" title="" />
		</a>
	</div>

	<div class="componentheading">
		<?php echo JText::_('EMAIL_THIS_LINK_TO_A_FRIEND'); ?>
	</div>

	<p>
		<?php echo JText::_('EMAIL_TO'); ?>:
		<br/>
		<input type="text" name="mailto" class="inputbox" size="25" value="<?php echo $data->mailto ?>"/>
	</p>

	<p>
		<?php echo JText::_('SENDER'); ?>:
		<br/>
		<input type="text" name="sender" class="inputbox" value="<?php echo $data->sender ?>" size="25" />
	</p>

	<p>
		<?php echo JText::_('YOUR_EMAIL'); ?>:
		<br/>
		<input type="text" name="from" class="inputbox" value="<?php echo $data->from ?>" size="25" />
	</p>

	<p>
		<?php echo JText::_('SUBJECT'); ?>:
		<br/>
		<input type="text" name="subject" class="inputbox" value="<?php echo $data->subject ?>" size="25" />
	</p>

	<p>
		<button class="button" onclick="return submitbutton(this.form);">
			<?php echo JText::_('SEND'); ?>
		</button>
		<button class="button" onclick="window.close();return false;">
			<?php echo JText::_('CANCEL'); ?>
		</button>
	</p>
</div>

	<input type="hidden" name="option" value="com_mailto" />
	<input type="hidden" name="task" value="send" />
	<input type="hidden" name="tmpl" value="component" />
	<input type="hidden" name="link" value="<?php echo $data->link; ?>" />
	<input type="hidden" name="<?php echo JUtility::getToken(); ?>" value="1" />
</form>