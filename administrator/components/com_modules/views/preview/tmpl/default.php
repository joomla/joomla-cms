<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_modules
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
?>

<script>
var form = window.top.document.adminForm
var title = form.title.value;

var alltext = window.top.<?php echo $this->editor->getContent('text') ?>;
</script>

<table class="center" width="90%">
	<tr>
		<td class="contentheading" colspan="2"><script>document.write(title);</script></td>
	</tr>
<tr>
	<td valign="top" height="90%" colspan="2">
		<script>document.write(alltext);</script>
	</td>
</tr>
</table>
