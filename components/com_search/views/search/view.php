<?php
/**
* @version $Id$
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

jimport( 'joomla.application.view');

/**
 * HTML View class for the WebLinks component
 *
 * @static
 * @package Joomla
 * @subpackage Weblinks
 * @since 1.0
 */
class SearchViewSearch extends JView
{
	function __construct()
	{
		$this->setViewName('search');
		$this->setTemplatePath(dirname(__FILE__).DS.'tmpl');
	}

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
		$this->_loadTemplate('search_form');
	}

	function results()
	{
		global $option, $Itemid;

		$searchword   = $this->request->searchword;
		$searchphrase = $this->request->searchphrase;
		$ordering     = $this->request->ordering;

		//create pagination
		jimport('joomla.presentation.pagination');
		$pagination = new JPagination($this->data->total, $this->request->limitstart, $this->request->limit);

		$this->data->link = "index.php?option=$option&Itemid=$Itemid&searchword=$searchword&searchphrase=$searchphrase&ordering=$ordering";

		$this->data->result = sprintf( JText::_( 'TOTALRESULTSFOUND' ), $this->data->total, $this->request->searchword );
		$this->data->image 	= mosAdminMenus::ImageCheck( 'google.png', '/images/M_images/', NULL, NULL, 'Google', 'Google', 1 );

		for($i = 0; $i < count($this->data->results); $i++ )
		{
			$result =& $this->data->results[$i];
			if ($result->created) {
				$created = mosFormatDate ( $result->created, JText::_( 'DATE_FORMAT_LC' ) );
			}
			else {
				$created = '';
			}

			$result->created = $created;
			$result->count   = $i + 1;
		}
		
		$this->set('pagination', $pagination);

		$this->_loadTemplate('search_results');
	}

	function error()
	{
		$this->_loadTemplate('search_error');
	}
}
?>