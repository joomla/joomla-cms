<?php
/**
 * @version $Id: admin.menus.php 3504 2006-05-15 05:25:43Z eddieajau $
 * @package Joomla
 * @subpackage Menus
 * @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights
 * reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */

/**
 * @package Joomla
 * @subpackage Menus
 * @author Andrew Eddie
 */
class JMenuHelper extends JObject {
	// TODO: Move to library, derive similar class for module support

	/**
	 * @var string The component file name
	 */
	var $_option = null;

	var $_metadata;

	/**
	 * Constructor
	 */
	function __construct( $option )
	{
		// clean the option
		$option = preg_replace( '#\W#', '', $option );
		$option = str_replace( 'com_', '', $option );

		$this->_option = $option;

		// load the xml metadata
		$this->_metadata = null;

		$path = JPATH_SITE . '/components/com_' . $this->_option . '/metadata.xml';

		if (file_exists( $path ))
		{
			$xml = & JFactory::getXMLParser('Simple');

			if ($xml->loadFile($path))
			{
				$this->_metadata = &$xml;
			}
		}
	}

	/**
	 * @access private
	 */
	function &_getMetadataDoc()
	{
		$result = null;
		if (isset( $this->_metadata->document ))
		{
			$result = &$this->_metadata->document;
		}
		return $result;
	}

	function hasControlParams()
	{
		return (boolean) $this->_getMetadataDoc();
	}

	/**
	 * @param string A params string
	 * @param string The option
	 */
	function &getControlParams( $params, $path='' )
	{
		$params = new JParameter( $params );

		if ($xmlDoc =& $this->_getMetadataDoc())
		{
			if (isset( $xmlDoc->control[0] ))
			{
				if (isset( $xmlDoc->control[0]->params[0] ))
				{
					$params->setXML( $xmlDoc->control[0]->params[0] );
				}
			}
		}
		return $params;
	}

	function prepForStore(&$values) {
		return $values;
	}

	/**
	 * Allows for the parameter handling to be overridden
	 * if the component supports new parameter types
	 * @param string A params string
	 * @param string The option
	 * @return object A
	 */
	function &getViewParams( $ini, $control )
	{
		if ($this->_metadata == null)
		{
			// Check for component metadata.xml file
			$path = JApplicationHelper::getPath( 'com_xml', 'com_' . $this->_option );
			$params = new JParameter( $ini, $path );
		}
		else
		{
			$params = new JParameter( $ini );

			$viewName = $control->get( 'view_name' );
			if ($viewName && $xmlDoc =& $this->_getMetadataDoc())
			{
				$eViews = &$xmlDoc->getElementByPath( 'control/views' );
				if ($eViews)
				{
					// we have a views element
					$eParams = &$eViews->getElementByPath( $viewName . '/params' );
					if ($eParams)
					{
						// we have a params element in the metadata
						$params->setXML( $eParams );
					}
					else
					{
						// check for a different source
						$source = $eViews->attributes( 'source' );
						if ($source)
						{
							// TODO: check for injection
							$path = JPATH_SITE . str_replace( '{VIEW_NAME}', $viewName, $source );
							if (file_exists( $path ))
							{
								// load the metadata file local to the view
								$xml = & JFactory::getXMLParser('Simple');
								if ($xml->loadFile($path))
								{
									$eParams = &$xml->document->getElementByPath( 'params' );
									$params->setXML( $eParams );
								}
							}
						}
					}
				}
			}
		}
		return $params;
	}

	/**
	* build the link/url of a menu item
	*/
	function Link( &$row, $id, $link=NULL ) {
		if ( $id ) {
			switch ($row->type) {
				case 'content_item_link':
				case 'content_typed':
					// load menu params
					$params = new JParameter( $row->params, JApplicationHelper::getPath( 'menu_xml', $row->type ), 'menu' );

					if ( $params->get( 'unique_itemid' ) ) {
						$row->link .= '&Itemid='. $row->id;
					} else {
						$temp = split( '&task=view&id=', $row->link);
						require_once( JPATH_SITE . '/components/com_content/content.helper.php' );
						$row->link .= '&Itemid='. JContentHelper::getItemid($temp[1], 0, 0);
					}

					$link = $row->link;
					break;

				default:
					if ( $link ) {
						$link = $row->link;
					} else {
						$link = $row->link .'&amp;Itemid='. $row->id;
					}
					break;
			}
		} else {
			$link = NULL;
		}

		return $link;
	}

	/**
	 * Loads files required for menu items
	 * @param string Item type
	 */
	function menuItem( $item ) {
		$path = JPATH_ADMINISTRATOR .'/components/com_menus/'. $item .'/';
		include_once( $path . $item .'.class.php' );
		include_once( $path . $item .'.menu.html.php' );
	}

	/**
	 * Build the select list for parent menu item
	 */
	function Parent( &$row ) {
		global $database;

		$id = '';
		if ( $row->id ) {
			$id = "\n AND id != $row->id";
		}

		// get a list of the menu items
		// excluding the current menu item and its child elements
		$query = "SELECT m.*"
		. "\n FROM #__menu m"
		. "\n WHERE menutype = '$row->menutype'"
		. "\n AND published != -2"
		. $id
		. "\n ORDER BY parent, ordering"
		;
		$database->setQuery( $query );
		$mitems = $database->loadObjectList();

		// establish the hierarchy of the menu
		$children = array();

		if ( $mitems ) {
			// first pass - collect children
			foreach ( $mitems as $v ) {
				$pt 	= $v->parent;
				$list 	= @$children[$pt] ? $children[$pt] : array();
				array_push( $list, $v );
				$children[$pt] = $list;
			}
		}

		// second pass - get an indent list of the items
		$list = mosTreeRecurse( 0, '', array(), $children, 9999, 0, 0 );

		// assemble menu items to the array
		$mitems 	= array();
		$mitems[] 	= mosHTML::makeOption( '0', JText::_( 'Top' ) );

		foreach ( $list as $item ) {
			$mitems[] = mosHTML::makeOption( $item->id, '&nbsp;&nbsp;&nbsp;'. $item->treename );
		}

		$output = mosHTML::selectList( $mitems, 'parent', 'class="inputbox" size="10"', 'value', 'text', $row->parent );

		return $output;
	}

	/**
	* build the select list for target window
	*/
	function Target( &$row ) {
		$click[] = mosHTML::makeOption( '0', JText::_( 'Parent Window With Browser Navigation' ) );
		$click[] = mosHTML::makeOption( '1', JText::_( 'New Window With Browser Navigation' ) );
		$click[] = mosHTML::makeOption( '2', JText::_( 'New Window Without Browser Navigation' ) );
		$target = mosHTML::selectList( $click, 'browserNav', 'class="inputbox" size="4"', 'value', 'text', intval( $row->browserNav ) );
		return $target;
	}
}
?>