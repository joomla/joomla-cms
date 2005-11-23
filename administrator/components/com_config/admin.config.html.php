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
		global $mosConfig_absolute_path;
	
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
			<td width="270">
				<span class="componentheading"><?php echo JText::_( 'configuration.php is' ); ?> :
				<?php echo is_writable( '../configuration.php' ) ? '<b><font color="green"> '. JText::_( 'Writeable' ) .'</font></b>' : '<b><font color="red"> '. JText::_( 'Unwriteable' ) .'</font></b>' ?>
				</span>
			</td>
			<?php
			if (mosIsChmodable('../configuration.php')) {
				if (is_writable('../configuration.php')) {
					?>
					<td>
						<input type="checkbox" id="disable_write" name="disable_write" value="1"/>
						<label for="disable_write"><?php echo JText::_( 'Make unwriteable after saving' ); ?></label>
					</td>
					<?php
				} else {
					?>
					<td>
						<input type="checkbox" id="enable_write" name="enable_write" value="1"/>
						<label for="enable_write"><?php echo JText::_( 'Override write protection while saving' ); ?></label>
					</td>
				<?php
				} // if
			} // if
			?>
		</tr>
		</table>
		
			<?php
		$title = JText::_( 'Site' );
		$tabs->startPane("configPane");
		$tabs->startTab( $title, "site-page" );
			?>
			
			<table class="adminform">
			<tr>
				<td width="185"><?php echo JText::_( 'Site Offline' ); ?>:</td>
				<td><?php echo $lists['offline']; ?></td>
			</tr>
			<tr>
				<td valign="top"><?php echo JText::_( 'Offline Message' ); ?>:</td>
				<td><textarea class="text_area" cols="60" rows="2" style="width:500px; height:40px" name="config_offline_message"><?php echo htmlspecialchars( stripslashes( $row->config_offline_message ), ENT_QUOTES); ?></textarea><?php
					$tip = JText::_( 'TIPIFYOURSITEISOFFLINE' );
					echo mosToolTip( $tip );
				?></td>
			</tr>
			<tr>
				<td valign="top"><?php echo JText::_( 'System Error Message' ); ?>:</td>
				<td><textarea class="text_area" cols="60" rows="2" style="width:500px; height:40px" name="config_error_message"><?php echo htmlspecialchars( stripslashes( $row->config_error_message ), ENT_QUOTES); ?></textarea><?php
					$tip = JText::_( 'TIPCOULDNOTCONNECTDB' );
					echo mosToolTip( $tip );
				?></td>
			</tr>
			<tr>
				<td><?php echo JText::_( 'Site Name' ); ?>:</td>
				<td><input class="text_area" type="text" name="config_sitename" size="50" value="<?php echo $row->config_sitename; ?>"/></td>
			</tr>
			<tr>
				<td><?php echo JText::_( 'Show UnAuthorized Links' ); ?>:</td>
				<td><?php echo $lists['shownoauth']; ?><?php
					$tip = JText::_( 'TIPLINKS' );
					echo mosToolTip( $tip );
				?></td>
			</tr>
			<tr>
				<td><?php echo JText::_( 'Allow User Registration' ); ?>:</td>
				<td><?php echo $lists['allowUserRegistration']; ?><?php
					$tip = JText::_( 'If yes, allows users to self-register' );
					echo mosToolTip( $tip );
				?></td>
			</tr>
			<tr>
				<td><?php echo JText::_( 'Use New Account Activation' ); ?>:</td>
				<td><?php echo $lists['useractivation']; ?>
				<?php
					$tip = JText::_( 'TIPIFYESUSERMAILEDLINK' );
					echo mosToolTip( $tip );
				?></td>
			</tr>
			<tr>
				<td><?php echo JText::_( 'Require Unique Email' ); ?>:</td>
				<td><?php echo $lists['uniquemail']; ?><?php
					$tip = JText::_( 'TIPIFYESUSERSCANNOTSHAREEMAIL' );
					echo mosToolTip( $tip );
				?></td>
			</tr>
			<tr>
				<td><?php echo JText::_( 'Debug Site' ); ?>:</td>
				<td><?php echo $lists['debug']; ?><?php
					$tip = JText::_( 'TIPIFYESINFO' );
					echo mosToolTip( $tip );
				?></td>
			</tr>
			<tr>
				<td><?php echo JText::_( 'Default WYSIWYG Editor' ); ?>:</td>
				<td><?php echo $lists['editor']; ?></td>
			</tr>
			<tr>
				<td><?php echo JText::_( 'List Length' ); ?>:</td>
				<td><?php echo $lists['list_limit']; ?><?php
					$tip = JText::_( 'TIPSETSDEFAULTLENGTHLISTS' );
					echo mosToolTip( $tip );
				?></td>
			</tr>
			<tr>
				<td><?php echo JText::_( 'Favourites Site Icon' ); ?>:</td>
				<td>
				<input class="text_area" type="text" name="config_favicon" size="20" value="<?php echo $row->config_favicon; ?>"/>
				<?php
				$tip = JText::_( 'TIPBLANKORFOUND' );
				echo mosToolTip( $tip, 'Favourite Icon' );
				?>
				</td>
			</tr>
			</table>
			
			<?php
		$title = JText::_( 'Locale' );
		$tabs->endTab();
		$tabs->startTab( $title, "Locale-page" );
			?>
			
			<table class="adminform">
			<tr>
				<td width="185"><?php echo JText::_( 'Language' ); ?>:</td>
				<td><?php echo $lists['lang']; ?></td>
			</tr>
			<tr>
				<td width="185"><?php echo JText::_( 'Time Offset' ); ?>:</td>
				<td>
				<?php echo $lists['offset']; ?>
				<?php
				$tip = JText::_( 'Current date/time configured to display' ) .': '. mosCurrentDate( JText::_( '_DATE_FORMAT_LC2' ) );
				echo mosToolTip( $tip );
				?>
				</td>
			</tr>
			<tr>
				<td width="185"><?php echo JText::_( 'Server Offset' ); ?>:</td>
				<td>
				<input class="text_area" type="text" name="config_offset" size="15" value="<?php echo $row->config_offset; ?>" disabled="true"/>
				</td>
			</tr>
			<tr>
				<td width="185"><?php echo JText::_( 'Country Locale' ); ?>:</td>
				<td>
				<input class="text_area" type="text" name="config_locale" size="15" value="<?php echo $row->config_locale; ?>"/>
				</td>
			</tr>
			</table>
			
			<?php
		$title = JText::_( 'Content' );
		$tabs->endTab();
		$tabs->startTab( $title, "content-page" );
			?>
			
			<table class="adminform">
			<tr>
				<td colspan="3"><?php echo JText::_( 'DESCCONTROLOUTPUTELEMENTS' ); ?><br /><br /></td>
			</tr>
			<tr>
				<td width="200"><?php echo JText::_( 'Linked Titles' ); ?>:</td>
				<td width="100"><?php echo $lists['link_titles']; ?></td>
				<td><?php
					$tip = JText::_( 'TIPIFYESTITLECONTENTITEMS' );
					echo mosToolTip( $tip );
				?></td>
			</tr>
			<tr>
				<td width="200"><?php echo JText::_( 'Read More Link' ); ?>:</td>
				<td width="100"><?php echo $lists['readmore']; ?></td>
				<td><?php
					$tip = JText::_( 'TIPIFSETTOSHOWREADMORELINK' );
					echo mosToolTip( $tip );
				?></td>
			</tr>
			<tr>
				<td><?php echo JText::_( 'Item Rating/Voting' ); ?>:</td>
				<td><?php echo $lists['vote']; ?></td>
				<td><?php
					$tip = JText::_( 'TIPIFSETTOSHOWVOTING' );
					echo mosToolTip( $tip );
				?></td>
			</tr>
			<tr>
				<td><?php echo JText::_( 'Author Names' ); ?>:</td>
				<td><?php echo $lists['hideAuthor']; ?></td>
				<td><?php
					$tip = JText::_( 'TIPIFSETTOSHOWAUTHOR' );
					echo mosToolTip( $tip );
				?></td>
			</tr>
			<tr>
				<td><?php echo JText::_( 'Created Date and Time' ); ?>:</td>
				<td><?php echo $lists['hideCreateDate']; ?></td>
				<td><?php
					$tip = JText::_( 'TIPIFSETTOSHOWDATETIMECREATED' );
					echo mosToolTip( $tip );
				?></td>
			</tr>
			<tr>
				<td><?php echo JText::_( 'Modified Date and Time' ); ?>:</td>
				<td><?php echo $lists['hideModifyDate']; ?></td>
				<td><?php
					$tip = JText::_( 'TIPIFSETTOSHOWDATETIMEMODIFIED' );
					echo mosToolTip( $tip );
				?></td>
			</tr>
			<tr>
				<td><?php echo JText::_( 'Hits' ); ?>:</td>
				<td><?php echo $lists['hits']; ?></td>
				<td><?php
					$tip = JText::_( 'TIPIFSETTOSHOWHITS' );
					echo mosToolTip( $tip );
				?></td>
			</tr>
			<tr>
				<td><?php echo JText::_( 'PDF Icon' ); ?>:</td>
				<td><?php echo $lists['hidePdf']; ?></td>
				<?php
				if (!is_writable( "$mosConfig_absolute_path/media/" )) {
                    $tip = JText::_( 'TIPOPTIONMEDIA' );
					echo "<td align=\"left\">";
					echo mosToolTip( $tip );
					echo "</td>";
				} else {
					?>
					<td>&nbsp;</td>
					<?php
				}
				?>
			</tr>
			<tr>
				<td><?php echo JText::_( 'Print Icon' ); ?>:</td>
				<td><?php echo $lists['hidePrint']; ?></td>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<td><?php echo JText::_( 'Email Icon' ); ?>:</td>
				<td><?php echo $lists['hideEmail']; ?></td>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<td><?php echo JText::_( 'Icons' ); ?>:</td>
				<td><?php echo $lists['icons']; ?></td>
				<td><?php
                    $tip = JText::_( 'TIPPRINTPDFEMAIL' );
                    echo mosToolTip( $tip ); ?></td>
			</tr>
			<tr>
				<td><?php echo JText::_( 'Table of Contents on multi-page items' ); ?>:</td>
				<td><?php echo $lists['multipage_toc']; ?></td>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<td><?php echo JText::_( 'Back Button' ); ?>:</td>
				<td><?php echo $lists['back_button']; ?></td>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<td><?php echo JText::_( 'Content Item Navigation' ); ?>:</td>
				<td><?php echo $lists['item_navigation']; ?></td>
				<td>&nbsp;</td>
			</tr>
			</table>
			<?php
		$title = JText::_( 'Database' );
		$tabs->endTab();
		$tabs->startTab( $title, "db-page" );
			?>
			
			<table class="adminform">
			<tr>
				<td width="185"><?php echo JText::_( 'Database type' ); ?>:</td>
				<td><input class="text_area" type="text" name="config_dbtype" size="25" value="<?php echo $row->config_dbtype; ?>"/></td>
			</tr>
			<tr>
				<td width="185"><?php echo JText::_( 'Hostname' ); ?>:</td>
				<td><input class="text_area" type="text" name="config_host" size="25" value="<?php echo $row->config_host; ?>"/></td>
			</tr>
			<tr>
				<td><?php echo JText::_( 'Username' ); ?>:</td>
				<td><input class="text_area" type="text" name="config_user" size="25" value="<?php echo $row->config_user; ?>"/></td>
			</tr>
			<tr>
				<td><?php echo JText::_( 'Password' ); ?>:</td>
				<td><input class="text_area" type="password" name="config_password" size="25" value="<?php echo $row->config_password; ?>"/></td>
			</tr>
			<tr>
				<td><?php echo JText::_( 'Database' ); ?>:</td>
				<td><input class="text_area" type="text" name="config_db" size="25" value="<?php echo $row->config_db; ?>"/></td>
			</tr>
			<tr>
				<td><?php echo JText::_( 'Database Prefix' ); ?>:</td>
				<td>
				<input class="text_area" type="text" name="config_dbprefix" size="10" value="<?php echo $row->config_dbprefix; ?>"/>
				&nbsp;<?php
                $warn = JText::_( 'WARNDONOTCHANGEDATABASETABLESPREFIX' );
                echo JWarning( $warn ); ?>
				</td>
			</tr>
			</table>
			
			<?php
		$title = JText::_( 'Server' );
		$tabs->endTab();
		$tabs->startTab( $title, "server-page" );
			?>
			
			<table class="adminform">
			<tr>
				<td width="185"><?php echo JText::_( 'Absolute Path' ); ?>:</td>
				<td width="450"><strong><?php echo $row->config_absolute_path; ?></strong></td>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<td><?php echo JText::_( 'Live Site' ); ?>:</td>
				<td><strong><?php echo $row->config_live_site; ?></strong></td>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<td><?php echo JText::_( 'Secure Site' ); ?>:</td>
				<td>
				<input class="text_area" type="text" name="config_secure_site" size="50" value="<?php echo $row->config_secure_site; ?>"/>
				<?php
                    $tip = JText::_( 'TIPSECURESITE' );
                    echo mosToolTip( $tip ); ?>
				</td>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<td><?php echo JText::_( 'Secret Word' ); ?>:</td>
				<td><strong><?php echo $row->config_secret; ?></strong></td>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<td><?php echo JText::_( 'GZIP Page Compression' ); ?>:</td>
				<td>
				<?php echo $lists['gzip']; ?>
				<?php
                    $tip = JText::_( 'Compress buffered output if supported' );
                    echo mosToolTip( $tip ); ?>
				</td>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<td><?php echo JText::_( 'Login Session Lifetime' ); ?>:</td>
				<td>
				<input class="text_area" type="text" name="config_lifetime" size="10" value="<?php echo $row->config_lifetime; ?>"/>
				&nbsp;<?php echo JText::_('seconds'); ?>&nbsp;
				<?php
                    $tip = JText::_( 'TIPAUTOLOGOUTTIMEOF' );
                    echo mosToolTip( $tip ); ?>
				</td>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<td><?php echo JText::_( 'Error Reporting' ); ?>:</td>
				<td><?php echo $lists['error_reporting']; ?></td>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<td><?php echo JText::_( 'Help Server' ); ?>:</td>
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
				<td valign="top"><?php echo JText::_( 'File Creation' ); ?>:</td>
				<td>
					<fieldset><legend><?php echo JText::_( 'File Permissions' ); ?></legend>
						<table cellpadding="1" cellspacing="1" border="0">
							<tr>
								<td><input type="radio" id="filePermsMode0" name="filePermsMode" value="0" onclick="changeFilePermsMode(0)"<?php if (!$mode) echo ' checked="checked"'; ?>/></td>
								<td><label for="filePermsMode0"><?php echo JText::_( 'DESCDONTCHMODNEWFILES' ); ?></label></td>
							</tr>
							<tr>
								<td><input type="radio" id="filePermsMode1" name="filePermsMode" value="1" onclick="changeFilePermsMode(1)"<?php if ($mode) echo ' checked="checked"'; ?>/></td>
								<td>
									<label for="filePermsMode1"><?php echo JText::_( 'CHMOD new files' ); ?></label>
									<span id="filePermsValue"<?php if (!$mode) echo ' style="display:none"'; ?>>
									<?php echo JText::_( 'to' ); ?>:	<input class="text_area" type="text" readonly="readonly" name="config_fileperms" size="4" value="<?php echo $row->config_fileperms; ?>"/>
									</span>
									<span id="filePermsTooltip"<?php if ($mode) echo ' style="display:none"'; ?>>
									&nbsp;<?php
                                    $tip = JText::_( 'TIPPERMISSIONFLAGSFILES' );
                                    echo mosToolTip( $tip ); ?>
									</span>
								</td>
							</tr>
							<tr id="filePermsFlags"<?php if (!$mode) echo ' style="display:none"'; ?>>
								<td>&nbsp;</td>
								<td>
									<table cellpadding="0" cellspacing="1" border="0">
										<tr>
											<td style="padding:0px"><?php echo JText::_( 'User' ); ?>:</td>
											<td style="padding:0px"><input type="checkbox" id="filePermsUserRead" name="filePermsUserRead" value="1" onclick="saveFilePerms()"<?php if ($flags & 0400) echo ' checked="checked"'; ?>/></td>
											<td style="padding:0px"><label for="filePermsUserRead"><?php echo JText::_( 'read' ); ?></label></td>
											<td style="padding:0px"><input type="checkbox" id="filePermsUserWrite" name="filePermsUserWrite" value="1" onclick="saveFilePerms()"<?php if ($flags & 0200) echo ' checked="checked"'; ?>/></td>
											<td style="padding:0px"><label for="filePermsUserWrite"><?php echo JText::_( 'write' ); ?></label></td>
											<td style="padding:0px"><input type="checkbox" id="filePermsUserExecute" name="filePermsUserExecute" value="1" onclick="saveFilePerms()"<?php if ($flags & 0100) echo ' checked="checked"'; ?>/></td>
											<td style="padding:0px" colspan="3"><label for="filePermsUserExecute"><?php echo JText::_( 'execute' ); ?></label></td>
										</tr>
										<tr>
											<td style="padding:0px"><?php echo JText::_( 'Group' ); ?>:</td>
											<td style="padding:0px"><input type="checkbox" id="filePermsGroupRead" name="filePermsGroupRead" value="1" onclick="saveFilePerms()"<?php if ($flags & 040) echo ' checked="checked"'; ?>/></td>
											<td style="padding:0px"><label for="filePermsGroupRead"><?php echo JText::_( 'read' ); ?></label></td>
											<td style="padding:0px"><input type="checkbox" id="filePermsGroupWrite" name="filePermsGroupWrite" value="1" onclick="saveFilePerms()"<?php if ($flags & 020) echo ' checked="checked"'; ?>/></td>
											<td style="padding:0px"><label for="filePermsGroupWrite"><?php echo JText::_( 'write' ); ?></label></td>
											<td style="padding:0px"><input type="checkbox" id="filePermsGroupExecute" name="filePermsGroupExecute" value="1" onclick="saveFilePerms()"<?php if ($flags & 010) echo ' checked="checked"'; ?>/></td>
											<td style="padding:0px" width="70"><label for="filePermsGroupExecute"><?php echo JText::_( 'execute' ); ?></label></td>
											<td><input type="checkbox" id="applyFilePerms" name="applyFilePerms" value="1"/></td>
											<td nowrap="nowrap">
												<label for="applyFilePerms">
													<?php echo JText::_( 'Apply to existing files' ); ?>
													&nbsp;<?php
													$warn = JText::_( 'WARNWILLAPPLYPERMISSIONFLAGSTO' );
													echo JWarning( $warn );?>
												</label>
											</td>
										</tr>
										<tr>
											<td style="padding:0px"><?php echo JText::_( 'World' ); ?>:</td>
											<td style="padding:0px"><input type="checkbox" id="filePermsWorldRead" name="filePermsWorldRead" value="1" onclick="saveFilePerms()"<?php if ($flags & 04) echo ' checked="checked"'; ?>/></td>
											<td style="padding:0px"><label for="filePermsWorldRead"><?php echo JText::_( 'read' ); ?></label></td>
											<td style="padding:0px"><input type="checkbox" id="filePermsWorldWrite" name="filePermsWorldWrite" value="1" onclick="saveFilePerms()"<?php if ($flags & 02) echo ' checked="checked"'; ?>/></td>
											<td style="padding:0px"><label for="filePermsWorldWrite"><?php echo JText::_( 'write' ); ?></label></td>
											<td style="padding:0px"><input type="checkbox" id="filePermsWorldExecute" name="filePermsWorldExecute" value="1" onclick="saveFilePerms()"<?php if ($flags & 01) echo ' checked="checked"'; ?>/></td>
											<td style="padding:0px" colspan="4"><label for="filePermsWorldExecute"><?php echo JText::_( 'execute' ); ?></label></td>
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
				<td valign="top"><?php echo JText::_( 'Directory Creation' ); ?>:</td>
				<td>
					<fieldset><legend><?php echo JText::_( 'Directory Permissions' ); ?></legend>
						<table cellpadding="1" cellspacing="1" border="0">
							<tr>
								<td><input type="radio" id="dirPermsMode0" name="dirPermsMode" value="0" onclick="changeDirPermsMode(0)"<?php if (!$mode) echo ' checked="checked"'; ?>/></td>
								<td><label for="dirPermsMode0"><?php echo JText::_( 'DESCDONTCHMODNEWDIR' ); ?></label></td>
							</tr>
							<tr>
								<td><input type="radio" id="dirPermsMode1" name="dirPermsMode" value="1" onclick="changeDirPermsMode(1)"<?php if ($mode) echo ' checked="checked"'; ?>/></td>
								<td>
									<label for="dirPermsMode1"><?php echo JText::_( 'CHMOD new directories' ); ?></label>
									<span id="dirPermsValue"<?php if (!$mode) echo ' style="display:none"'; ?>>
									<?php echo JText::_( 'to' ); ?>: <input class="text_area" type="text" readonly="readonly" name="config_dirperms" size="4" value="<?php echo $row->config_dirperms; ?>"/>
									</span>
									<span id="dirPermsTooltip"<?php if ($mode) echo ' style="display:none"'; ?>>
									&nbsp;<?php
                                    $tip = JText::_( 'TIPPERMISSIONFLAGSDIR' );
                                    echo mosToolTip( $tip ); ?>
									</span>
								</td>
							</tr>
							<tr id="dirPermsFlags"<?php if (!$mode) echo ' style="display:none"'; ?>>
								<td>&nbsp;</td>
								<td>
									<table cellpadding="1" cellspacing="0" border="0">
										<tr>
											<td style="padding:0px"><?php echo JText::_( 'User' ); ?>:</td>
											<td style="padding:0px"><input type="checkbox" id="dirPermsUserRead" name="dirPermsUserRead" value="1" onclick="saveDirPerms()"<?php if ($flags & 0400) echo ' checked="checked"'; ?>/></td>
											<td style="padding:0px"><label for="dirPermsUserRead"><?php echo JText::_( 'read' ); ?></label></td>
											<td style="padding:0px"><input type="checkbox" id="dirPermsUserWrite" name="dirPermsUserWrite" value="1" onclick="saveDirPerms()"<?php if ($flags & 0200) echo ' checked="checked"'; ?>/></td>
											<td style="padding:0px"><label for="dirPermsUserWrite"><?php echo JText::_( 'write' ); ?></label></td>
											<td style="padding:0px"><input type="checkbox" id="dirPermsUserSearch" name="dirPermsUserSearch" value="1" onclick="saveDirPerms()"<?php if ($flags & 0100) echo ' checked="checked"'; ?>/></td>
											<td style="padding:0px" colspan="3"><label for="dirPermsUserSearch"><?php echo JText::_( 'search' ); ?></label></td>
										</tr>
										<tr>
											<td style="padding:0px"><?php echo JText::_( 'Group' ); ?>:</td>
											<td style="padding:0px"><input type="checkbox" id="dirPermsGroupRead" name="dirPermsGroupRead" value="1" onclick="saveDirPerms()"<?php if ($flags & 040) echo ' checked="checked"'; ?>/></td>
											<td style="padding:0px"><label for="dirPermsGroupRead"><?php echo JText::_( 'read' ); ?></label></td>
											<td style="padding:0px"><input type="checkbox" id="dirPermsGroupWrite" name="dirPermsGroupWrite" value="1" onclick="saveDirPerms()"<?php if ($flags & 020) echo ' checked="checked"'; ?>/></td>
											<td style="padding:0px"><label for="dirPermsGroupWrite"><?php echo JText::_( 'write' ); ?></label></td>
											<td style="padding:0px"><input type="checkbox" id="dirPermsGroupSearch" name="dirPermsGroupSearch" value="1" onclick="saveDirPerms()"<?php if ($flags & 010) echo ' checked="checked"'; ?>/></td>
											<td style="padding:0px" width="70"><label for="dirPermsGroupSearch"><?php echo JText::_( 'search' ); ?></label></td>
											<td><input type="checkbox" id="applyDirPerms" name="applyDirPerms" value="1"/></td>
											<td nowrap="nowrap">
												<label for="applyDirPerms">
													<?php echo JText::_( 'Apply to existing directories' ); ?>
													&nbsp;<?php
													$tip = JText::_( 'WARNCHECK' );
													echo JWarning( $tip );?>
												</label>
											</td>
										</tr>
										<tr>
											<td style="padding:0px"><?php echo JText::_( 'World' ); ?>:</td>
											<td style="padding:0px"><input type="checkbox" id="dirPermsWorldRead" name="dirPermsWorldRead" value="1" onclick="saveDirPerms()"<?php if ($flags & 04) echo ' checked="checked"'; ?>/></td>
											<td style="padding:0px"><label for="dirPermsWorldRead"><?php echo JText::_( 'read' ); ?></label></td>
											<td style="padding:0px"><input type="checkbox" id="dirPermsWorldWrite" name="dirPermsWorldWrite" value="1" onclick="saveDirPerms()"<?php if ($flags & 02) echo ' checked="checked"'; ?>/></td>
											<td style="padding:0px"><label for="dirPermsWorldWrite"><?php echo JText::_( 'write' ); ?></label></td>
											<td style="padding:0px"><input type="checkbox" id="dirPermsWorldSearch" name="dirPermsWorldSearch" value="1" onclick="saveDirPerms()"<?php if ($flags & 01) echo ' checked="checked"'; ?>/></td>
											<td style="padding:0px" colspan="3"><label for="dirPermsWorldSearch"><?php echo JText::_( 'search' ); ?></label></td>
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
		$title = JText::_( 'Metadata' );
		$tabs->endTab();
		$tabs->startTab( $title, "metadata-page" );
			?>
			
			<table class="adminform">
			<tr>
				<td width="185" valign="top"><?php echo JText::_( 'Global Site Meta Description' ); ?>:</td>
				<td><textarea class="text_area" cols="50" rows="3" style="width:500px; height:50px" name="config_MetaDesc"><?php echo htmlspecialchars($row->config_MetaDesc, ENT_QUOTES); ?></textarea></td>
			</tr>
			<tr>
				<td valign="top"><?php echo JText::_( 'Global Site Meta Keywords' ); ?>:</td>
				<td><textarea class="text_area" cols="50" rows="3" style="width:500px; height:50px" name="config_MetaKeys"><?php echo htmlspecialchars($row->config_MetaKeys, ENT_QUOTES); ?></textarea></td>
			</tr>
			<tr>
				<td valign="top"><?php echo JText::_( 'Show Title Meta Tag' ); ?>:</td>
				<td>
				<?php echo $lists['MetaTitle']; ?>
				&nbsp;&nbsp;&nbsp;
				<?php
                    $tip = JText::_( 'TIPSHOWTITLEMETATAGITEMS' );
                    echo mosToolTip( $tip ); ?>
				</td>
			  	</tr>
			<tr>
				<td valign="top"><?php echo JText::_( 'Show Author Meta Tag' ); ?>:</td>
				<td>
				<?php echo $lists['MetaAuthor']; ?>
				&nbsp;&nbsp;&nbsp;
				<?php
                    $tip = JText::_( 'TIPSHOWAUTHORMETATAGITEMS' );
                    echo mosToolTip( $tip ); ?>
				</td>
			</tr>
			</table>
			
			<?php
		$title = JText::_( 'Mail' );
		$tabs->endTab();
		$tabs->startTab( $title, "mail-page" );
			?>
			
			<table class="adminform">
			<tr>
				<td width="185"><?php echo JText::_( 'Mailer' ); ?>:</td>
				<td><?php echo $lists['mailer']; ?></td>
			</tr>
			<tr>
				<td><?php echo JText::_( 'Mail From' ); ?>:</td>
				<td><input class="text_area" type="text" name="config_mailfrom" size="50" value="<?php echo $row->config_mailfrom; ?>"/></td>
			</tr>
			<tr>
				<td><?php echo JText::_( 'From Name' ); ?>:</td>
				<td><input class="text_area" type="text" name="config_fromname" size="50" value="<?php echo $row->config_fromname; ?>"/></td>
			</tr>
			<tr>
				<td><?php echo JText::_( 'Sendmail Path' ); ?>:</td>
				<td><input class="text_area" type="text" name="config_sendmail" size="50" value="<?php echo $row->config_sendmail; ?>"/></td>
			</tr>
			<tr>
				<td><?php echo JText::_( 'SMTP Auth' ); ?>:</td>
				<td><?php echo $lists['smtpauth']; ?></td>
			</tr>
			<tr>
				<td><?php echo JText::_( 'SMTP User' ); ?>:</td>
				<td><input class="text_area" type="text" name="config_smtpuser" size="50" value="<?php echo $row->config_smtpuser; ?>"/></td>
			</tr>
			<tr>
				<td><?php echo JText::_( 'SMTP Pass' ); ?>:</td>
				<td><input class="text_area" type="text" name="config_smtppass" size="50" value="<?php echo $row->config_smtppass; ?>"/></td>
			</tr>
			<tr>
				<td><?php echo JText::_( 'SMTP Host' ); ?>:</td>
				<td><input class="text_area" type="text" name="config_smtphost" size="50" value="<?php echo $row->config_smtphost; ?>"/></td>
			</tr>
			</table>
			
			<?php
		$title = JText::_( 'Cache' );
		$tabs->endTab();
		$tabs->startTab( $title, "cache-page" );
			?>
			
			<table class="adminform" border="0">
			<?php
			if (is_writeable($row->config_cachepath)) {
				?>
				<tr>
					<td width="185"><?php echo JText::_( 'Caching' ); ?>:</td>
					<td width="500"><?php echo $lists['caching']; ?></td>
					<td>&nbsp;</td>
				</tr>
				<?php
			}
			?>
			<tr>
				<td><?php echo JText::_( 'Cache Folder' ); ?>:</td>
				<td>
				<input class="text_area" type="text" name="config_cachepath" size="50" value="<?php echo $row->config_cachepath; ?>"/>
				<?php
				if (is_writeable($row->config_cachepath)) {
                    $tip = JText::_( 'TIPDIRWRITEABLE' );
					echo mosToolTip( $tip );
				} else {
                    $warn = JText::_( 'TIPCACHEDIRISUNWRITEABLE' );
					echo JWarning( $warn );
				}
				?>
				</td>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<td><?php echo JText::_( 'Cache Time' ); ?>:</td>
				<td><input class="text_area" type="text" name="config_cachetime" size="5" value="<?php echo $row->config_cachetime; ?>"/> <?php echo JText::_( 'seconds' ); ?></td>
				<td>&nbsp;</td>
			</tr>
			</table>
			
			<?php
		$title = JText::_( 'Statistics' );
		$tabs->endTab();
		$tabs->startTab( $title, "stats-page" );
			?>
			
			<table class="adminform">
			<tr>
				<td width="185"><?php echo JText::_( 'Statistics' ); ?>:</td>
				<td width="100"><?php echo $lists['enable_stats']; ?></td>
				<td><?php
                    $tip = JText::_( 'TIPENABLEDISABLESTATS' );
                    echo mostooltip( $tip ); ?></td>
			</tr>
			<tr>
				<td><?php echo JText::_( 'Log Content Hits by Date' ); ?>:</td>
				<td><?php echo $lists['log_items']; ?></td>
				<td><span class="error"><?php
                    $warn = JText::_( 'TIPLARGEAMOUNTSOFDATA' );
                    echo JWarning( $warn ); ?></span></td>
			</tr>
			<tr>
				<td><?php echo JText::_( 'Log Search Strings' ); ?>:</td>
				<td><?php echo $lists['log_searches']; ?></td>
				<td>&nbsp;</td>
			</tr>
			</table>
			
			<?php
		$title = JText::_( 'SEO' );
		$tabs->endTab();
		$tabs->startTab( $title, "seo-page" );
			?>
			
			<table class="adminform">
			<tr>
				<td width="200"><strong><?php echo JText::_( 'Search Engine Optimization Settings' ); ?></strong></td>
				<td width="100">&nbsp;</td>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<td><?php echo JText::_( 'Search Engine Friendly URLs' ); ?>:</td>
				<td><?php echo $lists['sef']; ?>&nbsp;</td>
				<td><span class="error"><?php
                $tip = JText::_( 'WARNAPACHEONLY' );
                echo JWarning( $tip ); ?></span></td>
			</tr>
			<tr>
				<td><?php echo JText::_( 'Dynamic Page Titles' ); ?>:</td>
				<td><?php echo $lists['pagetitles']; ?></td>
				<td><?php echo
                    $tip = JText::_( 'TIPDYNAMICALLYCHANGESPAGETITLE' );
                    mosToolTip( $tip ); ?></td>
			</tr>
			</table>
			
		<?php
		$tabs->endTab();
		$tabs->endPane();
		?>

		<input type="hidden" name="option" value="<?php echo $option; ?>"/>
		<input type="hidden" name="config_admin_path" value="<?php echo $row->config_admin_path; ?>"/>
		<input type="hidden" name="config_admin_site" value="<?php echo $row->config_admin_site; ?>"/>
		<input type="hidden" name="config_absolute_path" value="<?php echo $row->config_absolute_path; ?>"/>
		<input type="hidden" name="config_live_site" value="<?php echo $row->config_live_site; ?>"/>
		<input type="hidden" name="config_secret" value="<?php echo $row->config_secret; ?>"/>
		<input type="hidden" name="config_multilingual_support" value="<?php echo $row->config_multilingual_support; ?>"/>
	  	<input type="hidden" name="task" value=""/>
		</form>
		
		<?php
		echo mosHTML::Script('/includes/js/overlib_mini.js');
	}
}
?>
