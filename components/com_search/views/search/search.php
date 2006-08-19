<?php
/**
* @version $Id: weblink.php 4457 2006-08-11 02:20:43Z Jinx $
* @package Joomla
* @subpackage Weblinks
* @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
* @license GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

/**
 * HTML View class for the WebLinks component
 *
 * @static
 * @package Joomla
 * @subpackage Weblinks
 * @since 1.0
 */
class SearchViewSearch extends JObject
{
	/**
	 * @param string
	 * @param array
	 * @param object
	 * @param array Array of the selected areas
	 */
	function display() 
	{	
		$this->_loadTemplate('search');
	}
	
	function form()
	{
		$showAreas = JArrayHelper::getValue( $this->lists, 'areas', array() );
	
		$areas  = array();
		foreach ($showAreas as $area) {
			$areas = array_merge( $areas, $area );
		}
		
		$this->data->areas = $areas;
		$this->_loadTemplate('_search_form');
	}
	
	function results()
	{
		global $option, $Itemid;
		
		$searchword   = $this->request->searchword;
		$searchphrase = $this->request->searchphrase;
		$ordering     = $this->request->ordering; 
		
		//create pagination
		jimport('joomla.presentation.pagination');
		$this->pagination = new JPagination($this->data->total, $this->request->limitstart, $this->request->limit);
		
		$this->data->link = "index.php?option=$option&Itemid=$Itemid&searchword=$searchword&searchphrase=$searchphrase&ordering=$ordering";
		
		$this->data->result = sprintf( JText::_( 'TOTALRESULTSFOUND' ), $this->data->total, $this->request->searchword );
		$this->data->image 	= mosAdminMenus::ImageCheck( 'google.png', '/images/M_images/', NULL, NULL, 'Google', 'Google', 1 );
			
		for($i = 0; $i < count($this->data->rows); $i++ ) 
		{
			$rows =& $this->data->rows[$i];
			if ($row->created) {
				$created = mosFormatDate ( $row->created, JText::_( 'DATE_FORMAT_LC' ) );
			}
			else { 
				$created = '';
			}
	 		
			$row->created = $created;
		}
		
		$this->_loadTemplate('_search_results');
	}
	
	function error()
	{
		$this->_loadTemplate('_search_error');
	}
	
	function _loadTemplate( $template )
	{
		global $mainframe, $Itemid, $option;
		
		require(dirname(__FILE__).DS.'tmpl'.DS.$template.'.php');	
	}
}
?>