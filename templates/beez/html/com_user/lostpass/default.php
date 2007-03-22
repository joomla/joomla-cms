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

echo '<form action="' . JRoute::_('index.php') . '" method="post" name="josForm" class="lost_password" >';
echo '<h' . $hlevel . ' class="componentheading">' . JText :: _('Lost your Password?') . '</h' . $hlevel . '>';

echo '<p>' . JText :: _('NEW_PASS_DESC') . '</p>';
echo '<p>' . '<label for="jusername">' . JText :: _('Username') . '</label>';
echo '<input type="text" id="jusername" name="jusername" class="inputbox"  maxlength="25" />' . '</p>';

echo '<p>' . '<label for="jemail">' . JText :: _('Email Address') . '</label>';
echo '<input type="text" id="jemail" name="jemail" class="inputbox"  />' . '</p>';

echo '<input type="submit" value="' . JText :: _('Send') . '" class="button" />';
echo '<input type="hidden" name="task" value="sendreminder" />';
echo '<input type="hidden" name="option" value="com_user" />';
echo '<input type="hidden" name="' . JUtility :: getToken() . '" value="1" />';
echo '</form>';
?>