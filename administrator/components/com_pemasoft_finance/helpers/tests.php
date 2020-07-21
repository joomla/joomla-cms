<?php

/**
 * Joomla! component pemasoft_finance
 *
 * @version $Id: tests.php 2020-07-18 17:17 svn $
 * @copyright 2020 PeMaSoft - Peter Mayer, All rights reserved.
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 * @package com_pemasoft_finance
 *
 */

// no direct access
defined('_JEXEC') or die('Restircted access');

class PeMaSoftFinanceTestHelper
{

    public static function test_system()
    {
    }

    public static function get_test_order_data()
    {
        global $test_order_data;
        if (!empty($test_order_data)) {
            return $test_order_data;
        }
        return json_decode('[
            {"id":"0","userid":"1","wkn":"593393","type":"","value":"0","options":"{\"ordertyp\":\"sell\",\"stoplimit\":0,\"trailinglimit\":0,\"limitoffset\":5,\"limitoffsettype\":\"\\u20ac\",\"limitstatus\":0,\"limitoffsetloss\":3.4,\"status\":0}"},
            {"id":"1","userid":"1","wkn":"593393","type":"","value":"0","options":"{\"ordertyp\":\"buy\",\"stoplimit\":100000,\"trailinglimit\":100000,\"limitoffset\":0.4,\"limitoffsettype\":\"\\u20ac\",\"limitstatus\":0,\"status\":0}"}
            ]', true);
    }

    public static function get_test_stock_info()
    {
        return json_decode('{"id":"3","wkn":"593393","name":"iShares Core DAX ETF","url":"https:\/\/www.finanzen.net\/\/etf\/ishares-core-dax-etf-de0005933931","type":"etf","symbol":"EXS1.DE","praefix":"","lastupdate":"1594598400"}', true);
    }

    public static function sendtelegram_test($msg)
    {
        echo "Telegram message: " . $msg . "\n";
    }

    public static function update_order_test($id, $options)
    {
        global $test_order_data;
        $test_order_data[$id]['options'] = json_encode($options);
    }

