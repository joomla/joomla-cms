<?php
defined('_JEXEC') or die('Restricted access');

// temporary fix
$hlevel = 2;

echo '<h' . $hlevel . '>';
echo JText :: _('Read more...');
echo '</h' . $hlevel . '>';
echo '<ul>';
foreach ($this->links as $link) {
	echo '<li>';
	echo '<a class="blogsection" href="'. JRoute ::_('index.php?view=article&id='.$link->slug).'">';
	echo $link->title;
	echo '</a>';
	echo '</li>';
}
echo '</ul>';
?>