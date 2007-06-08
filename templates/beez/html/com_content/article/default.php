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

$image = 'templates/' . $mainframe->getTemplate() . '/images/trans.gif';

echo '<div id="page">';
if ($this->user->authorize('com_content', 'edit', 'content', 'all') && !($this->print)) {
	echo '<div class="contentpaneopen_edit' . $this->params->get('pageclass_sfx') . '" style="float: left;">';
	echo JHTML::_('icon.edit', $this->article, $this->params, $this->access);
	echo '</div>';
}

$hopen = '<h' . $ptlevel . '>';
if ($this->params->get('show_section') && $this->article->sectionid) {
	echo '<h' . $ptlevel . '>';
	echo $this->article->section;
	if ($this->params->get('show_category') && $this->article->catid) {
		echo ' - ';
		$hopen = '';
	} else {
		echo '</h' . $ptlevel . '>';
	}
}

if ($this->params->get('show_category') && $this->article->catid) {
	echo $hopen;
	echo $this->article->category;
	echo '</h' . $ptlevel . '>';
}

if ($this->params->get('show_title')) {
	echo '<h' . $hlevel . ' class="contentheading' . $this->params->get('pageclass_sfx') . '">';
	if ($this->params->get('link_titles') && $this->article->readmore_link != '') {
		echo '<a href="' . $this->article->readmore_link . '" class="contentpagetitle' . $this->params->get('pageclass_sfx') . '">';
		echo $this->article->title;
		echo '</a>';
	} else {
		echo $this->article->title;
	}
	echo '</h' . $hlevel . '>';
}

if (!$this->params->get('show_intro')) {
	echo $this->article->event->afterDisplayTitle;
}

if ($this->print) {
	echo '<p class="buttonheading">';
	echo JHTML::_('icon.print_screen',  $this->article, $this->params, $this->access);
	echo '</p>';
} else {
	if ($this->params->get('show_pdf_icon') || $this->params->get('show_print_icon') || $this->params->get('show_email_icon')) {
		echo '<p class="buttonheading">';
		echo '<img src="' . $image . '" alt="' . JText :: _('attention open in a new window') . '" />';
		if ($this->params->get('show_pdf_icon')) {
			echo JHTML::_('icon.pdf',  $this->article, $this->params, $this->access);
		}
		if ($this->params->get('show_print_icon')) {
			echo JHTML::_('icon.print_popup',  $this->article, $this->params, $this->access);
		}
		if ($this->params->get('show_email_icon')) {
			echo JHTML::_('icon.email',  $this->article, $this->params, $this->access);
		}
		echo '</p>';
	}
}

if ((!empty ($this->article->modified) && $this->params->get('show_modify_date')) || ($this->params->get('show_author') && ($this->article->author != "")) || ($this->params->get('show_create_date'))) {
	echo '<p class="iteminfo">';

	if (!empty ($this->article->modified) && $this->params->get('show_modify_date')) {
		echo '<span class="modifydate">';
		echo JText :: _('Last Updated') . ' (' . JHTML :: Date($this->article->modified, JText::_('DATE_FORMAT_LC2')) . ')';
		echo '</span>';
	}
	if (($this->params->get('show_author')) && ($this->article->author != "")) {
		echo '<span class="createdby">';
		JText :: printf('Written by', ($this->article->created_by_alias ? $this->article->created_by_alias : $this->article->author));
		echo '</span>';
	}
	if ($this->params->get('show_create_date')) {
		echo '<span class="createdate">';
		echo JHTML :: Date($this->article->created, JText::_('DATE_FORMAT_LC2'));
		echo '</span>';
	}
	echo '</p>';
}

echo $this->article->event->beforeDisplayContent;

if ($this->params->get('show_url') && $this->article->urls) {
	echo '<span class="small">';
	/* sefreltoabs ??? */
	echo '<a href="' . JRoute::_($this->article->urls) . '" target="_blank">';
	echo $this->article->urls . '</a></span>';
}

if (isset ($this->article->toc)) {
	echo $this->article->toc;
}

echo JOutputFilter::ampReplace($this->article->text);

if ($this->params->get('show_readmore') && $this->params->get('show_intro') && $this->article->readmore_text) {
	echo '<p><a href="' . $this->article->readmore_link . '" class="readon' . $this->params->get('pageclass_sfx') . '">';
	$alias = JOutputFilter :: stringURLSafe($this->item->title);
	if ($this->article->title_alias == $alias || $this->article->title_alias == '') {
		echo $this->article->readmore_text;
	} else {
		echo $this->article->title_alias;
	}
	echo '</a></p>';
}

echo $this->article->event->afterDisplayContent;
echo '</div>';
?>