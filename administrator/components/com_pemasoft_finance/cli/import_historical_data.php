<?php

/**
 * @package    Joomla.Cli
 *
 * @copyright  2020 PeMaSoft - Peter Mayer, All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * This is a CRON script to import historical stock data which should be called from the command-line, not the
 * web. For example something like:
 * /usr/bin/php path_to_site/www/joomla/administrator/components/com_pemasoft_finance/cli/import_historical_data.php
 */

// Initialize Joomla framework
const _JEXEC = 1;

use Joomla\CMS\Factory;

define('JPATH_BASE', realpath(dirname(__FILE__) . '/../../../../'));
require_once JPATH_BASE . '/includes/defines.php';
require_once JPATH_BASE . '/includes/framework.php';

// Path to data directory.
define('DATA_PATH_STOCK_HISTORY',  __DIR__ . "/pms_finance_data");

// Temporary variable to reduce db queries.
$SYMBOL_WKN_MATCHING = [];

/**
 * Import Finance history data.
 *
 * @since  3.9.0
 */
class importdata
{
    public static function instance()
    {
        static $import;

        if (isset($import)) {
            return $import;
        }

        $import = new importdata();
        return $import;
    }

    private static function download_recent_file($lastupdate, $praefix, $symbol, $path,  $interval = '1d')
    {
        https: //query1.finance.yahoo.com/v7/finance/download/%5EGDAXI?period1=1594425600&period2=1594512000&interval=1d&events=history
        $import = self::instance();
        $today = mktime(0, 0, 0, date("m"), date("d"), date("Y"));

        // No new data accessable.
        if($today == $lastupdate){
            echo "No recent Data for $symbol!\n";
            return;
        }

        $url = 'https://query1.finance.yahoo.com/v7/finance/download/';
        $url .= $praefix . $symbol;
        $url .= '?period1=';
        $url .= $lastupdate;
        $url .= '&period2=';
        $url .= $today;
        $url .= '&interval=' .  $interval;
        $url .= '&events=history';

        echo $url . "\n";
        if (file_put_contents($path . $symbol . '.csv', file_get_contents($url))) {
            self::set_lastupdate_files($symbol, $today);
        }
    }

    private static function set_lastupdate_files($symbol, $lastupdate)
    {
        $import = self::instance();
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $fields = array(
            $db->quoteName('lastupdate') . ' = ' . $db->quote($lastupdate),
        );

        // Conditions for which records should be updated.
        $conditions = array(
            $db->quoteName('symbol') . ' = ' . $db->quote($symbol)
        );

        $query->update($db->quoteName('#__pms_finance_stocks'))->set($fields)->where($conditions);

        $db->setQuery($query);
        return $db->execute();
    }

