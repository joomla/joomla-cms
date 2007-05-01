<?php // no direct access
defined('_JEXEC') or die('Restricted access'); ?>
<?php if ( $this->params->get( 'show_page_title' ) ) : ?>
<div class="componentheading<?php echo $this->params->get( 'pageclass_sfx' ); ?>">
	<?php echo $this->params->get( 'page_title' ); ?>
</div>
<?php endif; ?>

<?php echo $this->loadTemplate('form'); ?>
<?php if(!$this->error) :
	echo $this->loadTemplate('results');
else :
	echo $this->loadTemplate('error');
endif; ?>