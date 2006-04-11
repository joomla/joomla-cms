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
	function __construct( $useCookies = true )
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
	function &getInstance( $behavior = 'Tabs', $useCookies = true) 
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
	* @param string The pane identifier
	*/
	function startPane( $id )
	{
		global $mainframe;
		
		$document =& $mainframe->getDocument();
		
		echo "<div class=\"tab-page\" id=\"".$id."\">";
		echo "<script type=\"text/javascript\">\n";
		echo "	var tabPane1 = new WebFXTabPane( document.getElementById( \"".$id."\" ), ".(int)$this->useCookies." )\n";
		echo "</script>\n";
	}

   /**
	* Ends the pane
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

/**
 * JPanelSliders class to to draw parameter panes
 *
 * @author		Johan Janssens <johan.janssens@joomla.org>
 * @package		Joomla.Framework
 * @subpackage	Presentation
 * @since		1.5
 */
class JPaneSliders extends JPane
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

		if(!$mainframe->get( 'JPanelSliders_loaded')) {
			$this->loadBehavior();
		}
	}
	
	/**
	* Load the javascript behavior and attach it to the document
	*/
	function loadBehavior() 
	{	
		global $mainframe;
		
		$document =& $mainframe->getDocument();
		$lang     =& $mainframe->getLanguage();

		$url = $mainframe->isAdmin() ? $mainframe->getSiteURL() : $mainframe->getBaseURL();

		$document->addScript( $url. 'includes/js/moofx/prototype.lite.js' );
		$document->addScript( $url. 'includes/js/moofx/moo.fx.js' );
		$document->addScript( $url. 'includes/js/moofx/moo.fx.pack.js' );
		$document->addScript( $url. 'includes/js/moofx/moo.fx.slide.js' );
		
		$mainframe->set( 'JPanelSliders_loaded', true );
	}
	
   /**
	* Creates a pane and creates the javascript object for it
	*
	* @param string The pane identifier
	*/
	function startPane( $id )
	{
		echo '<div id="'.$id.'" class="pane-sliders">';	
	}

   /**
	* Ends the pane
	*/
	function endPane() {
		echo '</div>';
		echo '<script type="text/javascript">';
		echo '	init_moofx();';
		echo '</script>';
	}

	/**
	* Creates a tab panel with title text and starts that panel
	*
	* @param text - The name of the tab
	* @param id - The tab identifier
	*/
	function startPanel( $text, $id ) 
	{
		echo '<div class="panel">';
		echo '<h3 class="moofx-toggler title" id="'.$id.'">'.$text.'</h3>';
		echo '<div class="moofx-slider content">';
	}

	/** 
	* Ends a tab page
	*/
	function endPanel() {
		echo '</div></div>';
	}
}
?>