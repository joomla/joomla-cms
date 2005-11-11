<?php
/**
* @version $Id$
* @package Joomla
* @subpackage Admin
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
* @subpackage Admin
*/
class HTML_admin_misc {

	/**
	* Control panel
	*/
	function controlPanel() {
		global $mainframe;
		
		$path = JPATH_ADMINISTRATOR . '/templates/' . $mainframe->getTemplate() . '/cpanel.php';
		if (file_exists( $path )) {
			require $path;
		} else {
			echo '<br />';
			mosLoadAdminModules( 'cpanel', 1 );
		}
	}

	function get_php_setting($val) {
		global $_LANG;

		$r =  (ini_get($val) == '1' ? 1 : 0);
		return $r ? $_LANG->_( 'ON' ) : $_LANG->_( 'OFF' ) ;
	}

	function get_server_software() {
		global $_LANG;

		if (isset($_SERVER['SERVER_SOFTWARE'])) {
			return $_SERVER['SERVER_SOFTWARE'];
		} else if (($sf = getenv('SERVER_SOFTWARE'))) {
			return $sf;
		} else {
			return $_LANG->_( 'n/a' );
		}
	}

	function system_info( ) {
		global $mosConfig_absolute_path, $database, $_LANG, $_VERSION;
		
		$width = 400;	// width of 100%
		$tabs = new mosTabs(0);
		
		$title = $_LANG->_( 'System Info' );
		$tabs->startPane("sysinfo");
		$tabs->startTab( $title, "system-page" );
		?>
			<table class="adminform">
			<tr>
				<th colspan="2">
				<?php echo $_LANG->_( 'System Information' ); ?>
				</th>
			</tr>
			<tr>
				<td valign="top" width="250">
					<strong><?php echo $_LANG->_( 'PHP built On' ); ?>:</strong>
				</td>
				<td>
					<?php echo php_uname(); ?>
				</td>
			</tr>
			<tr>
				<td>
					<strong><?php echo $_LANG->_( 'Database Version' ); ?>:</strong>
				</td>
				<td>
					<?php echo $database->getVersion(); ?>
				</td>
			</tr>
			<tr>
				<td>
					<strong><?php echo $_LANG->_( 'PHP Version' ); ?>:</strong>
				</td>
				<td>
					<?php echo phpversion(); ?>
				</td>
			</tr>
			<tr>
				<td>
					<strong><?php echo $_LANG->_( 'Web Server' ); ?>:</strong>
				</td>
				<td>
					<?php echo HTML_admin_misc::get_server_software(); ?>
				</td>
			</tr>
			<tr>
				<td>
					<strong><?php echo $_LANG->_( 'WebServer to PHP interface' ); ?>:</strong>
				</td>
				<td>
					<?php echo php_sapi_name(); ?>
				</td>
			</tr>
			<tr>
				<td>
					<strong><?php echo $_LANG->_( 'Joomla! Version' ); ?>:</strong>
				</td>
				<td>
					<?php echo $_VERSION->getLongVersion() ?>
				</td>
			</tr>
			<tr>
				<td>
					<strong><?php echo $_LANG->_( 'User Agent' ); ?>:</strong>
				</td>
				<td>
					<?php echo phpversion() <= "4.2.1" ? getenv( "HTTP_USER_AGENT" ) : $_SERVER['HTTP_USER_AGENT'];?>
				</td>
			</tr>
			<tr>
				<td valign="top">
					<strong><?php echo $_LANG->_( 'Relevant PHP Settings' ); ?>:</strong>
				</td>
				<td>
					<table cellspacing="1" cellpadding="1" border="0">
					<tr>
						<td>
							<?php echo $_LANG->_( 'Safe Mode' ); ?>:
						</td>
						<td>
							<?php echo HTML_admin_misc::get_php_setting('safe_mode'); ?>
						</td>
					</tr>
					<tr>
						<td>
							<?php echo $_LANG->_( 'Open basedir' ); ?>:
						</td>
						<td>
							<?php echo (($ob = ini_get('open_basedir')) ? $ob : $_LANG->_( 'none' ) ); ?>
						</td>
					</tr>
					<tr>
						<td>
							<?php echo $_LANG->_( 'Display Errors' ); ?>:
						</td>
						<td>
							<?php echo HTML_admin_misc::get_php_setting('display_errors'); ?>
						</td>
					</tr>
					<tr>
						<td>
							<?php echo $_LANG->_( 'Short Open Tags' ); ?>:
						</td>
						<td>
							<?php echo HTML_admin_misc::get_php_setting('short_open_tag'); ?>
						</td>
					</tr>
					<tr>
						<td>
							<?php echo $_LANG->_( 'File Uploads' ); ?>:
						</td>
						<td>
							<?php echo HTML_admin_misc::get_php_setting('file_uploads'); ?>
						</td>
					</tr>
					<tr>
						<td>
							<?php echo $_LANG->_( 'Magic Quotes' ); ?>:
						</td>
						<td>
							<?php echo HTML_admin_misc::get_php_setting('magic_quotes_gpc'); ?>
						</td>
					</tr>
					<tr>
						<td>
							<?php echo $_LANG->_( 'Register Globals' ); ?>:
						</td>
						<td>
							<?php echo HTML_admin_misc::get_php_setting('register_globals'); ?>
						</td>
					</tr>
					<tr>
						<td>
							<?php echo $_LANG->_( 'Output Buffering' ); ?>:
						</td>
						<td>
							<?php echo HTML_admin_misc::get_php_setting('output_buffering'); ?>
						</td>
					</tr>
					<tr>
						<td>
							<?php echo $_LANG->_( 'Session save path' ); ?>:
						</td>
						<td>
							<?php echo (($sp=ini_get('session.save_path')) ? $sp : $_LANG->_( 'none' ) ); ?>
						</td>
					</tr>
					<tr>
						<td>
							<?php echo $_LANG->_( 'Session auto start' ); ?>:
						</td>
						<td>
							<?php echo intval( ini_get( 'session.auto_start' ) ); ?>
						</td>
					</tr>
					<tr>
						<td>
							<?php echo $_LANG->_( 'XML enabled' ); ?>:
						</td>
						<td>
						<?php echo extension_loaded('xml') ? $_LANG->_( 'Yes' ) : $_LANG->_( 'No' ); ?>
						</td>
					</tr>
					<tr>
						<td>
							<?php echo $_LANG->_( 'Zlib enabled' ); ?>:
						</td>
						<td>
							<?php echo extension_loaded('zlib') ? $_LANG->_( 'Yes' ) : $_LANG->_( 'No' ); ?>
						</td>
					</tr>
					<tr>
						<td>
							<?php echo $_LANG->_( 'Disabled Functions' ); ?>:
						</td>
						<td>
							<?php echo (($df=ini_get('disable_functions')) ? $df : $_LANG->_( 'none' ) ); ?>
						</td>
					</tr>
					<?php
					$query = "SELECT name FROM #__mambots"
					. "\nWHERE folder='editors' AND published='1'"
					. "\nLIMIT 1";
					$database->setQuery( $query );
					$editor = $database->loadResult();
					?>
					<tr>
						<td>
							<?php echo $_LANG->_( 'WYSIWYG Editor' ); ?>:
						</td>
						<td>
							<?php echo $editor; ?>
						</td>
					</tr>
					</table>
				</td>
			</tr>
			<tr>
				<td valign="top">
					<strong><?php echo $_LANG->_( 'Configuration File' ); ?>:</strong>
				</td>
				<td>
				<?php
				$cf = file( $mosConfig_absolute_path . '/configuration.php' );
				foreach ($cf as $k=>$v) {
					if (eregi( 'mosConfig_host', $v)) {
						$cf[$k] = '$mosConfig_host = \'xxxxxx\'';
					} else if (eregi( 'mosConfig_user', $v)) {
						$cf[$k] = '$mosConfig_user = \'xxxxxx\'';
					} else if (eregi( 'mosConfig_password', $v)) {
						$cf[$k] = '$mosConfig_password = \'xxxxxx\'';
					} else if (eregi( 'mosConfig_db ', $v)) {
						$cf[$k] = '$mosConfig_db = \'xxxxxx\'';
					} else if (eregi( '<?php', $v)) {
						$cf[$k] = '&lt;?php';
					}
				}
				echo implode( "<br />", $cf );
				?>
				</td>
			</tr>
			</table>
		<?php
		$title = $_LANG->_( 'PHP Info' );
		$tabs->endTab();
		$tabs->startTab( $title, "php-page" );
		?>
			<table class="adminform">
			<tr>
				<th colspan="2">
					<?php echo $_LANG->_( 'PHP Information' ); ?>
				</th>
			</tr>
			<tr>
				<td>
				<?php
				ob_start();
				phpinfo(INFO_GENERAL | INFO_CONFIGURATION | INFO_MODULES);
				$phpinfo = ob_get_contents();
				ob_end_clean();
				preg_match_all('#<body[^>]*>(.*)</body>#siU', $phpinfo, $output);
				$output = preg_replace('#<table#', '<table class="adminlist" align="center"', $output[1][0]);
				$output = preg_replace('#(\w),(\w)#', '\1, \2', $output);
				$output = preg_replace('#border="0" cellpadding="3" width="600"#', 'border="0" cellspacing="1" cellpadding="4" width="95%"', $output);
				$output = preg_replace('#<hr />#', '', $output);
				echo $output;
				?>
				</td>
			</tr>
			</table>
		<?php
		$title = $_LANG->_( 'Permissions' );
		$tabs->endTab();
		$tabs->startTab( $title, "perms" );
		?>
			<table class="adminform">
			<tr>
				<th colspan="2">
					<?php echo $_LANG->_( 'Directory Permissions' ); ?>
				</th>
			</tr>
			<tr>
				<td>
					<strong><?php echo $_LANG->_( 'DescDirWritable' ); ?>:</strong>
					<?php
					mosHTML::writableCell( 'administrator/backups' );
					mosHTML::writableCell( 'administrator/components' );
					mosHTML::writableCell( 'administrator/modules' );
					mosHTML::writableCell( 'administrator/templates' );
					mosHTML::writableCell( 'cache' );
					mosHTML::writableCell( 'components' );
					mosHTML::writableCell( 'images' );
					mosHTML::writableCell( 'images/banners' );
					mosHTML::writableCell( 'images/stories' );
					mosHTML::writableCell( 'language' );
					mosHTML::writableCell( 'mambots' );
					mosHTML::writableCell( 'mambots/content' );
					mosHTML::writableCell( 'mambots/editors' );
					mosHTML::writableCell( 'mambots/editors-xtd' );
					mosHTML::writableCell( 'mambots/search' );
					mosHTML::writableCell( 'mambots/system' );
					mosHTML::writableCell( 'mambots/user' );
					mosHTML::writableCell( 'mambots/xmlrpc' );
					mosHTML::writableCell( 'media' );
					mosHTML::writableCell( 'modules' );
					mosHTML::writableCell( 'templates' );
					?>
				</td>
			</tr>
			</table>
		<?php
		$tabs->endTab();
		$tabs->endPane();
		?>
		<?php
	}

