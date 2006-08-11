<?php if ($access->canEdit) : ?>
	<div class="contentpaneopen_edit<?php echo $params->get( 'pageclass_sfx' ); ?>" style="float: left;">
		<?php JContentHTMLHelper::editIcon($row, $params, $access); ?>
	</div>
<?php endif; ?>

<?php if ($params->get('item_title') || $params->get('pdf') || $params->get('print') || $params->get('email')) : ?>
<table class="contentpaneopen<?php echo $params->get( 'pageclass_sfx' ); ?>">
<tr>
	<?php
	// displays Item Title
	JContentHTMLHelper::title($row, $params, $linkOn, $access);

	// displays PDF Icon
	JContentHTMLHelper::pdfIcon($row, $params, $linkOn, $hide_js);

	// displays Print Icon
	mosHTML::PrintIcon($row, $params, $hide_js, $print_link);

	// displays Email Icon
	JContentHTMLHelper::emailIcon($row, $params, $hide_js);
	?>
</tr>
</table>
<?php endif; ?>
<?php  if (!$params->get('intro_only')) :
	echo $row->afterDisplayTitle;   
endif; ?>
<?php echo $row->beforeDisplayContent; ?>		
<table class="contentpaneopen<?php echo $params->get( 'pageclass_sfx' ); ?>">
<?php
	// displays Section & Category
	JContentHTMLHelper::sectionCategory($row, $params);

	// displays Author Name
	JContentHTMLHelper::author($row, $params);

	// displays Created Date
	JContentHTMLHelper::createDate($row, $params);

	// displays Urls
	JContentHTMLHelper::url($row, $params);
?>
<tr>
	<td valign="top" colspan="2">
	<?php
	
	// displays Table of Contents
	JContentHTMLHelper::toc($row);

	// displays Item Text
	echo $row->text;
	?>
	</td>
</tr>
<?php
	// displays Modified Date
	JContentHTMLHelper::modifiedDate($row, $params);

	// displays Readmore button
	JContentHTMLHelper::readMore($params, $linkOn, $linkText);
?>
</table>
<span class="article_seperator">&nbsp;</span>
<?php echo $row->afterDisplayContent; ?>