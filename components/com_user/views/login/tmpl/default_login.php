<?php defined('_JEXEC') or die('Restricted access'); ?>
<form action="index.php" method="post" name="login" id="login">
<table width="100%" border="0" align="center" cellpadding="4" cellspacing="0" class="contentpane<?php echo $this->params->get( 'pageclass_sfx' ); ?>">
<tr>
	<td colspan="2">
		<?php if ( $this->params->get( 'page_title' ) ) : ?>
		<div class="componentheading<?php echo $this->params->get( 'pageclass_sfx' ); ?>">
			<?php echo $this->params->get( 'header_login' ); ?>
		</div>
		<?php endif; ?>
		<div>
			<?php echo $this->image; ?>
			<?php if ( $this->params->get( 'description_login' ) ) : ?>
				<?php echo $this->params->get( 'description_login_text' ); ?>
				<br/><br/>
			<?php endif; ?>
		</div>
	</td>
</tr>
<tr>
	<td align="center" width="50%">
		<br />
		<table>
		<tr>
			<td align="center">
				<?php echo JText::_( 'Username' ); ?><br />
			</td>
			<td align="center">
				<?php echo JText::_( 'Password' ); ?><br />
			</td>
		</tr>
		<tr>
			<td align="center">
				<input name="username" type="text" class="inputbox" size="20" />
			</td>
			<td align="center">
				<input name="passwd" type="password" class="inputbox" size="20" />
			</td>
		</tr>
		<tr>
			<td align="center" colspan="2">
				<br/>
				<?php echo JText::_( 'Remember me' ); ?>
				<input type="checkbox" name="remember" class="inputbox" value="yes" />
				<br/>
				<a href="<?php echo JRoute::_( 'index.php?option=com_user&task=lostPassword' ); ?>">
					<?php echo JText::_( 'Lost Password?' ); ?>
				</a>
				<?php if ( $this->params->get( 'registration' ) ) : ?>
				<br/>
				<?php echo JText::_( 'No account yet?' ); ?>
				<a href="<?php echo JRoute::_( 'index.php?option=com_user&task=register' ); ?>">
					<?php echo JText::_( 'Register' );?>
				</a>
				<?php endif; ?>
				<br/><br/><br/>
			</td>
		</tr>
		</table>
	</td>
	<td>
		<div align="center">
			<input type="submit" name="submit" class="button" value="<?php echo JText::_( 'Login' ); ?>" />
		</div>
	</td>
</tr>
<tr>
	<td colspan="2">
		<noscript>
			<?php echo JText::_( 'WARNJAVASCRIPT' ); ?>
		</noscript>
	</td>
</tr>
</table>

<input type="hidden" name="option" value="com_user" />
<input type="hidden" name="task" value="login" />
<input type="hidden" name="return" value="<?php echo base64_encode($this->params->get('login')); ?>" />
<input type="hidden" name="<?php echo JUtility::getToken(); ?>" value="1" />
</form>