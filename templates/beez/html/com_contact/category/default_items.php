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
foreach ($this->items as $item)
{
	echo '<tr>';
	echo '<td class="sectiontableentry" headers="Count">';
	echo $item->count + 1;
	echo '</td>';
	if ($this->params->get('position')) {
		echo '<td headers="Position" class="sectiontableentry' . $item->odd . '">' . $item->con_position . '</td>';
	}

	echo '<td height="20" class="sectiontableentry" headers="Name">';
	echo '<a href="' . $item->link . '" class="category' . $this->params->get('pageclass_sfx') . '">' . $item->name . '</a></td>';

	if ($this->params->get('email')) {
		echo '<td headers="Mail" class="sectiontableentry' . $item->odd . '">' . $item->email_to . '</td>';
	}

	if ($this->params->get('telephone')) {
		echo '<td headers="Phone" class="sectiontableentry">' . $item->telephone . '</td>';
	}

	if ($this->params->get('mobile')) {
		echo '<td headers="Mobile" class="sectiontableentry' . $item->odd . '">' . $item->mobile . '</td>';
	}

	if ($this->params->get('fax')) {
		echo '<td headers="Fax" class="sectiontableentry">' . $item->fax . '</td>';
	}
	echo '</tr>';
}
?>