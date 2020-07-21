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

// Temporary variable to reduce db queries.
$SYMBOL_WKN_MATCHING = [];

use Joomla\CMS\Factory;


define('JPATH_BASE', realpath(dirname(__FILE__) . '/../../../../'));

require_once JPATH_BASE . '/includes/defines.php';
require_once JPATH_BASE . '/includes/framework.php';
require_once JPATH_BASE . '/administrator/components/com_pemasoft_finance/helpers/helper.php';
// Path to data directory.

/**
 * Import Finance recent data.
 *
 * @since  3.9.0
 */
class importrecentdata
{
    public static function instance()
    {
        static $import;

        if (isset($import)) {
            return $import;
        }

        $import = new importrecentdata();
        return $import;
    }

    private static function get_symbol_from_db($wkn){
        
        global $SYMBOL_WKN_MATCHING;

        $import = self::instance();

        if(in_array($wkn,array_keys($SYMBOL_WKN_MATCHING))){
            return $SYMBOL_WKN_MATCHING;
        }

        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select('*');
        $query->from($db->quoteName('#__pms_finance_stocks'));
        $query->where($db->quoteName('wkn') . ' = ' . $db->quote($wkn));
        $db->setQuery($query);
        $result = $db->loadAssoc();
        $SYMBOL_WKN_MATCHING[$wkn] = $result['symbol'];
        return $SYMBOL_WKN_MATCHING;
    }
    
    public static function import()
    {
        $import = self::instance();
        $stocks = self::get_stocks();

        $patterns_index = [
            "wkn" => '/WKN: ([^<]+)/',
            "name" => '/<h1 class="font-resize">([^<]+)/',
            "datetime" => '/<strong>Kurszeit<\/strong><\/td><td colspan="[\d]"  >([^<U]+)|Kurszeit<\/td><td colspan="[\d]">([^<]+)/',
            "value" => '/<div class="col-xs-5 col-sm-4 text-sm-right text-nowrap">([^<]+)<span>/'
        ];

        $patterns_etf = [
            "wkn" => '/WKN:<\/span>([^<]+)/',
            "name" => '/<h1 class="no-mtop font-resize">([^<]+)<\/h1>/',
            "datetime" => '/<th>Kurszeit<\/th><td colspan="[\d]">([^<]+)<\/td>/',
            "value" => '/<th>Kurs<\/th><td colspan="[\d]">([^<E]+)/'
        ];

        foreach ($stocks as $stock) {
            $content = file_get_contents($stock['url']);
            $content = str_replace(array("\n", "\r", "\t"), '', $content, $count);
            $content = trim($content);
            switch ($stock['type']) {
                case "etf":
                    $patterns = $patterns_etf;
                    break;
                case "index":
                    $patterns = $patterns_index;
                    break;
                case "stock":
                    $patterns = $patterns_index;
                    break;
            }
            foreach ($patterns as $detail => $pattern) {
                preg_match($pattern, $content, $matches);
                switch ($detail) {
                    case "wkn":
                        $wkn = $matches[1];
                        $symbol_wkn_array = self::get_symbol_from_db($wkn);
                        $symbol = $symbol_wkn_array[$wkn];
                        break;
                    case "datetime":
                        if (empty($matches[1])) {
                            $matches[1] = $matches[2];
                        }
                        $matches[1] = strtotime($matches[1]);
                        $datetime = $matches[1];
                        break;
                    case "value":
                        $matches[1] = str_replace(array("."), '', $matches[1]);
                        $matches[1] = str_replace(array(","), '.', $matches[1]);
                        $matches[1] = floatval($matches[1]);
                        $value = $matches[1];
                        break;
                }
            }
            self::set_stock_data($wkn, $symbol, $value, $datetime);
            PeMaSoftFinanceHelper::check_orders($wkn,$value);
            $wkn = null;
        }
    }

    private static function get_stocks($active = 1)
    {
        $import = self::instance();
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select('url, type');
        $query->from($db->quoteName('#__pms_finance_stocks'));
        $db->setQuery($query);
        return $db->loadAssocList();
    }

    private static function set_stock_data($wkn, $symbol, $value, $datetime)
    {
        $import = self::instance();

        $columns = ['wkn', 'symbol', 'value', 'datetime'];
        $values = [$wkn, $symbol, $value, $datetime];

        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query
            ->insert($db->quoteName('#__pms_finance_data'))
            ->columns($db->quoteName($columns))
            ->values(implode(',', $db->quote($values)));
            // $query .= ' ON DUPLICATE KEY IGNORE';
            $db->setQuery(preg_replace('~INSERT \K~', 'IGNORE ', $query, 1));
            // $query .= ' ON DUPLICATE KEY UPDATE ' . $db->quoteName('value') . ' = ' . $db->quote($value);
            // $db->setQuery($query);
        $db->execute();
    }

}

importrecentdata::import();
