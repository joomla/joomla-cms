<?php

/**
 * @package    Joomla.Cli
 *
 * @copyright  2020 PeMaSoft - Peter Mayer, All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * This is a CRON script to simulation historical stock data which should be called from the command-line, not the
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

// Path to data directory.
define('DATA_PATH_STOCK_HISTORY',  __DIR__ . "/pms_finance_data");

// Temporary variable to reduce db queries.
$SYMBOL_WKN_MATCHING = [];

$limitoptions = [];
$orderbook = [];
$parameterbook = [];
$bestcombination = [];
$tradecounter = 0;

/**
 * simulation Finance Buy and Sell cascade.
 *
 * @since  3.9.0
 */
class simulatebuysellcascade
{

    // $asset = 5000;
    // $startdatetime = 1298937600; //=01.03.2011 === "946684800"; 01.01.2000
    // $wkn = 593393;
    // $buy = 52;




    public static function instance()
    {
        static $simulation;

        if (isset($simulation)) {
            return $simulation;
        }

        $simulation = new simulatebuysellcascade();
        return $simulation;
    }

    public static function calculate_distance_from_200d_line($wkn, $distancepo = 0.05, $distanceneg = -0.05)
    {

        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query = "SELECT * , (`close` - `200d`) / `close` as difference , IF((`close` - `200d`) / `close`<0, '-1','1') as `direction`";
        $query .= "FROM `#__pms_finance_data` ";
        $query .= "WHERE wkn = " . $wkn . " AND `200d`>0 ";
        $query .= "ORDER BY `datetime` ASC";
        $db->setQuery($query);
        $result = $db->loadAssocList();

        $processed = [];
        foreach ($result as $day) {
            $processed[$day['datetime']] = $day;
        }

        // Filter all days with bigger or smaler distance to 200d line.
        foreach ($processed as $datetime => $day) {
            if ($day['difference'] > $distancepo || $day['difference'] < $distanceneg) {
            } else {
                $processed[$datetime] = [];
            }
        }


        // Get all blocks with positive or negative distance to 200d line.
        $blocks = [];
        $tempdirection = 0;
        $jlauf = 1;

        foreach ($processed as $datetime => $day) {

            if ($day['direction'] <> 0) {
                $blocks[$jlauf][$datetime] = $day;
                if ($tempdirection <> $day['direction']) {
                    $jlauf++;
                }
                $tempdirection = $day['direction'];
            } else {
                $tempdirection = 0;
            }
        }

        $analysis = [];
        foreach ($blocks as $blockid => $blockelements) {
            foreach ($blockelements as $datetime => $element) {
                if (empty($element['direction'])) {
                    continue;
                }

                if (empty($analysis[$blockid]['start'])) {
                    $analysis[$blockid]['start'] = date("Y-m-d", $datetime);
                    $analysis[$blockid]['direction'] = $element['direction'];
                }

                if (empty($analysis[$blockid]['maxdistance'])) {
                    $analysis[$blockid]['maxdistance'] = $element['difference'];
                } elseif (!empty($analysis[$blockid]['maxdistance']) && $element['direction'] > 0 && $analysis[$blockid]['maxdistance'] < $element['difference']) {
                    $analysis[$blockid]['maxdistance'] = $element['difference'];
                } elseif (!empty($analysis[$blockid]['maxdistance']) && $element['direction'] < 0 && $analysis[$blockid]['maxdistance'] > $element['difference']) {
                    $analysis[$blockid]['maxdistance'] = $element['difference'];
                }

                if (empty($analysis[$blockid]['maxprice'])) {
                    $analysis[$blockid]['maxprice'] = $element['close'];
                    $analysis[$blockid]['maxpricedate'] = date("Y-m-d", $datetime);
                } elseif (!empty($analysis[$blockid]['maxprice']) && $analysis[$blockid]['maxprice'] < $element['close']) {
                    $analysis[$blockid]['maxprice'] = $element['close'];
                    $analysis[$blockid]['maxpricedate'] = date("Y-m-d", $datetime);
                }

                if (empty($analysis[$blockid]['minprice'])) {
                    $analysis[$blockid]['minprice'] = $element['close'];
                    $analysis[$blockid]['minpricedate'] = date("Y-m-d", $datetime);
                } elseif (!empty($analysis[$blockid]['minprice']) && $analysis[$blockid]['minprice'] > $element['close']) {
                    $analysis[$blockid]['minprice'] = $element['close'];
                    $analysis[$blockid]['minpricedate'] = date("Y-m-d", $datetime);
                }

                $analysis[$blockid]['difference_max_min_%'] = ($analysis[$blockid]['maxprice'] - $analysis[$blockid]['minprice']) / $analysis[$blockid]['minprice'];
                $analysis[$blockid]['end'] = date("Y-m-d", $datetime);
            }
        }

        foreach ($analysis as $blockid => $details) {
            if ($details['difference_max_min_%'] < 0.075) {
                unset($analysis[$blockid]);
            }
        }

        var_dump(array_values($analysis));
    }

