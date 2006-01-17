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
defined( '_JEXEC' ) or die( 'Restricted access' );


/**
 * @package Joomla
 */
class JInstallerScreens {

	function showInstallForm( $p_startdir = "", $backLink="" ) {
		?>
		<script language="javascript" type="text/javascript">
		function submitbutton3(pressbutton) {
			var form = document.adminForm_dir;

			// do field validation
			if (form.userfile.value == ""){
				alert( "<?php echo JText::_( 'Please select a directory', true ); ?>" );
			} else {
				form.submit();
			}
		}

		function submitbutton4(pressbutton) {
			var form = document.webinstall;

			// do field validation
			if (form.userfile.value == ""){
				alert( "<?php echo JText::_( 'Please enter a URL', true ); ?>" );
			} else {
				form.submit();
			}
		}

		</script>
		<div id="treecell">
			<fieldset title="JEM Navigation">
			<legend>
				JEM Navigation
			</legend>
			<p>Extensions</p>
			<p>&nbsp;&nbsp;&nbsp;<a href="" >Components</a></p>
			<p>&nbsp;&nbsp;&nbsp;<a href="" >Modules</a><br />
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="" >Administrator</a><br />
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="" >Site</a></p>
			<p>&nbsp;&nbsp;&nbsp;<a href="" >Plugins</a><br />
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="" >Site</a></p>
			<p>&nbsp;&nbsp;&nbsp;<a href="" >Languages</a><br />
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="" >Administrator</a><br />
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="" >Site</a></p>
			<p>&nbsp;&nbsp;&nbsp;<a href="" >Templates</a><br />
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="" >Administrator</a><br />
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="" >Site</a></p>
			<p>&nbsp;&nbsp;&nbsp;<a href="" >Installer</a></p>
			<p>&nbsp;</p>
			</fieldset>
		</div>
		<div id="datacell">
			<fieldset>
				<legend>
					<?php echo JText::_('Install Form'); ?>
				</legend>
				<form enctype="multipart/form-data" action="index2.php" method="post" name="filename">
				<table class="adminform">
				<tr>
					<th>
					<?php echo JText::_( 'Upload Package File' ); ?>
					</th>
				</tr>
				<tr>
					<td >
					<?php echo JText::_( 'Package File' ); ?>:
					<input class="text_area" name="userfile" type="file" size="70"/>
					<input class="button" type="submit" value="<?php echo JText::_( 'Upload File' ); ?> &amp; <?php echo JText::_( 'Install' ); ?>" />
					</td>
				</tr>
				</table>
		
				<input type="hidden" name="task" value="uploadpackage"/>
				<input type="hidden" name="option" value="com_installer"/>
				</form>
				<br />
		
				<form enctype="multipart/form-data" action="index2.php" method="post" name="adminForm_dir">
				<table class="adminform">
				<tr>
					<th>
					<?php echo JText::_( 'Install from directory' ); ?>
					</th>
				</tr>
				<tr>
					<td >
					<?php echo JText::_( 'Install directory' ); ?>:&nbsp;
					<input type="text" name="userfile" class="text_area" size="65" value="<?php echo $p_startdir; ?>"/>&nbsp;
					<input type="button" class="button" value="<?php echo JText::_( 'Install' ); ?>" onclick="submitbutton3()" />
					</td>
				</tr>
				</table>
		
				<input type="hidden" name="task" value="installfromdir" />
				<input type="hidden" name="option" value="com_installer"/>
				</form>
				<br />
		
		                <form enctype="multipart/form-data" action="index2.php" method="post" name="webinstall">
		                <table class="adminform">
		                <tr>
		                        <th>
		                        <?php echo JText::_( 'Install from URL' ); ?>
		                        </th>
		                </tr>
		                <tr>
		                        <td >
		                        <?php echo JText::_( 'Install URL' ); ?>:&nbsp;
		                        <input type="text" name="userfile" class="text_area" size="65" value="http://"/>&nbsp;
		                        <input type="button" class="button" value="<?php echo JText::_( 'Install' ); ?>" onclick="submitbutton4()" />
		                        </td>
		                </tr>
		                </table>
		
		                <input type="hidden" name="task" value="installfromurl" />
		                <input type="hidden" name="option" value="com_installer"/>
		                </form>
					</fieldset>
				</div>
		<?php
	}

	/**
	 * Display an installer message
	 * 
	 * @static
	 * @param string $title
	 * @param string $message
	 * @param string $scriptOutput
	 * @return void
	 * @since 1.0
	 */
	function showInstallMessage( $title, $message, $scriptOutput ) {
		?>
		<table>
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
			<td>
				<?php echo $scriptOutput; ?>
			</td>
		</tr>
		</table>
		<?php
	}

	/**
	 * Print a writable cell
	 * 
	 * @param string $folder
	 * @return string
	 */
	function writableCell( $folder ) {
	
		$txt .= '<tr>';
		$txt .= '<td class="item">' . $folder . '/</td>';
		$txt .= '<td >';
		$txt .= is_writable( JPATH_SITE . '/' . $folder ) ? '<b><font color="green">'. JText::_( 'Writeable' ) .'</font></b>' : '<b><font color="red">'. JText::_( 'Unwriteable' ) .'</font></b>';
		$txt .= '</td></tr>';
	
		return $txt;
	}

}
?>
