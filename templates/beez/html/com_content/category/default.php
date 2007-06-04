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
	echo $this->category->name;
	echo '</h' . $ptlevel . '>';
}

if ($this->params->def('show_description', 1) || $this->params->def('show_description_image', 1)) {
	$wrap = '';
	echo '<div class="contentdescription' . $this->params->get('pageclass_sfx') . '">';
	if ($this->params->get('show_description_image') && $this->category->image) {
		$wrap = '<div class="wrap_image">&nbsp;</div>';
		echo '<img src="images/stories/' . $this->category->image . '" class="image_' . $this->category->image_position . '" />';
	}

	if ($this->params->get('show_description') && $this->category->description) {
		echo $this->category->description;
	}
	echo $wrap;
	echo '</div>';
}

$this->items = & $this->getItems();
echo $this->loadTemplate('items');

if ($this->access->canEdit || $this->access->canEditOwn) {
	echo JHTML::_('icon.create', $this->category  , $this->params, $this->access);
}
?>

