<?php // no direct access
defined('_JEXEC') or die('Restricted access'); ?>
<?php if ($this->user->authorize('com_content', 'edit', 'content', 'all') && !$this->print) : ?>
	<div class="contentpaneopen_edit<?php echo $this->params->get( 'pageclass_sfx' ); ?>" style="float: left;">
		<?php echo JHTML::_('icon.edit', $this->article, $this->params, $this->access); ?>
	</div>
<?php endif; ?>

<?php if ($this->params->get('show_title') || $this->params->get('show_pdf_icon') || $this->params->get('show_print_icon') || $this->params->get('show_email_icon')) : ?>
<table class="contentpaneopen<?php echo $this->params->get( 'pageclass_sfx' ); ?>">
<tr>
	<?php if ($this->params->get('show_title')) : ?>
	<td class="contentheading<?php echo $this->params->get( 'pageclass_sfx' ); ?>" width="100%">
		<?php if ($this->params->get('link_titles') && $this->article->readmore_link != '') : ?>
		<a href="<?php echo $this->article->readmore_link; ?>" class="contentpagetitle<?php echo $this->params->get( 'pageclass_sfx' ); ?>">
			<?php echo $this->article->title; ?>
		</a>
		<?php else : ?>
			<?php echo $this->article->title; ?>
		<?php endif; ?>
	</td>
	<?php endif; ?>
	<?php if (!$this->print) : ?>
	<?php if ($this->params->get('show_pdf_icon')) : ?>
	<td align="right" width="100%" class="buttonheading">
	<?php echo JHTML::_('icon.pdf',  $this->article, $this->params, $this->access); ?>
	</td>
	<?php endif; ?>

	<?php if ( $this->params->get( 'show_print_icon' )) : ?>
	<td align="right" width="100%" class="buttonheading">
	<?php echo JHTML::_('icon.print_popup',  $this->article, $this->params, $this->access); ?>
	</td>
	<?php endif; ?>

	<?php if ($this->params->get('show_email_icon')) : ?>
	<td align="right" width="100%" class="buttonheading">
	<?php echo JHTML::_('icon.email',  $this->article, $this->params, $this->access); ?>
	</td>
	<?php endif; ?>
	<?php else : ?>
	<td align="right" width="100%" class="buttonheading">
	<?php echo JHTML::_('icon.print_screen',  $this->article, $this->params, $this->access); ?>
	</td>
	<?php endif; ?>
</tr>
</table>
<?php endif; ?>

<?php  if (!$this->params->get('show_intro')) :
	echo $this->article->event->afterDisplayTitle;
endif; ?>

<?php echo $this->article->event->beforeDisplayContent; ?>
<table class="contentpaneopen<?php echo $this->params->get( 'pageclass_sfx' ); ?>">
<?php if (($this->params->get('section') && $this->article->sectionid) || ($this->params->get('category') && $this->article->catid)) : ?>
<tr>
	<td>
		<?php if ($this->params->get('show_section') && $this->article->sectionid) : ?>
		<span>
			<?php echo $this->article->section; ?>
			<?php if ($this->params->get('show_category')) : ?>
				<?php echo ' - '; ?>
			<?php endif; ?>
		</span>
		<?php endif; ?>

		<?php if ($this->params->get('show_category') && $this->article->catid) : ?>
		<span>
			<?php echo $this->article->category; ?>
		</span>
		<?php endif; ?>
	</td>
</tr>
<?php endif; ?>

<?php if (($this->params->get('show_author')) && ($this->article->author != "")) : ?>
<tr>
	<td width="70%"  valign="top" colspan="2">
		<span class="small">
			<?php JText::printf( 'Written by', ($this->article->created_by_alias ? $this->article->created_by_alias : $this->article->author) ); ?>
		</span>
		&nbsp;&nbsp;
	</td>
</tr>
<?php endif; ?>

<?php if ($this->params->get('show_create_date')) : ?>
<tr>
	<td valign="top" colspan="2" class="createdate">
		<?php echo $this->article->created; ?>
	</td>
</tr>
<?php endif; ?>

<?php if ($this->params->get('show_url') && $this->article->urls) : ?>
<tr>
	<td valign="top" colspan="2">
		<a href="http://<?php echo $this->article->urls ; ?>" target="_blank">
			<?php echo $this->article->urls; ?></a>
	</td>
</tr>
<?php endif; ?>

<tr>
<td valign="top" colspan="2">
<?php if (isset ($this->article->toc)) : ?>
	<?php echo $this->article->toc; ?>
<?php endif; ?>
<?php echo $this->article->text; ?>
</td>
</tr>

<?php if ( $this->article->mod_date !='' && $this->params->get('show_modify_date')) : ?>
<tr>
	<td colspan="2"  class="modifydate">
		<?php echo JText::_( 'Last Updated' ); ?> ( <?php echo JHTML::_('date', $this->article->modified, JText::_('DATE_FORMAT_LC2')); ?> )
	</td>
</tr>
<?php endif; ?>

<?php if ($this->params->get('show_readmore') && $this->params->get('show_intro') && $this->article->readmore_text) : ?>
<tr>
	<td  colspan="2">
		<a href="<?php echo $this->article->readmore_link; ?>" class="readon<?php echo $this->params->get( 'pageclass_sfx' ); ?>">
			<?php echo $this->article->readmore_text; ?>
		</a>
	</td>
</tr>
<?php endif; ?>

</table>
<span class="article_separator">&nbsp;</span>
<?php echo $this->article->event->afterDisplayContent; ?>