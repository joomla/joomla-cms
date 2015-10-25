<?php
/**
* CBLib, Community Builder Library(TM)
* @version $Id: 8/10/14 12:35 AM $
* @package ${NAMESPACE}
* @copyright (C) 2004-2015 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

use CBLib\Database\Table\CheckedOrderedTable;

defined('CBLIB') or die();

/**
 * \moscomprofilerLegacyAdapterTable Class implementation
 * proxy-target class of class comprofilerDBTable
 * @see \comprofilerDBTable
 *
 * @deprecated 2.0 Use \CBLib\Database\Table\Table and its descendants instead
 * @see \CBLib\Database\Table\Table
 * @see \CBLib\Database\Table\CheckedOrderedTable
 */
class moscomprofilerLegacyAdapterTable extends CheckedOrderedTable
{
	/**
	 * Returns an array of public variable names (instead of properties names of parent class)
	 *
	 * @return array
	 */
	public function getPublicProperties()
	{
		// return $this->table->getPublicProperties();
		return array_filter( array_keys( get_object_vars( $this ) ), function( $k )
		{
			return ( substr( $k, 0, 1 ) != '_' );
		});
	}
}
