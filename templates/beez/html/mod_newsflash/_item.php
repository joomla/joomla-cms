<?php
defined('_JEXEC') or die('Restricted access');

// temp fix
$level = 4;

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
echo JFilterOutput::ampReplace($item->text);
if (isset ($item->linkOn) && $item->readmore) {
	echo '<a href="' . $item->linkOn . '" class="readon">' . JText :: _('Read more') . '</a>';
}
// AJE: Don't think this is relevent in the context of this module??
//echo $item->afterDisplayContent;
echo '<span class="article_separator">&nbsp;</span>';
?>