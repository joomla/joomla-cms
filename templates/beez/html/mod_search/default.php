<?php
defined('_JEXEC') or die('Restricted access');

echo '<form action="index.php"  method="post" class="search' . $params->get('moduleclass_sfx') . '">';
echo '<label for="mod_search_searchword">' . JText :: _('search') . ' </label>';
echo $inputfield;
echo '<input type="hidden" name="option" value="com_search" />';
echo '<input type="hidden" name="task"   value="search" />';
echo '<input type="hidden" name="Itemid" value="' . $itemid . '" />';
echo '</form>';
