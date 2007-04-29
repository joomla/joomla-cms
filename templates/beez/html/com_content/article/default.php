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

$image = 'templates' . DS . $mainframe->getTemplate() . DS . 'images' . DS . 'trans.gif';

echo '<div id="page">';
if ($this->user->authorize('action', 'edit', 'content', 'all') && !($this->print)) {
        echo '<div class="contentpaneopen_edit' . $this->params->get('pageclass_sfx') . '" style="float: left;">';
      echo ContentHelperHTML::Icon('edit', $this->article, $this->params, $this->access);
        echo '</div>';
}

$hopen = '<h' . $ptlevel . '>';
if ($this->params->get('section') && $this->article->sectionid) {
	echo '<h' . $ptlevel . '>';
	echo $this->article->section;
	if ($this->params->get('category') && $this->article->catid) {
		echo ' - ';
		$hopen = '';
	} else {
		echo '</h' . $ptlevel . '>';
	}
}

if ($this->params->get('category') && $this->article->catid) {
	echo $hopen;
	echo $this->article->category;
	echo '</h' . $ptlevel . '>';
}

if ($this->params->get('item_title')) {
	echo '<h' . $hlevel . ' class="contentheading' . $this->params->get('pageclass_sfx') . '">';
	if ($this->params->get('link_titles') && $this->article->readmore_link != '') {
		echo '<a href="' . JRoute::_($this->article->readmore_link) . '" class="contentpagetitle' . $this->params->get('pageclass_sfx') . '">';
		echo $this->article->title;
		echo '</a>';
	} else {
		echo $this->article->title;
	}
	echo '</h' . $hlevel . '>';
}

if (!$this->params->get('intro_only')) {
	echo $this->article->event->afterDisplayTitle;
}

if ($this->print) {
        echo '<p class="buttonheading">';
          echo ContentHelperHTML::Icon('print_screen',  $this->article, $this->params, $this->access);
        echo '</p>';
} else {
        if ($this->params->get('pdf') || $this->params->get('print') || $this->params->get('email')) {
                echo '<p class="buttonheading">';
                echo '<img src="' . $image . '" alt="' . JText :: _('attention open in a new window') . '" />';
                if ($this->params->get('pdf')) {
                        echo ContentHelperHTML::Icon('pdf',  $this->article, $this->params, $this->access);
                }
                if ($this->params->get('print')) {
                        echo ContentHelperHTML::Icon('print',  $this->article, $this->params, $this->access);
                }
                if ($this->params->get('email')) {
                       echo ContentHelperHTML::Icon('email',  $this->article, $this->params, $this->access);
                }

                echo '</p>';
        }
}

if ((!empty ($this->article->modified) && $this->params->get('modifydate')) || ($this->params->get('showAuthor') && ($this->article->author != "")) || ($this->params->get('createdate'))) {
	echo '<p class="iteminfo">';

	if (!empty ($this->article->modified) && $this->params->get('modifydate')) {
		echo '<span class="modifydate">';
		echo JText :: _('Last Updated') . ' (' . JHTML :: Date($this->article->modified, DATE_FORMAT_LC2) . ')';
		echo '</span>';
	}
	if (($this->params->get('showAuthor')) && ($this->article->author != "")) {
		echo '<span class="createdby">';
		JText :: printf('Written by', ($this->article->created_by_alias ? $this->article->created_by_alias : $this->article->author));
		echo '</span>';
	}
	if ($this->params->get('createdate')) {
		echo '<span class="createdate">';
		echo JHTML :: Date($this->article->created, DATE_FORMAT_LC2);
		echo '</span>';
	}
	echo '</p>';
}

echo $this->article->event->beforeDisplayContent;

if ($this->params->get('url') && $this->article->urls) {
	echo '<span class="small">';
	/* sefreltoabs ??? */
	echo '<a href="' . JRoute::_($this->article->urls) . '" target="_blank">';
	echo $this->article->urls . '</a></span>';
}

if (isset ($this->article->toc)) {
	echo $this->article->toc;
}

echo JOutputFilter::ampReplace($this->article->text);

if ($this->params->get('readmore') && $this->params->get('intro_only') && $this->article->readmore_text) {
	echo '<p>';
	echo '<a href="' . JRoute::_($this->article->readmore_link) . '" class="readon' . $this->params->get('pageclass_sfx') . '">';
	$alias = JOutputFilter :: stringURLSafe($this->item->title);
	if ($this->item->title_alias === $alias) {
		echo $this->item->readmore_text;
	} else {
		echo $this->item->title_alias;
	}
	echo '</a>';
	echo '</p>';
}

echo $this->article->event->afterDisplayContent;
echo '</div>';
?>