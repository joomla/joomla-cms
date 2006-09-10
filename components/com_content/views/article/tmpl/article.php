<?php if ($user->authorize('action', 'edit', 'content', 'all')) : ?>
	<div class="contentpaneopen_edit<?php echo $params->get( 'pageclass_sfx' ); ?>" style="float: left;">
		<?php echo $this->getIcon('edit'); ?>
	</div>
<?php endif; ?>

<?php if ($params->get('item_title') || $params->get('pdf') || $params->get('print') || $params->get('email')) : ?>
<table class="contentpaneopen<?php echo $params->get( 'pageclass_sfx' ); ?>">
<tr>
	<?php if ($params->get('item_title')) : ?>
	<td class="contentheading<?php echo $params->get( 'pageclass_sfx' ); ?>" width="100%">
		<?php if ($params->get('link_titles') && $article->readmore_link != '') : ?>
		<a href="<?php echo $article->readmore_link; ?>" class="contentpagetitle<?php echo $params->get( 'pageclass_sfx' ); ?>">
			<?php echo $article->title; ?>
		</a>
		<?php else : ?>
			<?php echo $article->title; ?>
		<?php endif; ?>
	</td>
	<?php endif; ?>
	
	<?php if ($params->get('pdf')) : ?>
	<td align="right" width="100%" class="buttonheading">
	<?php echo $this->getIcon('pdf'); ?>
	</td>
	<?php endif; ?>
	
	<?php if ( $params->get( 'print' )) : ?>
	<td align="right" width="100%" class="buttonheading">
	<?php echo $this->getIcon('print'); ?>
	</td>
	<?php endif; ?>
	
	<?php if ($params->get('email')) : ?>
	<td align="right" width="100%" class="buttonheading">
	<?php echo $this->getIcon('email'); ?>
	</td>
	<?php endif; ?>
</tr>
</table>
<?php endif; ?>

<?php  if (!$params->get('intro_only')) :
	echo $article->event->afterDisplayTitle;
endif; ?>

<?php echo $article->event->beforeDisplayContent; ?>
<table class="contentpaneopen<?php echo $this->params->get( 'pageclass_sfx' ); ?>">	
<?php if (($params->get('section') && $article->sectionid) || ($this->params->get('category') && $article->catid)) : ?>
<tr>
	<td>
		<?php if ($params->get('section') && $article->sectionid) : ?>
		<span>
			<?php echo $article->section; ?>
			<?php if ($params->get('category')) : ?>
				<?php echo ' - '; ?>
			<?php endif; ?>
		</span>
		<?php endif; ?>

		<?php if ($params->get('category') && $article->catid) : ?>
		<span>
			<?php echo $article->category; ?>
		</span>
		<?php endif; ?>
	</td>
</tr>
<?php endif; ?>

<?php if (($params->get('author')) && ($article->author != "")) : ?>
<tr>
	<td width="70%"  valign="top" colspan="2">
		<span class="small">
			<?php JText::printf( 'Written by', ($article->created_by_alias ? $article->created_by_alias : $article->author) ); ?>
		</span>
		&nbsp;&nbsp;
	</td>
</tr>
<?php endif; ?>

<?php if ($params->get('createdate')) : ?>
<tr>
	<td valign="top" colspan="2" class="createdate">
		<?php echo $article->created; ?>
	</td>
</tr>
<?php endif; ?>

<?php if ($params->get('url') && $article->urls) : ?>
<tr>
	<td valign="top" colspan="2">
		<a href="http://<?php echo $article->urls ; ?>" target="_blank">
			<?php echo $article->urls; ?></a>
	</td>
</tr>
<?php endif; ?>

<tr>
<td valign="top" colspan="2">
<?php if (isset ($article->toc)) : ?>
	<?php echo $article->toc; ?>
<?php endif; ?>
<?php echo ampReplace($article->text); ?>
</td>
</tr>

<?php if (!empty($article->modified) && $params->get('modifydate')) : ?>
<tr>
	<td colspan="2"  class="modifydate">
		<?php echo JText::_( 'Last Updated' ); ?> ( <?php echo $article->modified; ?> )
	</td>
</tr>
<?php endif; ?>

<?php if ($params->get('readmore') && $params->get('intro_only') && $article->readmore_text) : ?>
<tr>
	<td  colspan="2">
		<a href="<?php echo $article->readmore_link; ?>" class="readon<?php echo $params->get( 'pageclass_sfx' ); ?>">
			<?php echo $article->readmore_text; ?>
		</a>
	</td>
</tr>
<?php endif; ?>

</table>
<span class="article_seperator">&nbsp;</span>
<?php echo $article->event->afterDisplayContent; ?>