    /**
     * @param $maxtrades integer : 0 unilimited
     */
    public static function simulate_buy_sell($startdate = "2011-01-01", $enddate = "2011-01-01", $wkn = 593393, $initiallimit = 60, $buylimitoffset = 5, $selllimitoffset = 5, $selllimitoffsetloss = 5, $limitoffsettype = "€", $maxtrades = 250)
    {
        global $limitoptions, $orderbook, $bestcombination, $tradecounter;

        $assetinitial = 5000;
        $asset = $assetinitial;
        // $datetime = mktime(0, 0, 0, 3, 1, 2011);
        $datetime = strtotime($startdate);
        $enddatetime = strtotime($enddate);
        $numberoftrades = 0;
        $lastdatetime = 0;
        $orderbook = [];

        // $maxdatetime = mktime(0, 0, 0, date("m"), 1, date("Y"));
        $limit = $initiallimit;
        $price = $limit;
        self::set_orderbook("init", null, null, $asset, 0);
        $initialdatetime = null;
        $lastdatetime = null;
        $stockdata = [1];
        $countstockdata_old = [2];
        while (count($stockdata) <> $countstockdata_old) {
            // for ($i = 0; $i < 70; $i++) {
            // echo date("Y-m-d", $stockday['datetime']) ."\n";
            // Buy shares.
            $stockdata = self::get_stock_data($wkn, $datetime, $enddatetime);
            $countstockdata_old = count($stockdata);
            if (!count($stockdata)) {
                continue;
            }
            $ordertyp = "buy";
            $limit = 100000;
            $limitoffset = $buylimitoffset;
            // $limitoffsettype = "€";
            self::get_limitoptions_default($ordertyp, $limit, $limitoffset, $limitoffsettype);


            foreach ($stockdata as $stockday) {
                if (empty($initialdatetime) and $stockday['datetime'] > 100000) {
                    $initialdatetime = $stockday['datetime'];
                }
                if (empty($stockday['close']) || empty($stockday['datetime'])) {
                    continue;
                }
                if (self::check_buy_order($stockday['close'])) {
                    $numbershares = self::calculate_max_number_of_shares($asset, $stockday['close']);
                    if ($numbershares == 0) {
                        break;
                    }
                    $asset = $asset - $numbershares * $stockday['close'];
                    self::set_orderbook("buy", $stockday['datetime'], $stockday['close'], $asset, $numbershares);
                    $datetime = $stockday['datetime'];
                    $price = $stockday['close'];
                    $limitoptions = [];
                    $tradecounter++;
                    break;
                }
            }
            // echo "====================\n";

            // Sell shares.
            $lastorder = end($orderbook);
            if (!$lastorder['numberOfShares']) {
                continue;
            }
            $stockdata = self::get_stock_data($wkn, $datetime, $enddatetime);
            if (!count($stockdata)) {
                continue;
            }
            $ordertyp = "sell";
            $limit = 0;
            $limitoffset = $selllimitoffset;
            $limitoffsetloss = $selllimitoffsetloss;
            $limitoffsettype = "€";
            self::get_limitoptions_default($ordertyp, $limit, $limitoffset, $limitoffsettype, $price, $limitoffsetloss);

            foreach ($stockdata as $stockday) {
                // echo date("Y-m-d", $stockday['datetime']);
                // echo " => " . $stockday['close'];
                // echo " (tl sell: " . $limitoptions['trailinglimit'] . ")\n";
                if ($lastdatetime < $stockday['datetime']) {
                    $lastdatetime = $stockday['datetime'];
                }
                if (empty($stockday['close']) || empty($stockday['datetime'])) {
                    continue;
                }

                if (self::check_sell_order($stockday['close'])) {
                    $asset = $asset + $numbershares * $stockday['close'];
                    self::set_orderbook("sell", $stockday['datetime'], $stockday['close'], $asset, 0);
                    $datetime = $stockday['datetime'];
                    $price = $stockday['close'];
                    $limitoptions = [];
                    $tradecounter++;
                    break;
                }
            }

            if (count($orderbook) - 1 > $maxtrades && $maxtrades <> 0) {
                $noresult = true;
                break;
            }
        }

        if (empty($noresult)) {
            // var_dump($orderbook);
            $output = end($orderbook);
            while ($output['price'] == "" || $output['numberOfShares'] > 0) {
                array_pop($orderbook);
                $output = end($orderbook);
            }
            // var_dump($output);
            $numberoftrades = count($orderbook) - 1;
            // echo "========================\n";
            // echo "Endsumme: " . $output['asset'] . " ^= " . ($output['asset']-$assetinitial)*100/$assetinitial . "%\n";
            $datetimediffyears = ($lastdatetime - $initialdatetime) / (365 * 24 * 60 * 60);
            // echo date("Y-m-d", $lastdatetime) . " - " . date("Y-m-d", $initialdatetime) . "%\n";
            // echo "Anzahl Jahre der Anlage: " . $datetimediffyears ."\n";
            $rendite = round(($output['asset'] - (5.9 * $numberoftrades) - $assetinitial) * 100 / ($assetinitial * $datetimediffyears), 2);


            if (empty($bestcombination['rendite']) || floatval($rendite) > floatval($bestcombination['rendite'])) {
                $bestcombination['rendite'] = $rendite;
                $bestcombination['blo'] = $buylimitoffset;
                $bestcombination['slo'] = $selllimitoffset;
                $bestcombination['sllo'] = $selllimitoffsetloss;
                $bestcombination['numberoftrades'] = $numberoftrades;

                $outputdata = "(Start Datum: " . $startdate . " EndDatum: " . $enddate . " BLO: " . $buylimitoffset . ", SLO: " . $selllimitoffset . ", SLLO: " . $selllimitoffsetloss . " #trades: " . $numberoftrades . ") Durchschnittliche Rendite: " . $rendite . "%\n";
                echo $outputdata;
            }
            file_put_contents(__DIR__ . "/simulation_output.txt", $outputdata, FILE_APPEND);
            // echo "(BLO: " . $buylimitoffset . ", SLO: " . $selllimitoffset . ", SLLO: " . $selllimitoffsetloss . " #trades: " . $numberoftrades . ") Durchschnittliche Rendite: " . $rendite . "%\n";

            // echo $numberoftrades . ") Durchschnittliche Rendite: " . $rendite . "%\n";
            // echo "========================\n";
        } else {
            $noresult = false;
        }
        unset($noresult);
    }



