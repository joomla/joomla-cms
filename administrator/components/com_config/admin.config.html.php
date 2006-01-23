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

	function showConfig( &$row, &$lists, $option) 
	{
		mosCommonHTML::loadOverlib();
		
		$tabs = new mosTabs(1);
		?>
		<form action="index2.php" method="post" name="adminForm">

		<table cellpadding="1" cellspacing="1" border="0" width="100%">
		<tr>
			<td width="270">
				<span class="componentheading"><?php echo JText::_( 'configuration.php is' ); ?> :
				<?php echo is_writable( '../configuration.php' ) ? '<b><font color="green"> '. JText::_( 'Writeable' ) .'</font></b>' : '<b><font color="red"> '. JText::_( 'Unwriteable' ) .'</font></b>' ?>
				</span>
			</td>
			<?php
			if (JPath::canCHMOD('../configuration.php')) {
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
				<td width="185">
				<?php echo JText::_( 'Site Offline' ); ?>:
				</td>
				<td>
				<?php echo $lists['offline']; ?>
				</td>
			</tr>
			<tr>
				<td valign="top">
				<?php echo JText::_( 'Offline Message' ); ?>:
				</td>
				<td>
				<textarea class="text_area" cols="60" rows="2" style="width:500px; height:40px" name="offline_message"><?php echo htmlspecialchars( stripslashes( $row->offline_message ), ENT_QUOTES); ?></textarea>
				<?php
				$tip = JText::_( 'TIPIFYOURSITEISOFFLINE' );
				echo mosToolTip( $tip );
				?>
				</td>
			</tr>
			<tr>
				<td valign="top">
				<?php echo JText::_( 'System Error Message' ); ?>:
				</td>
				<td>
				<textarea class="text_area" cols="60" rows="2" style="width:500px; height:40px" name="error_message"><?php echo htmlspecialchars( stripslashes( $row->error_message ), ENT_QUOTES); ?></textarea>
				<?php
				$tip = JText::_( 'TIPCOULDNOTCONNECTDB' );
				echo mosToolTip( $tip );
				?>
				</td>
			</tr>
			<tr>
				<td>
				<?php echo JText::_( 'Site Name' ); ?>:
				</td>
				<td>
				<input class="text_area" type="text" name="sitename" size="50" value="<?php echo $row->sitename; ?>"/>
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
				<?php echo JText::_( 'List Length' ); ?>:
				</td>
				<td>
				<?php echo $lists['list_limit']; ?>
				<?php
				$tip = JText::_( 'TIPSETSDEFAULTLENGTHLISTS' );
				echo mosToolTip( $tip );
				?>
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
				<?php echo JText::_( 'Absolute Path' ); ?>:
				</td>
				<td width="450">
				<strong><?php echo $row->absolute_path; ?></strong>
				</td>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<td>
				<?php echo JText::_( 'Live Site' ); ?>:
				</td>
				<td>
				<strong><?php echo $row->live_site; ?></strong>
				</td>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<td>
				<?php echo JText::_( 'Secure Site' ); ?>:
				</td>
				<td>
				<input class="text_area" type="text" name="secure_site" size="50" value="<?php echo $row->secure_site; ?>"/>
				<?php
                $tip = JText::_( 'TIPSECURESITE' );
                echo mosToolTip( $tip ); 
				?>
				</td>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<td>
				<?php echo JText::_( 'Secret Word' ); ?>:
				</td>
				<td>
				<strong><?php echo $row->secret; ?></strong>
				</td>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<td>
				<?php echo JText::_( 'GZIP Page Compression' ); ?>:
				</td>
				<td>
				<?php echo $lists['gzip']; ?>
				<?php
                $tip = JText::_( 'Compress buffered output if supported' );
                echo mosToolTip( $tip ); 
				?>
				</td>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<td>
				<?php echo JText::_( 'Login Session Lifetime' ); ?>:
				</td>
				<td>
				<input class="text_area" type="text" name="lifetime" size="10" value="<?php echo $row->lifetime; ?>"/>
				&nbsp;<?php echo JText::_('seconds'); ?>&nbsp;
				<?php
                $tip = JText::_( 'TIPAUTOLOGOUTTIMEOF' );
                echo mosToolTip( $tip ); 
				?>
				</td>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<td>
				<?php echo JText::_( 'Error Reporting' ); ?>:
				</td>
				<td>
				<?php echo $lists['error_reporting']; ?>
				</td>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<td>
				<?php echo JText::_( 'Enable XML-PRC' ); ?>:
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
				<?php echo JText::_( 'Allow User Registration' ); ?>:
				</td>
				<td>
				<?php echo $lists['allowUserRegistration']; ?>
				<?php
				$tip = JText::_( 'If yes, allows users to self-register' );
				echo mosToolTip( $tip );
				?>
				</td>
			</tr>
			<tr>
				<td>
				<?php echo JText::_( 'Use New Account Activation' ); ?>:
				</td>
				<td>
				<?php echo $lists['useractivation']; ?>
				<?php
				$tip = JText::_( 'TIPIFYESUSERMAILEDLINK' );
				echo mosToolTip( $tip );
				?>
				</td>
			</tr>
			<tr>
				<td>
				<?php echo JText::_( 'Require Unique Email' ); ?>:
				</td>
				<td>
				<?php echo $lists['uniquemail']; ?>
				<?php
				$tip = JText::_( 'TIPIFYESUSERSCANNOTSHAREEMAIL' );
				echo mosToolTip( $tip );
				?>
				</td>
			</tr>
			<tr>
				<td>
				<?php echo JText::_( 'Show UnAuthorized Links' ); ?>:
				</td>
				<td>
				<?php echo $lists['shownoauth']; ?>
				<?php
				$tip = JText::_( 'TIPLINKS' );
				echo mosToolTip( $tip );
				?>
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
				<td>
					<table class="adminform">
					<tr>
						<td colspan="2">
						<?php echo JText::_( 'DESCCONTROLOUTPUTELEMENTS' ); ?>
						<br /><br />
						</td>
					</tr>
					<tr>
						<td width="185">
						<?php echo JText::_( 'Linked Titles' ); ?>:
						</td>
						<td>
						<?php echo $lists['link_titles']; ?>
						<?php
						$tip = JText::_( 'TIPIFYESTITLECONTENTITEMS' );
						echo mosToolTip( $tip );
						?>
						</td>
					</tr>
					<tr>
						<td>
						<?php echo JText::_( 'Read More Link' ); ?>:
						</td>
						<td>
						<?php echo $lists['readmore']; ?>
						<?php
						$tip = JText::_( 'TIPIFSETTOSHOWREADMORELINK' );
						echo mosToolTip( $tip );
						?>
						</td>
					</tr>
					<tr>
						<td>
						<?php echo JText::_( 'Item Rating/Voting' ); ?>:
						</td>
						<td>
						<?php echo $lists['vote']; ?>
						<?php
						$tip = JText::_( 'TIPIFSETTOSHOWVOTING' );
						echo mosToolTip( $tip );
						?>
						</td>
					</tr>
					<tr>
						<td>
						<?php echo JText::_( 'Author Names' ); ?>:
						</td>
						<td>
						<?php echo $lists['hideAuthor']; ?>
						<?php
						$tip = JText::_( 'TIPIFSETTOSHOWAUTHOR' );
						echo mosToolTip( $tip );
						?>
						</td>
					</tr>
					<tr>
						<td>
						<?php echo JText::_( 'Created Date and Time' ); ?>:
						</td>
						<td>
						<?php echo $lists['hideCreateDate']; ?>
						<?php
						$tip = JText::_( 'TIPIFSETTOSHOWDATETIMECREATED' );
						echo mosToolTip( $tip );
						?>
						</td>
					</tr>
					<tr>
						<td>
						<?php echo JText::_( 'Modified Date and Time' ); ?>:
						</td>
						<td>
						<?php echo $lists['hideModifyDate']; ?>
						<?php
						$tip = JText::_( 'TIPIFSETTOSHOWDATETIMEMODIFIED' );
						echo mosToolTip( $tip );
						?>
						</td>
					</tr>
					<tr>
						<td>
						<?php echo JText::_( 'Hits' ); ?>:
						</td>
						<td>
						<?php echo $lists['hits']; ?>
						<?php
						$tip = JText::_( 'TIPIFSETTOSHOWHITS' );
						echo mosToolTip( $tip );
						?>
						</td>
					</tr>
					</table>			
				</td>
				<td>
					<table class="adminform">
					<tr>
						<td colspan="2">
						&nbsp;
						<br /><br />
						</td>
					</tr>
					<tr>
						<td>
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
						<?php echo JText::_( 'Icons' ); ?>:
						</td>
						<td>
						<?php echo $lists['icons']; ?>
						<?php
		                $tip = JText::_( 'TIPPRINTPDFEMAIL' );
		                echo mosToolTip( $tip ); ?>
		                </td>
					</tr>
					<tr>
						<td>
						<?php echo JText::_( 'Table of Contents on multi-page items' ); ?>:
						</td>
						<td>
						<?php echo $lists['multipage_toc']; ?>
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
						<?php echo JText::_( 'Content Item Navigation' ); ?>:
						</td>
						<td>
						<?php echo $lists['item_navigation']; ?>
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
				<?php echo JText::_( 'Time Offset' ); ?>:
				</td>
				<td>
				<?php echo $lists['offset']; ?>
				<?php
				$tip = JText::_( 'Current date/time configured to display' ) .': '. mosCurrentDate( JText::_( '_DATE_FORMAT_LC2' ) );
				echo mosToolTip( $tip );
				?>
				</td>
			</tr>
			<tr>
				<td width="185">
				<?php echo JText::_( 'Server Offset' ); ?>:
				</td>
				<td>
				<input class="text_area" type="text" name="offset" size="15" value="<?php echo $row->offset; ?>" disabled="true"/>
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
				<?php echo JText::_( 'Show Title Meta Tag' ); ?>:
				</td>
				<td>
				<?php echo $lists['MetaTitle']; ?>
				&nbsp;&nbsp;&nbsp;
				<?php
                $tip = JText::_( 'TIPSHOWTITLEMETATAGITEMS' );
                echo mosToolTip( $tip ); 
                ?>
				</td>
		  	</tr>
			<tr>
				<td valign="top">
				<?php echo JText::_( 'Show Author Meta Tag' ); ?>:
				</td>
				<td>
				<?php echo $lists['MetaAuthor']; ?>
				&nbsp;&nbsp;&nbsp;
				<?php
                $tip = JText::_( 'TIPSHOWAUTHORMETATAGITEMS' );
                echo mosToolTip( $tip ); 
                ?>
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
                $tip = JText::_( 'WARNAPACHEONLY' );
                echo JWarning( $tip ); 
                ?>
                </span>
                </td>
			</tr>
			<tr>
				<td>
				<?php echo JText::_( 'Dynamic Page Titles' ); ?>:
				</td>
				<td>
				<?php echo $lists['pagetitles']; ?>
				<?php 				
                $tip = JText::_( 'TIPDYNAMICALLYCHANGESPAGETITLE' );
                echo mosToolTip( $tip ); 
                ?>
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
				<input class="text_area" type="text" name="mailfrom" size="50" value="<?php echo $row->mailfrom; ?>"/>
				</td>
			</tr>
			<tr>
				<td>
				<?php echo JText::_( 'From Name' ); ?>:
				</td>
				<td>
				<input class="text_area" type="text" name="fromname" size="50" value="<?php echo $row->fromname; ?>"/>
				</td>
			</tr>
			<tr>
				<td>
				<?php echo JText::_( 'Sendmail Path' ); ?>:
				</td>
				<td>
				<input class="text_area" type="text" name="sendmail" size="50" value="<?php echo $row->sendmail; ?>"/>
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
				<input class="text_area" type="text" name="smtpuser" size="50" value="<?php echo $row->smtpuser; ?>"/>
				</td>
			</tr>
			<tr>
				<td>
				<?php echo JText::_( 'SMTP Pass' ); ?>:
				</td>
				<td>
				<input class="text_area" type="text" name="smtppass" size="50" value="<?php echo $row->smtppass; ?>"/>
				</td>
			</tr>
			<tr>
				<td>
				<?php echo JText::_( 'SMTP Host' ); ?>:
				</td>
				<td>
				<input class="text_area" type="text" name="smtphost" size="50" value="<?php echo $row->smtphost; ?>"/>
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
				<input class="text_area" type="text" name="ftp_host" size="25" value="<?php echo $row->ftp_host; ?>"/>
				</td>
			</tr>
			<tr>
				<td>
				<?php echo JText::_( 'FTP Username' ); ?>:
				</td>
				<td>
				<input class="text_area" type="text" name="ftp_user" size="25" value="<?php echo $row->ftp_user; ?>"/>
				</td>
			</tr>
			<tr>
				<td>
				<?php echo JText::_( 'FTP Password' ); ?>:
				</td>
				<td>
				<input class="text_area" type="password" name="ftp_pass" size="25" value="<?php echo $row->ftp_pass; ?>"/>
				</td>
			</tr>
			<tr>
				<td>
				<?php echo JText::_( 'FTP Root' ); ?>:
				</td>
				<td>
				<input class="text_area" type="text" name="ftp_root" size="50" value="<?php echo $row->ftp_root; ?>"/>
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
				<input class="text_area" type="text" name="dbtype" size="25" value="<?php echo $row->dbtype; ?>"/>
				</td>
			</tr>
			<tr>
				<td width="185">
				<?php echo JText::_( 'Hostname' ); ?>:
				</td>
				<td>
				<input class="text_area" type="text" name="host" size="25" value="<?php echo $row->host; ?>"/>
				</td>
			</tr>
			<tr>
				<td>
				<?php echo JText::_( 'Username' ); ?>:
				</td>
				<td><input class="text_area" type="text" name="user" size="25" value="<?php echo $row->user; ?>"/>
				</td>
			</tr>
			<tr>
				<td>
				<?php echo JText::_( 'Database' ); ?>:
				</td>
				<td>
				<input class="text_area" type="text" name="db" size="25" value="<?php echo $row->db; ?>"/>
				</td>
			</tr>
			<tr>
				<td>
				<?php echo JText::_( 'Database Prefix' ); ?>:
				</td>
				<td>
				<input class="text_area" type="text" name="dbprefix" size="10" value="<?php echo $row->dbprefix; ?>"/>
				&nbsp;
				<?php
                $warn = JText::_( 'WARNDONOTCHANGEDATABASETABLESPREFIX' );
                echo JWarning( $warn ); 
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
				<?php echo JText::_( 'Statistics' ); ?>:
				</td>
				<td width="100">
				<?php echo $lists['enable_stats']; ?>
				</td>
				<td>
				<?php
                $tip = JText::_( 'TIPENABLEDISABLESTATS' );
                echo mosToolTip( $tip ); 
                ?>
                </td>
			</tr>
			<tr>
				<td>
				<?php echo JText::_( 'Log Content Hits by Date' ); ?>:
				</td>
				<td>
				<?php echo $lists['log_items']; ?>
				</td>
				<td>
				<span class="error">
				<?php
                $warn = JText::_( 'TIPLARGEAMOUNTSOFDATA' );
                echo JWarning( $warn ); 
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
				<td>&nbsp;</td>
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
					<td>&nbsp;</td>
				</tr>
				<?php
			}
			?>
			<tr>
				<td width="185">
				<?php echo JText::_( 'Cache Folder' ); ?>:
				</td>
				<td>
				<input class="text_area" type="text" name="cachepath" size="50" value="<?php echo $row->cachepath; ?>"/>
				<?php
				if (is_writeable($row->cachepath)) {
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
				<td>
				<?php echo JText::_( 'Cache Time' ); ?>:
				</td>
				<td>
				<input class="text_area" type="text" name="cachetime" size="5" value="<?php echo $row->cachetime; ?>"/> 
				<?php echo JText::_( 'seconds' ); ?>
				</td>
				<td>&nbsp;</td>
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
				<?php echo JText::_( 'Enable Debugging' ); ?>:
				</td>
				<td>
				<?php echo $lists['debug']; ?>
				<?php
				$tip = JText::_( 'TIPDEBUGGINGINFO' );
				echo mosToolTip( $tip );
				?>
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
				<?php echo JText::_( 'Enable Logging' ); ?>:
				</td>
				<td>
				<?php echo $lists['log']; ?>
				<?php
				$tip = JText::_( 'TIPLOGGINGINFO' );
				echo mosToolTip( $tip );
				?>
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

		<input type="hidden" name="option" value="<?php echo $option; ?>"/>
		<input type="hidden" name="admin_path" value="<?php echo $row->admin_path; ?>"/>
		<input type="hidden" name="absolute_path" value="<?php echo $row->absolute_path; ?>"/>
		<input type="hidden" name="live_site" value="<?php echo $row->live_site; ?>"/>
		<input type="hidden" name="secret" value="<?php echo $row->secret; ?>"/>
		<input type="hidden" name="multilingual_support" value="<?php echo $row->multilingual_support; ?>"/>
	  	<input type="hidden" name="lang" value="<?php echo $row->lang; ?>"/>
	  	<input type="hidden" name="lang_administrator" value="<?php echo $row->lang_administrator; ?>"/>
	  	<input type="hidden" name="task" value=""/>
		</form>
		<?php
	}
}
?>