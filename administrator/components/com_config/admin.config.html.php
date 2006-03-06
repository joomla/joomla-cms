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
defined( '_JEXEC' ) or die( 'Restricted access' );

/**
* @package Joomla
* @subpackage Config
*/
class JConfigView {

	function showConfig( &$row, &$lists, $option) {
		mosCommonHTML::loadOverlib();
		
		$tabs = new mosTabs(1);
		?>
		<form action="index2.php" method="post" name="adminForm">

		<div id="treecell">
			<fieldset title="File Status">
				<legend>
					<?php echo JText::_( 'File Status' ); ?>	
				</legend>
				
				<div id="extensions">
					<div class="ext">
						<span class="small">
							<?php
							if (JPath::canCHMOD('../configuration.php')) {
								if (is_writable('../configuration.php')) {
									?>
									<label for="disable_write">
										<?php echo JText::_( 'Make unwriteable after saving' ); ?>
									</label>
									<input type="checkbox" id="disable_write" name="disable_write" value="1" />
									<?php
								} else {
									?>
									<label for="enable_write">
										<?php echo JText::_( 'Override write protection while saving' ); ?>
									</label>
									<input type="checkbox" id="enable_write" name="enable_write" value="1" />
								<?php
								} 
							} 
							?>
						</span>
					</div>
					<div class="ext">
						<span class="small">
							<?php echo JText::_( 'configuration.php' ); ?> :							
							<?php echo is_writable( '../configuration.php' ) ? '<b><font color="green"> '. JText::_( 'Writeable' ) .'</font></b>' : '<b><font color="red"> '. JText::_( 'Unwriteable' ) .'</font></b>' ?>
						</span>
					</div>
				</div>
			</fieldset>
		</div>
		
		<div id="datacell">
			<fieldset>
				<legend>
					<?php echo JText::_( 'Details' ); ?>	
				</legend>
				
				<?php
				$title = JText::_( 'Site' );
				$tabs->startPane("configPane");
				$tabs->startTab( $title, "site-page" );
				?>
		
					<table class="adminform">
					<tr>
						<td width="185">
							<?php echo JText::_( 'Site Offline' ); ?>:
						</td>
						<td>
							<?php echo $lists['offline']; ?>
						</td>
					</tr>
					<tr>
						<td valign="top">
							<span class="editlinktip">
							<?php
							$tip = JText::_( 'TIPIFYOURSITEISOFFLINE' );
							echo mosToolTip( $tip, '', 280, 'tooltip.png', JText::_( 'Offline Message' ), '', 0 );
							?>
							:				
							</span>
						</td>
						<td>
							<textarea class="text_area" cols="60" rows="2" style="width:500px; height:40px" name="offline_message"><?php echo htmlspecialchars( stripslashes( $row->offline_message ), ENT_QUOTES); ?></textarea>
						</td>
					</tr>
					<tr>
						<td valign="top">
							<span class="editlinktip">
							<?php
							$tip = JText::_( 'TIPCOULDNOTCONNECTDB' );
							echo mosToolTip( $tip, '', 280, 'tooltip.png', JText::_( 'System Error Message' ), '', 0 );
							?>
							:				
							</span>
						</td>
						<td>
							<textarea class="text_area" cols="60" rows="2" style="width:500px; height:40px" name="error_message"><?php echo htmlspecialchars( stripslashes( $row->error_message ), ENT_QUOTES); ?></textarea>
						</td>
					</tr>
					<tr>
						<td>
							<?php echo JText::_( 'Site Name' ); ?>:
						</td>
						<td>
							<input class="text_area" type="text" name="sitename" size="50" value="<?php echo $row->sitename; ?>" />
						</td>
					</tr>
					<tr>
						<td>
							<?php echo JText::_( 'Default WYSIWYG Editor' ); ?>:
						</td>
						<td>
							<?php echo $lists['editor']; ?>
						</td>
					</tr>
					<tr>
						<td>
							<span class="editlinktip">
							<?php
							$tip = JText::_( 'TIPSETSDEFAULTLENGTHLISTS' );
							echo mosToolTip( $tip, '', 280, 'tooltip.png', JText::_( 'List Length' ), '', 0 );
							?>
							:				
							</span>
						</td>
						<td>
							<?php echo $lists['list_limit']; ?>
						</td>
					</tr>
					<tr>
						<td>
							<?php echo JText::_( 'Help Server' ); ?>:
						</td>
						<td>
							<?php echo $lists['helpsites']; ?>
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
						<td width="185">
							<?php echo JText::_( 'Secret Word' ); ?>:
						</td>
						<td>
							<strong><?php echo $row->secret; ?></strong>
						</td>
					</tr>
					<tr>
						<td>
							<span class="editlinktip">
							<?php
							$tip = JText::_( 'Compress buffered output if supported' );
							echo mosToolTip( $tip, '', 280, 'tooltip.png', JText::_( 'GZIP Page Compression' ), '', 0 );
							?>
							:				
							</span>
						</td>
						<td>
							<?php echo $lists['gzip']; ?>
						</td>
					</tr>
					<tr>
						<td>
							<span class="editlinktip">
							<?php
							$tip = JText::_( 'TIPAUTOLOGOUTTIMEOF' );
							echo mosToolTip( $tip, '', 280, 'tooltip.png', JText::_( 'Login Session Lifetime' ), '', 0 );
							?>
							:				
							</span>
						</td>
						<td>
							<input class="text_area" type="text" name="lifetime" size="10" value="<?php echo $row->lifetime; ?>" />
							&nbsp;<?php echo JText::_('seconds'); ?>&nbsp;
						</td>
					</tr>
					<tr>
						<td>
							<?php echo JText::_( 'Error Reporting' ); ?>:
						</td>
						<td>
							<?php echo $lists['error_reporting']; ?>
						</td>
					</tr>
					<tr>
						<td>
							<?php echo JText::_( 'Enable XML-RPC' ); ?>:
						</td>
						<td>
							<?php echo $lists['xmlrpc_server']; ?>
						</td>
					</tr>
					</table>
					
				<?php
				$title = JText::_( 'Users' );
				$tabs->endTab();
				$tabs->startTab( $title, "Users-page" );
				?>
				
					<table class="adminform">
					<tr>
						<td width="185">
							<span class="editlinktip">
							<?php
							$tip = JText::_( 'If yes, allows users to self-register' );
							echo mosToolTip( $tip, '', 280, 'tooltip.png', JText::_( 'Allow User Registration' ), '', 0 );
							?>
							:				
							</span>
						</td>
						<td>
							<?php echo $lists['allowUserRegistration']; ?>
						</td>
					</tr>
					<tr>
						<td>
							<span class="editlinktip">
							<?php
							$tip = JText::_( 'TIPIFYESUSERMAILEDLINK' );
							echo mosToolTip( $tip, '', 280, 'tooltip.png', JText::_( 'Use New Account Activation' ), '', 0 );
							?>
							:				
							</span>
						</td>
						<td>
							<?php echo $lists['useractivation']; ?>
						</td>
					</tr>
					<tr>
						<td>
							<span class="editlinktip">
							<?php
							$tip = JText::_( 'TIPIFYESUSERSCANNOTSHAREEMAIL' );
							echo mosToolTip( $tip, '', 280, 'tooltip.png', JText::_( 'Require Unique Email' ), '', 0 );
							?>
							:				
							</span>
						</td>
						<td>
							<?php echo $lists['uniquemail']; ?>
						</td>
					</tr>
					<tr>
						<td>
							<span class="editlinktip">
							<?php
							$tip = JText::_( 'TIPLINKS' );
							echo mosToolTip( $tip, '', 280, 'tooltip.png', JText::_( 'Show UnAuthorized Links' ), '', 0 );
							?>
							:				
							</span>
						</td>
						<td>
							<?php echo $lists['shownoauth']; ?>
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
						<td width="50%">
							<table class="adminform">
							<tr>
								<td colspan="2">
									<?php echo JText::_( 'DESCCONTROLOUTPUTELEMENTS' ); ?>
									<br /><br />
								</td>
							</tr>
							<tr>
								<td width="185">
									<span class="editlinktip">
									<?php
									$tip = JText::_( 'TIPIFYESTITLECONTENTITEMS' );
									echo mosToolTip( $tip, '', 280, 'tooltip.png', JText::_( 'Linked Titles' ), '', 0 );
									?>
									:				
									</span>
								</td>
								<td>
									<?php echo $lists['link_titles']; ?>
								</td>
							</tr>
							<tr>
								<td>
									<span class="editlinktip">
									<?php
									$tip = JText::_( 'TIPIFSETTOSHOWREADMORELINK' );
									echo mosToolTip( $tip, '', 280, 'tooltip.png', JText::_( 'Read More Link' ), '', 0 );
									?>
									:				
									</span>
								</td>
								<td>
									<?php echo $lists['readmore']; ?>
								</td>
							</tr>
							<tr>
								<td>
									<span class="editlinktip">
									<?php
									$tip = JText::_( 'TIPIFSETTOSHOWVOTING' );
									echo mosToolTip( $tip, '', 280, 'tooltip.png', JText::_( 'Item Rating/Voting' ), '', 0 );
									?>
									:				
									</span>
								</td>
								<td>
									<?php echo $lists['vote']; ?>
								</td>
							</tr>
							<tr>
								<td>
									<span class="editlinktip">
									<?php
									$tip = JText::_( 'TIPIFSETTOSHOWAUTHOR' );
									echo mosToolTip( $tip, '', 280, 'tooltip.png', JText::_( 'Author Names' ), '', 0 );
									?>
									:				
									</span>
								</td>
								<td>
									<?php echo $lists['hideAuthor']; ?>
								</td>
							</tr>
							<tr>
								<td>
									<span class="editlinktip">
									<?php
									$tip = JText::_( 'TIPIFSETTOSHOWDATETIMECREATED' );
									echo mosToolTip( $tip, '', 280, 'tooltip.png', JText::_( 'Created Date and Time' ), '', 0 );
									?>
									:				
									</span>
								</td>
								<td>
									<?php echo $lists['hideCreateDate']; ?>
								</td>
							</tr>
							<tr>
								<td>
									<span class="editlinktip">
									<?php
									$tip = JText::_( 'TIPIFSETTOSHOWDATETIMEMODIFIED' );
									echo mosToolTip( $tip, '', 280, 'tooltip.png', JText::_( 'Modified Date and Time' ), '', 0 );
									?>
									:				
									</span>
								</td>
								<td>
									<?php echo $lists['hideModifyDate']; ?>
								</td>
							</tr>
							<tr>
								<td colspan="2">
									<span class="editlinktip">
									<?php
									$tip = JText::_( 'TIPPAGENAV' );
									echo mosToolTip( $tip, '', 280, 'tooltip.png', JText::_( 'Content Item Navigation' ), 'index2.php?option=com_plugins&client=site&task=editA&hidemainmenu=1&id=25' );
									?>
									:				
									</span>
								</td>
							</tr>
							</table>			
						</td>
						<td width="50%">
							<table class="adminform">
							<tr>
								<td colspan="2">
									&nbsp;
									<br /><br />
								</td>
							</tr>
							<tr>
								<td width="185">
									<?php echo JText::_( 'PDF Icon' ); ?>:
								</td>
								<td>
									<?php echo $lists['hidePdf']; ?>
									<?php
									if (!is_writable( JPATH_SITE . '/media/' )) {
					                    $tip = JText::_( 'TIPOPTIONMEDIA' );
										echo mosToolTip( $tip );
									}
									?>
								</td>
							</tr>
							<tr>
								<td>
									<?php echo JText::_( 'Print Icon' ); ?>:
								</td>
								<td>
									<?php echo $lists['hidePrint']; ?>
								</td>
							</tr>
							<tr>
								<td>
									<?php echo JText::_( 'Email Icon' ); ?>:
								</td>
								<td>
									<?php echo $lists['hideEmail']; ?>
								</td>
							</tr>
							<tr>
								<td>
									<span class="editlinktip">
									<?php
									$tip = JText::_( 'TIPPRINTPDFEMAIL' );
									echo mosToolTip( $tip, '', 280, 'tooltip.png', JText::_( 'Icons' ), '', 0 );
									?>
									:				
									</span>
								</td>
								<td>
									<?php echo $lists['icons']; ?>
				                </td>
							</tr>
							<tr>
								<td>
									<?php echo JText::_( 'Back Button' ); ?>:
								</td>
								<td>
									<?php echo $lists['back_button']; ?>
								</td>
							</tr>
							<tr>
								<td>
									<span class="editlinktip">
									<?php
									$tip = JText::_( 'TIPIFSETTOSHOWHITS' );
									echo mosToolTip( $tip, '', 280, 'tooltip.png', JText::_( 'Hits' ), '', 0 );
									?>
									:				
									</span>
								</td>
								<td>
									<?php echo $lists['hits']; ?>
								</td>
							</tr>
							<tr>
								<td colspan="2">
									<span class="editlinktip">
									<?php
									$tip = JText::_( 'TIPTOC' );
									echo mosToolTip( $tip, '', 280, 'tooltip.png', JText::_( 'Table of Contents on multi-page items' ), 'index2.php?option=com_plugins&client=site&task=editA&hidemainmenu=1&id=2' );
									?>
									:				
									</span>
								</td>
							</tr>
							</table>
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
						<td width="185">
							<span class="editlinktip">
							<?php
							$tip = JText::_( 'Current date/time configured to display' ) .': '. mosCurrentDate( JText::_( '_DATE_FORMAT_LC2' ) );
							echo mosToolTip( $tip, '', 280, 'tooltip.png', JText::_( 'Time Offset' ), '', 0 );
							?>
							:				
							</span>
						</td>
						<td>
							<?php echo $lists['offset']; ?>
						</td>
					</tr>
					<tr>
						<td width="185">
							<?php echo JText::_( 'Server Offset' ); ?>:
						</td>
						<td>
							<input class="text_area" type="text" name="offset" size="15" value="<?php echo $row->offset; ?>" disabled="disabled" />
						</td>
					</tr>
					</table>
		
				<?php
				$title = JText::_( 'Metadata' );
				$tabs->endTab();
				$tabs->startTab( $title, "metadata-page" );
				?>
		
					<table class="adminform">
					<tr>
						<td width="185" valign="top">
							<?php echo JText::_( 'Global Site Meta Description' ); ?>:
						</td>
						<td>
							<textarea class="text_area" cols="50" rows="3" style="width:500px; height:50px" name="MetaDesc"><?php echo htmlspecialchars($row->MetaDesc, ENT_QUOTES); ?></textarea>
						</td>
					</tr>
					<tr>
						<td valign="top">
							<?php echo JText::_( 'Global Site Meta Keywords' ); ?>:
						</td>
						<td>
							<textarea class="text_area" cols="50" rows="3" style="width:500px; height:50px" name="MetaKeys"><?php echo htmlspecialchars($row->MetaKeys, ENT_QUOTES); ?></textarea>
						</td>
					</tr>
					<tr>
						<td valign="top">
							<span class="editlinktip">
							<?php
							$tip = JText::_( 'TIPSHOWTITLEMETATAGITEMS' );
							echo mosToolTip( $tip, '', 280, 'tooltip.png', JText::_( 'Show Title Meta Tag' ), '', 0 );
							?>
							:				
							</span>
						</td>
						<td>
							<?php echo $lists['MetaTitle']; ?>
						</td>
				  	</tr>
					<tr>
						<td valign="top">
							<span class="editlinktip">
							<?php
							$tip = JText::_( 'TIPSHOWAUTHORMETATAGITEMS' );
							echo mosToolTip( $tip, '', 280, 'tooltip.png', JText::_( 'Show Author Meta Tag' ), '', 0 );
							?>
							:				
							</span>
						</td>
						<td>
							<?php echo $lists['MetaAuthor']; ?>
						</td>
					</tr>
					</table>
		
				<?php
				$title = JText::_( 'SEO' );
				$tabs->endTab();
				$tabs->startTab( $title, "seo-page" );
				?>
		
					<table class="adminform">
					<tr>
						<td colspan="2">
							<strong><?php echo JText::_( 'Search Engine Optimization Settings' ); ?></strong>
						</td>
					</tr>
					<tr>
						<td width="185">
							<?php echo JText::_( 'Search Engine Friendly URLs' ); ?>:
						</td>
						<td>
							<?php echo $lists['sef']; ?>
							<span class="error">
							<?php
			                $tip = JText::_( 'WARNAPACHEONLY', true );
			                echo mosHTML::WarningIcon( $tip ); 
			                ?>
		               		</span>
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
						<td width="185">
							<?php echo JText::_( 'Mailer' ); ?>:
						</td>
						<td>
							<?php echo $lists['mailer']; ?>
						</td>
					</tr>
					<tr>
						<td>
							<?php echo JText::_( 'Mail From' ); ?>:
						</td>
						<td>
							<input class="text_area" type="text" name="mailfrom" size="50" value="<?php echo $row->mailfrom; ?>" />
						</td>
					</tr>
					<tr>
						<td>
							<?php echo JText::_( 'From Name' ); ?>:
						</td>
						<td>
							<input class="text_area" type="text" name="fromname" size="50" value="<?php echo $row->fromname; ?>" />
						</td>
					</tr>
					<tr>
						<td>
							<?php echo JText::_( 'Sendmail Path' ); ?>:
						</td>
						<td>
							<input class="text_area" type="text" name="sendmail" size="50" value="<?php echo $row->sendmail; ?>" />
						</td>
					</tr>
					<tr>
						<td>
							<?php echo JText::_( 'SMTP Auth' ); ?>:
						</td>
						<td>
							<?php echo $lists['smtpauth']; ?>
						</td>
					</tr>
					<tr>
						<td>
							<?php echo JText::_( 'SMTP User' ); ?>:
						</td>
						<td>
							<input class="text_area" type="text" name="smtpuser" size="50" value="<?php echo $row->smtpuser; ?>" />
						</td>
					</tr>
					<tr>
						<td>
							<?php echo JText::_( 'SMTP Pass' ); ?>:
						</td>
						<td>
							<input class="text_area" type="text" name="smtppass" size="50" value="<?php echo $row->smtppass; ?>" />
						</td>
					</tr>
					<tr>
						<td>
							<?php echo JText::_( 'SMTP Host' ); ?>:
						</td>
						<td>
							<input class="text_area" type="text" name="smtphost" size="50" value="<?php echo $row->smtphost; ?>" />
						</td>
					</tr>
					</table>
		
				<?php
				$title = JText::_( 'FTP' );
				$tabs->endTab();
				$tabs->startTab( $title, "ftp-page" );
				?>
		
					<table class="adminform">
					<tr>
						<td width="185">
							<?php echo JText::_( 'Enable FTP' ); ?>:
						</td>
						<td>
							<?php echo $lists['enable_ftp']; ?>
						</td>
					</tr>
					<tr>
						<td>
							<?php echo JText::_( 'FTP Host' ); ?>:
						</td>
						<td>
							<input class="text_area" type="text" name="ftp_host" size="25" value="<?php echo $row->ftp_host; ?>" />
						</td>
					</tr>
					<tr>
						<td>
							<?php echo JText::_( 'FTP Port' ); ?>:
						</td>
						<td>
							<input class="text_area" type="text" name="ftp_port" size="25" value="<?php echo $row->ftp_port; ?>" />
						</td>
					</tr>
					<tr>
						<td>
							<?php echo JText::_( 'FTP Username' ); ?>:
						</td>
						<td>
							<input class="text_area" type="text" name="ftp_user" size="25" value="<?php echo $row->ftp_user; ?>" />
						</td>
					</tr>
					<tr>
						<td>
							<?php echo JText::_( 'FTP Password' ); ?>:
						</td>
						<td>
							<input class="text_area" type="password" name="ftp_pass" size="25" value="<?php echo $row->ftp_pass; ?>" />
						</td>
					</tr>
					<tr>
						<td>
							<?php echo JText::_( 'FTP Root' ); ?>:
						</td>
						<td>
							<input class="text_area" type="text" name="ftp_root" size="50" value="<?php echo $row->ftp_root; ?>" />
						</td>
					</tr>
					</table>
		
				<?php
				$title = JText::_( 'Database' );
				$tabs->endTab();
				$tabs->startTab( $title, "db-page" );
				?>
		
					<table class="adminform">
					<tr>
						<td width="185">
							<?php echo JText::_( 'Database type' ); ?>:
						</td>
						<td>
							<input class="text_area" type="text" name="dbtype" size="25" value="<?php echo $row->dbtype; ?>" />
						</td>
					</tr>
					<tr>
						<td width="185">
							<?php echo JText::_( 'Hostname' ); ?>:
						</td>
						<td>
							<input class="text_area" type="text" name="host" size="25" value="<?php echo $row->host; ?>" />
						</td>
					</tr>
					<tr>
						<td>
							<?php echo JText::_( 'Username' ); ?>:
						</td>
						<td>
							<input class="text_area" type="text" name="user" size="25" value="<?php echo $row->user; ?>" />
						</td>
					</tr>
					<tr>
						<td>
							<?php echo JText::_( 'Database' ); ?>:
						</td>
						<td>
							<input class="text_area" type="text" name="db" size="25" value="<?php echo $row->db; ?>" />
						</td>
					</tr>
					<tr>
						<td>
							<?php echo JText::_( 'Database Prefix' ); ?>:
						</td>
						<td>
							<input class="text_area" type="text" name="dbprefix" size="10" value="<?php echo $row->dbprefix; ?>" />
							&nbsp;
							<?php
			                $warn = JText::_( 'WARNDONOTCHANGEDATABASETABLESPREFIX', true );
			                echo mosHTML::WarningIcon( $warn ); 
			                ?>
						</td>
					</tr>
					</table>
		
				<?php
				$title = JText::_( 'Statistics' );
				$tabs->endTab();
				$tabs->startTab( $title, "stats-page" );
				?>
		
					<table class="adminform">
					<tr>
						<td width="185">
							<span class="editlinktip">
							<?php
							$tip = JText::_( 'TIPENABLEDISABLESTATS' );
							echo mosToolTip( $tip, '', 280, 'tooltip.png', JText::_( 'Statistics' ), '', 0 );
							?>
							:				
							</span>
						</td>
						<td>
							<?php echo $lists['enable_stats']; ?>
						</td>
					</tr>
					<tr>
						<td>
							<?php echo JText::_( 'Log Content Hits by Date' ); ?>:
						</td>
						<td>
							<?php echo $lists['log_items']; ?>
							<span class="error">
							<?php
			                $warn = JText::_( 'TIPLARGEAMOUNTSOFDATA', true );
			                echo mosHTML::WarningIcon( $warn ); 
			                ?>
							</span>
						</td>
					</tr>
					<tr>
						<td>
							<?php echo JText::_( 'Log Search Strings' ); ?>:
						</td>
						<td>
							<?php echo $lists['log_searches']; ?>
						</td>
					</tr>
					</table>
		
				<?php
				$title = JText::_( 'Cache' );
				$tabs->endTab();
				$tabs->startTab( $title, "cache-page" );
				?>
		
					<table class="adminform" border="0">
					<?php
					if (is_writeable($row->cachepath)) {
						?>
						<tr>
							<td>
								<?php echo JText::_( 'Caching' ); ?>:
							</td>
							<td>
								<?php echo $lists['caching']; ?>
							</td>
						</tr>
						<?php
					}
					?>
					<tr>
						<td width="185">
							<span class="editlinktip">
							<?php
							if (is_writeable($row->cachepath)) {
								$tip = JText::_( 'TIPDIRWRITEABLE' );
							}
							echo mosToolTip( $tip, '', 280, 'tooltip.png', JText::_( 'Cache Folder' ), '', 0 );
							?>
							:				
							</span>
						</td>
						<td>
							<input class="text_area" type="text" name="cachepath" size="50" value="<?php echo $row->cachepath; ?>" />
							<?php
							if (!is_writeable($row->cachepath)) {
								$warn = JText::_( 'TIPCACHEDIRISUNWRITEABLE', true );
								echo mosHTML::WarningIcon( $warn );
							}
							?>
						</td>
					</tr>
					<tr>
						<td>
							<?php echo JText::_( 'Cache Time' ); ?>:
						</td>
						<td>
							<input class="text_area" type="text" name="cachetime" size="5" value="<?php echo $row->cachetime; ?>" /> 
							<?php echo JText::_( 'seconds' ); ?>
						</td>
					</tr>
					</table>
		
				<?php
				$title = JText::_( 'Debug' );
				$tabs->endTab();
				$tabs->startTab( $title, "Debug-page" );
				?>
				
					<table class="adminform">
					<tr>
						<td width="185">
							<span class="editlinktip">
							<?php
							$tip = JText::_( 'TIPDEBUGGINGINFO' );
							echo mosToolTip( $tip, '', 280, 'tooltip.png', JText::_( 'Enable Debugging' ), '', 0 );
							?>
							:				
							</span>
						</td>
						<td>
							<?php echo $lists['debug']; ?>
						</td>
					</tr>
					<tr>
						<td>
							<?php echo JText::_( 'Debug Database' ); ?>:
						</td>
						<td>
							<?php echo $lists['debug_db']; ?>
						</td>
					</tr>
					<tr>
						<td>
							<span class="editlinktip">
							<?php
							$tip = JText::_( 'TIPLOGGINGINFO' );
							echo mosToolTip( $tip, '', 280, 'tooltip.png', JText::_( 'Enable Logging' ), '', 0 );
							?>
							:				
							</span>
						</td>
						<td>
							<?php echo $lists['log']; ?>
						</td>
					</tr>
					<tr>
						<td>
							<?php echo JText::_( 'Log Database' ); ?>:
						</td>
						<td>
							<?php echo $lists['log_db']; ?>
						</td>
					</tr>
					</table>
		
				<?php
				$tabs->endTab();
				$tabs->endPane();
				?>
			</fieldset>
		</div>

		<input type="hidden" name="option" value="<?php echo $option; ?>" />
		<?php
		/*
		<input type="hidden" name="admin_path" value="<?php echo $row->admin_path; ?>" />
		<input type="hidden" name="absolute_path" value="<?php echo $row->absolute_path; ?>" />
		<input type="hidden" name="live_site" value="<?php echo $row->live_site; ?>" />
		*/
		?>
		<input type="hidden" name="secret" value="<?php echo $row->secret; ?>" />
		<input type="hidden" name="multilingual_support" value="<?php echo $row->multilingual_support; ?>" />
	  	<input type="hidden" name="lang" value="<?php echo $row->lang; ?>" />
	  	<input type="hidden" name="lang_administrator" value="<?php echo $row->lang_administrator; ?>" />
	  	<input type="hidden" name="task" value="" />
		</form>
		<?php
	}
}
?>