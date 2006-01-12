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

require_once( 'includes/framework.php' );

/**
* Joomla! Application class
*
* Provide many supporting API functions
* 
* @package Joomla
* @final
*/
class JSite extends JApplication {

	/**
	* Class constructor
	* 
	* @access protected
	* @param integer A client id
	*/
	function __construct() {
		parent::__construct(0);
	}
	
	/**
	* Set Page Title
	* 
	* @param string $title The title for the page
	* @since 1.1
	*/
	function setPageTitle( $title=null ) {
		
		if($this->getCfg('pagetitles'))
		{
			$title = trim( htmlspecialchars( $title ));
			$site  = $this->getCfg('sitename');
			
			$title  = $title ? $site . ' - '. $title : $site;
		}
		
		$document=& $this->getDocument();
		$document->setTitle($title);
	}

	/**
	* Get Page title
	* 
	* @return string The page title
	* @since 1.1
	*/
	function getPageTitle() 
	{
		$document=& $this->getDocument();
		return $document->getTitle();
	}
	
	/**
	* Get the template
	* 
	* @return string The template name
	* @since 1.0
	*/
	function getTemplate()
	{
		global $Itemid;

		static $templates;

		if (!isset ($templates))
		{
			$templates = array();
			
			/*
			 * Load template entries for each menuid
			 */
			$db = $this->getDBO();
			$query = "SELECT template, menuid"
				. "\n FROM #__templates_menu"
				. "\n WHERE 1"
				;
			$db->setQuery( $query );
			$tmpls = $db->loadObjectList();
			
			/*
			 * Build the static templates array
			 */
			foreach ($tmpls as $tmpl)
			{
				$templates[$tmpl->menuid] = $tmpl->template;	
			}
		}

		if (!empty($Itemid) && ($templates[$Itemid]))
		{
			$template = $templates[$Itemid];
		}
		else
		{
			$template = $templates[0];
		}

		// TemplateChooser Start
		$jos_user_template   = mosGetParam( $_COOKIE, 'jos_user_template', '' );
		$jos_change_template = mosGetParam( $_REQUEST, 'jos_change_template', $jos_user_template );
		
		if ($jos_change_template) {
			// check that template exists in case it was deleted
			if (file_exists( JPATH_SITE .'/templates/'. $jos_change_template .'/index.php' )) {
				$lifetime = 60*10;
				$template = $jos_change_template;
				setcookie( 'jos_user_template', "$jos_change_template", time() + $lifetime);
			} else {
				setcookie( 'jos_user_template', '', time()-3600 );
			}
		}

		$this->_template = $template;
		return $this->_template;
	}
}


/** 
 * @global $_VERSION 
 */
$_VERSION = new JVersion();

/**
 *  Legacy global
 * 	use JApplicaiton->registerEvent and JApplication->triggerEvent for event handling
 *  use JPlugingHelper::importGroup and JPluginHelper::import to load bot code
 *  @deprecated As of version 1.1
 */
$_MAMBOTS = new mosMambotHandler();
?>