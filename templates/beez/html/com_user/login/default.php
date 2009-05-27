<?php // @version $Id$
defined('_JEXEC') or die;
 ?>
<?php if ($this->params->get('show_page_title',1)) : ?>
<h2 class="componentheading<?php echo $this->params->get('pageclass_sfx') ?>">
	<?php echo $this->escape($this->params->get('page_title')) ?>
</h2>
<?php endif; ?>

<?php echo $this->loadTemplate($this->type);
