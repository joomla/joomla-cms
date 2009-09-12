<?php defined('_JEXEC') or die; ?>

<script>
var form = window.top.document.adminForm
var title = form.title.value;

var alltext = window.top.<?php echo $this->editor->getContent('text') ?>;
</script>

<table align="center" width="90%" cellspacing="2" cellpadding="2" border="0">
	<tr>
		<td class="contentheading" colspan="2"><script>document.write(title);</script></td>
	</tr>
<tr>
	<td valign="top" height="90%" colspan="2">
		<script>document.write(alltext);</script>
	</td>
</tr>
</table>
