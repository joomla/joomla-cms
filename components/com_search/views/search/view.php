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

jimport( 'joomla.application.component.view');

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
	function display($tpl = null)
	{
		global $option, $Itemid;
		
		$showAreas = JArrayHelper::getValue( $this->lists, 'areas', array() );

		$areas  = array();
		foreach ($showAreas as $area) {
			$areas = array_merge( $areas, $area );
		}

		$this->areas = $areas;

		$searchword   = $this->searchword;
		$searchphrase = $this->searchphrase;
		$ordering     = $this->ordering;

		//create pagination
		jimport('joomla.html.pagination');
		$pagination = new JPagination($this->total, $this->limitstart, $this->limit);

		$this->result = sprintf( JText::_( 'TOTALRESULTSFOUND' ), $this->total, $this->searchword );
		$this->image  = mosAdminMenus::ImageCheck( 'google.png', '/images/M_images/', NULL, NULL, 'Google', 'Google', 1 );

		for($i = 0; $i < count($this->results); $i++ )
		{
			$result =& $this->results[$i];
			if ($result->created) {
				$created = mosHTML::Date ( $result->created );
			}
			else {
				$created = '';
			}

			$result->created = $created;
			$result->count   = $i + 1;
		}
		
		$this->assign('pagination', $pagination);

		parent::display($tpl);
	}
}
?>