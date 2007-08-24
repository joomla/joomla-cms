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
$hlevel=$hlevel+1;
$ptlevel = $templateParams->get('pageTitleHeaderLevel', '1');
echo '<h'.$hlevel.'>';
echo $this->message->title ;
echo '</h'.$hlevel.'>';

echo '<p class="message">';
echo  $this->message->text ;
echo '</p>';
?>