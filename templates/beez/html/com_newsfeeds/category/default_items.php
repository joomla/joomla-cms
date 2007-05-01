<?php
defined('_JEXEC') or die('Restricted access');

if ($this->params->get('show_limit')) {
	echo '<div class="display">';
	echo '<form action="index.php" method="post" name="adminForm">';
	echo '<label for="limit">';
 	echo JText :: _('Display Num') . '&nbsp;';
	echo '</label>';
	echo $this->pagination->getLimitBox();
	echo '</form>';
	echo '</div>';
}

echo '<table class="newsfeeds">';

if ($this->params->get('show_headings')) {
	echo '<tr><th class="sectiontableheader' . $this->params->get('pageclass_sfx') . '" width="5" id="num">';
	echo JText :: _('Num');
	echo '</th>';
	if ($this->params->get('show_name')) {
		echo '<th width="90% class="sectiontableheader' . $this->params->get('pageclass_sfx') . '" id="name">';
		echo JText :: _('Feed Name');
		echo '</th>';
	}
	if ($this->params->get('show_articles')) {
		echo '<th width="10% class="sectiontableheader' . $this->params->get('pageclass_sfx') . '" nowrap="nowrap" id="num_a">';
		echo JText :: _('Num Articles');
		echo '</th>';
	}
	echo '</tr>';
}
foreach ($this->items as $item) {
	$odd=$item->odd + 1;
	echo '<tr class="sectiontableentry' . $odd . '">';
	echo '<td align="center" width="5" headers="num">';
	echo $item->count + 1;
	echo '</td>';
	if ($this->params->get('show_name')) {
		echo '<td  width="90%" headers="name"><a href="' . JRoute::_($item->link) . '" class="category' . $this->params->get('pageclass_sfx') . '">';		echo $item->name;
		echo '</a></td>';
	}
	if ($this->params->get('show_articles')) {
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
