<form action="index.php" method="post" name="login" id="login">
<?php if ( $params->get( 'page_title' ) ) : ?>
<div class="componentheading<?php echo $params->get( 'pageclass_sfx' ); ?>">
	<?php echo $params->get( 'header_logout' ); ?>
</div>
<?php endif; ?>
<table border="0" align="center" cellpadding="4" cellspacing="0" class="contentpane<?php echo $params->get( 'pageclass_sfx' ); ?>" width="100%">
<tr>
	<td valign="top">
		<div>
		<?php echo $image; ?>
		<? if ( $params->get( 'description_logout' ) ) : 
			echo $params->get( 'description_logout_text' );
		endif; ?>
		</div>
	</td>
</tr>
<tr>
	<td align="center">
		<div align="center">
			<input type="submit" name="Submit" class="button" value="<?php echo JText::_( 'Logout' ); ?>" />
		</div>
	</td>
</tr>
</table>

<br/><br/>

<input type="hidden" name="option" value="com_login" />
<input type="hidden" name="task" value="logout" />
<input type="hidden" name="return" value="<?php echo sefRelToAbs( $return ); ?>" />
</form>