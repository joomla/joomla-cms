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
$headerOpen = '<h'.$ptLevel.' class="componentheading'.$this->params->get('pageclass_sfx').'">';
$headerClose = '</h'.$ptLevel.'>';
?>


<?php if ($this->params->get('show_page_title', 1)) : ?>
<?php echo $headerOpen; ?>
	<?php echo $this->category->title; ?>
<?php echo $headerClose; ?>
<?php endif; ?>


<div class="weblinks<?php echo $this->params->get('pageclass_sfx'); ?>">

	<?php if ( $this->category->image || $this->category->description) : ?>
	<div class="contentdescription<?php echo $this->params->get('pageclass_sfx'); ?>">

		<?php if ($this->category->image) : ?>
		<?php echo $this->category->image; ?>
		<?php endif; ?>

		<?php echo $this->category->description; ?>

		<?php if ($this->category->image) : ?>
		<div class="wrap_image">&nbsp;</div>
		<?php endif; ?>

	</div>
	<?php endif; ?>

	<?php echo $this->loadTemplate('items'); ?>

</div>
