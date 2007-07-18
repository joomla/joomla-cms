<?php
/**
 * @version		$Id$
 */
?>
<fieldset class="adminform">
	<legend><?php echo JText::_( 'Directory Permissions' ); ?></legend>
		<table class="adminlist">
		<thead>
			<tr>
				<th width="650">
					<?php echo JText::_( 'Directory' ); ?>
				</th>
				<th>
					<?php echo JText::_( 'Status' ); ?>
				</th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<td colspan="2">
					&nbsp;
				</td>
			</tr>
		</tfoot>
		<tbody>
			<?php
			writableCell( 'administrator/backups' );
			writableCell( 'administrator/components' );
			writableCell( 'administrator/modules' );
			writableCell( 'administrator/templates' );
			writableCell( 'components' );
			writableCell( 'images' );
			writableCell( 'images/banners' );
			writableCell( 'images/stories' );
			writableCell( 'language' );
			writableCell( 'modules' );
			writableCell( 'plugins' );
			writableCell( 'plugins/content' );
			writableCell( 'plugins/editors' );
			writableCell( 'plugins/editors-xtd' );
			writableCell( 'plugins/search' );
			writableCell( 'plugins/system' );
			writableCell( 'plugins/user' );
			writableCell( 'plugins/xmlrpc' );
			writableCell( 'tmp' );
			writableCell( 'templates' );
			writableCell( JPATH_SITE.DS.'cache', 0, '<strong>'. JText::_( 'Cache Directory' ) .'</strong> ' );
			writableCell( JPATH_ADMINISTRATOR.DS.'cache', 0, '<strong>'. JText::_( 'Cache Directory' ) .'</strong> ' );
			?>
		</tbody>
		</table>
</fieldset>