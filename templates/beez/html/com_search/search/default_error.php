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

echo '<h'.$hlevel .' class="error'. $this->params->get( 'pageclass_sfx' ).'">'.JText::_('Error').'</h'.$hlevel .'>';
echo '<div class="error'. $this->params->get( 'pageclass_sfx' ).'"><p>';
echo $this->error;
echo '</p></div>';
