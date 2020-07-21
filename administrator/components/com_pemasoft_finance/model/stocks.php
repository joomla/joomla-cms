<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_pemasoft_finance
 *
 * @copyright   2020 PeMaSoft - Peter Mayer, All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * PeMaSoft-Finance Component data Model
 *
 * @since  1.5
 */
class PeMaSoftFinanceModelStocks extends JModelList
{
    public function getListQuery()
    {
        $db = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->select('*');
		$query->from('#__pms_finance_stocks');
	
		return $query;
        // $db = JFactory::getDbo();
        // $query = $db->getQuery(true);
        // $query->select('id,name');
        // $query->from('#__whi_countries');
        // return $query;        
        // $db->setQuery($query);
        // $result = $db->loadAssocList();
        // $temp = [];
        // foreach ($result as $row) {
        //     $temp[$row['name']] = $row['id'];
        // }
    }
}