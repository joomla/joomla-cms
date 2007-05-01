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

if ($this->params->get('show_page_title')) {
	echo '<h' . $ptlevel . ' class="componentheading' . $this->params->get('pageclass_sfx') . '">';
	echo $this->params->get('page_title');
	echo '</h' . $ptlevel . '>';
}

if ($this->params->def('show_comp_description', 1) || $this->params->get('image', -1) != -1) {
	echo '<div class="contentdescription' . $this->params->get('pageclass_sfx') . '">';
	$wrap = '';
	if ($this->params->get('image', -1) != -1) {
		$wrap = '<div class="wrap_image">&nbsp;</div>';
		echo '<img src="images/stories/' . $this->params->get('image') . '" class="image_' . $this->params->get('image_align') . '" />';
	}
	echo $this->params->get('comp_description');
	echo $wrap;
	echo '</div>';
}

if (count($this->categories)) {
	echo '<ul>';
	foreach ($this->categories as $category) {

		echo '<li>';
		echo '<a href="' . JRoute::_($category->link) . '" class="category" >';
		echo $category->title;
		echo '</a>';
		if ($this->params->get('show_cat_items')) {
			echo '&nbsp;<span class="small">( ';
			echo $category->numlinks . " " . JText :: _('items') . ' )</span>';
		}
		if ($this->params->def('show_cat_description', 1) && $category->description) {
			echo '<br />';
			echo $category->description;
		}
		echo '</li>';
	}
	echo '</ul>';
}
?>