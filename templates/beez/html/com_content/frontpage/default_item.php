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
$image = 'templates/' . $mainframe->getTemplate() . '/images/trans.gif';

if ($this->user->authorize('com_content', 'edit', 'content', 'all') ) {
	echo '<div class="contentpaneopen_edit' . $this->params->get('pageclass_sfx') . '" style="float: left;">';
	echo JHTML::_('icon.edit', $this->item, $this->params, $this->access);
	echo '</div>';
}

if ($this->params->get('show_title')) {
	echo '<h' . $hlevel . ' class="contentheading' . $this->params->get('pageclass_sfx') . '">';
	if ($this->params->get('link_titles') && $this->item->readmore_link != '') {
		echo '<a href="' .$this->item->readmore_link. '" class="contentpagetitle' . $this->params->get('pageclass_sfx') . '">';
		echo $this->item->title;
		echo '</a>';
	} else {
		echo $this->item->title;
	}
	echo '</h' . $hlevel . '>';
}

if (!$this->params->get('show_intro')) {
	echo $this->item->event->afterDisplayTitle;
}

if ($this->params->get('show_pdf_icon') || $this->params->get('show_print_icon') || $this->params->get('show_email_icon')) {
	echo '<p class="buttonheading">';
	echo '<img src="' . $image . '" alt="' . JText :: _('attention open in a new window') . '" />';
	if ($this->params->get('show_pdf_icon')) {
		echo JHTML::_('icon.pdf',  $this->item, $this->params, $this->access);
	}
	if ($this->params->get('show_print_icon')) {
		echo JHTML::_('icon.print_popup',  $this->item, $this->params, $this->access);
	}
	if ($this->params->get('show_email_icon')) {
		echo JHTML::_('icon.email', $this->item, $this->params, $this->access);
	}
	echo '</p>';
}

if (($this->params->get('show_section') && $this->item->sectionid) || ($this->params->get('show_category') && $this->item->catid)) {
	echo '<p class="pageinfo">';
	if ($this->params->get('show_section') && $this->item->sectionid) {
		echo '<span>';
		echo $this->item->section;
		if ($this->params->get('show_category')) {
			echo ' - ';
		}
		echo '</span>';
	}

	if ($this->params->get('show_category') && $this->item->catid) {
		echo '<span>';
		echo $this->item->category;
		echo '</span>';
	}
	echo '</p>';
}

if ((!empty ($this->item->modified) && $this->params->get('show_modify_date')) || ($this->params->get('show_author') && ($this->item->author != "")) || ($this->params->get('show_create_date'))) {
	echo '<p class="iteminfo">';

	if (!empty ($this->item->modified) && $this->params->get('show_modify_date')) {
		echo '<span class="modifydate">';
		echo JText :: _('Last Updated') . ' (' . JHTML :: Date($this->item->modified, JText::_('DATE_FORMAT_LC2')) . ')';
		echo '</span>';
	}
	if (($this->params->get('show_author')) && ($this->item->author != "")) {
		echo '<span class="createdby">';
		JText :: printf('Written by', ($this->item->created_by_alias ? $this->item->created_by_alias : $this->item->author));
		echo '</span>';
	}
	if ($this->params->get('show_create_date')) {
		echo '<span class="createdate">';
		echo JHTML :: Date($this->item->created, JText::_('DATE_FORMAT_LC2'));
		echo '</span>';
	}
	echo '</p>';
}

echo $this->item->event->beforeDisplayContent;

if ($this->params->get('show_url') && $this->item->urls) {
	echo '<span class="small">';
	echo '<a href="' . JRoute::_($this->item->urls) . '" target="_blank">';
	echo $this->item->urls . '</a></span>';
}

if (isset ($this->item->toc)) {
	echo $this->item->toc;
}

echo JOutputFilter::ampReplace($this->item->text);

if ($this->params->get('show_readmore') && $this->params->get('show_intro') && $this->item->readmore_text) {
	echo '<p><a href="' . $this->item->readmore_link . '" class="readon' . $this->params->get('pageclass_sfx') . '">';
	$alias = JOutputFilter :: stringURLSafe($this->item->title);
	if ($this->item->title_alias == $alias || $this->item->title_alias == '') {
		echo $this->item->readmore_text;
	} else {
		echo $this->item->title_alias;
	}
	echo '</a></p>';
}

echo $this->item->event->afterDisplayContent;