	function ListComponents() {
		global $database;

		$query = "SELECT params"
		. "\n FROM #__modules "
		. "\n WHERE module = 'mod_components'"
		;
		$database->setQuery( $query );
		$row = $database->loadResult();
		$params = new mosParameters( $row );

		mosLoadAdminModule( 'components', $params );
	}

	/**
	 * Display Help Page
	 */
	function help() {
		global $mosConfig_live_site, $_LANG;
		$helpurl 	= mosGetParam( $GLOBALS, 'mosConfig_helpurl', '' );

		if ( $helpurl == 'http://help.mamboserver.com' ) {
			$helpurl = 'http://help.joomla.org';
		}

		$fullhelpurl = $helpurl . '/index2.php?option=com_content&amp;task=findkey&pop=1&keyref=';

		$helpsearch = mosGetParam( $_REQUEST, 'helpsearch', '' );
		$page 		= mosGetParam( $_REQUEST, 'page', 'joomla.whatsnew100.html' );
		$toc 		= getHelpToc( $helpsearch );
		if (!eregi( '\.html$', $page )) {
			$page .= '.xml';
		}
		?>
		<style type="text/css">
		.helpIndex {
			border: 0px;
			width: 95%;
			height: 100%;
			padding: 0px 5px 0px 10px;
			overflow: auto;
		}
		.helpFrame {
			border-left: 0px solid #222;
			border-right: none;
			border-top: none;
			border-bottom: none;
			width: 100%;
			height: 700px;
			padding: 0px 5px 0px 10px;
		}
		</style>
		<form name="adminForm">
		<table class="adminform" border="1">
		<tr>
			<td colspan="2">
				<table width="100%">
					<tr>
						<td>
							<strong><?php echo $_LANG->_( 'Search' ); ?>:</strong>
							<input class="text_area" type="hidden" name="option" value="com_admin" />
							<input type="text" name="helpsearch" value="<?php echo $helpsearch;?>" class="inputbox" />
							<input type="submit" value="<?php echo $_LANG->_( 'Go' ); ?>" class="button" />
							<input type="button" value="<?php echo $_LANG->_( 'Clear Results' ); ?>" class="button" onclick="f=document.adminForm;f.helpsearch.value='';f.submit()" />
							</td>
							<td style="text-align:right">
							<?php
							if ($helpurl) {
							?>
							<a href="<?php echo $fullhelpurl;?>joomla.glossary" target="helpFrame">
								<?php echo $_LANG->_( 'Glossary' ); ?></a>
							|
							<a href="<?php echo $fullhelpurl;?>joomla.credits" target="helpFrame">
								<?php echo $_LANG->_( 'Credits' ); ?></a>
							|
							<a href="<?php echo $fullhelpurl;?>joomla.support" target="helpFrame">
								<?php echo $_LANG->_( 'Support' ); ?></a>
							<?php
							} else {
							?>
							<a href="<?php echo $mosConfig_live_site;?>/help/joomla.glossary.html" target="helpFrame">
								<?php echo $_LANG->_( 'Glossary' ); ?></a>
							|
							<a href="<?php echo $mosConfig_live_site;?>/help/joomla.credits.html" target="helpFrame">
								<?php echo $_LANG->_( 'Credits' ); ?></a>
							|
							<a href="<?php echo $mosConfig_live_site;?>/help/joomla.support.html" target="helpFrame">
								<?php echo $_LANG->_( 'Support' ); ?></a>
							<?php
							}
							?>
							|
							<a href="http://www.gnu.org/copyleft/gpl.html" target="helpFrame">
								<?php echo $_LANG->_( 'License' ); ?></a>
							|
							<a href="http://help.joomla.org" target="_blank">
								help.joomla.org</a>
							|
							<a href="<?php echo $mosConfig_live_site;?>/administrator/index3.php?option=com_admin&task=changelog" target="helpFrame">
								<?php echo $_LANG->_( 'Changelog' ); ?></a>
							|
							<a href="<?php echo $mosConfig_live_site;?>/administrator/index3.php?option=com_admin&task=sysinfo" target="helpFrame">
								<?php echo $_LANG->_( 'System Info' ); ?></a>
							|
							<a href="http://www.joomla.org/content/blogcategory/32/66/" target="_blank">
								<?php echo $_LANG->_( 'Latest Version Check' ); ?></a>
						</td>
					</tr>
				</table>
			</td>
		</tr>
		<tr valign="top">
			<td width="20%" valign="top">
				<strong><?php echo $_LANG->_( 'Index' ); ?></strong>
				<div class="helpIndex">
				<?php
				foreach ($toc as $k=>$v) {
					if ($helpurl) {
						echo '<br /><a href="' . $fullhelpurl . urlencode( $k ) . '" target="helpFrame">' . $v . '</a>';
					} else {
						echo '<br /><a href="' . $mosConfig_live_site . '/help/' . $k . '" target="helpFrame">' . $v . '</a>';
					}
				}
				?>
				</div>
			</td>
			<td valign="top">
				<iframe name="helpFrame" src="<?php echo $mosConfig_live_site . '/help/' . $page;?>" class="helpFrame" frameborder="0" /></iframe>
			</td>
		</tr>
		</table>

		<input type="hidden" name="task" value="help" />
		</form>
		<?php
	}