    private static function calculate_max_number_of_shares($asset, $price)
    {
        // echo "\n Anzahl: " . floor($asset / $price) . "\n";
        return floor($asset / $price);
    }

    private static function set_orderbook($ordertyp, $datetime, $price, $asset, $numbershares)
    {
        global $orderbook;

        $orderbook[] = [
            "type" => $ordertyp,
            "datetime" =>  date("Y-m-d", $datetime),
            "datetimeUNIX" =>  $datetime,
            "price" => $price,
            "asset" => $asset,
            "numberOfShares" => $numbershares
        ];
    }


    private static function get_limitoptions_default($ordertyp, $limit, $limitoffset, $limitoffsettype, $buyprice = 0, $limitoffsetloss = 5)
    {
        global $limitoptions;

        switch ($ordertyp) {
            case "buy":
                $limitoptions = [
                    "ordertyp" => "buy",
                    "stoplimit" => $limit,
                    "trailinglimit" => $limit,
                    "limitoffset" => $limitoffset,
                    "limitoffsettype" => $limitoffsettype,
                    "limitstatus" => 0
                ];
                break;
            case "sell":
                $limitoptions = [
                    "ordertyp" => "sell",
                    "buyprice" => $buyprice,
                    "stoplimit" => 0,
                    "trailinglimit" => 0,
                    "limitoffset" => $limitoffset,
                    "limitoffsettype" => $limitoffsettype,
                    "limitstatus" => 0,
                    "limitoffsetloss" => $limitoffsetloss
                ];
                break;
        }
    }

    /**
     * limitoptions:
     *      ordertyp: buy; sell;
     *      stoplimit
     *      trailinglimit
     *      limitoffset
     *      limitoffsettype -> % or €
     *      limitstatus -> 1: aktiv; 0: inaktiv
     */

