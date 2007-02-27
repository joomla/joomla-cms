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

if ($this->params->get('page_title')) {
	echo '<h' . $ptlevel . ' class="componentheading' . $this->params->get('pageclass_sfx') . '">';
	echo $this->section->name;
	echo '</h' . $ptlevel . '>';
}

if ($this->params->def('description', 1) || $this->params->def('description_image', 1)) {
	$wrap = '';
	echo '<div class="contentdescription' . $this->params->get('pageclass_sfx') . '">';
	if ($this->params->get('description_image') && $this->section->image) {
		$wrap = '<div class="wrap_image">&nbsp;</div>';
		echo '<img src="images/stories/' . $this->section->image . '" class=image_"' . $this->section->image_position . '" />';
	}

	if ($this->params->get('description') && $this->section->description) {
		echo $this->section->description;
	}
	echo $wrap;
	echo '</div>';
}

if ($this->params->def('other_cat_section', 1)) {
	if (count($this->categories)) {
		echo '<ul>';
		foreach ($this->categories as $category) {

			echo '<li>';
			echo '<a href="' . $category->link . '" class="category" >';
			echo $category->name;
			echo '</a>';
			if ($this->params->get('cat_items')) {
				echo '&nbsp;<span class="small">( ';
				echo $category->numitems . " " . JText :: _('items') . ' )</span>';
			}
			if ($this->params->def('cat_description', 1) && $category->description) {
				echo '<br />';
				echo $category->description;
			}
			echo '</li>';
		}
		echo '</ul>';
	}
}
?>