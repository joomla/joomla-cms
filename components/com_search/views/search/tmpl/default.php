<?php if ( $params->get( 'page_title' ) ) : ?>
<div class="componentheading<?php echo $params->get( 'pageclass_sfx' ); ?>">
	<?php echo $params->get( 'header' ); ?>
</div>
<?php endif; ?>

<?php echo $this->loadTemplate('form'); ?>
<?php if(!$error) :
	echo $this->loadTemplate('results');
else :
	echo $this->loadTemplate('error');
endif; ?>