    private static function get_stock_data($wkn = 593393, $datetime, $enddatetime = null)
    {
        $db = JFactory::getDbo();
        $enddatequerystr = "";
        if (!empty($enddatetime)) {
            $enddatequerystr = " AND `datetime`<" . $db->quote($enddatetime);
        }
        $query = $db->getQuery(true);
        $query = "SELECT * ";
        $query .= "FROM `#__pms_finance_data` ";
        $query .= "WHERE wkn = " . $db->quote($wkn) . " AND `datetime`>=" . $db->quote($datetime) . $enddatequerystr;
        $query .= " ORDER BY `datetime` ASC";
        // echo $query ."\n";
        $db->setQuery($query);
        return $db->loadAssocList();
    }

    private static function check_buy_order($price)
    {
        global $limitoptions;

        self::checklimitstatus("buy", $price);
        self::get_trailing_limit($price);

        if ($limitoptions['status'] && $limitoptions['trailinglimit'] < $price) {
            return true;
        }

        return false;
    }

    private static function check_sell_order($price)
    {
        global $limitoptions;

        self::checklimitstatus("sell", $price);
        self::get_trailing_limit($price);

        if (($limitoptions['status'] && $limitoptions['trailinglimit'] > $price) || $price < $limitoptions['buyprice'] - $limitoptions['limitoffsetloss']) {
            // if (($limitoptions['status'] && $limitoptions['trailinglimit'] > $price)) {
            return true;
        }

        return false;
    }

    private static function checklimitstatus($limitoptions, $price)
    {
        global $limitoptions;
        // Activate limit if necessary and possible.
        if ($limitoptions['ordertyp'] == "buy" && ($limitoptions['status'] || $limitoptions['stoplimit'] > $price)) {
            $limitoptions['status'] = 1;
        }
        if ($limitoptions['ordertyp'] == "sell" && ($limitoptions['status'] || $limitoptions['stoplimit'] < $price)) {
            $limitoptions['status'] = 1;
        }
    }

    private static function get_trailing_limit($price)
    {
        global $limitoptions;

        if (empty($limitoptions['count'])) {
            $limitoptions['count'] = 0;
        }
        // Check if trailinglimit is set.
        if (empty($limitoptions['trailinglimit'])) {
            $limitoptions['trailinglimit'] = $limitoptions['stoplimit'];
        }

        $trailinglimit = $limitoptions['trailinglimit'];

        if ($limitoptions['limitoffsettype'] == "€") {
            $margin = $limitoptions['limitoffset'];
        } else {
            $margin = floatval($trailinglimit) * floatval($limitoptions['limitoffset']);
        }
        switch ($limitoptions['ordertyp']) {
            case "buy":
                $trailinglimit_new =  $price + $margin;
                if ($trailinglimit_new < $trailinglimit) {
                    $trailinglimit = $trailinglimit_new;
                    $limitoptions['count'] = 0;
                }
                if ($limitoptions['count'] % 10 == 0) {
                    // $trailinglimit = $trailinglimit + 1;
                }
                // echo "buy => " . $price . " => ". $margin ."=>".$trailinglimit." \n";

                break;
            case "sell":
                // echo $price . " => ". $margin ."\n";
                $trailinglimit_new =  $price - $margin;
                if ($trailinglimit_new > $trailinglimit) {
                    $trailinglimit = $trailinglimit_new;
                    $limitoptions['count'] = 0;
                }
                if ($limitoptions['count'] % 10 == 0) {
                    // $trailinglimit = $trailinglimit - 1;
                }
                // echo "sell => " . $price . " => ". $margin ."=>".$trailinglimit." bzw: " . $trailinglimit_new ." \n";
                break;
        }
        $limitoptions['trailinglimit'] = $trailinglimit;
        $limitoptions['count'] = $limitoptions['count'] + 1;
        return $limitoptions;
    }


    private static function analyze_orderbook()
    {
        global $orderbook;

        $orderbookprocess = $orderbook;
        array_shift($orderbookprocess);
        $ordersperyear = [];

        foreach ($orderbookprocess as $order) {
            $year = date("Y", strtotime($order['datetimeUNIX']));
        }
    }