    public static function get_test_data()
    {
        return json_decode('[{"id":"11486","wkn":"593393","symbol":"EXS1.DE","value":"","datetime":"1583107200","high":"103.940002","low":"99.180000","open":"103.440002","close":"101.580002","volume":"849209","200d":"108.285749865"},{"id":"11487","wkn":"593393","symbol":"EXS1.DE","value":"","datetime":"1583193600","high":"104.839996","low":"102.500000","open":"102.980003","close":"102.900002","volume":"72651","200d":"108.274849865"},{"id":"11488","wkn":"593393","symbol":"EXS1.DE","value":"","datetime":"1583280000","high":"104.320000","low":"102.260002","open":"102.400002","close":"103.519997","volume":"63771","200d":"108.276349845"},{"id":"11489","wkn":"593393","symbol":"EXS1.DE","value":"","datetime":"1583366400","high":"104.379997","low":"101.400002","open":"104.180000","close":"102.260002","volume":"78233","200d":"108.266749855"},{"id":"11490","wkn":"593393","symbol":"EXS1.DE","value":"","datetime":"1583452800","high":"100.720001","low":"98.000000","open":"100.620003","close":"98.940002","volume":"134583","200d":"108.241049855"},{"id":"11491","wkn":"593393","symbol":"EXS1.DE","value":"","datetime":"1583712000","high":"94.150002","low":"90.370003","open":"91.070000","close":"90.599998","volume":"1438531","200d":"108.18134984"},{"id":"11492","wkn":"593393","symbol":"EXS1.DE","value":"","datetime":"1583798400","high":"94.379997","low":"89.470001","open":"91.680000","close":"90.050003","volume":"86001","200d":"108.117099845"},{"id":"11493","wkn":"593393","symbol":"EXS1.DE","value":"","datetime":"1583884800","high":"92.000000","low":"89.389999","open":"91.250000","close":"89.559998","volume":"126600","200d":"108.04749982"},{"id":"11494","wkn":"593393","symbol":"EXS1.DE","value":"","datetime":"1583971200","high":"84.900002","low":"78.260002","open":"84.510002","close":"79.169998","volume":"126973","200d":"107.92784982"},{"id":"11495","wkn":"593393","symbol":"EXS1.DE","value":"","datetime":"1584057600","high":"85.040001","low":"77.764999","open":"81.589996","close":"78.389999","volume":"83229","200d":"107.8116998"},{"id":"11496","wkn":"593393","symbol":"EXS1.DE","value":"","datetime":"1584316800","high":"76.760002","low":"70.599998","open":"75.470001","close":"75.440002","volume":"1426640","200d":"107.678699805"},{"id":"11497","wkn":"593393","symbol":"EXS1.DE","value":"","datetime":"1584403200","high":"77.610001","low":"72.250000","open":"77.370003","close":"76.250000","volume":"108550","200d":"107.557149815"},{"id":"11498","wkn":"593393","symbol":"EXS1.DE","value":"","datetime":"1584489600","high":"73.959999","low":"71.970001","open":"73.910004","close":"72.510002","volume":"134855","200d":"107.414699825"},{"id":"11499","wkn":"593393","symbol":"EXS1.DE","value":"","datetime":"1584576000","high":"74.129997","low":"70.769997","open":"72.440002","close":"73.709999","volume":"343419","200d":"107.26944981"},{"id":"11500","wkn":"593393","symbol":"EXS1.DE","value":"","datetime":"1584662400","high":"78.339996","low":"75.830002","open":"77.699997","close":"75.830002","volume":"74748","200d":"107.13489983"},{"id":"11501","wkn":"593393","symbol":"EXS1.DE","value":"","datetime":"1584921600","high":"78.089996","low":"72.580002","open":"72.820000","close":"74.750000","volume":"1022708","200d":"106.996349835"},{"id":"11502","wkn":"593393","symbol":"EXS1.DE","value":"","datetime":"1585008000","high":"81.959999","low":"78.209999","open":"79.309998","close":"81.820000","volume":"25216","200d":"106.889549835"},{"id":"11503","wkn":"593393","symbol":"EXS1.DE","value":"","datetime":"1585094400","high":"86.580002","low":"80.805000","open":"85.010002","close":"84.040001","volume":"110634","200d":"106.78764985"},{"id":"11504","wkn":"593393","symbol":"EXS1.DE","value":"","datetime":"1585180800","high":"85.349998","low":"81.500000","open":"82.900002","close":"85.320000","volume":"50392","200d":"106.694949845"},{"id":"11505","wkn":"593393","symbol":"EXS1.DE","value":"","datetime":"1585267200","high":"84.550003","low":"81.699997","open":"84.230003","close":"82.080002","volume":"57965","200d":"106.584449855"},{"id":"11506","wkn":"593393","symbol":"EXS1.DE","value":"","datetime":"1585526400","high":"83.599998","low":"80.989998","open":"83.000000","close":"83.800003","volume":"11742","200d":"106.484949885"},{"id":"11507","wkn":"593393","symbol":"EXS1.DE","value":"","datetime":"1585612800","high":"86.260002","low":"83.120003","open":"85.900002","close":"84.540001","volume":"35976","200d":"106.3889499"},{"id":"11508","wkn":"593393","symbol":"EXS1.DE","value":"","datetime":"1585699200","high":"82.699997","low":"81.489998","open":"82.459999","close":"82.230003","volume":"18488","200d":"106.27159993"},{"id":"11509","wkn":"593393","symbol":"EXS1.DE","value":"","datetime":"1585785600","high":"82.239998","low":"80.000000","open":"82.070000","close":"81.870003","volume":"18021","200d":"106.152949955"},{"id":"11510","wkn":"593393","symbol":"EXS1.DE","value":"","datetime":"1585872000","high":"82.269997","low":"81.410004","open":"81.410004","close":"81.410004","volume":"9044","200d":"106.03019998"},{"id":"11511","wkn":"593393","symbol":"EXS1.DE","value":"","datetime":"1586131200","high":"86.379997","low":"84.279999","open":"84.690002","close":"86.190002","volume":"655371","200d":"105.932949995"},{"id":"11512","wkn":"593393","symbol":"EXS1.DE","value":"","datetime":"1586217600","high":"90.580002","low":"88.099998","open":"89.480003","close":"88.379997","volume":"43395","200d":"105.849749995"},{"id":"11513","wkn":"593393","symbol":"EXS1.DE","value":"","datetime":"1586304000","high":"88.449997","low":"87.309998","open":"87.930000","close":"88.220001","volume":"27731","200d":"105.765949985"},{"id":"11514","wkn":"593393","symbol":"EXS1.DE","value":"","datetime":"1586390400","high":"91.120003","low":"88.570000","open":"89.699997","close":"90.410004","volume":"61245","200d":"105.69360002"},{"id":"11515","wkn":"593393","symbol":"EXS1.DE","value":"","datetime":"1586822400","high":"92.639999","low":"90.540001","open":"92.260002","close":"91.540001","volume":"30806","200d":"105.62530004"},{"id":"11516","wkn":"593393","symbol":"EXS1.DE","value":"","datetime":"1586908800","high":"91.059998","low":"87.639999","open":"91.059998","close":"87.900002","volume":"60364","200d":"105.53360006"},{"id":"11517","wkn":"593393","symbol":"EXS1.DE","value":"","datetime":"1586995200","high":"89.199997","low":"87.930000","open":"88.550003","close":"88.440002","volume":"75571","200d":"105.438900085"},{"id":"11518","wkn":"593393","symbol":"EXS1.DE","value":"","datetime":"1587081600","high":"91.989998","low":"90.470001","open":"90.730003","close":"90.620003","volume":"42509","200d":"105.354700105"},{"id":"11519","wkn":"593393","symbol":"EXS1.DE","value":"","datetime":"1587340800","high":"91.410004","low":"89.580002","open":"90.919998","close":"91.050003","volume":"80168","200d":"105.26945013"},{"id":"11520","wkn":"593393","symbol":"EXS1.DE","value":"","datetime":"1587427200","high":"89.970001","low":"87.900002","open":"89.919998","close":"87.989998","volume":"76720","200d":"105.168000125"},{"id":"11521","wkn":"593393","symbol":"EXS1.DE","value":"","datetime":"1587513600","high":"89.089996","low":"88.360001","open":"88.529999","close":"89.089996","volume":"47536","200d":"105.0766501"},{"id":"11522","wkn":"593393","symbol":"EXS1.DE","value":"","datetime":"1587600000","high":"90.489998","low":"88.400002","open":"89.230003","close":"90.099998","volume":"34342","200d":"104.989550105"},{"id":"11523","wkn":"593393","symbol":"EXS1.DE","value":"","datetime":"1587686400","high":"89.760002","low":"88.190002","open":"88.610001","close":"88.639999","volume":"64798","200d":"104.89975011"},{"id":"11524","wkn":"593393","symbol":"EXS1.DE","value":"","datetime":"1587945600","high":"91.000000","low":"90.150002","open":"90.440002","close":"91.000000","volume":"26383","200d":"104.82475011"},{"id":"11525","wkn":"593393","symbol":"EXS1.DE","value":"","datetime":"1588032000","high":"93.260002","low":"91.330002","open":"91.330002","close":"92.500000","volume":"38117","200d":"104.758750125"},{"id":"11526","wkn":"593393","symbol":"EXS1.DE","value":"","datetime":"1588118400","high":"95.099998","low":"92.300003","open":"92.750000","close":"95.099998","volume":"38717","200d":"104.706250125"},{"id":"11527","wkn":"593393","symbol":"EXS1.DE","value":"","datetime":"1588204800","high":"96.010002","low":"92.860001","open":"95.699997","close":"92.860001","volume":"28994","200d":"104.63925012"}]', true);
    }
}
