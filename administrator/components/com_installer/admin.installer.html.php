<?php
/**
* @version $Id$
* @package Joomla
* @subpackage Installer
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_VALID_MOS' ) or die( 'Restricted access' );

function writableCell( $folder ) {
	global $_LANG;
	echo '<tr>';
	echo '<td class="item">' . $folder . '/</td>';
	echo '<td >';
	echo is_writable( $GLOBALS['mosConfig_absolute_path'] . '/' . $folder ) ? '<b><font color="green">'. $_LANG->_( 'Writeable' ) .'</font></b>' : '<b><font color="red">'. $_LANG->_( 'Unwriteable' ) .'</font></b>';
	echo '</td></tr>';
}

/**
* @package Joomla
*/
class HTML_installer {

	function showInstallForm( $title, $option, $element, $client = "", $p_startdir = "", $backLink="" ) {
    	global $_LANG;
		?>
		<script language="javascript" type="text/javascript">
		function submitbutton3(pressbutton) {
			var form = document.adminForm_dir;

			// do field validation
			if (form.userfile.value == ""){
				alert( "<?php echo $_LANG->_( 'Please select a directory' ); ?>" );
			} else {
				form.submit();
			}
		}
		</script>
		<form enctype="multipart/form-data" action="index2.php" method="post" name="filename">
		<table class="adminheading">
		<tr>
			<th class="install">
			<?php echo $title;?>
			</th>
			<td align="right" nowrap="true">
			<?php echo $backLink;?>
			</td>
		</tr>
		</table>

		<table class="adminform">
		<tr>
			<th>
			<?php echo $_LANG->_( 'Upload Package File' ); ?>
			</th>
		</tr>
		<tr>
			<td >
			<?php echo $_LANG->_( 'Package File' ); ?>:
			<input class="text_area" name="userfile" type="file" size="70"/>
			<input class="button" type="submit" value="<?php echo $_LANG->_( 'Upload File' ); ?> &amp; <?php echo $_LANG->_( 'Install' ); ?>" />
			</td>
		</tr>
		</table>

		<input type="hidden" name="task" value="uploadfile"/>
		<input type="hidden" name="option" value="<?php echo $option;?>"/>
		<input type="hidden" name="element" value="<?php echo $element;?>"/>
		<input type="hidden" name="client" value="<?php echo $client;?>"/>
		</form>
		<br />

		<form enctype="multipart/form-data" action="index2.php" method="post" name="adminForm_dir">
		<table class="adminform">
		<tr>
			<th>
			<?php echo $_LANG->_( 'Install from directory' ); ?>
			</th>
		</tr>
		<tr>
			<td >
			<?php echo $_LANG->_( 'Install directory' ); ?>:&nbsp;
			<input type="text" name="userfile" class="text_area" size="65" value="<?php echo $p_startdir; ?>"/>&nbsp;
			<input type="button" class="button" value="<?php echo $_LANG->_( 'Install' ); ?>" onclick="submitbutton3()" />
			</td>
		</tr>
		</table>

		<input type="hidden" name="task" value="installfromdir" />
		<input type="hidden" name="option" value="<?php echo $option;?>"/>
		<input type="hidden" name="element" value="<?php echo $element;?>"/>
		<input type="hidden" name="client" value="<?php echo $client;?>"/>
		</form>

                <form enctype="multipart/form-data" action="index2.php" method="post" name="adminForm_url">
                <table class="adminform">
                <tr>
                        <th>
                        <?php echo $_LANG->_( 'Install from URL' ); ?>
                        </th>
                </tr>
                <tr>
                        <td >
                        <?php echo $_LANG->_( 'Install URL' ); ?>:&nbsp;
                        <input type="text" name="userfile" class="text_area" size="65" value="http://"/>&nbsp;
                        <input type="button" class="button" value="<?php echo $_LANG->_( 'Install' ); ?>" onclick="submitbutton3()" />
                        </td>
                </tr>
                </table>

                <input type="hidden" name="task" value="installfromurl" />
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
			<td >
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
