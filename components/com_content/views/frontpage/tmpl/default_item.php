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
		<?php if ($params->get('link_titles') && $this->item->readmore_link != '') : ?>
		<a href="<?php echo $item->readmore_link; ?>" class="contentpagetitle<?php echo $params->get( 'pageclass_sfx' ); ?>">
			<?php echo $item->title; ?>
		</a>
		<?php else : ?>
			<?php echo $item->title; ?>
		<?php endif; ?>
	</td>
	<?php endif; ?>
	
	<?php if ($params->get('pdf')) : ?>
	<td align="right" width="100%" class="buttonheading">
	<?php echo $this->icon('pdf'); ?>
	</td>
	<?php endif; ?>
	
	<?php if ( $params->get( 'print' )) : ?>
	<td align="right" width="100%" class="buttonheading">
	<?php echo $this->icon('print'); ?>
	</td>
	<?php endif; ?>
	
	<?php if ($params->get('email')) : ?>
	<td align="right" width="100%" class="buttonheading">
	<?php echo $this->icon('email'); ?>
	</td>
	<?php endif; ?>
</tr>
</table>
<?php endif; ?>
<?php  if (!$params->get('intro_only')) :
	echo $item->event->afterDisplayTitle;
endif; ?>
<?php echo $item->event->beforeDisplayContent; ?>
<table class="contentpaneopen<?php echo $params->get( 'pageclass_sfx' ); ?>">
<?php if (($params->get('section') && $item->sectionid) || ($params->get('category') && $item->catid)) : ?>
<tr>
	<td>
		<?php if ($params->get('section') && $item->sectionid) : ?>
		<span>
			<?php echo $item->section; ?>
			<?php if ($params->get('category')) : ?>
				<?php echo ' - '; ?>
			<?php endif; ?>
		</span>
		<?php endif; ?>

		<?php if ($params->get('category') && $item->catid) : ?>
		<span>
			<?php echo $item->category; ?>
		</span>
		<?php endif; ?>
	</td>
</tr>
<?php endif; ?>

<?php if (($params->get('author')) && ($item->author != "")) : ?>
<tr>
	<td width="70%"  valign="top" colspan="2">
		<span class="small">
			<?php JText::printf( 'Written by', ($item->created_by_alias ? $item->created_by_alias : $item->author) ); ?>
		</span>
		&nbsp;&nbsp;
	</td>
</tr>
<?php endif; ?>

<?php if ($params->get('createdate')) : ?>
<tr>
	<td valign="top" colspan="2" class="createdate">
		<?php echo $item->created; ?>
	</td>
</tr>
<?php endif; ?>

<?php if ($params->get('url') && $item->urls) : ?>
<tr>
	<td valign="top" colspan="2">
		<a href="http://<?php echo $item->urls ; ?>" target="_blank">
			<?php echo $item->urls; ?></a>
	</td>
</tr>
<?php endif; ?>

<tr>
<td valign="top" colspan="2">
<?php if (isset ($item->toc)) : ?>
	<?php echo $item->toc; ?>
<?php endif; ?>
<?php echo ampReplace($item->text); ?>
</td>
</tr>

<?php if (!empty($item->modified) && $params->get('modifydate')) : ?>
<tr>
	<td colspan="2"  class="modifydate">
		<?php echo JText::_( 'Last Updated' ); ?> ( <?php echo $item->modified; ?> )
	</td>
</tr>
<?php endif; ?>

<?php if ($params->get('readmore') && $params->get('intro_only') && $item->readmore_text) : ?>
<tr>
	<td  colspan="2">
		<a href="<?php echo $item->readmore_link; ?>" class="readon<?php echo $params->get( 'pageclass_sfx' ); ?>">
			<?php echo $item->readmore_text; ?>
		</a>
	</td>
</tr>
<?php endif; ?>

</table>
<span class="article_seperator">&nbsp;</span>
<?php echo $item->event->afterDisplayContent; ?>