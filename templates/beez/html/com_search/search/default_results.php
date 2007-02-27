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

if (stripslashes($this->searchword) != '') {
	echo '<div class="searchintro' . $this->params->get('pageclass_sfx') . '">';
	echo '<p>' . JText :: _('Search Keyword') . ' <strong>' . stripslashes($this->searchword) . '</strong></p>';
	echo '<p>' . eval ('echo "' . $this->result . '";');
	echo '<a href="http://www.google.com/search?q=' . $this->searchword . '" target="_blank">';
	echo $this->image . '</a></p>';
	echo '<p> <a href="#form1" onclick="document.getElementById(' . "'search_searchword'" . ').focus();return false" onkeypress="document.getElementById(' . "'search_searchword'" . ').focus();return false" >' . JText :: _('Search_again') . ' </a></p>';
	echo '</div>';
}

if (count($this->results)) {
	echo '<div class="results">';
	$level = $hlevel +1;
	echo '<h' . $level . '>';
	echo JText :: _('Search_result');
	echo '</h' . $level . '>';
	echo '<div class="display">';
	echo '<form  action="index.php" method="get" class="limit">';
	echo '<label for="limit">' . JText :: _('Display Num') . '</label>';
	$link = $this->pagination->_link;
	echo $this->pagination->getLimitBox($link);
	echo '<p>';
	echo $this->pagination->getPagesCounter();
	echo '</p>';
	echo '</form>';
	echo '</div>';
	$start = $this->pagination->limitstart + 1;
	echo '<ol class="list' . $this->params->get('pageclass_sfx') . '" start="' . $start . '">';
	foreach ($this->results as $result) {
		echo '<li>';
		echo '<span class="small' . $this->params->get('pageclass_sfx') . '"></span>';
		if ($result->href) {
			$result->href = ampReplace($result->href);
			if ($result->browsernav == 1) {
				$level = $hlevel +2;
				echo '<h' . $level . '>';
				echo '<a href="' . JRoute :: _($result->href) . '" target="_blank">';
			} else {
				$level = $hlevel +2;
				echo '<h' . $level . '>';
				echo '<a href="' . JRoute :: _($result->href) . '">';
			}
			echo $result->title;
			echo '</h' . $level . '>';
			echo '</a>';
			if ($result->section) {
				echo '<p>' . JText :: _('Category') . ':<span class="small' . $this->params->get('pageclass_sfx') . '">(';
				echo $result->section;
				echo ')</span></p>';
			}
		}
		echo ampReplace($result->text);
		if (!$mainframe->getCfg('hideCreateDate')) {
			echo '<span class="small' . $this->params->get('pageclass_sfx') . '">';
			echo $result->created;
			echo '</span>';
		}
		echo '</li>';
	}
	echo '</ol>';
	echo $this->pagination->getPagesLinks();
	echo "</div>";
}
?>