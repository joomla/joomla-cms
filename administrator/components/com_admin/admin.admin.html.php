<?php
/**
* @version $Id: admin.admin.html.php 137 2005-09-12 10:21:17Z eddieajau $
* @package Joomla
* @subpackage Admin
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software and parts of it may contain or be derived from the
* GNU General Public License or other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_VALID_MOS' ) or die( 'Restricted access' );


/**
 * @package Joomla
 * @subpackage Contact
 */
class adminScreens {
	/**
	 * Static method to create the template object
	 * @param array An array of other standard files to include
	 * @return patTemplate
	 */
	function &createTemplate( $files=null ) {
		$tmpl =& mosFactory::getPatTemplate( $files );
		$tmpl->setRoot( dirname( __FILE__ ) . '/tmpl' );

		return $tmpl;
	}

	/**
	* List languages
	* @param array
	*/
	function sysinfo() {
		$tmpl =& adminScreens::createTemplate();

		$tmpl->readTemplatesFromInput( 'sysinfo.html' );

		$tmpl->displayParsedTemplate( 'body2' );
	}

	/**
	* List languages
	* @param array
	*/
	function help( $search, $index ) {
		global $mosConfig_helpurl, $mosConfig_live_site;

		if ( $mosConfig_helpurl ) {
            $url = $mosConfig_helpurl .'/index2.php?option=com_content&amp;task=findkey&pop=1&keyref=';
		} else {
		    $url = $mosConfig_live_site .'/help/';
		}

		$tmpl =& adminScreens::createTemplate();

		$tmpl->readTemplatesFromInput( 'help.html' );

		$tmpl->addVar( 'body2', 'url', $url );
		$tmpl->addVar( 'body2', 'search', $search );

		$tmpl->addObject( 'index', $index, 'index_' );

		$tmpl->displayParsedTemplate( 'body2' );
	}
}

/**
* @package Joomla
* @subpackage Admin
*/
class HTML_admin_misc {

