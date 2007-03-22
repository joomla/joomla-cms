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
$level = $hlevel +2;

if ($params->get('item_title')) {
	if ($params->get('link_titles') && $linkOn != '') {
		echo '<h' . $level . '><a href="' . JRoute::_($linkOn) . '" class="contentpagetitle' . $params->get('moduleclass_sfx') . '">';
		echo $item->title;
		echo '</a></h' . $level . '>';
	} else {
		echo '<h' . $level . '>' . $item->title . '</h' . $level . '>';
	}

}

if (!$params->get('intro_only')) {
	echo $item->afterDisplayTitle;
}

echo $item->beforeDisplayContent;
echo JOutputFilter::ampReplace($item->text);
if (isset ($item->linkOn) && $item->readmore) {
	echo '<a href="' . $item->linkOn . '">' . JText :: _('Read more') . '</a>';
}
// AJE: Don't think this is relevent in the context of this module??
//echo $item->afterDisplayContent;
echo '<span class="article_separator">&nbsp;</span>';
?>