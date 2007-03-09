<?php // no direct access
defined('_JEXEC') or die('Restricted access'); ?>
<?php if ($this->user->authorize('action', 'edit', 'content', 'all') && !$this->print) : ?>
	<div class="contentpaneopen_edit<?php echo $this->params->get( 'pageclass_sfx' ); ?>" style="float: left;">
		<?php echo $this->getIcon('edit'); ?>
	</div>
<?php endif; ?>

<?php if ($this->params->get('item_title') || $this->params->get('pdf') || $this->params->get('print') || $this->params->get('email')) : ?>
<table class="contentpaneopen<?php echo $this->params->get( 'pageclass_sfx' ); ?>">
<tr>
	<?php if ($this->params->get('item_title')) : ?>
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
	<?php if ($this->params->get('pdf')) : ?>
	<td align="right" width="100%" class="buttonheading">
	<?php echo $this->getIcon('pdf'); ?>
	</td>
	<?php endif; ?>

	<?php if ( $this->params->get( 'print' )) : ?>
	<td align="right" width="100%" class="buttonheading">
	<?php echo $this->getIcon('print'); ?>
	</td>
	<?php endif; ?>

	<?php if ($this->params->get('email')) : ?>
	<td align="right" width="100%" class="buttonheading">
	<?php echo $this->getIcon('email'); ?>
	</td>
	<?php endif; ?>
	<?php else : ?>
	<td align="right" width="100%" class="buttonheading">
	<?php echo $this->getIcon('print_screen'); ?>
	</td>
	<?php endif; ?>
</tr>
</table>
<?php endif; ?>

<?php  if (!$this->params->get('intro_only')) :
	echo $this->article->event->afterDisplayTitle;
endif; ?>

<?php echo $this->article->event->beforeDisplayContent; ?>
<table class="contentpaneopen<?php echo $this->params->get( 'pageclass_sfx' ); ?>">
<?php if (($this->params->get('section') && $this->article->sectionid) || ($this->params->get('category') && $this->article->catid)) : ?>
<tr>
	<td>
		<?php if ($this->params->get('section') && $this->article->sectionid) : ?>
		<span>
			<?php echo $this->article->section; ?>
			<?php if ($this->params->get('category')) : ?>
				<?php echo ' - '; ?>
			<?php endif; ?>
		</span>
		<?php endif; ?>

		<?php if ($this->params->get('category') && $this->article->catid) : ?>
		<span>
			<?php echo $this->article->category; ?>
		</span>
		<?php endif; ?>
	</td>
</tr>
<?php endif; ?>

<?php if (($this->params->get('showAuthor')) && ($this->article->author != "")) : ?>
<tr>
	<td width="70%"  valign="top" colspan="2">
		<span class="small">
			<?php JText::printf( 'Written by', ($this->article->created_by_alias ? $this->article->created_by_alias : $this->article->author) ); ?>
		</span>
		&nbsp;&nbsp;
	</td>
</tr>
<?php endif; ?>

<?php if ($this->params->get('createdate')) : ?>
<tr>
	<td valign="top" colspan="2" class="createdate">
		<?php echo $this->article->created; ?>
	</td>
</tr>
<?php endif; ?>

<?php if ($this->params->get('url') && $this->article->urls) : ?>
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

<?php if ( $this->article->mod_date !='' && $this->params->get('modifydate')) : ?>
<tr>
	<td colspan="2"  class="modifydate">
		<?php echo JText::_( 'Last Updated' ); ?> ( <?php echo $this->article->modified; ?> )
	</td>
</tr>
<?php endif; ?>

<?php if ($this->params->get('readmore') && $this->params->get('intro_only') && $this->article->readmore_text) : ?>
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