	/**
	* Control panel
	*/
	function controlPanel() {
	    global $mosConfig_absolute_path, $mainframe;
		global $_LANG;

		$path = $mosConfig_absolute_path . '/administrator/templates/' . $mainframe->getTemplate() . '/cpanel.php';
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

	function system_info() {
		global $mosConfig_absolute_path, $database;
		global $_LANG;

		$width = 400;	// width of 100%
		?>

		<?php
		adminScreens::sysinfo();
		?>
				<?php
				$title = $_LANG->_( 'System Info' );
				?>
<form action="index2.php" method="post" name="adminForm">
		<div id="mosswitcher">
			<div id="system-info">
					<table class="adminform">
					<tr>
						<th colspan="2">
			            	<?php echo $_LANG->_( 'System Information' ); ?>
						</th>
					</tr>
					<tr>
						<td valign="top" width="250">
							<b>
				            <?php echo $_LANG->_( 'PHP built On' ); ?>:
							</b>
						</td>
						<td>
							<?php echo php_uname(); ?>
						</td>
					</tr>
					<tr>
						<td>
							<b>
				            <?php echo $_LANG->_( 'Database Version' ); ?>:
							</b>
						</td>
						<td>
							<?php echo $database->getVersion(); ?>
						</td>
					</tr>
					<tr>
						<td>
							<b>
				            <?php echo $_LANG->_( 'PHP Version' ); ?>:
							</b>
						</td>
						<td>
							<?php echo phpversion(); ?>
						</td>
					</tr>
					<tr>
						<td>
							<b>
				            <?php echo $_LANG->_( 'Web Server' ); ?>:
							</b>
						</td>
						<td>
							<?php echo HTML_admin_misc::get_server_software(); ?>
						</td>
					</tr>
					<tr>
						<td>
							<b>
				            <?php echo $_LANG->_( 'WebServer to PHP interface' ); ?>:
							</b>
						</td>
						<td>
							<?php echo php_sapi_name(); ?>
						</td>
					</tr>
					<tr>
						<td>
							<b>
				            <?php echo $_LANG->_( 'Joomla! Version' ); ?>:
							</b>
						</td>
						<td>
							<?php echo $GLOBALS['_VERSION']->getLongVersion(); ?>
						</td>
					</tr>
					<tr>
						<td>
							<b>
				            <?php echo $_LANG->_( 'User Agent' ); ?>:
							</b>
						</td>
						<td>
							<?php echo phpversion() <= "4.2.1" ? getenv( "HTTP_USER_AGENT" ) : $_SERVER['HTTP_USER_AGENT'];?>
						</td>
					</tr>
					<tr>
						<td valign="top">
							<b>
				            <?php echo $_LANG->_( 'Relevant PHP Settings' ); ?>:
							</b>
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
									<?php echo extension_loaded('xml') ? $_LANG->_( 'Yes' ) : $_LANG->_( 'No' ) ; ?>
								</td>
							</tr>
							<tr>
								<td>
			                    	<?php echo $_LANG->_( 'Zlib enabled' ); ?>:
								</td>
								<td>
									<?php echo extension_loaded('zlib') ? $_LANG->_( 'Yes' ) : $_LANG->_( 'No' ) ; ?>
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
							. "\n WHERE folder='editors' AND published='1'";
							$database->setQuery( $query, 0, 1 );
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
							<b>
				            <?php echo $_LANG->_( 'Configuration File' ); ?>:
							</b>
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
				?>
		</div>
			<div id="php-info">
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
				?>
			</div>
			<div id="permissions">
					<table class="adminform">
					<tr>
						<th colspan="2">
							<?php echo $_LANG->_( 'Directory Permissions' ); ?>
						</th>
					</tr>
					<tr>
						<td>
							<strong>
								<?php echo $_LANG->_( 'DESCDIRWRITABLE' ); ?>:
							</strong>
							<table class="adminform">
							<?php
							mosHTML::writableCell( 'administrator/backups' );
							mosHTML::writableCell( 'administrator/components' );
							mosHTML::writableCell( 'administrator/components/com_export/files' );
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
							mosHTML::writableCell( 'mambots/search' );
							mosHTML::writableCell( 'media' );
							mosHTML::writableCell( 'modules' );
							mosHTML::writableCell( 'templates' );
							?>
							</table>
						</td>
					</tr>
					</table>
				</div>
				</div>
				</form>
			</fieldset>
		</div>
		<?php
	}

	/**
	* Display Help Page
	*/
	function help() {
		global $mosConfig_live_site, $mosConfig_helpurl;
		global $mainframe;
		global $_LANG;

		$fullhelpurl = $mosConfig_helpurl . '/index2.php?option=com_content&amp;task=findkey&pop=1&keyref=';

		$search 	= $mainframe->getUserStateFromRequest( 'search', 'search', '' );
		$search 	= trim( strtolower( $search ) );
		$page 		= mosGetParam( $_REQUEST, 'page', 'mambo.whatsnew452.html' );
		$toc 		= getHelpToc( $search );
		if ( !eregi( '\.html$', $page ) ) {
			$page .= '.xml';
		}

		$i = 0;
		foreach ( $toc as $k => $v ) {
			if ( $mosConfig_helpurl ) {
				$index[$i]->url 	= $fullhelpurl . urlencode( $k );
			} else {
				$index[$i]->url 	= $mosConfig_live_site . '/help/' . $k;
			}
			$index[$i]->text 	= addslashes( $v );
			$index[$i]->num 	= $i + 1;

			$i++;
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
			height: 600px;
			padding: 0px 5px 0px 10px;
		}
		</style>
		<form name="adminForm">

		<?php
		adminScreens::help( $search, $index );
		?>
				<table class="adminform" border="1">
				<tr valign="top">
					<td valign="top">
						<iframe name="helpFrame" src="<?php echo $mosConfig_live_site . '/help/' . $page;?>" class="helpFrame" frameborder="0" /></iframe>
					</td>
				</tr>
				</table>
			</fieldset>
		</div>

		<input type="hidden" name="option" value="com_admin" />
		<input type="hidden" name="task" value="help" />
		</form>
		<?php
	}

	function changelog() {
        ?>
        <style type="text/css">
        s {
        	color: red;
        }
        .todo {
        	background-color: #E9EFF5;
        	text-align: left;
        	overflow: auto;
        	color: blue;
        	border: 1px solid #999999;
        	padding: 20px;
        }
        hr {
        	border: 1px dotted black;
        }
        span.todotitle {
        	font-weight: bold;
        	color: black;
        }
        </style>
        <pre class="todo">
            <?php
    		readfile( $GLOBALS['mosConfig_absolute_path'].'/changelog.php' );
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

	mosFS::load( 'includes/domit/xml_domit_lite_include.php' );

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
