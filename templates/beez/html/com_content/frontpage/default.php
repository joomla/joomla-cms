<?php
defined('_JEXEC') or die('Restricted access');
/**
 * @version $Id$
 * @author Design & Accessible Team ( Angie Radtke / Robert Deutz ) 
 * @package Joomla
 * @subpackage Accessible-Template-Beez
 * @copyright Copyright (C) 2005 - 2007 Open Source Matters. All rights reserved.
 * @license GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */

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
$total = $this->total;

if ($this->params->get('show_header')) {
	echo '<h' . $ptlevel . ' class="componentheading' . $this->params->get('pageclass_sfx') . '">';
	echo $this->params->get('header');
	echo '</h' . $ptlevel . '>';
}
echo '<div class="blog' . $this->params->get('pageclass_sfx') . '">';
if (isset ($this->frontpage->description) && ($this->params->def('description', 1) || $this->params->def('description_image', 1))) {
	$wrap = '';
	echo '<div class="contentdescription' . $this->params->get('pageclass_sfx') . '">';

	if ($this->params->get('descrip_image') && $this->frontpage->description->image) {
		$wrap = '<div class="wrap_image">&nbsp;</div>';
		echo '<img src="images/stories/' . $this->image . '" class="image_' . $this->frontpage->image_position . '" />';
	}
	if ($this->params->get('description') && $this->frontpage->description->text) {
		echo $this->frontpage->description->text;
	}
	echo $wrap;
	echo '</div>';
}
$i = 0;
if ($this->params->def('leading', 1)) {

	$rowcount = (int) $this->params->get('leading');
	for ($y = 0; $y < $rowcount && $i < $total; $y++) {
		echo '<div  class="leading' . $this->params->get('pageclass_sfx') . '" >';
		if (($i < $this->params->get('leading')) && ($i < $total)) {
			$this->item = & $this->getItem($i, $this->params);
			echo $this->loadTemplate('item');
			$i++;
		}
		echo '</div>';
		echo '<span class="leading_separator' . $this->params->get('pageclass_sfx') . '">&nbsp;</span>';
	}
} else {
	$i = 0;
}
if ($this->params->def('intro', 4) && ($i < $total)) {
	$rowcount = (int) $this->params->get('intro') / $this->params->def('columns', 2);
	$ii = 0;
	for ($y = 0; $y < $rowcount && $i < $total; $y++) {
		echo '<div class="article_row' . $this->params->get('pageclass_sfx') . '">';
		$colcount = $this->params->get('columns');
		for ($z = 0; $z < $colcount; $z++) {
			$columnnumber = $z +1;
			echo '<div  class="article_column column' . $columnnumber . ' cols' . $colcount . '" >';
			if ($ii < $this->params->get('intro') && ($i < $total)) {
				$this->item = & $this->getItem($i, $this->params);
				echo $this->loadTemplate('item');
				$i++;
				$ii++;
			}
			echo '</div>';
			echo '<span class="article_separator">&nbsp;</span>';
		}

		echo '<span class="row_separator' . $colcount . $this->params->get('pageclass_sfx') . '">&nbsp;</span>';
		echo '</div>';
	}
}

if ($this->params->def('link', 4) && ($i < $total)) {
	echo '<div class="blog_more' . $this->params->get('pageclass_sfx') . '" >';
	$numberitems4links = $this->params->get('link');
	if ($i + $numberitems4links > $total) {
		$numberitems4links = $total - $i;
	}
	$this->links = array_slice($this->items, $i, $numberitems4links);
	echo $this->loadTemplate('links');
	echo '</div>';
}
if ($this->params->def('pagination_results', 1)) {
	echo '<p class="counter">';
	echo $this->pagination->getPagesCounter();
	echo "</p>";
}
if ($this->params->def('pagination', 2)) {
	echo $this->pagination->getPagesLinks();
}
echo '</div>';
?>
