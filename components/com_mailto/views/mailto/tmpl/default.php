<?php
/**
 * @version		$Id$
 * @package		Joomla.Site
 * @subpackage	com_mailto
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;
JHtml::_('behavior.keepalive');
?>
<script type="text/javascript">
<!--
	function submitbutton(pressbutton) {
		var form = document.mailtoForm;

		// do field validation
		if (form.mailto.value == "" || form.from.value == "") {
			alert('<?php echo JText::_('COM_MAILTO_EMAIL_ERR_NOINFO'); ?>');
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
			<?php echo JText::_('COM_MAILTO_CLOSE_WINDOW'); ?> <?php echo JHTML::_('image','mailto/close-x.png', NULL, NULL, true); ?></a>
	</div>

	<h2>
		<?php echo JText::_('COM_MAILTO_EMAIL_TO_A_FRIEND'); ?>
	</h2>

	<p>
		<?php echo JText::_('COM_MAILTO_EMAIL_TO'); ?>:
		<br />
		<input type="text" name="mailto" class="inputbox" size="25" value="<?php echo $data->mailto ?>"/>
	</p>

	<p>
		<?php echo JText::_('COM_MAILTO_SENDER'); ?>:
		<br />
		<input type="text" name="sender" class="inputbox" value="<?php echo $data->sender ?>" size="25" />
	</p>

	<p>
		<?php echo JText::_('COM_MAILTO_YOUR_EMAIL'); ?>:
		<br />
		<input type="text" name="from" class="inputbox" value="<?php echo $data->from ?>" size="25" />
	</p>

	<p>
		<?php echo JText::_('COM_MAILTO_SUBJECT'); ?>:
		<br />
		<input type="text" name="subject" class="inputbox" value="<?php echo $data->subject ?>" size="25" />
	</p>

	<p>
		<button class="button" onclick="return submitbutton('send');">
			<?php echo JText::_('COM_MAILTO_SEND'); ?>
		</button>
		<button class="button" onclick="window.close();return false;">
			<?php echo JText::_('COM_MAILTO_CANCEL'); ?>
		</button>
	</p>
	<input type="hidden" name="layout" value="<?php echo $this->getLayout();?>" />
	<input type="hidden" name="option" value="com_mailto" />
	<input type="hidden" name="task" value="send" />
	<input type="hidden" name="tmpl" value="component" />
	<input type="hidden" name="link" value="<?php echo $data->link; ?>" />
	<?php echo JHtml::_('form.token'); ?>
</div>
</form>