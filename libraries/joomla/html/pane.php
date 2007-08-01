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

// Check to ensure this file is within the rest of the framework
defined('JPATH_BASE') or die();

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
 	* @param	array	$params		Associative array of values
	*/
	function __construct( $params = array() )
	{
	}

	/**
	 * Returns a reference to a JPanel object
	 *
	 * @param	string 	$behavior   The behavior to use
	 * @param	boolean	$useCookies Use cookies to remember the state of the panel
	 * @param	array 	$params		Associative array of values
	 * @return	object
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
	 * @param	string	The pane identifier
	 */
	function startPane( $id )
	{
		return;
	}

	/**
	 * Ends the pane
	 *
	 * @abstract
	 */
	function endPane()
	{
		return;
	}

	/**
	 * Creates a panel with title text and starts that panel
	 *
	 * @abstract
	 * @param	string	$text The panel name and/or title
	 * @param	string	$id The panel identifer
	 */
	function startPanel( $text, $id )
	{
		return;
	}

	/**
	 * Ends a panel
	 *
	 * @abstract
	 */
	function endPanel()
	{
		return;
	}

	/**
	 * Load the javascript behavior and attach it to the document
	 *
	 * @abstract
	 */
	function _loadBehavior()
	{
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
	 * @param	array 	$params		Associative array of values
	 */
	function __construct( $params = array() )
	{
		static $loaded = false;

		parent::__construct($params);

		if (!$loaded) {
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
		return '<dl class="tabs" id="'.$id.'">';
	}

	/**
	 * Ends the pane
	 */
	function endPane()
	{
		return "</dl>";
	}

	/**
	 * Creates a tab panel with title text and starts that panel
	 *
	 * @param	string	$text	The name of the tab
	 * @param	string	$id		The tab identifier
	 */
	function startPanel( $text, $id )
	{
		return '<dt id="'.$id.'"><span>'.$text.'</span></dt><dd>';
	}

	/**
	 * Ends a tab page
	 */
	function endPanel()
	{
		return "</dd>";
	}

	/**
	 * Load the javascript behavior and attach it to the document
	 *
	 * @param	array 	$params		Associative array of values
	 */
	function _loadBehavior($params = array())
	{
		global $mainframe;

		// Include mootools framework
		JHTML::_('behavior.mootools');

		$document =& JFactory::getDocument();

		$options = '{';
		$opt['onActive']		= (isset($params['onActive'])) ? $params['onActive'] : null ;
		$opt['onBackground'] = (isset($params['onBackground'])) ? $params['onBackground'] : null ;
		$opt['display']		= (isset($params['startOffset'])) ? (int)$params['startOffset'] : null ;
		foreach ($opt as $k => $v)
		{
			if ($v) {
				$options .= $k.': '.$v.',';
			}
		}
		if (substr($options, -1) == ',') {
			$options = substr($options, 0, -1);
		}
		$options .= '}';

		$js = '		window.addEvent(\'domready\', function(){ $$(\'dl.tabs\').each(function(tabs){ new JTabs(tabs, '.$options.'); }); });';

		$document->addScriptDeclaration( $js );
		$url = $mainframe->isAdmin() ? $mainframe->getSiteURL() : JURI::base();
		$document->addScript( $url. 'media/system/js/tabs.js' );
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
		return '</div>';
	}

	/**
	 * Creates a tab panel with title text and starts that panel
	 *
	 * @param	string	$text - The name of the tab
	 * @param	string	$id - The tab identifier
	 */
	function startPanel( $text, $id )
	{
		return '<div class="panel">'
			.'<h3 class="jpane-toggler title" id="'.$id.'"><span>'.$text.'</span></h3>'
			.'<div class="jpane-slider content">';
	}

	/**
	 * Ends a tab page
	 */
	function endPanel()
	{
		return '</div></div>';
	}

	/**
	 * Load the javascript behavior and attach it to the document
	 *
	 * @param	array 	$params		Associative array of values
	 */
	function _loadBehavior($params = array())
	{
		// Include mootools framework
		JHTML::_('behavior.mootools');

		$document =& JFactory::getDocument();

		$options = '{';
		$opt['onActive']	 = 'function(toggler, i) { toggler.addClass(\'jpane-toggler-down\'); toggler.removeClass(\'jpane-toggler\'); }';
		$opt['onBackground'] = 'function(toggler, i) { toggler.addClass(\'jpane-toggler\'); toggler.removeClass(\'jpane-toggler-down\'); }';
		$opt['duration']	 = (isset($params['duration'])) ? (int)$params['duration'] : 300;
		$opt['display']		 = (isset($params['startOffset']) && ($params['startTransition'])) ? (int)$params['startOffset'] : null ;
		$opt['show']		 = (isset($params['startOffset']) && (!$params['startTransition'])) ? (int)$params['startOffset'] : null ;
		$opt['opacity']		 = (isset($params['opacityTransition']) && ($params['opacityTransition'])) ? 'true' : 'false' ;
		$opt['alwaysHide']	 = (isset($params['allowAllClose']) && ($params['allowAllClose'])) ? 'true' : null ;
		foreach ($opt as $k => $v)
		{
			if ($v) {
				$options .= $k.': '.$v.',';
			}
		}
		if (substr($options, -1) == ',') {
			$options = substr($options, 0, -1);
		}
		$options .= '}';

		$js = '		window.addEvent(\'domready\', function(){ new Accordion($$(\'.panel h3.jpane-toggler\'), $$(\'.panel div.jpane-slider\'), '.$options.'); });';

		$document->addScriptDeclaration( $js );
	}
}