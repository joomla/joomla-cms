<?php
/**
* @version $Id$
* @package Joomla
* @subpackage Admin
* @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
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
* @subpackage Admin
*/
class HTML_admin_misc 
{
	/**
	* Control panel
	*/
	function controlPanel() 
	{
		global $mainframe;
	
		$path = JPATH_BASE . '/templates/' . $mainframe->getTemplate() . '/cpanel.php';
		if (file_exists( $path )) {
			require_once $path;
		}
	}

	function get_php_setting($val) 
	{
		$r =  (ini_get($val) == '1' ? 1 : 0);
		return $r ? JText::_( 'ON' ) : JText::_( 'OFF' ) ;
	}

	function get_server_software() 
	{
		if (isset($_SERVER['SERVER_SOFTWARE'])) {
			return $_SERVER['SERVER_SOFTWARE'];
		} else if (($sf = getenv('SERVER_SOFTWARE'))) {
			return $sf;
		} else {
			return JText::_( 'n/a' );
		}
	}

	function system_info( ) 
	{
		global $database, $_VERSION, $mosConfig_cachepath;

		$width = 400;	// width of 100%
		$tabs = new mosTabs(0);
		?>
		
		<fieldset title="Details">
			<legend>
				Details
			</legend>
				
			<?php
			$title = JText::_( 'System Info' );
			$tabs->startPane("sysinfo");
			$tabs->startTab( $title, "system-page" );
			?>
			
				<table class="adminform">
				<thead>
				<tr>
					<th colspan="2">
						<?php echo JText::_( 'System Information' ); ?>
					</th>
				</tr>
				</thead>
				<tfoot>
				<tr>
					<th colspan="2">
						&nbsp;
					</th>
				</tr>
				</tfoot>			
				<tbody>
				<tr>
					<td valign="top" width="250">
						<strong><?php echo JText::_( 'PHP built On' ); ?>:</strong>
					</td>
					<td>
						<?php echo php_uname(); ?>
					</td>
				</tr>
				<tr>
					<td>
						<strong><?php echo JText::_( 'Database Version' ); ?>:</strong>
					</td>
					<td>
						<?php echo $database->getVersion(); ?>
					</td>
				</tr>
				<tr>
					<td>
						<strong><?php echo JText::_( 'Database Collation' ); ?>:</strong>
					</td>
					<td>
						<?php echo $database->getCollation(); ?>
					</td>
				</tr>
				<tr>
					<td>
						<strong><?php echo JText::_( 'PHP Version' ); ?>:</strong>
					</td>
					<td>
						<?php echo phpversion(); ?>
					</td>
				</tr>
				<tr>
					<td>
						<strong><?php echo JText::_( 'Web Server' ); ?>:</strong>
					</td>
					<td>
						<?php echo HTML_admin_misc::get_server_software(); ?>
					</td>
				</tr>
				<tr>
					<td>
						<strong><?php echo JText::_( 'WebServer to PHP interface' ); ?>:</strong>
					</td>
					<td>
						<?php echo php_sapi_name(); ?>
					</td>
				</tr>
				<tr>
					<td>
						<strong><?php echo JText::_( 'Joomla! Version' ); ?>:</strong>
					</td>
					<td>
						<?php echo $_VERSION->getLongVersion() ?>
					</td>
				</tr>
				<tr>
					<td>
						<strong><?php echo JText::_( 'User Agent' ); ?>:</strong>
					</td>
					<td>
						<?php echo phpversion() <= "4.2.1" ? getenv( "HTTP_USER_AGENT" ) : $_SERVER['HTTP_USER_AGENT'];?>
					</td>
				</tr>
				<tr>
					<td valign="top">
						<strong><?php echo JText::_( 'Relevant PHP Settings' ); ?>:</strong>
					</td>
					<td>
						<table cellspacing="1" cellpadding="1" border="0">
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
								<?php echo JText::_( 'Session save path' ); ?>:
							</td>
							<td>
								<?php echo (($sp=ini_get('session.save_path')) ? $sp : JText::_( 'none' ) ); ?>
							</td>
						</tr>
						<tr>
							<td>
								<?php echo JText::_( 'Session auto start' ); ?>:
							</td>
							<td>
								<?php echo intval( ini_get( 'session.auto_start' ) ); ?>
							</td>
						</tr>
						<tr>
							<td>
								<?php echo JText::_( 'XML enabled' ); ?>:
							</td>
							<td>
							<?php echo extension_loaded('xml') ? JText::_( 'Yes' ) : JText::_( 'No' ); ?>
							</td>
						</tr>
						<tr>
							<td>
								<?php echo JText::_( 'Zlib enabled' ); ?>:
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
								<?php echo JText::_( 'Mbstring enabled' ); ?>:
							</td>
							<td>
								<?php echo extension_loaded('mbstring') ? JText::_( 'Yes' ) : JText::_( 'No' ); ?>
							</td>
						</tr>
						<tr>
							<td>
								<?php echo JText::_( 'Iconv available' ); ?>:
							</td>
							<td>
								<?php echo function_exists('iconv') ? JText::_( 'Yes' ) : JText::_( 'No' ); ?>
							</td>
						</tr>
	
						<?php
						$query = "SELECT name FROM #__plugins"
						. "\nWHERE folder='editors' AND published='1'"
						. "\nLIMIT 1";
						$database->setQuery( $query );
						$editor = $database->loadResult();
						?>
						<tr>
							<td>
								<?php echo JText::_( 'WYSIWYG Editor' ); ?>:
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
						<strong><?php echo JText::_( 'Configuration File' ); ?>:</strong>
					</td>
					<td>
						<?php
						$cf = file( JPATH_CONFIGURATION . '/configuration.php' );
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
				</tbody>
				</table>
				
			<?php
			$title = JText::_( 'PHP Info' );
			$tabs->endTab();
			$tabs->startTab( $title, "php-page" );
			?>
			
				<table class="adminform">
				<thead>
				<tr>
					<th colspan="2">
						<?php echo JText::_( 'PHP Information' ); ?>
					</th>
				</tr>
				</thead>
				<tfoot>
				<tr>
					<th colspan="2">
						&nbsp;
					</th>
				</tr>
				</tfoot>			
				<tbody>
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
				</tbody>
				</table>
				
			<?php
			$title = JText::_( 'Permissions' );
			$tabs->endTab();
			$tabs->startTab( $title, "perms" );
			?>
			
				<table class="adminform">
				<thead>
				<tr>
					<th colspan="2">
						<?php echo JText::_( 'Directory Permissions' ); ?>
					</th>
				</tr>
				</thead>
				<tfoot>
				<tr>
					<th colspan="2">
						&nbsp;
					</th>
				</tr>
				</tfoot>			
				<tbody>
				<tr>
					<td>
						<strong><?php echo JText::_( 'DescDirWritable' ); ?>:</strong>
					</td>
				</tr>
				<?php
				$sp = ini_get('session.save_path');

				mosHTML::writableCell( 'administrator/backups' );
				mosHTML::writableCell( 'administrator/components' );
				mosHTML::writableCell( 'administrator/modules' );
				mosHTML::writableCell( 'administrator/templates' );
				mosHTML::writableCell( 'components' );
				mosHTML::writableCell( 'images' );
				mosHTML::writableCell( 'images/banners' );
				mosHTML::writableCell( 'images/stories' );
				mosHTML::writableCell( 'language' );
				mosHTML::writableCell( 'plugins' );
				mosHTML::writableCell( 'plugins/content' );
				mosHTML::writableCell( 'plugins/editors' );
				mosHTML::writableCell( 'plugins/editors-xtd' );
				mosHTML::writableCell( 'plugins/search' );
				mosHTML::writableCell( 'plugins/system' );
				mosHTML::writableCell( 'plugins/user' );
				mosHTML::writableCell( 'plugins/xmlrpc' );
				mosHTML::writableCell( 'media' );
				mosHTML::writableCell( 'modules' );
				mosHTML::writableCell( 'templates' );
				mosHTML::writableCell( $mosConfig_cachepath, 0, '<strong>'. JText::_( 'Cache Directory' ) .'</strong> ' );
				mosHTML::writableCell( $sp, 0, '<strong>'. JText::_( 'Session Directory' ) .'</strong> ' );
				?>
				</tbody>
				</table>
				
			<?php
			$tabs->endTab();
			$tabs->endPane();
			?>
		</fieldset>
		<?php
	}

