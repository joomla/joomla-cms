<?php defined('_JEXEC') or die('Restricted access'); ?>

<?php
		$editor		=& JFactory::getEditor();
		JHTML::_('behavior.caption');
?>

<script>
var form = window.top.document.adminForm
var title = form.title.value;

var alltext = window.top.<?php echo $editor->getContent('text') ?>;
alltext = alltext.replace('<hr id=\"system-readmore\" \/>', '');

</script>

<table align="center" width="90%" cellspacing="2" cellpadding="2" border="0">
	<tr>
		<td class="contentheading" colspan="2"><script>document.write(title);</script></td>
	</tr>
<tr>
	<script>document.write("<td valign=\"top\" height=\"90%\" colspan=\"2\">" + alltext + "</td>");</script>
</tr>
</table>