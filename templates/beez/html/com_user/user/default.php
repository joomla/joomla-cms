<?php // no direct access
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
echo '<h' . $ptlevel . ' class="componentheading">';
echo JText::_( 'Welcome!' );
echo '</h' . $ptlevel . '>';

echo '<div class="contentdescription">';
echo JText::_( 'WELCOME_DESC' );
echo '</div>';
?>