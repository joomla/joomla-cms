<?php defined('_JEXEC') or die('Restricted access'); ?>

<?php JHTML::_('behavior.tooltip'); ?>

<form action="<?php echo JRoute::_('index.php'); ?>" method="post" name="adminForm">

<table class="adminform">
	<tr>
		<td width="100">
			<?php echo JText::_( 'From' ); ?>:
		</td>
		<td width="85%" bgcolor="#ffffff">
			<?php echo $this->row->user_from;?>
		</td>
	</tr>
	<tr>
		<td>
			<?php echo JText::_( 'Posted' ); ?>:
		</td>
		<td bgcolor="#ffffff">
			<?php echo $this->row->date_time;?>
		</td>
	</tr>
	<tr>
		<td>
			<?php echo JText::_( 'Subject' ); ?>:
		</td>
		<td bgcolor="#ffffff">
			<?php echo $this->row->subject;?>
		</td>
	</tr>
	<tr>
		<td valign="top">
			<?php echo JText::_( 'Message' ); ?>:
		</td>
		<td width="100%" bgcolor="#ffffff">
			<pre><?php echo htmlspecialchars( $this->row->message, ENT_COMPAT, 'UTF-8' );?></pre>
		</td>
	</tr>
</table>

<input type="hidden" name="option" value="<?php echo $option;?>" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="boxchecked" value="1" />
<input type="hidden" name="cid[]" value="<?php echo $this->row->message_id; ?>" />
<input type="hidden" name="userid" value="<?php echo $this->row->user_id_from; ?>" />
<input type="hidden" name="subject" value="Re: <?php echo $this->row->subject; ?>" />
<?php echo JHTML::_( 'form.token' ); ?>
</form>
