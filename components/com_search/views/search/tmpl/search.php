<?php if ( $this->params->get( 'page_title' ) ) : ?>
<div class="componentheading<?php echo $this->params->get( 'pageclass_sfx' ); ?>">
	<?php echo $this->params->get( 'header' ); ?>
</div>
<?php endif; ?>
<?php $this->form();    ?>
<?php if(!$this->data->error) :
	$this->results();
else :
	$this->error();
endif; ?>