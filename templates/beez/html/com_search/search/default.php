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

if ($this->params->get('show_page_title') && $option == 'com_search') {
	echo '<h' . $ptlevel . ' class="componentheading' . $this->params->get('pageclass_sfx') . '">';
	echo $this->params->get('page_title');
	echo '</h' . $ptlevel . '>';
} else {
	echo '<h' . $ptlevel . ' class="componentheading' . $this->params->get('pageclass_sfx') . '">';
	echo JText :: _('Search');
	echo '</h' . $ptlevel . '>';
}

echo '<div id="page">';

if (!$this->error) {
	echo $this->loadTemplate('results');
} else {
	echo $this->loadTemplate('error');
}

echo $this->loadTemplate('form');
echo '</div>';
?>