	/**
	* Preview site
	*/
	function preview( $tp=0 ) {
		global $mosConfig_live_site;
		global $_LANG;

		$tp = intval( $tp );
		?>
		<style type="text/css">
		.previewFrame {
			border: none;
			width: 95%;
			height: 600px;
			padding: 0px 5px 0px 10px;
		}
		</style>
		<table class="adminform">
		<tr>
			<th width="50%" class="title">
			<?php echo $_LANG->_( 'Site Preview' ); ?>
			</th>
			<th width="50%" style="text-align:right">
			<a href="<?php echo $mosConfig_live_site . '/index.php?tp=' . $tp;?>" target="_blank">
			<?php echo $_LANG->_( 'Open in new window' ); ?>
			</a>
			</th>
		</tr>
		<tr>
			<td width="100%" valign="top" colspan="2">
			<iframe name="previewFrame" src="<?php echo $mosConfig_live_site . '/index.php?tp=' . $tp;?>" class="previewFrame" /></iframe>
			</td>
		</tr>
		</table>
		<?php
	}

	/*
	* Displays contents of Changelog.php file
	*/
	function changelog() {
		?>
		<pre>
			<?php
			readfile( $GLOBALS['mosConfig_absolute_path'].'/CHANGELOG.php' );
			?>
		</pre>
		<?php
	}
}

/**
 * Compiles the help table of contents
 * @param string A specific keyword on which to filter the resulting list
 */
function getHelpTOC( $helpsearch ) {
	global $mosConfig_absolute_path;
	$helpurl = mosGetParam( $GLOBALS, 'mosConfig_helpurl', '' );

	$files = mosReadDirectory( $mosConfig_absolute_path . '/help/', '\.xml$|\.html$' );

	require_once( $mosConfig_absolute_path . '/includes/domit/xml_domit_lite_include.php' );

	$toc = array();
	foreach ($files as $file) {
		$buffer = file_get_contents( $mosConfig_absolute_path . '/help/' . $file );
		if (preg_match( '#<title>(.*?)</title>#', $buffer, $m )) {
			$title = trim( $m[1] );
			if ($title) {
				if ($helpurl) {
					// strip the extension
					$file = preg_replace( '#\.xml$|\.html$#', '', $file );
				}
				if ($helpsearch) {
					if (strpos( strip_tags( $buffer ), $helpsearch ) !== false) {
						$toc[$file] = $title;
					}
				} else {
					$toc[$file] = $title;
				}
			}
		}
	}
	asort( $toc );
	return $toc;
}
?>