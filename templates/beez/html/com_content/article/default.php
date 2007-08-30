<?php
/**
 * @version $Id$
 */
defined('_JEXEC') or die('Restricted access');

?>

<div id="page">

<?php if ($this->user->authorize('com_content', 'edit', 'content', 'all') && !($this->print)) : ?>
<div class="contentpaneopen_edit<?php echo $this->escape($this->params->get('pageclass_sfx')); ?>" style="float: left;">
	<?php echo JHTML::_('icon.edit', $this->article, $this->params, $this->access); ?>
</div>
<?php endif; ?>

<?php if ($this->params->get('show_section') && $this->article->sectionid) : ?>
<h1>
	<?php echo $this->article->section;
	if ($this->params->get('show_category') && $this->article->catid) :
		echo ' - '.$this->article->category;
	endif; ?>
</h1>
<?php elseif ($this->params->get('show_category') && $this->article->catid) : ?>
<h1>
	<?php echo $this->article->category; ?>
</h1>
<?php endif; ?>

<?php if ($this->params->get('show_title')) : ?>
<h2 class="contentheading<?php echo $this->params->get('pageclass_sfx'); ?>">
	<?php if ($this->params->get('link_titles') && $this->article->readmore_link != '') : ?>
	<a href="<?php echo $this->article->readmore_link; ?>" class="contentpagetitle<?php echo $this->params->get('pageclass_sfx'); ?>">
		<?php echo $this->article->title; ?>
	</a>
	<?php else :
		echo $this->article->title;
	endif; ?>
</h2>
<?php endif; ?>

<?php if (!$this->params->get('show_intro')) :
	echo $this->article->event->afterDisplayTitle;
endif; ?>

<p class="buttonheading">
	<?php if ($this->print) :
		echo JHTML::_('icon.print_screen', $this->article, $this->params, $this->access);
	elseif ($this->params->get('show_pdf_icon') || $this->params->get('show_print_icon') || $this->params->get('show_email_icon')) : ?>
	<img src="templates/<?php echo $mainframe->getTemplate(); ?>/images/trans.gif" alt="<?php echo JText::_('attention open in a new window'); ?>" />
	<?php if ($this->params->get('show_pdf_icon')) :
		echo JHTML::_('icon.pdf', $this->article, $this->params, $this->access);
	endif;
	if ($this->params->get('show_print_icon')) :
		echo JHTML::_('icon.print_popup', $this->article, $this->params, $this->access);
	endif;
	if ($this->params->get('show_email_icon')) :
		echo JHTML::_('icon.email', $this->article, $this->params, $this->access);
	endif;
	endif; ?>
</p>

<?php if ((!empty ($this->article->modified) && $this->params->get('show_modify_date')) || ($this->params->get('show_author') && ($this->article->author != "")) || ($this->params->get('show_create_date'))) : ?>
<p class="iteminfo">
	<?php if (!empty ($this->article->modified) && $this->params->get('show_modify_date')) : ?>
	<span class="modifydate">
		<?php echo JText::_('Last Updated').' ('.JHTML::_('date', $this->article->modified, JText::_('DATE_FORMAT_LC2')).')'; ?>
	</span>
	<?php endif;
	if (($this->params->get('show_author')) && ($this->article->author != "")) : ?>
	<span class="createdby">
		<?php echo JText::sprintf('Written by', ($this->article->created_by_alias ? $this->article->created_by_alias : $this->article->author)); ?>
	</span>
	<?php endif;
	if ($this->params->get('show_create_date')) : ?>
	<span class="createdate">
		<?php echo JHTML::_('date', $this->article->created, JText::_('DATE_FORMAT_LC2')); ?>
	</span>
	<?php endif; ?>
</p>
<?php endif; ?>

<?php echo $this->article->event->beforeDisplayContent; ?>

<?php if ($this->params->get('show_url') && $this->article->urls) : ?>
<span class="small">
	<a href="<?php echo $this->article->urls; ?>" target="_blank">
		<?php echo $this->article->urls; ?>
	</a>
</span>
<?php endif; ?>

<?php if (isset ($this->article->toc)) :
	echo $this->article->toc;
endif; ?>

<?php echo JFilterOutput::ampReplace($this->article->text); ?>

<?php if ($this->params->get('show_readmore') && $this->params->get('show_intro') && $this->article->readmore_text) : ?>
<p>
	<a href="<?php echo $this->article->readmore_link; ?>" class="readon<?php echo $this->params->get('pageclass_sfx'); ?>">
	<?php $alias = JFilterOutput::stringURLSafe($this->item->title);
	if ($this->article->title_alias == $alias || $this->article->title_alias == '') :
		echo $this->article->readmore_text;
	else :
		echo $this->article->title_alias;
	endif; ?>
	</a>
</p>
<?php endif; ?>

<?php echo $this->article->event->afterDisplayContent; ?>

</div>