	function ListComponents() 
	{
		global $database;

		$query = "SELECT params"
		. "\n FROM #__modules "
		. "\n WHERE module = 'mod_components'"
		;
		$database->setQuery( $query );
		$row = $database->loadResult();
		$params = new JParameter( $row );

		mosLoadAdminModule( 'components', $params );
	}

	/**
	 * Display Help Page
	 */
	function help() 
	{
		$helpurl 	= mosGetParam( $GLOBALS, 'mosConfig_helpurl', '' );

		if ( $helpurl == 'http://help.mamboserver.com' ) {
			$helpurl = 'http://help.joomla.org';
		}

		$fullhelpurl = $helpurl . '/index2.php?option=com_content&amp;task=findkey&pop=1&keyref=';

		$helpsearch = JRequest::getVar( 'helpsearch' );
		$page 		= JRequest::getVar( 'page', 'joomla.whatsnew15.html' );
		$toc 		= getHelpToc( $helpsearch );
		if (!eregi( '\.html$', $page )) {
			$page .= '.xml';
		}
		?>
		<form action="index2.php?option=com_admin&amp;task=help" method="post" name="adminForm">
		
		<table class="adminform" border="1">
		<tr>
			<td colspan="2">
				<table width="100%">
					<tr>
						<td>
							<strong><?php echo JText::_( 'Search' ); ?>:</strong>
							<input class="text_area" type="hidden" name="option" value="com_admin" />
							<input type="text" name="helpsearch" value="<?php echo $helpsearch;?>" class="inputbox" />
							<input type="submit" value="<?php echo JText::_( 'Go' ); ?>" class="button" />
							<input type="button" value="<?php echo JText::_( 'Clear Results' ); ?>" class="button" onclick="f=document.adminForm;f.helpsearch.value='';f.submit()" />
						</td>
						<td style="text-align:right">
							<?php
							if ($helpurl) {
							?>
							<?php echo mosHTML::Link($fullhelpurl.'joomla.glossary', JText::_( 'Glossary' ), array('target' => '"helpFrame"')) ?>
							|
							<?php echo mosHTML::Link($fullhelpurl.'joomla.credits', JText::_( 'Credits' ), array('target' => "'helpFrame'")) ?>
							|
							<?php echo mosHTML::Link($fullhelpurl.'joomla.support', JText::_( 'Support' ), array('target' => "'helpFrame'")) ?>
							<?php
							} else {
							?>
							<?php echo mosHTML::Link('/help/joomla.glossary.html', JText::_( 'Glossary' ), array('target' => "'helpFrame'")) ?>
							|
							<?php echo mosHTML::Link('/help/joomla.credits.html', JText::_( 'Credits' ), array('target' => "'helpFrame'")) ?>
							|
							<?php echo mosHTML::Link('/help/joomla.support.html', JText::_( 'Support' ), array('target' => "'helpFrame'")) ?>
							<?php
							}
							?>
							|
							<?php echo mosHTML::Link('http://www.gnu.org/copyleft/gpl.html', JText::_( 'License' ), array('target' => "'helpFrame'")) ?>
							|
							<?php echo mosHTML::Link('http://help.joomla.org', 'help.joomla.org', array('target' => '"_blank"')) ?>
							|
							<?php echo mosHTML::Link('index3.php?option=com_admin&task=changelog', JText::_( 'Changelog' ), array('target' => "'helpFrame'")) ?>
							|
							<?php echo mosHTML::Link('index3.php?option=com_admin&task=sysinfo', JText::_( 'System Info' ), array('target' => "'helpFrame'")) ?>
							|
							<?php echo mosHTML::Link('http://www.joomla.org/content/blogcategory/32/66/', JText::_( 'Latest Version Check' ), array('target' => '"_blank"')) ?>
						</td>
					</tr>
				</table>
			</td>
		</tr>		
		</table>
				
		<div id="treecellhelp">
			<fieldset title="Index">
				<legend>
					<?php echo JText::_( 'Index' ); ?>
				</legend>
				
				<div class="helpIndex">
					<ul class="subext">
						<?php
						foreach ($toc as $k=>$v) {
							if ($helpurl) {
								echo '<li>';
								echo mosHTML::Link($fullhelpurl . urlencode( $k ), $v, array('target' => "'helpFrame'"));
								echo '</li>';
							} else {
								echo '<li>';
								echo mosHTML::Link('/help/'.$k, $v, array('target' => "'helpFrame'"));
								echo '</li>';
							}
						}
						?>
					</ul>
				</div>
			</fieldset>
		</div>
		
		<div id="datacellhelp">
			<fieldset title="Details">
				<legend>
					Details
				</legend>					
				
				<iframe name="helpFrame" src="<?php echo 'help/en-GB/' . $page;?>" class="helpFrame" frameborder="0"></iframe>
			</fieldset>
		</div>			

		<input type="hidden" name="task" value="help" />
		</form>
		<?php
	}

	/*
	* Displays contents of Changelog.php file
	*/
	function changelog() 
	{
		?>
		<pre>
			<?php
			readfile( JPATH_SITE.'/CHANGELOG.php' );
			?>
		</pre>
		<?php
	}
}

/**
 * Compiles the help table of contents
 * @param string A specific keyword on which to filter the resulting list
 */
function getHelpTOC( $helpsearch ) 
{
	$helpurl = mosGetParam( $GLOBALS, 'mosConfig_helpurl', '' );

	$files = mosReadDirectory( JPATH_BASE . '/help/en-GB/', '\.xml$|\.html$' );

	$toc = array();
	foreach ($files as $file) {
		$buffer = file_get_contents( JPATH_BASE . '/help/en-GB/' . $file );
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