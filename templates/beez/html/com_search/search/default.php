<?php defined('_JEXEC') or die('Restricted access'); ?>

<?php if($this->params->get('show_page_title')) : ?>
<h2 class="componentheading<?php echo $this->params->get('pageclass_sfx') ?>">
	<?php echo $this->params->get('page_title') ?>
</h2>
<?php endif; ?>

<div id="page">

<?php if (!$this->error && count($this->results) > 0) :
	echo $this->loadTemplate('results');
else :
	echo $this->loadTemplate('error');
endif; ?>

<?php echo $this->loadTemplate('form'); ?>
</div>