    public static function simulate_buy_sell_cascade_by_year($startyear, $endyear, $wkn, $initialbuylimit)
    {
        global $orderbook;
        $orderbook = [];
        for ($i = $startyear; $i <= $endyear; $i++) {
            echo "Jahr " . $i . ": \n";
            for ($blo = 0.4; $blo < 10; $blo = $blo + 0.1) {
                for ($slo = 3; $slo < 10; $slo = $slo + 0.1) {
                    for ($sllo = 2.5; $sllo < 10; $sllo = $sllo + 0.1) {
                        echo "(BLO: $blo, SLO: $slo, SLLO: $sllo #trades: ";
                        self::simulate_buy_sell($i . "-01-01", $i + 1 . "-12-31", $wkn, $initialbuylimit, $blo, $slo, $sllo);
                    }
                }
            }
            // simulatebuysellcascade::simulate_buy_sell($i . "-01-01",null,"A1W8SB", 100,0.4,0.9,0.4);
            // for($j=0.1; $j<7; $j=$j+0.1){
            // simulatebuysellcascade::simulate_buy_sell($i . "-01-01",$i."-12-31","593393", 100,0.3, 1.7,0.6);
            // simulatebuysellcascade::simulate_buy_sell($i . "-01-01",$i."-12-31","593393", 100,0.4,5,3.4);
            // }
        }
    }

    /**
     * @param string $wkn
     * @param int $startdate UNIX timestamp
     * @param int $enddate UNIX timestamp
     * @param float $slo Sell limit offset
     * @param float $sllo Sell loss limit offset
     * @param float $initialbuylimit
     * 
     */
    public static function simulate_buy_sell_cascade_by_blo($wkn, $startdate, $enddate, $initialbuylimit, $slo, $sllo, $limitoffsettype = "€", $maxtrades = 100)
    {
        global $orderbook;
        $orderbook = [];

        if (!is_int($startdate)) {
            $startdate = strtotime($startdate);
        } else if (!is_int($enddate)) {
            $enddate = strtotime($enddate);
        }

        for ($blo = 0.1; $blo < 10; $blo = $blo + 0.1) {
            simulatebuysellcascade::simulate_buy_sell($startdate, $enddate, $wkn, $initialbuylimit, $blo, $slo, $sllo, $limitoffsettype, $maxtrades);
        }
    }

    /**
     * @param string $wkn
     * @param int $startdate UNIX timestamp
     * @param int $enddate UNIX timestamp
     * @param float $slo Sell limit offset
     * @param float $sllo Sell loss limit offset
     * @param float $initialbuylimit
     * 
     */
    public static function simulate_buy_sell_cascade_by_slo($wkn, $startdate, $enddate, $initialbuylimit, $blo, $sllo, $limitoffsettype = "€", $maxtrades = 100)
    {
        global $orderbook;
        $orderbook = [];

        if (!is_int($startdate)) {
            $startdate = strtotime($startdate);
        } else if (!is_int($enddate)) {
            $enddate = strtotime($enddate);
        }

        for ($slo = 0.1; $slo < 10; $slo = $slo + 0.1) {
            simulatebuysellcascade::simulate_buy_sell($startdate, $enddate, $wkn, $initialbuylimit, $blo, $slo, $sllo, $limitoffsettype, $maxtrades);
        }
    }

    /**
     * @param string $wkn
     * @param int $startdate UNIX timestamp
     * @param int $enddate UNIX timestamp
     * @param float $slo Sell limit offset
     * @param float $sllo Sell loss limit offset
     * @param float $initialbuylimit
     * 
     */
    public static function simulate_buy_sell_cascade_by_sllo($wkn, $startdate, $enddate, $initialbuylimit, $blo, $slo, $limitoffsettype = "€", $maxtrades = 100)
    {
        global $orderbook;
        $orderbook = [];

        if (!is_int($startdate)) {
            $startdate = strtotime($startdate);
        } else if (!is_int($enddate)) {
            $enddate = strtotime($enddate);
        }

        for ($sllo = 0.1; $sllo < 10; $sllo = $sllo + 0.1) {
            simulatebuysellcascade::simulate_buy_sell($startdate, $enddate, $wkn, $initialbuylimit, $blo, $slo, $sllo, $limitoffsettype, $maxtrades);
        }
    }

