<?php
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

$hlevel = $templateParams->get('headerLevelComponent', '2');
$ptlevel = $templateParams->get('pageTitleHeaderLevel', '1');

if ($this->params->get('show_page_title', 1)) {
	echo '<h' . $ptlevel . ' class="componentheading' . $this->params->get('pageclass_sfx') . '">';
	echo $this->category->title;
	echo '</h' . $ptlevel . '>';
}
echo '<div class="weblinks' . $this->params->get('pageclass_sfx') . '">';

if (@ $this->_models->weblinksmodelcategory->_category->image || @ $this->category->description) {
	echo '<div  class="contentdescription' . $this->params->get('pageclass_sfx') . '">';
	$wrap = '';
	/* we use the model data, the normal object has only the complete img-tag */
	$image = $this->_models['category']->_category->image;
	$image_align = $this->_models['category']->_category->image_position;

	if (isset ($image)) {
		$wrap = '<div class="wrap_image">&nbsp;</div>';
		echo $image;
	}

	echo $this->category->description;
	echo $wrap;
	echo '</div>';
}
echo $this->loadTemplate('items');
echo '</div>';
?>