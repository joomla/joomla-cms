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

if ($this->params->get('display')) {
	echo '<div class="display">';
	echo '<form action="index.php" method="post" name="adminForm">';
	echo JText :: _('Display Num') . '&nbsp;';
	echo $this->pagination->getLimitBox();
	echo '</form>';
	echo '</div>';
}

echo '<table class="newsfeeds">';

if ($this->params->get('headings')) {
	echo '<tr><th class="sectiontableheader' . $this->params->get('pageclass_sfx') . '" width="5" id="num">';
	echo JText :: _('Num');
	echo '</th>';
	if ($this->params->get('name')) {
		echo '<tr><th width="90% class="sectiontableheader' . $this->params->get('pageclass_sfx') . '" id="name">';
		echo JText :: _('Feed Name');
		echo '</th>';
	}
	if ($this->params->get('articles')) {
		echo '<tr><th width="10% class="sectiontableheader' . $this->params->get('pageclass_sfx') . '" nowrap="nowrap" id="num_a">';
		echo JText :: _('Num Articles');
		echo '</th>';
	}
}
foreach ($this->items as $item) {
	$odd=$item->odd + 1;
	echo '<tr class="sectiontableentry' . $odd . '">';
	echo '<td align="center" width="5" headers="num">';
	echo $item->count + 1;
	echo '</td>';
	if ($this->params->get('name')) {
		echo '<td  width="90%" headers="name"><a href="' . $item->link . '" class="category' . $this->params->get('pageclass_sfx') . '">';
		echo $item->name;
		echo '</a></td>';
	}
	if ($this->params->get('articles')) {
		echo '<td width="10%"  headers="num_a">' . $item->numarticles . '</td>';
	}

	echo '</tr>';
}
echo '</table>';

echo '<p class="counter">';
echo $this->pagination->getPagesCounter();
echo '</p>';
echo $this->pagination->getPagesLinks();
?>