    public static function find_best_combination($wkn = "593393", $mindate = "2020-01-01", $maxdate = "2020-12-31",  $minblo = 0.1, $maxblo = 10, $minslo = 0.1, $maxslo = 10, $minsllo = 0.1, $maxsllo = 10, $deltastep = 0.1, $limitoffsettyp = "€", $maxtrades = 500)
    {
        global $bestcombination, $orderbook;
        $combinations = 0;

        $maxcombinations = ((floatval($maxblo) - floatval($minblo)+floatval($deltastep))/floatval($deltastep) * (floatval($maxslo) - floatval($minslo)+floatval($deltastep))/floatval($deltastep) * (floatval($maxsllo) - floatval($minsllo)+floatval($deltastep))/floatval($deltastep));
echo "\n".$maxcombinations."\n";
        echo "Combinations:      ";  // 5 characters of padding at the end
        for ($i = $minblo; $i < $maxblo; $i = $i + $deltastep) {
            //     // simulatebuysellcascade::simulate_buy_sell($i . "-01-01",null,"A1W8SB", 100,0.4,0.9,0.4);
            // simulatebuysellcascade::simulate_buy_sell("2010-01-01","2020-12-31","A1W8SB", 100,0.4,0.9,0.4);
            for ($j = $minslo; $j < $maxslo; $j = $j + $deltastep) {
                for ($k = $minsllo; $k < $maxsllo; $k = $k + $deltastep) {
                    $orderbook = [];
                    $combinations++;
                    simulatebuysellcascade::simulate_buy_sell($mindate, $maxdate, $wkn, 100, $i, $j, $k, $limitoffsettyp, $maxtrades);

                    echo "\033[6D";      // Move 5 characters backward
                    echo str_pad(round(floatval($combinations) * 100 / floatval($maxcombinations), 1), 5, '_', STR_PAD_LEFT) . "%";    // Output is always 5 characters long
                    //     // simulatebuysellcascade::simulate_buy_sell("2020-01-01","2020-12-31","593393", 100,0.4,5,3.4);
                }
            }
        }

        $outputdata =  "\n Es wurden " . $combinations . " Kombinationen getestet! \n";
        file_put_contents(__DIR__ . "/simulation_output.txt", $outputdata, FILE_APPEND);

        return json_encode($bestcombination);
    }
}

simulatebuysellcascade::find_best_combination("593393", "2016-01-01", "2020-12-31",  0.3, 0.4, 3, 6, 2.5, 6, 0.1, "€", 250);

// simulatebuysellcascade::calculate_distance_from_200d_line(593393, 0.1, -0.1);
// simulatebuysellcascade::calculate_distance_from_200d_line(593393,0.05,-0.05);

// simulatebuysellcascade::simulate_buy_sell_cascade_by_sllo("593393", "2010-01-01", "2020-12-31", 10, 0.4, 5);
// simulatebuysellcascade::simulate_buy_sell("2010-01-01", "2020-12-31", "593393", 100, 0.4, 5, 3.4);
// simulatebuysellcascade::simulate_buy_sell("2010-01-01","2020-12-31","593393", 100,0.05,.1,0.1, "%");


// for($i=2014; $i<=2018; $i++){
//     $orderbook = [];
//     echo "Jahr " . $i .": \n";
//     // simulatebuysellcascade::simulate_buy_sell($i . "-01-01",null,"A1W8SB", 100,0.4,0.9,0.4);
//     // for($j=0.1; $j<7; $j=$j+0.1){
//         // simulatebuysellcascade::simulate_buy_sell($i . "-01-01",$i."-12-31","593393", 100,0.3, 1.7,0.6);
//         // simulatebuysellcascade::simulate_buy_sell($i . "-01-01",$i."-12-31","593393", 100,0.4,5,3.4);
//     simulatebuysellcascade::simulate_buy_sell($i . "-01-01",$i+2 ."-12-31","A1W8SB", 100,0.4,0.9,0.4);
//     // }
// }

// for ($i = 0.01; $i < 0.15; $i = $i + 0.01) {
//     $orderbook = [];
//     //     // simulatebuysellcascade::simulate_buy_sell($i . "-01-01",null,"A1W8SB", 100,0.4,0.9,0.4);
//     // simulatebuysellcascade::simulate_buy_sell("2010-01-01","2020-12-31","A1W8SB", 100,0.4,0.9,0.4);
//     simulatebuysellcascade::simulate_buy_sell("2020-01-01", "2020-12-31", "593393", 100, $i, 0.15, 0.35, "%");
//     //     // simulatebuysellcascade::simulate_buy_sell("2020-01-01","2020-12-31","593393", 100,0.4,5,3.4);
// }
// PeMaSoftFinanceHelper::sendtelegram("Dieser ETF hat die Buy-Grenze überschritten");