<?php
/**
 * @version $Id$
 */
defined( '_JEXEC' ) or die( 'Restricted access' );

?>

<?php if ( $this->params->get( 'show_page_title' ) ) : ?>
<h1 class="componentheading<?php echo $this->params->get('pageclass_sfx'); ?>">
	<?php echo $this->category->title; ?>
</h1>
<?php endif; ?>

<div class="newsfeed<?php echo $this->params->get( 'pageclass_sfx' ); ?>">
	<?php if ( $this->category->image || $this->category->description ) : ?>
	<div class="contentdescription<?php echo $this->params->get('pageclass_sfx'); ?>">

		<?php if ( $this->category->image ) : ?>
		<img src="images/stories/<?php echo $this->category->image; ?>" class="image_<?php echo $this->category->image_position; ?>" />
		<?php endif; ?>

		<?php if ( $this->params->get( 'description' ) ) :
			echo $this->category->description;
		endif; ?>

		<?php if ( $this->category->image ) : ?>
		<div class="wrap_image">&nbsp;</div>
		<?php endif; ?>

	</div>
	<?php endif; ?>

	<?php echo $this->loadTemplate('items'); ?>
</div>
