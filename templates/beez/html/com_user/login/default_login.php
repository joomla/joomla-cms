<?php
/**
 * @version $Id$
 */
defined( '_JEXEC' ) or die( 'Restricted access' );

?>
<form action="index.php" method="post" name="login" id="login" class="login_form<?php echo $this->params->get( 'pageclass_sfx' ); ?>">
	<?php if ( $this->params->get( 'page_title' ) ) : ?>
	<h1 class="componentheading<?php echo $this->params->get( 'pageclass_sfx' ); ?>">
		<?php echo $this->params->get( 'header_login' ); ?>
	</h1>
	<?php endif; ?>

	<?php if ( $this->params->get( 'description_login' ) || isset( $this->image ) ) : ?>
		<div class="contentdescription<?php echo $this->params->get( 'pageclass_sfx' );?>">
			<?php if (isset ($this->image)) :
				echo $this->image;
			endif;
			if ($this->params->get('description_login')) : ?>
			<p>
				<?php echo $this->params->get('description_login_text'); ?>
			</p>
			<?php endif;
			if (isset ($this->image)) : ?>
			<div class="wrap_image">&nbsp;</div>
			<?php endif; ?>
		</div>
	<?php endif; ?>
	<fieldset>
		<div class="name">
			<label for="user" ><?php echo JText::_( 'Username' ); ?></label>
			<input name="username" type="text" class="inputbox" size="20"  id="user" />
		</div>
		<div class="pass">
			<label for="pass" ><?php echo JText::_( 'Password' ); ?></label>
			<input name="passwd" type="password" class="inputbox" size="20" id="pass" />
		</div>
		<div class="remember">
			<label for="rem"><?php echo JText::_( 'Remember me' ); ?></label>
			<input type="checkbox" name="remember" class="inputbox" value="yes" id="rem" />
		</div>
	</fieldset>
	<p>
		<a href="<?php echo JRoute::_( 'index.php?option=com_user&amp;task=lostPassword' ); ?>">
			<?php echo JText::_('Lost Password?'); ?>
		</a>
		<?php if ( $this->params->get( 'registration' ) ) : ?>
		<?php echo JText::_('No account yet?'); ?>
		<a href="<?php echo JRoute::_( 'index.php?option=com_user&amp;task=register' ); ?>">
			<?php echo JText::_( 'Register' ); ?>
		</a>
		<?php endif; ?>
	</p>

	<input type="submit" name="submit" class="button" value="<?php echo JText::_( 'Login' ); ?>" />
	<noscript><?php echo JText::_( 'WARNJAVASCRIPT' ); ?></noscript>
	<input type="hidden" name="option" value="com_user" />
	<input type="hidden" name="task" value="login" />
	<input type="hidden" name="return" value="<?php echo $this->return; ?>" />
	<input type="hidden" name="token" value="<?php echo JUtility::getToken(); ?>" />
</form>
