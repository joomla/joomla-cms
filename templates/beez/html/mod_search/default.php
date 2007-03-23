<?php
defined('_JEXEC') or die('Restricted access');

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

echo '<form action="index.php" method="post" class="search' . $params->get('moduleclass_sfx') . '">';
// echo '<fieldset>';
echo '<label for="mod_search_searchword">' . JText :: _('search') . ' </label>';
echo $inputfield;
//   echo '</fieldset>';
// echo '<p><input type="submit" name="Submit" class="button" value="'. JText::_( 'search').'" /></p>';
echo '<input type="hidden" name="option" value="com_search" />';
echo '<input type="hidden" name="task"   value="search" />';

echo '<input type="hidden" name="Itemid" value="' . $itemid . '" />';

echo '</form>'
?>