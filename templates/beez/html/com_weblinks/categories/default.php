<?php
/**
 * @version $Id$
 */
defined('_JEXEC') or die('Restricted access');

/*
 *
 * Get the template parameters
 *
 */
$filename = JPATH_ROOT . DS . 'templates' . DS . $mainframe->getTemplate() . DS . 'params.ini';
if ($content = @ file_get_contents($filename)) {
	$templateParams = new JParameter($content);
} else {
	$templateParams = null;
}
/*
 * hope to get a better solution very soon
 */

$ptLevel = $templateParams->get('pageTitleHeaderLevel', '1');
$headerOpen		= '<h'.$ptLevel.' class="componentheading'.$this->params->get('pageclass_sfx').'">';
$headerClose	= '</h'.$ptLevel.'>';
?>


<?php if ($this->params->get('show_page_title', 1)) : ?>
<?php echo $headerOpen; ?>
	<?php echo $this->params->get('page_title'); ?>
<?php echo $headerClose; ?>
<?php endif; ?>


<div class="weblinks<?php echo $this->params->get('pageclass_sfx'); ?>">

	<?php if ($this->params->def('show_comp_description', 1) || $this->params->def('image', -1) != -1) : ?>
	<div class="contentdescription<?php echo $this->params->get('pageclass_sfx'); ?>">

		<?php if ($this->params->def('image', -1) != -1) : ?>
		<img src="images/stories/<?php echo $this->params->get('image'); ?>" alt="" class="image_<?php echo $this->params->get('image_align'); ?>" />
		<?php endif; ?>

		<?php if ($this->params->get('show_comp_description')) : ?>
		<?php echo $this->params->get('comp_description'); ?>
		<?php endif; ?>

		<?php if ($this->params->def('image', -1) != -1) : ?>
		<div class="wrap_image">&nbsp;</div>
		<?php endif; ?>

	</div>
	<?php endif; ?>

</div>


<?php if (count($this->categories)) : ?>
<ul>
	<?php foreach ($this->categories as $category) : ?>
	<li>
		<a href="<?php echo $category->link; ?>" class="category<?php echo $this->params->get('pageclass_sfx'); ?>">
			<?php echo $category->title; ?>
		</a>
		&nbsp;<span class="small">(<?php echo $category->numlinks ?>)</span>
	</li>
	<?php endforeach; ?>
</ul>
<?php endif;
