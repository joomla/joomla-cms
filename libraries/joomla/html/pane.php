<?php
/**
* @version		$Id$
* @package		Joomla.Framework
* @subpackage	HTML
* @copyright	Copyright (C) 2005 - 2007 Open Source Matters. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

/**
 * JPane abstract class
 *
 * @abstract
 * @author		Johan Janssens <johan.janssens@joomla.org>
 * @package		Joomla.Framework
 * @subpackage	HTML
 * @since		1.5
 */
class JPane extends JObject
{

	var $useCookies = false;

	/**
	* Constructor
	*
 	* @param array 	$params		Associative array of values
	*/
	function __construct( $params = array() ) {

	}

	/**
	 * Returns a reference to a JPanel object
	 *
	 * @param string 	$behavior   The behavior to use
	 * @param boolean	$useCookies Use cookies to remember the state of the panel
	 * @param array 	$params		Associative array of values
	 */
	function &getInstance( $behavior = 'Tabs', $params = array())
	{
		$classname = 'JPane'.$behavior;
		$instance = new $classname($params);

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

	/**
	* Load the javascript behavior and attach it to the document
	*
	* @abstract
	*/
	function _loadBehavior() {
		return;
	}
}

/**
 * JPanelTabs class to to draw parameter panes
 *
 * @author		Johan Janssens <johan.janssens@joomla.org>
 * @package		Joomla.Framework
 * @subpackage	HTML
 * @since		1.5
 */
class JPaneTabs extends JPane
{
	/**
	* Constructor
	*
	* @param array 	$params		Associative array of values
	*/
	function __construct( $params = array() )
	{
		static $loaded = false;

		parent::__construct($params);

		if(!$loaded) {
			$this->_loadBehavior($params);
			$loaded = true;
		}
	}

   /**
	* Creates a pane and creates the javascript object for it
	*
	* @param string The pane identifier
	*/
	function startPane( $id )
	{
		$document =& JFactory::getDocument();

		return "<div class=\"tab-page\" id=\"".$id."\">"
			."<script type=\"text/javascript\">\n"
			."	var tabPane1 = new WebFXTabPane( document.getElementById( \"".$id."\" ), ".(int)$this->useCookies." )\n"
			."</script>\n";
	}

   /**
	* Ends the pane
	*/
	function endPane() {
		return "</div>";
	}

	/**
	* Creates a tab panel with title text and starts that panel
	*
	* @param text - The name of the tab
	* @param id - The tab identifier
	*/
	function startPanel( $text, $id )
	{
		return "<div class=\"tab-page\" id=\"".$id."\">"
			."<h2 class=\"tab\"><span>".$text."</span></h2>"
			."<script type=\"text/javascript\">\n"
			."  tabPane1.addTabPage( document.getElementById( \"".$id."\" ) );"
			."</script>";
	}

	/**
	* Ends a tab page
	*/
	function endPanel() {
		return "</div>";
	}

	/**
	* Load the javascript behavior and attach it to the document
	*
	* @param array 	$params		Associative array of values
	*/
	function _loadBehavior($params = array())
	{
		global $mainframe;

		$document	=& JFactory::getDocument();
		$lang	 	=& JFactory::getLanguage();
		$css		= $lang->isRTL() ? 'tabpane_rtl.css' : 'tabpane.css';
		$url		= $mainframe->isAdmin() ? $mainframe->getSiteURL() : JURI::base();

		$document->addStyleSheet( $url. 'includes/js/tabs/'.$css, 'text/css', null, array(' id' => 'luna-tab-style-sheet' ));
		$document->addScript( $url. 'includes/js/tabs/tabpane_mini.js' );
	}
}

/**
 * JPanelSliders class to to draw parameter panes
 *
 * @author		Johan Janssens <johan.janssens@joomla.org>
 * @package		Joomla.Framework
 * @subpackage	HTML
 * @since		1.5
 */
class JPaneSliders extends JPane
{
	/**
	* Constructor
	*
	* @param int useCookies, if set to 1 cookie will hold last used tab between page refreshes
	*/
	function __construct( $params = array() )
	{
		static $loaded = false;

		parent::__construct($params);

		if(!$loaded) {
			$this->_loadBehavior($params);
			$loaded = true;
		}
	}

   /**
	* Creates a pane and creates the javascript object for it
	*
	* @param string The pane identifier
	*/
	function startPane( $id )
	{
		return '<div id="'.$id.'" class="pane-sliders">';
	}

   /**
	* Ends the pane
	*/
	function endPane() {
		return '</div>'
			.'<script type="text/javascript">'
			.'	init_moofx();'
			.'</script>';
	}

	/**
	* Creates a tab panel with title text and starts that panel
	*
	* @param text - The name of the tab
	* @param id - The tab identifier
	*/
	function startPanel( $text, $id )
	{
		return '<div class="panel">'
			.'<h3 class="moofx-toggler title" id="'.$id.'"><span>'.$text.'</span></h3>'
			.'<div class="moofx-slider content">';
	}

	/**
	* Ends a tab page
	*/
	function endPanel() {
		return '</div></div>';
	}

	/**
	* Load the javascript behavior and attach it to the document
	*
	* @param array 	$params		Associative array of values
	*/
	function _loadBehavior($params = array())
	{
		global $mainframe;

		$document =& JFactory::getDocument();
		$lang	 =& JFactory::getLanguage();

		$url = $mainframe->isAdmin() ? $mainframe->getSiteURL() : JURI::base();

		$document->addScript( $url. 'includes/js/moofx/moo.fx.js' );
		$document->addScript( $url. 'includes/js/moofx/moo.fx.pack.js' );
		$document->addScript( $url. 'includes/js/moofx/moo.fx.slide.js' );
	}
}
?>
