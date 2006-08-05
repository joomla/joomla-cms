<?php
/**
* @version $Id$
* @package Joomla
* @subpackage Admin
* @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
* @license GNU/GPL, see LICENSE.php
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
		global $_VERSION;
		global $mainframe;

		$db =& JFActory::getDBO();

		define( 'JPATH_COM_ADMIN', dirname( __FILE__ ));

		$document =& $mainframe->getDocument();
		$document->addScript($mainframe->getBaseURL().'components/com_config/assets/switcher.js');

		$contents = '';
		ob_start();
		require_once(JPATH_COM_ADMIN.DS.'tmpl'.DS.'navigation.html');
		$contents = ob_get_contents();
		ob_clean();

		$document->set('module', 'submenu', $contents);
		?>
		<form action="index2.php" method="post" name="adminForm">

		<div id="config-document">
			<div id="page-site">
				<table class="noshow">
				<tr>
					<td>
						<?php require_once(JPATH_COM_ADMIN.DS.'tmpl'.DS.'sysinfo_system.html'); ?>
					</td>
				</tr>
				</table>
			</div>

			<div id="page-phpsettings">
				<table class="noshow">
				<tr>
					<td>
						<?php require_once(JPATH_COM_ADMIN.DS.'tmpl'.DS.'sysinfo_phpsettings.html'); ?>
					</td>
				</tr>
				</table>
			</div>

			<div id="page-config">
				<table class="noshow">
				<tr>
					<td>
						<?php require_once(JPATH_COM_ADMIN.DS.'tmpl'.DS.'sysinfo_config.html'); ?>
					</td>
				</tr>
				</table>
			</div>

			<div id="page-directory">
				<table class="noshow">
				<tr>
					<td>
						<?php require_once(JPATH_COM_ADMIN.DS.'tmpl'.DS.'sysinfo_directory.html'); ?>
					</td>
				</tr>
				</table>
			</div>

			<div id="page-phpinfo">
				<table class="noshow">
				<tr>
					<td>
						<?php require_once(JPATH_COM_ADMIN.DS.'tmpl'.DS.'sysinfo_phpinfo.html'); ?>
					</td>
				</tr>
				</table>
			</div>
		</div>

		<div class="clr"></div>
		<?php
	}

	function ListComponents()
	{
		$db =& JFactory::getDBO();

		$query = "SELECT params"
		. "\n FROM #__modules "
		. "\n WHERE module = 'mod_components'"
		;
		$db->setQuery( $query );
		$row = $db->loadResult();
		$params = new JParameter( $row );

		mosLoadAdminModule( 'components', $params );
	}

	/**
	 * Display Help Page
	 */
	function help()
	{
		$helpurl 	= JArrayHelper::getValue( $GLOBALS, 'mosConfig_helpurl', '' );

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
							<?php echo mosHTML::Link('index2.php?option=com_admin&task=changelog&file=component.html', JText::_( 'Changelog' ), array('target' => "'helpFrame'")) ?>
							|
							<?php echo mosHTML::Link('http://www.joomla.org/content/blogcategory/32/66/', JText::_( 'Latest Version Check' ), array('target' => '"_blank"')) ?>
						</td>
					</tr>
				</table>
			</td>
		</tr>
		</table>

		<div id="treecellhelp">
			<fieldset title="<?php echo JText::_( 'Index' ); ?>">
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
			<fieldset title="<?php echo JText::_( 'Details' ); ?>">
				<legend>
					<?php echo JText::_( 'Details' ); ?>
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
	global $mainframe;
	
	jimport( 'joomla.filesystem.folder' );

	$helpurl = $mainframe->getCfg('helpurl');

	$files = JFolder::files( JPATH_BASE . '/help/en-GB/', '\.xml$|\.html$' );

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
					if (JString::strpos( strip_tags( $buffer ), $helpsearch ) !== false) {
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