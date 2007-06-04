<?php // no direct access
defined('_JEXEC') or die('Restricted access'); ?>
<?php if ($this->user->authorize('com_content', 'edit', 'content', 'all')) : ?>
	<div class="contentpaneopen_edit<?php echo $this->params->get( 'pageclass_sfx' ); ?>" style="float: left;">
		<?php echo JHTML::_('icon.edit', $this->item, $this->params, $this->access); ?>
	</div>
<?php endif; ?>

<?php if ($this->params->get('show_title') || $this->params->get('show_pdf_icon') || $this->params->get('show_print_icon') || $this->params->get('show_email_icon')) : ?>
<table class="contentpaneopen<?php echo $this->params->get( 'pageclass_sfx' ); ?>">
<tr>
	<?php if ($this->params->get('show_title')) : ?>
	<td class="contentheading<?php echo $this->params->get( 'pageclass_sfx' ); ?>" width="100%">
		<?php if ($this->params->get('link_titles') && $this->item->readmore_link != '') : ?>
		<a href="<?php echo $this->item->readmore_link; ?>" class="contentpagetitle<?php echo $this->params->get( 'pageclass_sfx' ); ?>">
			<?php echo $this->item->title; ?>
		</a>
		<?php else : ?>
			<?php echo $this->item->title; ?>
		<?php endif; ?>
	</td>
	<?php endif; ?>

	<?php if ($this->params->get('show_pdf_icon')) : ?>
	<td align="right" width="100%" class="buttonheading">
	<?php  echo JHTML::_('icon.pdf', $this->item, $this->params, $this->access); ?>
	</td>
	<?php endif; ?>

	<?php if ( $this->params->get( 'show_print_icon' )) : ?>
	<td align="right" width="100%" class="buttonheading">
	<?php echo JHTML::_('icon.print_popup', $this->item, $this->params, $this->access); ?>
	</td>
	<?php endif; ?>

	<?php if ($this->params->get('show_email_icon')) : ?>
	<td align="right" width="100%" class="buttonheading">
	<?php echo JHTML::_('icon.email', $this->item, $this->params, $this->access); ?>
	</td>
	<?php endif; ?>
</tr>
</table>
<?php endif; ?>
<?php  if (!$this->params->get('show_intro')) :
	echo $this->item->event->afterDisplayTitle;
endif; ?>
<?php echo $this->item->event->beforeDisplayContent; ?>
<table class="contentpaneopen<?php echo $this->params->get( 'pageclass_sfx' ); ?>">
<?php if (($this->params->get('show_section') && $this->item->sectionid) || ($this->params->get('show_category') && $this->item->catid)) : ?>
<tr>
	<td>
		<?php if ($this->params->get('show_section') && $this->item->sectionid) : ?>
		<span>
			<?php echo $this->item->section; ?>
			<?php if ($this->params->get('show_category')) : ?>
				<?php echo ' - '; ?>
			<?php endif; ?>
		</span>
		<?php endif; ?>

		<?php if ($this->params->get('show_category') && $this->item->catid) : ?>
		<span>
			<?php echo $this->item->category; ?>
		</span>
		<?php endif; ?>
	</td>
</tr>
<?php endif; ?>

<?php if (($this->params->get('show_author')) && ($this->item->author != "")) : ?>
<tr>
	<td width="70%"  valign="top" colspan="2">
		<span class="small">
			<?php JText::printf( 'Written by', ($this->item->created_by_alias ? $this->item->created_by_alias : $this->item->author) ); ?>
		</span>
		&nbsp;&nbsp;
	</td>
</tr>
<?php endif; ?>

<?php if ($this->params->get('show_create_date')) : ?>
<tr>
	<td valign="top" colspan="2" class="createdate">
		<?php echo $this->item->created; ?>
	</td>
</tr>
<?php endif; ?>

<?php if ($this->params->get('show_url') && $this->item->urls) : ?>
<tr>
	<td valign="top" colspan="2">
		<a href="http://<?php echo $this->item->urls ; ?>" target="_blank">
			<?php echo $this->item->urls; ?></a>
	</td>
</tr>
<?php endif; ?>

<tr>
<td valign="top" colspan="2">
<?php if (isset ($this->item->toc)) : ?>
	<?php echo $this->item->toc; ?>
<?php endif; ?>
<?php echo $this->item->text; ?>
</td>
</tr>

<?php if (!empty($this->item->modified) && $this->params->get('show_modify_date')) : ?>
<tr>
	<td colspan="2"  class="modifydate">
		<?php echo JText::_( 'Last Updated' ); ?> ( <?php echo $this->item->modified; ?> )
	</td>
</tr>
<?php endif; ?>

<?php if ($this->params->get('show_readmore') && $this->params->get('show_intro') && $this->item->readmore_text) : ?>
<tr>
	<td  colspan="2">
		<a href="<?php echo $this->item->readmore_link; ?>" class="readon<?php echo $this->params->get( 'pageclass_sfx' ); ?>">
			<?php echo $this->item->readmore_text; ?>
		</a>
	</td>
</tr>
<?php endif; ?>

</table>
<span class="article_separator">&nbsp;</span>
<?php echo $this->item->event->afterDisplayContent; ?>