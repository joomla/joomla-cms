<?php
/**
 * @version		$Id$
 */
// No direct access
defined('_JEXEC') or die('Restricted access');

?>
<fieldset class="adminform">
	<legend><?php echo JText::_( 'Relevant PHP Settings' ); ?></legend>
		<table class="adminlist">
		<thead>
			<tr>
				<th width="250">
					<?php echo JText::_( 'Setting' ); ?>
				</th>
				<th>
					<?php echo JText::_( 'Value' ); ?>
				</th>
			</tr>
		</thead>
		<tfoot>
		<tr>
			<th colspan="2">&nbsp;
			</th>
		</tr>
		</tfoot>
		<tbody>
		<tr>
			<td>
				<?php echo JText::_( 'Safe Mode' ); ?>:
			</td>
			<td>
				<?php echo HTML_admin_misc::get_php_setting('safe_mode'); ?>
			</td>
		</tr>
		<tr>
			<td>
				<?php echo JText::_( 'Open basedir' ); ?>:
			</td>
			<td>
				<?php echo (($ob = ini_get('open_basedir')) ? $ob : JText::_( 'none' ) ); ?>
			</td>
		</tr>
		<tr>
			<td>
				<?php echo JText::_( 'Display Errors' ); ?>:
			</td>
			<td>
				<?php echo HTML_admin_misc::get_php_setting('display_errors'); ?>
			</td>
		</tr>
		<tr>
			<td>
				<?php echo JText::_( 'Short Open Tags' ); ?>:
			</td>
			<td>
				<?php echo HTML_admin_misc::get_php_setting('short_open_tag'); ?>
			</td>
		</tr>
		<tr>
			<td>
				<?php echo JText::_( 'File Uploads' ); ?>:
			</td>
			<td>
				<?php echo HTML_admin_misc::get_php_setting('file_uploads'); ?>
			</td>
		</tr>
		<tr>
			<td>
				<?php echo JText::_( 'Magic Quotes' ); ?>:
			</td>
			<td>
				<?php echo HTML_admin_misc::get_php_setting('magic_quotes_gpc'); ?>
			</td>
		</tr>
		<tr>
			<td>
				<?php echo JText::_( 'Register Globals' ); ?>:
			</td>
			<td>
				<?php echo HTML_admin_misc::get_php_setting('register_globals'); ?>
			</td>
		</tr>
		<tr>
			<td>
				<?php echo JText::_( 'Output Buffering' ); ?>:
			</td>
			<td>
				<?php echo HTML_admin_misc::get_php_setting('output_buffering'); ?>
			</td>
		</tr>
		<tr>
			<td>
				<?php echo JText::_( 'Session Save Path' ); ?>:
			</td>
			<td>
				<?php echo (($sp=ini_get('session.save_path')) ? $sp : JText::_( 'none' ) ); ?>
			</td>
		</tr>
		<tr>
			<td>
				<?php echo JText::_( 'Session Auto Start' ); ?>:
			</td>
			<td>
				<?php echo intval( ini_get( 'session.auto_start' ) ); ?>
			</td>
		</tr>
		<tr>
			<td>
				<?php echo JText::_( 'XML Enabled' ); ?>:
			</td>
			<td>
			<?php echo extension_loaded('xml') ? JText::_( 'Yes' ) : JText::_( 'No' ); ?>
			</td>
		</tr>
		<tr>
			<td>
				<?php echo JText::_( 'Zlib Enabled' ); ?>:
			</td>
			<td>
				<?php echo extension_loaded('zlib') ? JText::_( 'Yes' ) : JText::_( 'No' ); ?>
			</td>
		</tr>
		<tr>
			<td>
				<?php echo JText::_( 'Disabled Functions' ); ?>:
			</td>
			<td>
				<?php echo (($df=ini_get('disable_functions')) ? $df : JText::_( 'none' ) ); ?>
			</td>
		</tr>
		<tr>
			<td>
				<?php echo JText::_( 'Mbstring Enabled' ); ?>:
			</td>
			<td>
				<?php echo extension_loaded('mbstring') ? JText::_( 'Yes' ) : JText::_( 'No' ); ?>
			</td>
		</tr>
		<tr>
			<td>
				<?php echo JText::_( 'Iconv Available' ); ?>:
			</td>
			<td>
				<?php echo function_exists('iconv') ? JText::_( 'Yes' ) : JText::_( 'No' ); ?>
			</td>
		</tr>
		<?php
		$query = 'SELECT name FROM #__plugins'
		. ' WHERE folder="editors" AND published="1"';
		$db->setQuery( $query, 0, 1 );
		$editor = $db->loadResult();
		?>
		<tr>
			<td>
				<?php echo JText::_( 'WYSIWYG Editor' ); ?>:
			</td>
			<td>
				<?php echo $editor; ?>
			</td>
		</tr>
		</tbody>
		</table>
</fieldset>
