<?php
/**
* @version $Id$
* @package Joomla
* @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

jimport( 'joomla.common.base.object' );

/**
 * JPane abstract class
 *
 * @abstract
 * @author		Johan Janssens <johan.janssens@joomla.org>
 * @package		Joomla.Framework
 * @subpackage	Presentation
 * @since		1.5
 */
class JPane extends JObject
{
	/** 
	 * Use cookies
	 * 
	 * @var boolean  
	 */
	var $useCookies = false;

	/**
	* Constructor
	* 
	* @param int useCookies, if set to 1 cookie will hold last used tab between page refreshes
	*/
	function __construct( $useCookies )
	{
		global $mainframe;

		if($mainframe->get( 'JPanel_loaded')) {
			return;
		}

		$this->useCookies = $useCookies;

		$mainframe->set( 'JPanel', true );

	}
	
	/**
	* Load the javascript behavior and attach it to the document
	* 
	* @abstract
	*/
	function loadBehavior() {
		return;
	}
	
	/**
	 * Returns a reference to a JPanel object
	 * 
	 * @param string 	The behavior to use
	 * @param boolean	Use cookies to remember the state of the panel
	 */
	function &getInstance( $behavior = 'Tabs', $useCookies = false) 
	{
		$classname = 'JPane'.$behavior;
		$instance = new $classname($useCookies);

		return $instance;
	}

	/**
	* Creates a pane and creates the javascript object for it
	* 
	* @abstract
	* @param string The pane identifier
	*/
	function startPane( $id ) {
		return;
	}

	/**
	* Ends the pane
	* 
	* @abstract
	*/
	function endPane() {
		return;
	}

	/**
	* Creates a panel with title text and starts that panel
	* 
	* @abstract
	* @param text - The panel name and/or title
	* @param id - The panel identifer
	*/
	function startPanel( $text, $id ) { 
		return;
	}

	/** 
	* Ends a panel
	* 
	* @abstract
	*/
	function endPanel() { 
		return;
	}
}

/**
 * JPanelTabs class to to draw parameter panes
 *
 * @author		Johan Janssens <johan.janssens@joomla.org>
 * @package		Joomla.Framework
 * @subpackage	Presentation
 * @since		1.5
 */
class JPaneTabs extends JPane
{
	/**
	* Constructor
	* 
	* @param int useCookies, if set to 1 cookie will hold last used tab between page refreshes
	*/
	function __construct( $useCookies )
	{
		parent::__construct($useCookies);
		
		global $mainframe;

		if(!$mainframe->get( 'JPanelTabs_loaded')) {
			$this->loadBehavior();
		}
	}
	
	/**
	* Load the javascript behavior and attach it to the document
	* 
	* @abstract
	*/
	function loadBehavior() 
	{	
		global $mainframe;
		
		$document =& $mainframe->getDocument();
		$lang     =& $mainframe->getLanguage();

		$css  = $lang->isRTL() ? 'tabpane_rtl.css' : 'tabpane.css';
		$url = $mainframe->isAdmin() ? $mainframe->getSiteURL() : $mainframe->getBaseURL();

		$document->addStyleSheet( $url. 'includes/js/tabs/'.$css, 'text/css', null, array(' id' => 'luna-tab-style-sheet' ));
		$document->addScript( $url. 'includes/js/tabs/tabpane_mini.js' );
		
		$mainframe->set( 'JPanelTabs_loaded', true );
	}
	
   /**
	* Creates a pane and creates the javascript object for it
	* 
	* @abstract
	* @param string The pane identifier
	*/
	function startPane( $id )
	{
		global $mainframe;
		
		$document =& $mainframe->getDocument();
		
		echo "<div class=\"tab-page\" id=\"".$id."\">";
		echo "<script type=\"text/javascript\">\n";
		echo "	var tabPane1 = new WebFXTabPane( document.getElementById( \"".$id."\" ), ".$this->useCookies." )\n";
		echo "</script>\n";
	}

   /**
	* Ends the pane
	* 
	* @abstract
	*/
	function endPane() {
		echo "</div>";
	}

	/**
	* Creates a tab panel with title text and starts that panel
	*
	* @param text - The name of the tab
	* @param id - The tab identifier
	*/
	function startPanel( $text, $id ) 
	{
		echo "<div class=\"tab-page\" id=\"".$id."\">";
		echo "<h2 class=\"tab\"><span>".$text."</span></h2>";
		echo "<script type=\"text/javascript\">\n";
		echo "  tabPane1.addTabPage( document.getElementById( \"".$id."\" ) );";
		echo "</script>";
	}

	/** 
	* Ends a tab page
	*/
	function endPanel() {
		echo "</div>";
	}
}
?>