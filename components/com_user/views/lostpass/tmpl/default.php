<?php // no direct access
defined('_JEXEC') or die('Restricted access'); ?>
<form action="<?php echo JRoute::_('index.php'); ?>" method="post" name="josForm">

<div class="componentheading">
	<?php echo JText::_( 'Lost your Password?' ); ?>
</div>

<table cellpadding="0" cellspacing="0" border="0" width="100%" class="contentpane">
<tr>
	<td colspan="2" height="40">
		<?php echo JText::_( 'NEW_PASS_DESC' ); ?>
	</td>
</tr>
<tr>
	<td height="40">
		<label for="jusername">
			<?php echo JText::_( 'Username' ); ?>:
		</label>
	</td>
	<td>
		<input type="text" id="jusername" name="jusername" class="inputbox" size="40" maxlength="25" />
	</td>
</tr>
<tr>
	<td height="40">
		<label for="jemail">
			<?php echo JText::_( 'Email Address' ); ?>:
		</label>
	</td>
	<td>
		<input type="text" id="jemail" name="jemail" class="inputbox" size="40" />
	</td>
</tr>
</table>

<input type="submit" value="<?php echo JText::_('Send'); ?>" />
<input type="hidden" name="task" value="sendreminder" />
<input type="hidden" name="option" value="com_user" />
<input type="hidden" name="<?php echo JUtility::getToken(); ?>" value="1" />
</form>