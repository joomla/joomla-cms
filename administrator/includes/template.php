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

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

require_once( JPATH_BASE . '/includes/template.html.php' );

/**
 * Get the number of modules loaded for a particular template position
 *
 * @param 	string 	The mdoule position
 * @return 	integer The number of modules loaded for that position
 */
function mosCountAdminModules(  $position='left' ) 
{
	global $mainframe;
	$document =& $mainframe->getDocument();
	return count($document->getModules($position));
}

/**
 * Insert a component placeholder
 */
function mosMainBody_Admin() 
{
	?>
	<jdoc:placeholder type="component" />
	<?php
}

/**
 * Insert a modules placholder
 *
 * @param string 	The position of the modules
 * @param integer 	The style.  0=no wrapper, 1=tabbed, 2=xhtml
 */
 
function mosLoadAdminModules( $position='left', $style=0 ) 
{
	?>
	<jdoc:placeholder type="modules" name="<?php echo $position ?>" style="<?php echo $style ?>"/>
	<?php
}

/**
 * Insert a module placholder
 *
 * @param string 	The name of the module
 * @param integer 	The style.  0=no wrapper, 1=tabbed, 2=xhtml
 */
function mosLoadAdminModule( $name, $style=0 ) 
{
	?>
	<jdoc:placeholder type="module" name="<?php echo $name ?>" style="<?php echo $style ?>" />
	<?php
}

/**
* Insert a head placeholder
*/
function mosShowHead_Admin() 
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
function initDocument(&$doc, $file = 'index.php') 
{		
	global $mainframe;
	
	$user    =& $mainframe->getUser();
	$db      =& $mainframe->getDBO();
	$lang    = $mainframe->getLanguage();
	$version = new JVersion();
	
	$template = $mainframe->getTemplate();
	
	$doc->setMetaContentType();
		
	$doc->setTitle( $mainframe->getCfg('sitename' ). '-' .JText::_( 'Administration' ) .'  [Joomla!]' );
	
	$doc->setMetaData( 'description', $mainframe->getCfg('MetaDesc' ));
	$doc->setMetaData( 'keywords', $mainframe->getCfg('MetaKeys' ));
	$doc->setMetaData( 'Generator', $version->PRODUCT . " - " . $version->COPYRIGHT);
	$doc->setMetaData( 'robots', 'noindex, nofollow' );
	
	$doc->setBase( $mainframe->getBaseURL( ));
	
	$doc->addGlobalVar( 'lang_tag', $lang->getTag());
	
	$doc->addVar( $file, 'lang_isrtl', $lang->isRTL());
	
	if ($lang->isRTL()) {
		$doc->addGlobalVar( 'lang_dir', 'rtl' );
	} else {
		$doc->addGlobalVar( 'lang_dir', 'ltr' );
	}
	
	$doc->addGlobalVar( 'template', $template);

	if ( $user->get('id') ) {
		$doc->addScript( '../includes/js/joomla.javascript.js');
	}

	$dirs = array(
		'templates/'.$mainframe->getTemplate().'/',
		'',
	);

	foreach ($dirs as $dir ) {
		$icon =   $dir . 'favicon.ico';

		if(file_exists( JPATH_BASE .'/'. $icon )) {
			$doc->addFavicon( $icon );
			break;
		}
	}
}
?>