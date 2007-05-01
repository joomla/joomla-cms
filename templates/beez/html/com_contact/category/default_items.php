<?php
defined('_JEXEC') or die('Restricted access');
foreach ($this->items as $item)
{
	echo '<tr>';
	echo '<td class="sectiontableentry" headers="Count">';
	echo $item->count + 1;
	echo '</td>';
	if ($this->params->get('show_position')) {
		echo '<td headers="Position" class="sectiontableentry' . $item->odd . '">' . $item->con_position . '</td>';
	}

	echo '<td height="20" class="sectiontableentry" headers="Name">';
	echo '<a href="' . $item->link . '" class="category' . $this->params->get('pageclass_sfx') . '">' . $item->name . '</a></td>';

	if ($this->params->get('show_email')) {
		echo '<td headers="Mail" class="sectiontableentry' . $item->odd . '">' . $item->email_to . '</td>';
	}

	if ($this->params->get('show_telephone')) {
		echo '<td headers="Phone" class="sectiontableentry">' . $item->telephone . '</td>';
	}

	if ($this->params->get('show_mobile')) {
		echo '<td headers="Mobile" class="sectiontableentry' . $item->odd . '">' . $item->mobile . '</td>';
	}

	if ($this->params->get('show_fax')) {
		echo '<td headers="Fax" class="sectiontableentry">' . $item->fax . '</td>';
	}
	echo '</tr>';
}
?>