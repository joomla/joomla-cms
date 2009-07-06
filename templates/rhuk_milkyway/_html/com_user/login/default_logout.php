<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php /** @todo Should this be routed */ ?>
<form action="<?php echo JRoute::_( 'index.php' ); ?>" method="post" name="login" id="login">
<?php if ( $this->params->get( 'show_logout_title' ) ) : ?>
<div class="componentheading<?php echo $this->escape($this->params->get('pageclass_sfx')); ?>">
	<?php echo $this->escape($this->params->get( 'header_logout' )); ?>
</div>
<?php endif; ?>
<table border="0" align="center" cellpadding="4" cellspacing="0" class="contentpane<?php echo $this->escape($this->params->get('pageclass_sfx')); ?>" width="100%">
<tr>
	<td valign="top">
		<div>
		<?php echo $this->image; ?>
		<?php
			if ($this->params->get('description_logout')) :
				echo $this->escape($this->params->get('description_logout_text'));
			endif;
		?>
		</div>
	</td>
</tr>
<tr>
	<td align="center">
		<div align="center">
			<input type="submit" name="Submit" class="button" value="<?php echo JText::_( 'Logout' ); ?>" />
		</div>
	</td>
</tr>
</table>

<br /><br />

<input type="hidden" name="option" value="com_user" />
<input type="hidden" name="task" value="logout" />
<input type="hidden" name="return" value="<?php echo $this->return; ?>" />
</form>
