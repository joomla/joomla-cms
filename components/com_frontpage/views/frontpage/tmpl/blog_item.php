<?php if ($this->user->authorize('action', 'edit', 'content', 'all')) : ?>
	<div class="contentpaneopen_edit<?php echo $this->params->get( 'pageclass_sfx' ); ?>" style="float: left;">
		<?php JContentHTMLHelper::editIcon($this->item, $this->params, $this->access); ?>
	</div>
<?php endif; ?>

<?php if ($this->params->get('item_title') || $this->params->get('pdf') || $this->params->get('print') || $this->params->get('email')) : ?>
<table class="contentpaneopen<?php echo $this->params->get( 'pageclass_sfx' ); ?>">
<tr>
	<?php
	// displays Item Title
	JContentHTMLHelper::title($this->item, $this->params, $this->item->readmore_link, $this->access);

	// displays PDF Icon
	JContentHTMLHelper::pdfIcon($this->item, $this->params, $this->item->readmore_link, false);

	// displays Print Icon
	mosHTML::PrintIcon($this->item, $this->params, false, $this->item->print_link);

	// displays Email Icon
	JContentHTMLHelper::emailIcon($this->item, $this->params, false);
	?>
</tr>
</table>
<?php endif; ?>
<?php  if (!$this->params->get('intro_only')) :
	echo $this->item->event->afterDisplayTitle;
endif; ?>
<?php echo $this->item->event->beforeDisplayContent; ?>
<table class="contentpaneopen<?php echo $this->params->get( 'pageclass_sfx' ); ?>">
<?php
	// displays Section & Category
	JContentHTMLHelper::sectionCategory($this->item, $this->params);

	// displays Author Name
	JContentHTMLHelper::author($this->item, $this->params);

	// displays Created Date
	JContentHTMLHelper::createDate($this->item, $this->params);

	// displays Urls
	JContentHTMLHelper::url($this->item, $this->params);
?>
<tr>
	<td valign="top" colspan="2">
	<?php

	// displays Table of Contents
	JContentHTMLHelper::toc($this->item);

	// displays Item Text
	echo $this->item->text;
	?>
	</td>
</tr>
<?php
	// displays Modified Date
	JContentHTMLHelper::modifiedDate($this->item, $this->params);

	// displays Readmore button
	JContentHTMLHelper::readMore($this->params, $this->item->readmore_link, $this->item->readmore_text);
?>
</table>
<span class="article_seperator">&nbsp;</span>
<?php echo $this->item->event->afterDisplayContent; ?>