<?php defined('_JEXEC') or die; ?>

<?php if ($this->params->get('show_page_title', 1)) : ?>
<h2 class="<?php echo $this->params->get('pageclass_sfx'); ?>">
	<?php echo $this->params->get('page_title'); ?>
</h2>
<?php endif; ?>

<?php echo $this->loadTemplate('form'); ?>
<?php if (!$this->error && count($this->results) > 0) :
	echo $this->loadTemplate('results');
else :
	echo $this->loadTemplate('error');
endif; ?>