    private static function get_csv_contents($path, $file)
    {
        $import = self::instance();
        $symbol = basename($file, '.csv');

        $data = [];
        if (($handle = fopen($path . $file, "r")) !== FALSE) {
            $headers = fgetcsv($handle, 256, ',');
            if(!array_count_values($headers)){return;}
            $headers = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $headers);

            // In order to remove hidden evil characters from array elements.
            $headers = array_map(function ($value) {
                return str_replace(
                    ['Date', 'Open', 'High', 'Low', 'Close', 'Adj Close', 'Volume'],
                    ['datetime', 'open', 'high', 'low', 'close', 'adjclose', 'volume'],
                    $value
                );
            }, $headers);
            $dismiss = [];
            $search = ['datetime', 'open', 'high', 'low', 'close', 'volume'];
            foreach ($headers as $head) {
                if (!in_array($head, $search)) {
                    array_push($dismiss, $head);
                }
            }

            // Fetching Data to fields.
            while ($row = fgetcsv($handle, 256, ',')) {
                $combined = array_combine($headers, $row);

                // Processing data.
                foreach ($combined as $key => $value) {
                    if (in_array($key, $dismiss)) {
                        unset($combined[$key]);
                    }
                    switch ($key) {
                        case 'datetime':
                            $combined['datetime'] = strtotime($combined['datetime']);
                            break;
                    }
                }
                $combined['symbol'] = $symbol;
                $symbol_wkn_array = self::get_wkn_from_db($symbol);
                $combined['wkn'] = $symbol_wkn_array[$symbol];
                $data[] = $combined;
            }
        }
        return $data;
    }


    private static function get_wkn_from_db($symbol)
    {

        global $SYMBOL_WKN_MATCHING;

        $import = self::instance();

        if (in_array($symbol, array_keys($SYMBOL_WKN_MATCHING))) {
            return $SYMBOL_WKN_MATCHING;
        }

        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select('*');
        $query->from($db->quoteName('#__pms_finance_stocks'));
        $query->where($db->quoteName('symbol') . ' = ' . $db->quote($symbol));
        $db->setQuery($query);
        $result = $db->loadAssoc();
        $SYMBOL_WKN_MATCHING[$symbol] = $result['wkn'];
        return $SYMBOL_WKN_MATCHING;
    }

    private static function check_md5_hash($path, $file)
    {
        $import = self::instance();
        $md5hash = md5_file($path . $file);
        $fileresult = self::get_file_by_params($path, $file);
        $md5hashold = $fileresult['hash'];

        if ($md5hash == $md5hashold) {
            return true;
        }

        self::set_file_infos($md5hash, $path, $file);
        return false;
    }

    private static function get_file_by_params($path, $file)
    {
        $import = self::instance();
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select('*');
        $query->from($db->quoteName('#__pms_finance_data_files'));
        $query->where($db->quoteName('filename') . ' = ' . $db->quote($file) . ' AND ' . $db->quoteName('path') . ' = ' . $db->quote($path));
        $db->setQuery($query);
        return $db->loadAssoc();
    }

    private static function set_file_infos($hash, $path, $filename)
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $columns = array('hash', 'path', 'filename');
        $values = array($db->quote($hash), $db->quote($path), $db->quote($filename));

        // Prepare the insert query.
        $query
            ->insert($db->quoteName('#__pms_finance_data_files'))
            ->columns($db->quoteName($columns))
            ->values(implode(',', $values));

        // Set the query using our newly populated query object and execute it.
        $db->setQuery($query . " ON DUPLICATE KEY UPDATE `hash` = VALUES(`hash`), `path` = VALUES(`path`), `filename` = VALUES(`filename`)");
        return $db->execute();
    }

    private static function get_files($path)
    {
        return array_diff(scandir($path), array('.', '..', '.gitignore'));
    }

    private function write_finance_data($data)
    {
        $import = self::instance();
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $columnsinit = [
            'datetime', 'open', 'high', 'low', 'close', 'volume'
        ];
        $vals = [];
        foreach ($columnsinit as $col) {
            $vals[] = 0;
        }
        $dataarr = array_combine($columnsinit, $vals);

        foreach ($data as $key => $value) {
            if (!$value) {
                $value = 0;
            }
            $dataarr[$key] = $db->quote($value);
        }

        $columns = $db->quoteName(array_keys($dataarr));
        $values = array_values($dataarr);

        $query = "INSERT IGNORE INTO " . $db->quoteName('#__pms_finance_data')
            . "(" . implode(", ", $columns) . ")"
            . " VALUES (" . implode(", ", $values) . ")";

            $db->setQuery($query);
        $db->execute();
    }

    private static function insert_data($output)
    {
        foreach ($output as $row) {
            self::write_finance_data($row);
        }
    }

    private static function check_for_new_data($path)
    {

        $import = self::instance();
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select('*');
        $query->from($db->quoteName('#__pms_finance_stocks'));
        $db->setQuery($query);
        $stocks = $db->loadAssocList();
        foreach ($stocks as $stock) {
            self::download_recent_file($stock['lastupdate'], $stock['praefix'], $stock['symbol'], $path);
        }
    }

    public static function import()
    {
        $import = self::instance();

        define('CSVBASEDIR', JPATH_BASE . "/pms_finance_data/");

        self::check_for_new_data(CSVBASEDIR);

        $files = self::get_files(CSVBASEDIR);

        foreach ($files as $file) {
            if (self::check_md5_hash(CSVBASEDIR, $file)) {
                continue;
            }
            $output = self::get_csv_contents(CSVBASEDIR, $file);

            self::insert_data($output);
            echo ".";
        }
        echo "\n";
    }
}

importdata::import();
