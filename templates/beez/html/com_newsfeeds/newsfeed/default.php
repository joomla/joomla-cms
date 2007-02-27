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
?>
<div style="direction: <?php echo $this->newsfeed->rtl ? 'rtl' :'ltr'; ?>; text-align: <?php echo $this->newsfeed->rtl ? 'right' :'left'; ?>">


<?php

if ($this->params->get('header')) {
	echo '<h' . $ptlevel . ' class="componentheading' . $this->params->get('pageclass_sfx') . '">';
	echo $this->params->get('header');
	echo '</h' . $ptlevel . '>';
}

echo '<h' . $hlevel . ' class="contentheading' . $this->params->get('pageclass_sfx') . '">';
echo '<a href="' . ampReplace($this->newsfeed->channel['link']) . '" target="_blank">';
echo str_replace('&apos;', "'", $this->newsfeed->channel['title']);
echo '</a></h' . $hlevel . '>';

if ($this->params->get('feed_descr')) {
	echo str_replace('&apos;', "'", $this->newsfeed->channel['description']);
}
if (isset ($this->newsfeed->image['url']) && isset ($this->newsfeed->image['title']) && $this->params->get('feed_image')) {
	echo '<img src="' . $this->newsfeed->image['url'] . '" alt="' . $this->newsfeed->image['title'] . '" />';

}
if (count($this->newsfeed->items)) {
	echo '<ul>';
	foreach ($this->newsfeed->items as $item) {
		echo '<li>';
		if (!is_null($item->get_link())) {
			echo '<a href="' . ampReplace($item->get_link()) . '" target="_blank">';
			echo $item->get_title();
			echo '</a>';
		}
		if ($this->params->get('item_descr') && $item->get_description()) {
			echo '<br />';
			$text = $this->limitText($item->get_description(), $this->params->get('word_count'));
			echo str_replace('&apos;', "'", $text);
			echo '<br /><br />';
		}
		echo '</li>';
	}
	echo '</ul>';
}
echo '</div>';
?>