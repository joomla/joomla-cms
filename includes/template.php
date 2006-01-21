<?php
/**
* @version $Id$
* @package Joomla
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

defined( '_JEXEC' ) or die( 'Restricted access' );

require_once( JPATH_BASE . '/includes/template.html.php' );

/**
 * Get the number of modules loaded for a particular template position
 *
 * @param 	string 	The mdoule position
 * @return 	integer The number of modules loaded for that position
 */
function mosCountModules( $position='left' ) 
{
	return count(JModuleHelper::getModules($position));
}

/**
 * Insert a component placeholdere
 */
function mosMainBody()
{
	?>
	<jdoc:placeholder type="component" />
	<?php
}

/**
 * Insert a modules placholder
 *
 * @param string 	The position of the modules
 * @param integer 	The style.  0=normal, 1=horiz, -1=no wrapper
 */
function mosLoadModules( $position='left', $style=0 )
{
	?>
	<jdoc:placeholder type="modules" name="<?php echo $position ?>" style="<?php echo $style ?>"/>
	<?php
}

/**
 * Insert a module placholder
 *
 * @param string 	The name of the module
 * @param integer 	The style.  0=normal, 1=horiz, -1=no wrapper
 */
function mosLoadModule( $name, $style=-1 )
{
	?>
	<jdoc:placeholder type="module" name="<?php echo $name ?>" style="<?php echo $style ?>" />
	<?php
}

/**
* Insert a head placeholder
*/
function mosShowHead()
{
	?>
	<jdoc:placeholder type="head" />
	<?php
}

/**
 * Initialise the document  
 *  
 * @param object $doc The document instance to initialise
 */
function initDocument(&$doc) 
{		
	global $mainframe;
	
	$user    =& $mainframe->getUser();
	$db      =& $mainframe->getDBO();
	$version = new JVersion();
	
			
	$doc->setMetaContentType();
	
	$doc->setMetaData( 'description', $mainframe->getCfg('MetaDesc' ));
	$doc->setMetaData( 'keywords', $mainframe->getCfg('MetaKeys' ));
	$doc->setMetaData( 'Generator', $version->PRODUCT . " - " . $version->COPYRIGHT);
	$doc->setMetaData( 'robots', 'index, follow' );
	
	$doc->setBase( $mainframe->getBaseURL() );

	if ( $user->id ) {
		$doc->addScript( 'includes/js/joomla.javascript.js');
	}

	// support for Firefox Live Bookmarks ability for site syndication
	$query = "SELECT a.id"
	. "\n FROM #__components AS a"
	. "\n WHERE a.name = 'Syndicate'"
	;
	$db->setQuery( $query );
	$id = $db->loadResult();

	// load the row from the db table
	$row = new mosComponent( $db );
	$row->load( $id );

	// get params definitions
	$params = new JParameters( $row->params, JApplicationHelper::getPath( 'com_xml', $row->option ), 'component' );

	$live_bookmark = $params->get( 'live_bookmark', 0 );

	// support for Live Bookmarks ability for site syndication
	if ($live_bookmark) {
		$show = 1;

		$link_file 	= 'index2.php?option=com_rss&feed='. $live_bookmark .'&no_html=1';

		// xhtml check
		$link_file = ampReplace( $link_file );

		// outputs link tag for page
		if ($show) {
			$doc->addHeadLink( $link_file, 'alternate', array('type' => 'application/rss+xml'));
		}
	}
	
	$dirs = array(
		'templates/'.$mainframe->getTemplate().'/',
		'',
	);
		
	foreach ($dirs as $dir ) {
		$icon =   $dir . 'favicon.ico';

		if(file_exists( JPATH_SITE .'/'. $icon )) {
			$doc->addFavicon( $icon);
			break;
		}
	}
}
?>