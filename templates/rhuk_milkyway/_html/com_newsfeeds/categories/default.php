<?php // no direct access
defined('_JEXEC') or die('Restricted access'); ?>
<script language="javascript" type="text/javascript">
<!--
	function submitbutton(pressbutton) {
	    var form = document.mailtoForm;

		// do field validation
		if (form.mailto.value == "" || form.from.value == "") {
			alert( '<?php echo JText::_('EMAIL_ERR_NOINFO'); ?>' );
			return false;
		}
		form.submit();
	}
-->
</script>
<?php
$data	= $this->get('data');
?>

<form action="<?php echo JURI::base() ?>index.php" name="mailtoForm" method="post">

<div style="padding: 10px;">
	<div style="text-align:right">
		<a href="javascript: void window.close()">
			<?php echo JText::_('CLOSE_WINDOW'); ?> <img src="<?php echo JURI::base() ?>components/com_mailto/assets/close-x.png" border="0" alt="" title="" /></a>
	</div>

	<div class="componentheading">
		<?php echo JText::_('EMAIL_THIS_LINK_TO_A_FRIEND'); ?>
	</div>

	<p>
		<?php echo JText::_('EMAIL_TO'); ?>:
		<br />
		<input type="text" name="mailto" class="inputbox" size="25" value="<?php echo $this->escape($data->mailto) ?>"/>
	</p>

	<p>
		<?php echo JText::_('SENDER'); ?>:
		<br />
		<input type="text" name="sender" class="inputbox" value="<?php echo $this->escape($data->sender) ?>" size="25" />
	</p>

	<p>
		<?php echo JText::_('YOUR_EMAIL'); ?>:
		<br />
		<input type="text" name="from" class="inputbox" value="<?php echo $this->escape($data->from) ?>" size="25" />
	</p>

	<p>
		<?php echo JText::_('SUBJECT'); ?>:
		<br />
		<input type="text" name="subject" class="inputbox" value="<?php echo $this->escape($data->subject) ?>" size="25" />
	</p>

	<p>
		<button class="button" onclick="return submitbutton('send');">
			<?php echo JText::_('SEND'); ?>
		</button>
		<button class="button" onclick="window.close();return false;">
			<?php echo JText::_('CANCEL'); ?>
		</button>
	</p>
</div>

	<input type="hidden" name="layout" value="<?php echo $this->getLayout();?>" />
	<input type="hidden" name="option" value="com_mailto" />
	<input type="hidden" name="task" value="send" />
	<input type="hidden" name="tmpl" value="component" />
	<input type="hidden" name="link" value="<?php echo $data->link; ?>" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>
