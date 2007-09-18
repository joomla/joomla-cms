<?php // @version $Id$
defined( '_JEXEC' ) or die( 'Restricted access' );
?>

<form action="index.php" method="post" name="login" id="login" class="logout_form<?php echo $this->params->get( 'pageclass_sfx' ); ?>">
	<?php if ( $this->params->get( 'page_title' ) ) : ?>
	<h1 class="componentheading<?php echo $this->params->get( 'pageclass_sfx' ); ?>">
		<?php echo $this->params->get( 'header_logout' ); ?>
	</h1>
	<?php endif; ?>

	<?php if ( $this->params->get( 'description_logout' ) || isset( $this->image ) ) : ?>
	<div class="contentdescription<?php echo $this->params->get( 'pageclass_sfx' ); ?>">
		<?php if (isset ($this->image)) :
			echo $this->image;
		endif;
		if ( $this->params->get( 'description_logout' ) ) : ?>
		<p>
			<?php echo $this->params->get('description_logout_text'); ?>
		</p>
		<?php endif;
		if (isset ($this->image)) : ?>
		<div class="wrap_image">&nbsp;</div>
		<?php endif; ?>
	</div>
	<?php endif; ?>

	<p><input type="submit" name="Submit" class="button" value="<?php echo JText::_( 'Logout' ); ?>" /></p>
	<input type="hidden" name="option" value="com_user" />
	<input type="hidden" name="task" value="logout" />
	<input type="hidden" name="return" value="<?php echo $this->return; ?>" />
</form>
