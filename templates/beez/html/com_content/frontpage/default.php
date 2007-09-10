<?php
defined('_JEXEC') or die('Restricted access');

// temp fix
$hlevel = 2;
$ptlevel = 1;
$total = $this->total;
$colcount=$this->params->def('num_columns', 2);
if ($colcount == 0) {$colcount = 1;}

if ($this->params->get('show_page_title')) {
	echo '<h' . $ptlevel . ' class="componentheading' . $this->params->get('pageclass_sfx') . '">';
	echo $this->params->get('page_title');
	echo '</h' . $ptlevel . '>';
}
echo '<div class="blog' . $this->params->get('pageclass_sfx') . '">';
$i = 0;
if ($this->params->def('num_leading_articles', 1)) {

	$rowcount = (int) $this->params->get('num_leading_articles');
	for ($y = 0; $y < $rowcount && $i < $total; $y++) {
		echo '<div  class="leading' . $this->params->get('pageclass_sfx') . '" >';
		if (($i < $this->params->get('num_leading_articles')) && ($i < $total)) {
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
if ($this->params->def('num_intro_articles', 4) && ($i < $total)) {
	$rowcount = (int) $this->params->get('num_intro_articles') / $colcount;
	$ii = 0;
	for ($y = 0; $y < $rowcount && $i < $total; $y++) {
		echo '<div class="article_row' . $this->params->get('pageclass_sfx') . '">';
		$colcount = $this->params->get('num_columns');
		for ($z = 0; $z < $colcount; $z++) {
			$columnnumber = $z +1;
			echo '<div  class="article_column column' . $columnnumber . ' cols' . $colcount . '" >';
			if ($ii < $this->params->get('num_intro_articles') && ($i < $total)) {
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

if ($this->params->def('num_links', 4) && ($i < $total)) {
	echo '<div class="blog_more' . $this->params->get('pageclass_sfx') . '" >';
	$numberitems4links = $this->params->get('num_links');
	if ($i + $numberitems4links > $total) {
		$numberitems4links = $total - $i;
	}
	$this->links = array_slice($this->items, $i, $numberitems4links);
	echo $this->loadTemplate('links');
	echo '</div>';
}
if ($this->params->def('show_pagination_results', 1)) {
	echo '<p class="counter">';
	echo $this->pagination->getPagesCounter();
	echo "</p>";
}
if ($this->params->def('show_pagination', 2)) {
	echo $this->pagination->getPagesLinks();
}
echo '</div>';
?>
