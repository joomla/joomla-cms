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
	<jdoc:include type="component" />
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
	<jdoc:include type="modules" name="<?php echo $position ?>" style="<?php echo $style ?>"/>
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
	<jdoc:include type="module" name="<?php echo $name ?>" style="<?php echo $style ?>" />
	<?php
}

/**
* Insert a head placeholder
*/
function mosShowHead()
{
	?>
	<jdoc:include type="head" />
	<?php
}

/**
 * Initialise the document
 *
 * @param object $doc The document instance to initialise
 */
function initDocument( &$doc, $file = 'index.php' ) 
{
	global $mainframe, $Itemid;

	$user    	=& $mainframe->getUser();
	$db      	=& $mainframe->getDBO();
	$lang    	=& $mainframe->getLanguage();
	$version 	= new JVersion();

	$template 	= $mainframe->getTemplate();

	$doc->setMetaContentType();
	
	$doc->setMetaData( 'description', 	$mainframe->getCfg('MetaDesc') );
	$doc->setMetaData( 'keywords', 		$mainframe->getCfg('MetaKeys') );
	$doc->setMetaData( 'Generator', 	$version->PRODUCT .' - '. $version->COPYRIGHT );
	$doc->setMetaData( 'robots', 		'index, follow' );

	$doc->setBase( $mainframe->getBaseURL() );

	$doc->addGlobalVar( 'template', 	$template);

	// support for text direction change
	$doc->addGlobalVar( 'lang_tag', 	$lang->getTag());
	$doc->addVar( $file, 'lang_isrtl', 	$lang->isRTL());
	if ($lang->isRTL()) {
		$doc->addGlobalVar( 'lang_dir', 'rtl' );
	} else {
		$doc->addGlobalVar( 'lang_dir', 'ltr' );
	}

	if ( $user->get('id') ) {
		$doc->addScript( 'includes/js/joomla.javascript.js');
	}

	// support for Firefox Live Bookmarks ability for site syndication
	$menu = JMenu::getInstance();
	$row = $menu->getItem($Itemid);
	$params 	= new JParameter( @$row->params );

	$live_bookmark 	= $params->get( 'live_bookmark', '' );
	if ($live_bookmark) {
		$from = @$_SERVER['QUERY_STRING'];

		if ( $from ) {
			$parts      = explode( 'option=', $from );

			$link_file 	= 'index.php?option=com_syndicate&feed='. $live_bookmark .'&live=1&type='. $parts[1];

			// xhtml check
			$link_file = ampReplace( $link_file );

			// outputs link tag for page
			$doc->addHeadLink( $link_file, 'alternate', 'rel', array('type' => 'application/rss+xml'));
		}
	}

	// favicon support
	$path = 'templates/'. $template .'/';
	$dirs = array( $path, '' );
	foreach ($dirs as $dir ) {
		$icon =   $dir . 'favicon.ico';

		if(file_exists( JPATH_SITE .'/'. $icon )) {
			$doc->addFavicon( $icon);
			break;
		}
	}
}
?>