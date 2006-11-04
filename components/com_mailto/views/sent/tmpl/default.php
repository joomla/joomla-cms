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
		return true;
	}
-->
</script>

<div style="padding: 10px;">
	<div style="text-align:right">
		<a href="javascript: void window.close()">
			<?php echo JText::_('Close Window'); ?> <img src="components/com_mailto/assets/close-x.png" border="0" alt="" title="" />
		</a>
	</div>

	<div class="componentheading">
		<?php echo JText::_('EMAIL_SENT'); ?>
	</div>
</div>
