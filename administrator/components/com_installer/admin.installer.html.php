<?php
/**
* @version $Id: admin.installer.html.php 137 2005-09-12 10:21:17Z eddieajau $
* @package Joomla
* @subpackage Installer
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software and parts of it may contain or be derived from the
* GNU General Public License or other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_VALID_MOS' ) or die( 'Restricted access' );

function writableCell( $folder, $layout=1 ) {
	global $_LANG;

	if ( is_writable( $GLOBALS['mosConfig_absolute_path'] . '/' . $folder ) ) {
		$value = '<b><font color="green">'. $_LANG->_( 'Writeable' ) .'</font></b>';
	} else {
		$value = '<b><font color="red">'. $_LANG->_( 'Unwriteable' ) .'</font></b>';
	}

	if ( $layout ) {
		?>
	    <tr>
			<td class="item">
			<?php echo $folder; ?>
			</td>
			<td align="left">
			<?php echo $value; ?>
			</td>
		</tr>
		<?php
	} else {
		return $value;
	}
}

/**
* @package Joomla
*/
class HTML_installer {

	function showInstallForm( $title, $option, $element, $client='', $p_startdir='', $backLink='' ) {
    	global $_LANG;
		?>
		<script language="javascript" type="text/javascript">
		function submit_package() {
			var form = document.filename;

			// do field validation
			if ( form.userfile.value == '' ){
				alert( "<?php echo $_LANG->_( 'Please select a Package File' ); ?>" );
				return;
			} else {
				form.submit();
			}
		}

		function submit_directory() {
			var form = document.adminForm_dir;

			// do field validation
			if ( form.userfile.value == '' ){
				alert( "<?php echo $_LANG->_( 'Please select a Directory' ); ?>" );
				return;
			} else {
				form.submit();
			}
		}
		</script>
		<form enctype="multipart/form-data" action="index2.php" method="post" name="filename">

		<fieldset>
			<legend>
			<?php echo $_LANG->_( 'Upload Package File' );?>
			</legend>

			<table>
			<tr>
				<td align="left">
				<?php echo $_LANG->_( 'Package File' ); ?>:
				<input class="text_area" name="userfile" type="file" size="80"/>
				</td>
			</tr>
			<tr>
				<td>
					<input type="checkbox" name="overwrite" value="1" />
					<?php echo $_LANG->_( 'Overwrite existing files' ); ?>
	 			</td>
			</tr>
			<tr>
				<td>
					<input type="checkbox" name="backup" value="1" />
					<?php echo $_LANG->_( 'Backup existing files' ); ?>
	 			</td>
			</tr>
			<tr>
				<td>
					<?php echo $_LANG->_( 'Backup suffix' ); ?>
					<input type="text" name="backup_suffix" value="bak" class="inputbox" />
	 			</td>
			</tr>
			<tr>
				<td align="left">
				<input class="button" type="button" value="<?php echo $_LANG->_( 'Upload' ); ?>" onclick="submit_package()"/>
				</td>
			</tr>
			</table>

			<input type="hidden" name="task" value="uploadfile"/>
			<input type="hidden" name="option" value="<?php echo $option;?>"/>
			<input type="hidden" name="element" value="<?php echo $element;?>"/>
			<input type="hidden" name="client" value="<?php echo $client;?>"/>
			</form>
		</fieldset>

		<fieldset>
			<legend>
			<?php echo $_LANG->_( 'Install from directory' );?>
			</legend>

			<form enctype="multipart/form-data" action="index2.php" method="post" name="adminForm_dir">
			<table>
			<tr>
				<td align="left">
				<?php echo $_LANG->_( 'Install directory' ); ?>:&nbsp;
				<input type="text" name="userfile" class="text_area" size="80" value="<?php echo $p_startdir; ?>"/>&nbsp;
				<br/>
				<input type="button" class="button" value="<?php echo $_LANG->_( 'Install' ); ?>" onclick="submit_directory()" />
				</td>
			</tr>
			</table>
		</fieldset>

		<input type="hidden" name="task" value="installfromdir" />
		<input type="hidden" name="option" value="<?php echo $option;?>"/>
		<input type="hidden" name="element" value="<?php echo $element;?>"/>
		<input type="hidden" name="client" value="<?php echo $client;?>"/>
		</form>
		<?php
	}

	/**
	* @param string
	* @param string
	* @param string
	* @param string
	*/
	function showInstallMessage( $message, $title, $url ) {
		global $PHP_SELF;
    	global $_LANG;
		?>
		<table class="adminheading">
		<tr>
			<th class="install">
			<?php echo $title; ?>
			</th>
		</tr>
		</table>

		<table class="adminform">
		<tr>
			<td align="left">
			<strong><?php echo $message; ?></strong>
			</td>
		</tr>
		<tr>
			<td colspan="2" align="center">
			[&nbsp;<a href="<?php echo $url;?>" style="font-size: 16px; font-weight: bold"><?php echo $_LANG->_( 'Continue ...' ); ?></a>&nbsp;]
			</td>
		</tr>
		</table>
		<?php
	}
}
?>