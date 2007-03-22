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
$hlevel++;
$hlevel++;

echo "<h$hlevel>" . $item->title . "</h$hlevel>";
echo '<form name="form2" method="post" action="'.JRoute::_('index.php').'" class="poll">';
echo '<fieldset>';
for ($i = 0, $n = count($options); $i < $n; $i++) {
	echo '<input type="radio" name="voteid" id="voteid' . $options[$i]->id . '" value="' . $options[$i]->id . '" alt="' . $options[$i]->id . '" />';
	echo '<label for="voteid' . $options[$i]->id . '">';
	echo $options[$i]->text;
	echo '</label><br />';
}
echo '</fieldset>';

echo '<input type="submit" name="task_button" class="button" value="' . JText :: _('Vote') . '" />';
echo '<a href="' . JRoute :: _("index.php?option=com_poll&task=results&id=$item->id#content") . '">';
echo JText :: _('Results');
echo '</a>';

echo '<input type="hidden" name="option" value="com_poll" />';
echo '<input type="hidden" name="Itemid" value="' . $itemid . '" />';
echo '<input type="hidden" name="id" value="' . $item->id . '" />';
echo '<input type="hidden" name="task" value="vote" />';
echo '<input type="hidden" name="' . JUtility :: getToken() . '" value="1" />';
echo '</form>';
?>