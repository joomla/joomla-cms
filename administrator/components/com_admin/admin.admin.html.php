<?php
/**
 * @version		$Id$
 * @package		Joomla
 * @subpackage	Admin
 * @copyright	Copyright (C) 2005 - 2007 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

/**
* @package		Joomla
* @subpackage	Admin
*/
class HTML_admin_misc
{
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
		global $mainframe;

		//Load switcher behavior
		JHTML::_('behavior.switcher');

		$db =& JFactory::getDBO();

		$contents = '';
		ob_start();
		require_once(JPATH_COMPONENT.DS.'tmpl'.DS.'navigation.php');
		$contents = ob_get_contents();
		ob_clean();

		$document =& JFactory::getDocument();
		$document->setBuffer($contents, 'module', 'submenu');
		?>
		<form action="index.php" method="post" name="adminForm">

		<div id="config-document">
			<div id="page-site">
				<table class="noshow">
				<tr>
					<td>
						<?php require_once(JPATH_COMPONENT.DS.'tmpl'.DS.'sysinfo_system.php'); ?>
					</td>
				</tr>
				</table>
			</div>

			<div id="page-phpsettings">
				<table class="noshow">
				<tr>
					<td>
						<?php require_once(JPATH_COMPONENT.DS.'tmpl'.DS.'sysinfo_phpsettings.php'); ?>
					</td>
				</tr>
				</table>
			</div>

			<div id="page-config">
				<table class="noshow">
				<tr>
					<td>
						<?php require_once(JPATH_COMPONENT.DS.'tmpl'.DS.'sysinfo_config.php'); ?>
					</td>
				</tr>
				</table>
			</div>

			<div id="page-directory">
				<table class="noshow">
				<tr>
					<td>
						<?php require_once(JPATH_COMPONENT.DS.'tmpl'.DS.'sysinfo_directory.php'); ?>
					</td>
				</tr>
				</table>
			</div>

			<div id="page-phpinfo">
				<table class="noshow">
				<tr>
					<td>
						<?php require_once(JPATH_COMPONENT.DS.'tmpl'.DS.'sysinfo_phpinfo.php'); ?>
					</td>
				</tr>
				</table>
			</div>
		</div>

