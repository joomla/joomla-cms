<?php
/**
* @version $Id$
* @package Joomla
* @subpackage Config
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

/**
* @package Joomla
* @subpackage Config
*/
class HTML_config {

	function showconfig( &$row, &$lists, $option) {
		global $mosConfig_absolute_path, $mosConfig_live_site;
		$tabs = new mosTabs(1);
		?>
		<script type="text/javascript">
		<!--
			function saveFilePerms()
			{
				var f = document.adminForm;
				if (f.filePermsMode0.checked)
					f.config_fileperms.value = '';
				else {
					var perms = 0;
					if (f.filePermsUserRead.checked) perms += 400;
					if (f.filePermsUserWrite.checked) perms += 200;
					if (f.filePermsUserExecute.checked) perms += 100;
					if (f.filePermsGroupRead.checked) perms += 40;
					if (f.filePermsGroupWrite.checked) perms += 20;
					if (f.filePermsGroupExecute.checked) perms += 10;
					if (f.filePermsWorldRead.checked) perms += 4;
					if (f.filePermsWorldWrite.checked) perms += 2;
					if (f.filePermsWorldExecute.checked) perms += 1;
					f.config_fileperms.value = '0'+''+perms;
				}
			}
			function changeFilePermsMode(mode)
			{
				if(document.getElementById) {
					switch (mode) {
						case 0:
							document.getElementById('filePermsValue').style.display = 'none';
							document.getElementById('filePermsTooltip').style.display = '';
							document.getElementById('filePermsFlags').style.display = 'none';
							break;
						default:
							document.getElementById('filePermsValue').style.display = '';
							document.getElementById('filePermsTooltip').style.display = 'none';
							document.getElementById('filePermsFlags').style.display = '';
					} // switch
				} // if
				saveFilePerms();
			}
			function saveDirPerms()
			{
				var f = document.adminForm;
				if (f.dirPermsMode0.checked)
					f.config_dirperms.value = '';
				else {
					var perms = 0;
					if (f.dirPermsUserRead.checked) perms += 400;
					if (f.dirPermsUserWrite.checked) perms += 200;
					if (f.dirPermsUserSearch.checked) perms += 100;
					if (f.dirPermsGroupRead.checked) perms += 40;
					if (f.dirPermsGroupWrite.checked) perms += 20;
					if (f.dirPermsGroupSearch.checked) perms += 10;
					if (f.dirPermsWorldRead.checked) perms += 4;
					if (f.dirPermsWorldWrite.checked) perms += 2;
					if (f.dirPermsWorldSearch.checked) perms += 1;
					f.config_dirperms.value = '0'+''+perms;
				}
			}
			function changeDirPermsMode(mode)
			{
				if(document.getElementById) {
					switch (mode) {
						case 0:
							document.getElementById('dirPermsValue').style.display = 'none';
							document.getElementById('dirPermsTooltip').style.display = '';
							document.getElementById('dirPermsFlags').style.display = 'none';
							break;
						default:
							document.getElementById('dirPermsValue').style.display = '';
							document.getElementById('dirPermsTooltip').style.display = 'none';
							document.getElementById('dirPermsFlags').style.display = '';
					} // switch
				} // if
				saveDirPerms();
			}
		//-->
		</script>
		<form action="index2.php" method="post" name="adminForm">
		<div id="overDiv" style="position:absolute; visibility:hidden; z-index:10000;"></div>
		<table cellpadding="1" cellspacing="1" border="0" width="100%">
		<tr>
			<td width="250"><table class="adminheading"><tr><th nowrap class="config">Global Configuration</th></tr></table></td>
			<td width="270">
				<span class="componentheading">configuration.php is :
				<?php echo is_writable( '../configuration.php' ) ? '<b><font color="green"> Writeable</font></b>' : '<b><font color="red"> Unwriteable</font></b>' ?>
				</span>
			</td>
			<?php
			if (mosIsChmodable('../configuration.php')) {
				if (is_writable('../configuration.php')) {
					?>
					<td>
						<input type="checkbox" id="disable_write" name="disable_write" value="1"/>
						<label for="disable_write">Make unwriteable after saving</label>
					</td>
					<?php
				} else {
					?>
					<td>
						<input type="checkbox" id="enable_write" name="enable_write" value="1"/>
						<label for="enable_write">Override write protection while saving</label>
					</td>
				<?php
				} // if
			} // if
			?>
		</tr>
		</table>
			<?php
		$tabs->startPane("configPane");
		$tabs->startTab("Site","site-page");
			?>
			<table class="adminform">
			<tr>
				<td width="185">Site Offline:</td>
				<td><?php echo $lists['offline']; ?></td>
			</tr>
			<tr>
				<td valign="top">Offline Message:</td>
				<td><textarea class="text_area" cols="60" rows="2" style="width:500px; height:40px" name="config_offline_message"><?php echo htmlspecialchars( stripslashes( $row->config_offline_message ), ENT_QUOTES); ?></textarea><?php
					$tip = 'A message that displays if your site is offline';
					echo mosToolTip( $tip );
				?></td>
			</tr>
			<tr>
				<td valign="top">System Error Message:</td>
				<td><textarea class="text_area" cols="60" rows="2" style="width:500px; height:40px" name="config_error_message"><?php echo htmlspecialchars( stripslashes( $row->config_error_message ), ENT_QUOTES); ?></textarea><?php
					$tip = 'A message that displays if Joomla! could not connect to the database';
					echo mosToolTip( $tip );
				?></td>
			</tr>
			<tr>
				<td>Site Name:</td>
				<td><input class="text_area" type="text" name="config_sitename" size="50" value="<?php echo $row->config_sitename; ?>"/></td>
			</tr>
			<tr>
				<td>Show UnAuthorized Links:</td>
				<td><?php echo $lists['shownoauth']; ?><?php
					$tip = 'If yes, will show links to content to registered content even if you are not logged in.  The user will need to login to see the item in full.';
					echo mosToolTip( $tip );
				?></td>
			</tr>
			<tr>
				<td>Allow User Registration:</td>
				<td><?php echo $lists['allowUserRegistration']; ?><?php
					$tip = 'If yes, allows users to self-register';
					echo mosToolTip( $tip );
				?></td>
			</tr>
			<tr>
				<td>Use New Account Activation:</td>
				<td><?php echo $lists['useractivation']; ?>
				<?php
					$tip = 'If yes, the user will be mailed a link to activate their account before they can log in.';
					echo mosToolTip( $tip );
				?></td>
			</tr>
			<tr>
				<td>Require Unique Email:</td>
				<td><?php echo $lists['uniquemail']; ?><?php
					$tip = 'If yes, users cannot share the same email address';
					echo mosToolTip( $tip );
				?></td>
			</tr>
			<tr>
				<td>Debug Site:</td>
				<td><?php echo $lists['debug']; ?><?php
					$tip = 'If yes, displays diagnostic information and SQL errors if present';
					echo mosToolTip( $tip );
				?></td>
			</tr>
			<tr>
				<td>Default WYSIWYG Editor:</td>
				<td><?php echo $lists['editor']; ?></td>
			</tr>
			<tr>
				<td>List Length:</td>
				<td><?php echo $lists['list_limit']; ?><?php
					$tip = 'Sets the default length of lists in the administrator for all users';
					echo mosToolTip( $tip );
				?></td>
			</tr>
			<tr>
				<td>Favourites Site Icon:</td>
				<td>
				<input class="text_area" type="text" name="config_favicon" size="20" value="<?php echo $row->config_favicon; ?>"/>
				<?php
				$tip = 'If left blank or the file cannot be found, the default favicon.ico will be used.';
				echo mosToolTip( $tip, 'Favourite Icon' );
				?>			
				</td>
			</tr>
			</table>
			<?php
		$tabs->endTab();
		$tabs->startTab("Locale","Locale-page");
			?>
			<table class="adminform">
			<tr>
				<td width="185">Language:</td>
				<td><?php echo $lists['lang']; ?></td>
			</tr>
			<tr>
				<td width="185">Time Offset:</td>
				<td>
				<?php echo $lists['offset']; ?>
				<?php
				$tip = "Current date/time configured to display: " . mosCurrentDate(_DATE_FORMAT_LC2);
				echo mosToolTip($tip);
				?>			
				</td>
			</tr>
			<tr>
				<td width="185">Country Locale:</td>
				<td>
				<input class="text_area" type="text" name="config_locale" size="15" value="<?php echo $row->config_locale; ?>"/>
				</td>
			</tr>
			</table>
			<?php
		$tabs->endTab();
		$tabs->startTab("Content","content-page");
			?>
			<table class="adminform">
			<tr>
				<td colspan="3">* These Parameters control Output elements*<br/><br/></td>
			</tr>
			<tr>
				<td width="200">Linked Titles:</td>
				<td width="100"><?php echo $lists['link_titles']; ?></td>
				<td><?php
					$tip = 'If yes, the title of content items will be hyperlinked to the item';
					echo mosToolTip( $tip );
				?></td>
			</tr>
			<tr>
				<td width="200">Read More Link:</td>
				<td width="100"><?php echo $lists['readmore']; ?></td>
				<td><?php
					$tip = 'If set to show, the read-more link will show if main-text has been provided for the item';
					echo mosToolTip( $tip );
				?></td>
			</tr>
			<tr>
				<td>Item Rating/Voting:</td>
				<td><?php echo $lists['vote']; ?></td>
				<td><?php
					$tip = 'If set to show, a voting system will be enabled for content items';
					echo mosToolTip( $tip );
				?></td>
			</tr>
			<tr>
				<td>Author Names:</td>
				<td><?php echo $lists['hideAuthor']; ?></td>
				<td><?php
					$tip = 'If set to show, the name of the author will be displayed.  This a global setting but can be changed at menu and item levels.';
					echo mosToolTip( $tip );
				?></td>
			</tr>
			<tr>
				<td>Created Date and Time:</td>
				<td><?php echo $lists['hideCreateDate']; ?></td>
				<td><?php
					$tip = 'If set to show, the date and time an item was created will be displayed. This a global setting but can be changed at menu and item levels.';
					echo mosToolTip( $tip );
				?></td>
			</tr>
			<tr>
				<td>Modified Date and Time:</td>
				<td><?php echo $lists['hideModifyDate']; ?></td>
				<td><?php
					$tip = 'If set to show, the date and time an item was last modified will be displayed.  This a global setting but can be changed at menu and item levels.';
					echo mosToolTip( $tip );
				?></td>
			</tr>
			<tr>
				<td>Hits:</td>
				<td><?php echo $lists['hits']; ?></td>
				<td><?php
					$tip = 'If set to show, the hits for a particular item will be displayed.  This a global setting but can be changed at menu and item levels.';
					echo mosToolTip( $tip );
				?></td>
			</tr>
			<tr>
				<td>PDF Icon:</td>
				<td><?php echo $lists['hidePdf']; ?></td>
				<?php
				if (!is_writable( "$mosConfig_absolute_path/media/" )) {
					echo "<td align=\"left\">";
					echo mosToolTip('Option not available as /media directory not writable');
					echo "</td>";
				} else {
					?>				
					<td>&nbsp;</td>
					<?php
				}
				?>		
			</tr>
			<tr>
				<td>Print Icon:</td>
				<td><?php echo $lists['hidePrint']; ?></td>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<td>Email Icon:</td>
				<td><?php echo $lists['hideEmail']; ?></td>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<td>Icons:</td>
				<td><?php echo $lists['icons']; ?></td>
				<td><?php echo mosToolTip('Print, PDF and Email will utilise Icons or Text'); ?></td>
			</tr>
			<tr>
				<td>Table of Contents on multi-page items:</td>
				<td><?php echo $lists['multipage_toc']; ?></td>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<td>Back Button:</td>
				<td><?php echo $lists['back_button']; ?></td>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<td>Content Item Navigation:</td>
				<td><?php echo $lists['item_navigation']; ?></td>
				<td>&nbsp;</td>
			</tr>
			</table>
			<input type="hidden" name="config_ml_support" value="<?php echo $row->config_ml_support?>">
			<?php
		$tabs->endTab();
		$tabs->startTab("Database","db-page");
			?>
			<table class="adminform">
			<tr>
				<td width="185">Hostname:</td>
				<td><input class="text_area" type="text" name="config_host" size="25" value="<?php echo $row->config_host; ?>"/></td>
			</tr>
			<tr>
				<td>MySQL Username:</td>
				<td><input class="text_area" type="text" name="config_user" size="25" value="<?php echo $row->config_user; ?>"/></td>
			</tr>
			<tr>
				<td>MySQL Password:</td>
				<td><input class="text_area" type="text" name="config_password" size="25" value="<?php echo $row->config_password; ?>"/></td>
			</tr>
			<tr>
				<td>MySQL Database:</td>
				<td><input class="text_area" type="text" name="config_db" size="25" value="<?php echo $row->config_db; ?>"/></td>
			</tr>
			<tr>
				<td>MySQL Database Prefix:</td>
				<td>
				<input class="text_area" type="text" name="config_dbprefix" size="10" value="<?php echo $row->config_dbprefix; ?>"/>
				&nbsp;<?php echo mosWarning('!! DO NOT CHANGE UNLESS YOU HAVE A DATABASE BUILT USING TABLES WITH THE PREFIX YOU ARE SETTING !!'); ?>
				</td>
			</tr>
			</table>
			<?php
		$tabs->endTab();
		$tabs->startTab("Server","server-page");
			?>
			<table class="adminform">
			<tr>
				<td width="185">Absolute Path:</td>
				<td width="450"><strong><?php echo $row->config_absolute_path; ?></strong></td>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<td>Live Site:</td>
				<td><strong><?php echo $row->config_live_site; ?></strong></td>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<td>Secret Word:</td>
				<td><strong><?php echo $row->config_secret; ?></strong></td>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<td>GZIP Page Compression:</td>
				<td>
				<?php echo $lists['gzip']; ?>
				<?php echo mosToolTip('Compress buffered output if supported'); ?>
				</td>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<td>Login Session Lifetime:</td>
				<td>
				<input class="text_area" type="text" name="config_lifetime" size="10" value="<?php echo $row->config_lifetime; ?>"/>
				&nbsp;seconds&nbsp;
				<?php echo mosToolTip('Auto logout after this time of inactivity'); ?>
				</td>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<td>Error Reporting:</td>
				<td><?php echo $lists['error_reporting']; ?></td>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<td>Help Server:</td>
				<td><input class="text_area" type="text" name="config_helpurl" size="50" value="<?php echo $row->config_helpurl; ?>"/></td>
			</tr>
			<tr>
				<?php
				$mode = 0;
				$flags = 0644;
				if ($row->config_fileperms!='') {
					$mode = 1;
					$flags = octdec($row->config_fileperms);
				} // if
				?>
				<td valign="top">File Creation:</td>
				<td>
					<fieldset><legend>File Permissions</legend>
						<table cellpadding="1" cellspacing="1" border="0">
							<tr>
								<td><input type="radio" id="filePermsMode0" name="filePermsMode" value="0" onclick="changeFilePermsMode(0)"<?php if (!$mode) echo ' checked="checked"'; ?>/></td>
								<td><label for="filePermsMode0">Dont CHMOD new files (use server defaults)</label></td>
							</tr>
							<tr>
								<td><input type="radio" id="filePermsMode1" name="filePermsMode" value="1" onclick="changeFilePermsMode(1)"<?php if ($mode) echo ' checked="checked"'; ?>/></td>
								<td>
									<label for="filePermsMode1">CHMOD new files</label>
									<span id="filePermsValue"<?php if (!$mode) echo ' style="display:none"'; ?>>
									to:	<input class="text_area" type="text" readonly="readonly" name="config_fileperms" size="4" value="<?php echo $row->config_fileperms; ?>"/>
									</span>
									<span id="filePermsTooltip"<?php if ($mode) echo ' style="display:none"'; ?>>
									&nbsp;<?php echo mosToolTip('Select this option to define permission flags for new created files'); ?>
									</span>
								</td>
							</tr>
							<tr id="filePermsFlags"<?php if (!$mode) echo ' style="display:none"'; ?>>
								<td>&nbsp;</td>
								<td>
									<table cellpadding="0" cellspacing="1" border="0">
										<tr>
											<td style="padding:0px">User:</td>
											<td style="padding:0px"><input type="checkbox" id="filePermsUserRead" name="filePermsUserRead" value="1" onclick="saveFilePerms()"<?php if ($flags & 0400) echo ' checked="checked"'; ?>/></td>
											<td style="padding:0px"><label for="filePermsUserRead">read</label></td>
											<td style="padding:0px"><input type="checkbox" id="filePermsUserWrite" name="filePermsUserWrite" value="1" onclick="saveFilePerms()"<?php if ($flags & 0200) echo ' checked="checked"'; ?>/></td>
											<td style="padding:0px"><label for="filePermsUserWrite">write</label></td>
											<td style="padding:0px"><input type="checkbox" id="filePermsUserExecute" name="filePermsUserExecute" value="1" onclick="saveFilePerms()"<?php if ($flags & 0100) echo ' checked="checked"'; ?>/></td>
											<td style="padding:0px" colspan="3"><label for="filePermsUserExecute">execute</label></td>
										</tr>
										<tr>
											<td style="padding:0px">Group:</td>
											<td style="padding:0px"><input type="checkbox" id="filePermsGroupRead" name="filePermsGroupRead" value="1" onclick="saveFilePerms()"<?php if ($flags & 040) echo ' checked="checked"'; ?>/></td>
											<td style="padding:0px"><label for="filePermsGroupRead">read</label></td>
											<td style="padding:0px"><input type="checkbox" id="filePermsGroupWrite" name="filePermsGroupWrite" value="1" onclick="saveFilePerms()"<?php if ($flags & 020) echo ' checked="checked"'; ?>/></td>
											<td style="padding:0px"><label for="filePermsGroupWrite">write</label></td>
											<td style="padding:0px"><input type="checkbox" id="filePermsGroupExecute" name="filePermsGroupExecute" value="1" onclick="saveFilePerms()"<?php if ($flags & 010) echo ' checked="checked"'; ?>/></td>
											<td style="padding:0px" width="70"><label for="filePermsGroupExecute">execute</label></td>
											<td><input type="checkbox" id="applyFilePerms" name="applyFilePerms" value="1"/></td>
											<td nowrap="nowrap">
												<label for="applyFilePerms">
													Apply to existing files
													&nbsp;<?php
													echo mosWarning(
														'Checking here will apply the permission flags to <em>all existing files</em> of the site.<br/>'.
														'<b>INAPPROPRIATE USAGE OF THIS OPTION MAY RENDER THE SITE INOPERATIVE!</b>'
													);?>
												</label>
											</td>
										</tr>
										<tr>
											<td style="padding:0px">World:</td>
											<td style="padding:0px"><input type="checkbox" id="filePermsWorldRead" name="filePermsWorldRead" value="1" onclick="saveFilePerms()"<?php if ($flags & 04) echo ' checked="checked"'; ?>/></td>
											<td style="padding:0px"><label for="filePermsWorldRead">read</label></td>
											<td style="padding:0px"><input type="checkbox" id="filePermsWorldWrite" name="filePermsWorldWrite" value="1" onclick="saveFilePerms()"<?php if ($flags & 02) echo ' checked="checked"'; ?>/></td>
											<td style="padding:0px"><label for="filePermsWorldWrite">write</label></td>
											<td style="padding:0px"><input type="checkbox" id="filePermsWorldExecute" name="filePermsWorldExecute" value="1" onclick="saveFilePerms()"<?php if ($flags & 01) echo ' checked="checked"'; ?>/></td>
											<td style="padding:0px" colspan="4"><label for="filePermsWorldExecute">execute</label></td>
										</tr>
									</table>
								</td>
							</tr>
						</table>
					</fieldset>
				</td>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<?php
				$mode = 0;
				$flags = 0755;
				if ($row->config_dirperms!='') {
					$mode = 1;
					$flags = octdec($row->config_dirperms);
				} // if
				?>
				<td valign="top">Directory Creation:</td>
				<td>
					<fieldset><legend>Directory Permissions</legend>
						<table cellpadding="1" cellspacing="1" border="0">
							<tr>
								<td><input type="radio" id="dirPermsMode0" name="dirPermsMode" value="0" onclick="changeDirPermsMode(0)"<?php if (!$mode) echo ' checked="checked"'; ?>/></td>
								<td><label for="dirPermsMode0">Dont CHMOD new directories (use server defaults)</label></td>
							</tr>
							<tr>
								<td><input type="radio" id="dirPermsMode1" name="dirPermsMode" value="1" onclick="changeDirPermsMode(1)"<?php if ($mode) echo ' checked="checked"'; ?>/></td>
								<td>
									<label for="dirPermsMode1">CHMOD new directories</label>
									<span id="dirPermsValue"<?php if (!$mode) echo ' style="display:none"'; ?>>
									to: <input class="text_area" type="text" readonly="readonly" name="config_dirperms" size="4" value="<?php echo $row->config_dirperms; ?>"/>
									</span>
									<span id="dirPermsTooltip"<?php if ($mode) echo ' style="display:none"'; ?>>
									&nbsp;<?php echo mosToolTip('Select this option to define permission flags for new created directories'); ?>
									</span>
								</td>
							</tr>
							<tr id="dirPermsFlags"<?php if (!$mode) echo ' style="display:none"'; ?>>
								<td>&nbsp;</td>
								<td>
									<table cellpadding="1" cellspacing="0" border="0">
										<tr>
											<td style="padding:0px">User:</td>
											<td style="padding:0px"><input type="checkbox" id="dirPermsUserRead" name="dirPermsUserRead" value="1" onclick="saveDirPerms()"<?php if ($flags & 0400) echo ' checked="checked"'; ?>/></td>
											<td style="padding:0px"><label for="dirPermsUserRead">read</label></td>
											<td style="padding:0px"><input type="checkbox" id="dirPermsUserWrite" name="dirPermsUserWrite" value="1" onclick="saveDirPerms()"<?php if ($flags & 0200) echo ' checked="checked"'; ?>/></td>
											<td style="padding:0px"><label for="dirPermsUserWrite">write</label></td>
											<td style="padding:0px"><input type="checkbox" id="dirPermsUserSearch" name="dirPermsUserSearch" value="1" onclick="saveDirPerms()"<?php if ($flags & 0100) echo ' checked="checked"'; ?>/></td>
											<td style="padding:0px" colspan="3"><label for="dirPermsUserSearch">search</label></td>
										</tr>
										<tr>
											<td style="padding:0px">Group:</td>
											<td style="padding:0px"><input type="checkbox" id="dirPermsGroupRead" name="dirPermsGroupRead" value="1" onclick="saveDirPerms()"<?php if ($flags & 040) echo ' checked="checked"'; ?>/></td>
											<td style="padding:0px"><label for="dirPermsGroupRead">read</label></td>
											<td style="padding:0px"><input type="checkbox" id="dirPermsGroupWrite" name="dirPermsGroupWrite" value="1" onclick="saveDirPerms()"<?php if ($flags & 020) echo ' checked="checked"'; ?>/></td>
											<td style="padding:0px"><label for="dirPermsGroupWrite">write</label></td>
											<td style="padding:0px"><input type="checkbox" id="dirPermsGroupSearch" name="dirPermsGroupSearch" value="1" onclick="saveDirPerms()"<?php if ($flags & 010) echo ' checked="checked"'; ?>/></td>
											<td style="padding:0px" width="70"><label for="dirPermsGroupSearch">search</label></td>
											<td><input type="checkbox" id="applyDirPerms" name="applyDirPerms" value="1"/></td>
											<td nowrap="nowrap">
												<label for="applyDirPerms">
													Apply to existing directories
													&nbsp;<?php
													echo mosWarning(
														'Checking here will apply the permission flags to <em>all existing directories</em> of the site.<br/>'.
														'<b>INAPPROPRIATE USAGE OF THIS OPTION MAY RENDER THE SITE INOPERATIVE!</b>'
													);?>
												</label>
											</td>
										</tr>
										<tr>
											<td style="padding:0px">World:</td>
											<td style="padding:0px"><input type="checkbox" id="dirPermsWorldRead" name="dirPermsWorldRead" value="1" onclick="saveDirPerms()"<?php if ($flags & 04) echo ' checked="checked"'; ?>/></td>
											<td style="padding:0px"><label for="dirPermsWorldRead">read</label></td>
											<td style="padding:0px"><input type="checkbox" id="dirPermsWorldWrite" name="dirPermsWorldWrite" value="1" onclick="saveDirPerms()"<?php if ($flags & 02) echo ' checked="checked"'; ?>/></td>
											<td style="padding:0px"><label for="dirPermsWorldWrite">write</label></td>
											<td style="padding:0px"><input type="checkbox" id="dirPermsWorldSearch" name="dirPermsWorldSearch" value="1" onclick="saveDirPerms()"<?php if ($flags & 01) echo ' checked="checked"'; ?>/></td>
											<td style="padding:0px" colspan="3"><label for="dirPermsWorldSearch">search</label></td>
										</tr>
									</table>
								</td>
							</tr>
						</table>
					</fieldset>
				</td>
				<td>&nbsp;</td>
			  </tr>
			</table>
			<?php
		$tabs->endTab();
		$tabs->startTab("Metadata","metadata-page");
			?>
			<table class="adminform">
			<tr>
				<td width="185" valign="top">Global Site Meta Description:</td>
				<td><textarea class="text_area" cols="50" rows="3" style="width:500px; height:50px" name="config_MetaDesc"><?php echo htmlspecialchars($row->config_MetaDesc, ENT_QUOTES); ?></textarea></td>
			</tr>
			<tr>
				<td valign="top">Global Site Meta Keywords:</td>
				<td><textarea class="text_area" cols="50" rows="3" style="width:500px; height:50px" name="config_MetaKeys"><?php echo htmlspecialchars($row->config_MetaKeys, ENT_QUOTES); ?></textarea></td>
			</tr>
			<tr>
				<td valign="top">Show Title Meta Tag:</td>
				<td>
				<?php echo $lists['MetaTitle']; ?>
				&nbsp;&nbsp;&nbsp;
				<?php echo mosToolTip('Show the title meta tag when viewing content items'); ?>
				</td>
			  	</tr>
			<tr>
				<td valign="top">Show Author Meta Tag:</td>
				<td>
				<?php echo $lists['MetaAuthor']; ?>
				&nbsp;&nbsp;&nbsp;
				<?php echo mosToolTip('Show the author meta tag when viewing content items'); ?>
				</td>
			</tr>
			</table>
			<?php
		$tabs->endTab();
		$tabs->startTab("Mail","mail-page");
			?>
			<table class="adminform">
			<tr>
				<td width="185">Mailer:</td>
				<td><?php echo $lists['mailer']; ?></td>
			</tr>
			<tr>
				<td>Mail From:</td>
				<td><input class="text_area" type="text" name="config_mailfrom" size="50" value="<?php echo $row->config_mailfrom; ?>"/></td>
			</tr>
			<tr>
				<td>From Name:</td>
				<td><input class="text_area" type="text" name="config_fromname" size="50" value="<?php echo $row->config_fromname; ?>"/></td>
			</tr>
			<tr>
				<td>Sendmail Path:</td>
				<td><input class="text_area" type="text" name="config_sendmail" size="50" value="<?php echo $row->config_sendmail; ?>"/></td>
			</tr>
			<tr>
				<td>SMTP Auth:</td>
				<td><?php echo $lists['smtpauth']; ?></td>
			</tr>
			<tr>
				<td>SMTP User:</td>
				<td><input class="text_area" type="text" name="config_smtpuser" size="50" value="<?php echo $row->config_smtpuser; ?>"/></td>
			</tr>
			<tr>
				<td>SMTP Pass:</td>
				<td><input class="text_area" type="text" name="config_smtppass" size="50" value="<?php echo $row->config_smtppass; ?>"/></td>
			</tr>
			<tr>
				<td>SMTP Host:</td>
				<td><input class="text_area" type="text" name="config_smtphost" size="50" value="<?php echo $row->config_smtphost; ?>"/></td>
			</tr>
			</table>
			<?php
		$tabs->endTab();
		$tabs->startTab("Cache","cache-page");
			?>
			<table class="adminform" border="0">
			<?php
			if (is_writeable($row->config_cachepath)) {
				?>
				<tr>
					<td width="185">Caching:</td>
					<td width="500"><?php echo $lists['caching']; ?></td>
					<td>&nbsp;</td>
				</tr>
				<?php
			}
			?>
			<tr>
				<td>Cache Folder:</td>
				<td>
				<input class="text_area" type="text" name="config_cachepath" size="50" value="<?php echo $row->config_cachepath; ?>"/>
				<?php
				if (is_writeable($row->config_cachepath)) {
					echo mosToolTip('Current cache is directory is <b>Writeable</b>');
				} else {
					echo mosWarning('The cache directory is UNWRITEABLE - please set this directory to CHMOD755 before turning on the cache');
				}
				?>			
				</td>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<td>Cache Time:</td>
				<td><input class="text_area" type="text" name="config_cachetime" size="5" value="<?php echo $row->config_cachetime; ?>"/> seconds</td>
				<td>&nbsp;</td>
			</tr>
			</table>
			<?php
		$tabs->endTab();
		$tabs->startTab("Statistics","stats-page");
			?>
			<table class="adminform">
			<tr>
				<td width="185">Statistics:</td>
				<td width="100"><?php echo $lists['enable_stats']; ?></td>
				<td><?php echo mostooltip('Enable/disable collection of site statistics'); ?></td>
			</tr>
			<tr>
				<td>Log Content Hits by Date:</td>
				<td><?php echo $lists['log_items']; ?></td>
				<td><span class="error"><?php echo mosWarning('WARNING : Large amounts of data will be collected'); ?></span></td>
			</tr>
			<tr>
				<td>Log Search Strings:</td>
				<td><?php echo $lists['log_searches']; ?></td>
				<td>&nbsp;</td>
			</tr>
			</table>
			<?php
		$tabs->endTab();
		$tabs->startTab("SEO","seo-page");
			?>
			<table class="adminform">
			<tr>
				<td width="200"><strong>Search Engine Optimization</strong></td>
				<td width="100">&nbsp;</td>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<td>Search Engine Friendly URLs:</td>
				<td><?php echo $lists['sef']; ?>&nbsp;</td>
				<td><span class="error"><?php echo mosWarning('Apache only! Rename htaccess.txt to .htaccess before activating'); ?></span></td>
			</tr>
			<tr>
				<td>Dynamic Page Titles:</td>
				<td><?php echo $lists['pagetitles']; ?></td>
				<td><?php echo mosToolTip('Dynamically changes the page title to reflect current content viewed'); ?></td>
			</tr>
			</table>
			<?php
		$tabs->endTab();
		$tabs->endPane();
		?>
		
		<input type="hidden" name="option" value="<?php echo $option; ?>"/>
		<input type="hidden" name="config_absolute_path" value="<?php echo $row->config_absolute_path; ?>"/>
		<input type="hidden" name="config_live_site" value="<?php echo $row->config_live_site; ?>"/>
		<input type="hidden" name="config_secret" value="<?php echo $row->config_secret; ?>"/>
	  	<input type="hidden" name="task" value=""/>
		</form>
		<script  type="text/javascript" src="<?php echo $mosConfig_live_site;?>/includes/js/overlib_mini.js"></script>
		<?php
	}

}
?>