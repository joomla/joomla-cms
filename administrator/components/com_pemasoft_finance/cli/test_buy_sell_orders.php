<?php

/**
 * @package    Joomla.Cli
 *
 * @copyright  2020 PeMaSoft - Peter Mayer, All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * This is a CRON script to tests buy sell processes and should be called from the command-line, not the
 * web. For example something like:
 * /usr/bin/php path_to_site/www/joomla/administrator/components/com_pemasoft_finance/cli/simulation_historical_data.php
 */

// Initialize Joomla framework
const _JEXEC = 1;

use Joomla\CMS\Factory;

define('JPATH_BASE', realpath(dirname(__FILE__) . '/../../../../'));
require_once JPATH_BASE . '/includes/defines.php';
require_once JPATH_BASE . '/includes/framework.php';
require_once JPATH_BASE . '/administrator/components/com_pemasoft_finance/helpers/helper.php';
require_once JPATH_BASE . '/administrator/components/com_pemasoft_finance/helpers/tests.php';

// Path to data directory.
define('DATA_PATH_STOCK_HISTORY',  __DIR__ . "/pms_finance_data");

/**
 * simulation Finance Buy and Sell cascade.
 *
 * @since  3.9.0
 */

$testcase = [];
$test_order_data = [];

class test_buy_sell_notifications
{
    public static function run_buy_sell_process_test(){
        global $testcase;
        $testcase = true;

        $daystestdata = PeMaSoftFinanceTestHelper::get_test_data();
        foreach($daystestdata as $daytestdata){
            echo "\n" . $daytestdata['datetime'] . " => " . $daytestdata['close'] . " => ";
            PeMaSoftFinanceHelper::check_orders($daytestdata['wkn'], $daytestdata['close']);
        }
    }
}

test_buy_sell_notifications::run_buy_sell_process_test();