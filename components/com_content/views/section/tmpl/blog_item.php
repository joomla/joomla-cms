<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php if ($this->user->authorize('action', 'edit', 'content', 'all')) : ?>
	<div class="contentpaneopen_edit<?php echo $this->params->get( 'pageclass_sfx' ); ?>" style="float: left;">
		<?php echo $this->getIcon('edit'); ?>
	</div>
<?php endif; ?>

<?php if ($this->params->get('item_title') || $this->params->get('pdf') || $this->params->get('print') || $this->params->get('email')) : ?>
<table class="contentpaneopen<?php echo $this->params->get( 'pageclass_sfx' ); ?>">
<tr>
	<?php if ($this->params->get('item_title')) : ?>
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
</tr>
</table>
<?php endif; ?>
<?php  if (!$this->params->get('intro_only')) :
	echo $this->item->event->afterDisplayTitle;
endif; ?>
<?php echo $this->item->event->beforeDisplayContent; ?>
<table class="contentpaneopen<?php echo $this->params->get( 'pageclass_sfx' ); ?>">
<?php if (($this->params->get('section') && $this->item->sectionid) || ($this->params->get('category') && $this->item->catid)) : ?>
<tr>
	<td>
		<?php if ($this->params->get('section') && $this->item->sectionid) : ?>
		<span>
			<?php echo $this->item->section; ?>
			<?php if ($this->params->get('category')) : ?>
				<?php echo ' - '; ?>
			<?php endif; ?>
		</span>
		<?php endif; ?>

		<?php if ($this->params->get('category') && $this->item->catid) : ?>
		<span>
			<?php echo $this->item->category; ?>
		</span>
		<?php endif; ?>
	</td>
</tr>
<?php endif; ?>

<?php if (($this->params->get('showAuthor')) && ($this->item->author != "")) : ?>
<tr>
	<td width="70%"  valign="top" colspan="2">
		<span class="small">
			<?php JText::printf( 'Written by', ($this->item->created_by_alias ? $this->item->created_by_alias : $this->item->author) ); ?>
		</span>
		&nbsp;&nbsp;
	</td>
</tr>
<?php endif; ?>

<?php if ($this->params->get('createdate')) : ?>
<tr>
	<td valign="top" colspan="2" class="createdate">
		<?php echo $this->item->created; ?>
	</td>
</tr>
<?php endif; ?>

<?php if ($this->params->get('url') && $this->item->urls) : ?>
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

<?php if (!empty($this->item->modified) && $this->params->get('modifydate')) : ?>
<tr>
	<td colspan="2"  class="modifydate">
		<?php echo JText::_( 'Last Updated' ); ?> ( <?php echo $this->item->modified; ?> )
	</td>
</tr>
<?php endif; ?>

<?php if ($this->params->get('readmore') && $this->params->get('intro_only') && $this->item->readmore_text) : ?>
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