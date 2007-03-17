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
$image = 'templates' . DS . $mainframe->getTemplate() . DS . 'images' . DS . 'trans.gif';

if ($this->user->authorize('action', 'edit', 'content', 'all')) {
	echo '<div class="contentpaneopen_edit' . $this->params->get('pageclass_sfx') . '" style="float: left;">';
	echo $this->getIcon('edit');
	echo '</div>';
}

if ($this->params->get('item_title')) {
	echo '<h' . $hlevel . ' class="contentheading' . $this->params->get('pageclass_sfx') . '">';
	if ($this->params->get('link_titles') && $this->item->readmore_link != '') {
		echo '<a href="' . $this->item->readmore_link . '" class="contentpagetitle' . $this->params->get('pageclass_sfx') . '">';
		echo $this->item->title;
		echo '</a>';
	} else {
		echo $this->item->title;
	}
	echo '</h' . $hlevel . '>';
}

if (!$this->params->get('intro_only')) {
	echo $this->item->event->afterDisplayTitle;
}

if (isset($this->print) && $this->print) {
	echo '<p class="buttonheading">';
	echo $this->getIcon('print_screen');
	echo '</p>';
} else {
	if ($this->params->get('pdf') || $this->params->get('print') || $this->params->get('email')) {
		echo '<p class="buttonheading">';
		echo '<img src="' . $image . '" alt="' . JText :: _('attention open in a new window') . '" />';
		if ($this->params->get('pdf')) {
			echo $this->getIcon('pdf');
		}
		if ($this->params->get('print')) {
			echo $this->getIcon('print');
		}
		if ($this->params->get('email')) {
			echo $this->getIcon('email');
		}
		echo '</p>';
	}
}

if (($this->params->get('section') && $this->item->sectionid) || ($this->params->get('category') && $this->item->catid)) {
	echo '<p class="pageinfo">';
	if ($this->params->get('section') && $this->item->sectionid) {
		echo '<span>';
		echo $this->item->section;
		if ($this->params->get('category')) {
			echo ' - ';
		}
		echo '</span>';
	}

	if ($this->params->get('category') && $this->item->catid) {
		echo '<span>';
		echo $this->item->category;
		echo '</span>';
	}
	echo '</p>';
}

if ((!empty ($this->item->modified) && $this->params->get('modifydate')) || ($this->params->get('showAuthor') && ($this->item->author != "")) || ($this->params->get('createdate'))) {
	echo '<p class="iteminfo">';

	if (!empty ($this->item->modified) && $this->params->get('modifydate')) {
		echo '<span class="modifydate">';
		echo JText :: _('Last Updated') . ' (' . JHTML :: Date($this->item->modified, DATE_FORMAT_LC2) . ')';
		echo '</span>';
	}
	if (($this->params->get('showAuthor')) && ($this->item->author != "")) {
		echo '<span class="createdby">';
		JText :: printf('Written by', ($this->item->created_by_alias ? $this->item->created_by_alias : $this->item->author));
		echo '</span>';
	}
	if ($this->params->get('createdate')) {
		echo '<span class="createdate">';
		echo JHTML :: Date($this->item->created, DATE_FORMAT_LC2);
		echo '</span>';
	}
	echo '</p>';
}

echo $this->item->event->beforeDisplayContent;

if ($this->params->get('url') && $this->item->urls) {
	echo '<span class="small">';
	/* sefreltoabs ??? */
	echo '<a href="http://' . $this->item->urls . '" target="_blank">';
	echo $this->item->urls . '</a></span>';
}

if (isset ($this->item->toc)) {
	echo $this->item->toc;
}

echo JOutputFilter::ampReplace($this->item->text);

if ($this->params->get('readmore') && $this->params->get('intro_only') && $this->item->readmore_text) {
	echo '<p>';
	echo '<a href="' . $this->item->readmore_link . '" class="readon' . $this->params->get('pageclass_sfx') . '">';
	$alias = JOutputFilter :: stringURLSafe($this->item->title);
	if ($this->item->title_alias === $alias) {
		echo $this->item->readmore_text;
	} else {
		echo $this->item->title_alias;
	}
	echo '</a>';
	echo '</p>';
}

echo $this->item->event->afterDisplayContent;