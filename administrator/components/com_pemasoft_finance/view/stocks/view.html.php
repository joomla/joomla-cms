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
 * HTML View class for the com_pemasoft_finance component
 *
 * @since  1.0
 */
class PemasoftFinanceViewStocks extends JViewLegacy
{
    public function display($tpl = null){
        // Get some data from the models
        $stocks = $this->get('Items');
        $this->stocks = &$stocks;

        parent::display($tpl);
        
    }
}