		<div class="clr"></div>
		<?php
	}

	/**
	 * Display Help Page
	 *
	 * For this method the important two scenarios are local or remote help files.
	 * In the case of local help files the language tag will be added in order to
	 * allow different languages of help.<br />
	 * In case of the remote server it is assumed that this server provide one specific
	 * help set of files in one particular language.
	 */
	function help()
	{
		global $mainframe;
		jimport( 'joomla.filesystem.folder' );
		jimport( 'joomla.language.help' );

		// Get Help URL - an empty helpurl is interpreted as local help files!
		$helpurl	= $mainframe->getCfg('helpurl');
		if ( $helpurl == 'http://help.mamboserver.com' ) {
			$helpurl = 'http://help.joomla.org';
		}
		$fullhelpurl = $helpurl . '/index2.php?option=com_content&amp;task=findkey&amp;pop=1&amp;keyref=';

		$helpsearch = JRequest::getString('helpsearch');
		$page		= JRequest::getCmd('page', 'joomla.whatsnew15.html');
		$toc		= getHelpToc( $helpsearch );
		$lang		=& JFactory::getLanguage();
		$langTag = $lang->getTag();
		if( !JFolder::exists( JPATH_BASE.DS.'help'.DS.$langTag ) ) {
			$langTag = 'en-GB';		// use english as fallback
		}

		if (!eregi( '\.html$', $page )) {
			$page .= '.xml';
		}
		?>
		<form action="index.php?option=com_admin&amp;task=help" method="post" name="adminForm">

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
						<td class="helpMenu">
							<?php
							if ($helpurl) {
							?>
							<?php echo JHTML::_('link', JHelp::createUrl( 'joomla.glossary' ), JText::_( 'Glossary' ), array('target' => 'helpFrame')) ?>
							|
							<?php echo JHTML::_('link', JHelp::createUrl( 'joomla.credits' ), JText::_( 'Credits' ), array('target' => 'helpFrame')) ?>
							|
							<?php echo JHTML::_('link', JHelp::createUrl( 'joomla.support' ), JText::_( 'Support' ), array('target' => 'helpFrame')) ?>
							<?php
							} else {
							?>
							<?php echo JHTML::_('link', JURI::base() .'help/'.$langTag.'/joomla.glossary.html', JText::_( 'Glossary' ), array('target' => 'helpFrame')) ?>
							|
							<?php echo JHTML::_('link', JURI::base() .'help/'.$langTag.'/joomla.credits.html', JText::_( 'Credits' ), array('target' => 'helpFrame')) ?>
							|
							<?php echo JHTML::_('link', JURI::base() .'help/'.$langTag.'/joomla.support.html', JText::_( 'Support' ), array('target' => 'helpFrame')) ?>
							<?php
							}
							?>
							|
							<?php echo JHTML::_('link', 'http://www.gnu.org/licenses/gpl-2.0.html', JText::_( 'License' ), array('target' => 'helpFrame')) ?>
							|
							<?php echo JHTML::_('link', 'http://help.joomla.org', 'help.joomla.org', array('target' => 'helpFrame')) ?>
							|
							<?php echo JHTML::_('link', 'index.php?option=com_admin&amp;task=changelog&amp;tmpl=component', JText::_( 'Changelog' ), array('target' => 'helpFrame')) ?>
							|
							<?php echo JHTML::_('link', 'http://www.joomla.org/content/blogcategory/57/111/', JText::_( 'Latest Version Check' ), array('target' => 'helpFrame')) ?>
						</td>
					</tr>
				</table>
			</td>
		</tr>
		</table>

		<div id="treecellhelp">
			<fieldset title="<?php echo JText::_( 'Alphabetical Index' ); ?>">
				<legend>
					<?php echo JText::_( 'Alphabetical Index' ); ?>
				</legend>

				<div class="helpIndex">
					<ul class="subext">
						<?php
						foreach ($toc as $k=>$v) {
							if ($helpurl) {
								echo '<li>';
								echo JHTML::_('link', JHelp::createUrl( $k ), $v, array('target' => 'helpFrame'));
								echo '</li>';
							} else {
								echo '<li>';
								echo JHTML::_('link', JURI::base() .'help/'.$langTag.'/'.$k, $v, array('target' => 'helpFrame'));
								echo '</li>';
							}
						}
						?>
					</ul>
				</div>
			</fieldset>
		</div>

		<div id="datacellhelp">
			<fieldset title="<?php echo JText::_( 'View' ); ?>">
				<legend>
					<?php echo JText::_( 'View' ); ?>
				</legend>
				<?php
				if ($helpurl && $page != 'joomla.whatsnew15.html') {
					?>
					<iframe name="helpFrame" src="<?php echo $fullhelpurl .preg_replace( '#\.xml$|\.html$#', '', $page );?>" class="helpFrame" frameborder="0"></iframe>
					<?php
				} else {
					?>
					<iframe name="helpFrame" src="<?php echo JURI::base() .'/help/' .$lang->getTag(). '/' . $page;?>" class="helpFrame" frameborder="0"></iframe>
					<?php
				}
				?>
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
			readfile( JPATH_SITE.DS.'CHANGELOG.php' );
			?>
		</pre>
		<?php
	}
}

function writableCell( $folder, $relative=1, $text='', $visible=1 )
{
	$writeable		= '<b><font color="green">'. JText::_( 'Writable' ) .'</font></b>';
	$unwriteable	= '<b><font color="red">'. JText::_( 'Unwritable' ) .'</font></b>';

	echo '<tr>';
	echo '<td class="item">';
	echo $text;
	if ( $visible ) {
		echo $folder . '/';
	}
	echo '</td>';
	echo '<td >';
	if ( $relative ) {
		echo is_writable( "../$folder" )	? $writeable : $unwriteable;
	} else {
		echo is_writable( "$folder" )		? $writeable : $unwriteable;
	}
	echo '</td>';
	echo '</tr>';
}

/**
 * Compiles the help table of contents
 * @param string A specific keyword on which to filter the resulting list
 */
function getHelpTOC( $helpsearch )
{
	global $mainframe;

	$lang =& JFactory::getLanguage();
	jimport( 'joomla.filesystem.folder' );

	$helpurl		= $mainframe->getCfg('helpurl');

	// Check for files in the actual language
	$langTag = $lang->getTag();
	if( !JFolder::exists( JPATH_BASE.DS.'help'.DS.$langTag ) ) {
		$langTag = 'en-GB';		// use english as fallback
	}
	$files = JFolder::files( JPATH_BASE.DS.'help'.DS.$langTag, '\.xml$|\.html$' );

	$toc = array();
	foreach ($files as $file) {
		$buffer = file_get_contents( JPATH_BASE.DS.'help'.DS.$langTag.DS.$file );
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