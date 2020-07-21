<?php

/**
 * @package    Joomla.Cli
 *
 * @copyright  2020 PeMaSoft - Peter Mayer, All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * This is a CRON script to process historical stock data which should be called from the command-line, not the
 * web. For example something like:
 * /usr/bin/php path_to_site/www/joomla/administrator/components/com_pemasoft_finance/cli/processing_historical_data.php
 */

// Initialize Joomla framework
const _JEXEC = 1;

use Joomla\CMS\Factory;

define('JPATH_BASE', realpath(dirname(__FILE__) . '/../../../../'));
require_once JPATH_BASE . '/includes/defines.php';
require_once JPATH_BASE . '/includes/framework.php';

/**
 * Process Finance data.
 *
 * @since  3.9.0
 */
class processinghistoricaldata
{
    public static function instance()
    {
        static $process;

        if (isset($process)) {
            return $process;
        }

        $process = new processinghistoricaldata();
        return $process;
    }

    public static function process_data()
    {
        $wkns = self::get_all_wkns();
        foreach($wkns as $wkn){
        $elements = self::get_data_to_process('200d', $wkn, 100);
        foreach($elements as $element){
            $avg200d = self::get_200d_average($element['wkn'], $element['datetime']);
            self::update_data_200d($element['wkn'], $element['datetime'], $avg200d );
            echo ".";
        }
    }
        echo "\n";
    }

    private static function get_all_wkns(){
        $import = self::instance();

        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select('wkn');
        $query->from($db->quoteName('#__pms_finance_data'));
        $query->group($db->quoteName('wkn'));
        $db->setQuery($query);
        $result = $db->loadAssocList();  
        $wkns = [];
        foreach($result as $key => $value){
            if(empty($value['wkn'])){continue;}
            $wkns[] = $value['wkn'];
        }
        return $wkns;
    }

    private static function get_data_to_process($type = '200d', $wkn,  $limit = 10)
    {
        $import = self::instance();

        switch ($type) {
            case "200d":
                $elements = self::get_missing_200d($wkn);
                break;
        }
        return $elements;
    }

    private static function get_missing_200d($wkn)
    {
        $import = self::instance();

        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select('wkn, close, datetime');
        $query->from($db->quoteName('#__pms_finance_data'));
        $query->where($db->quoteName('200d') . ' = "" AND ' . $db->quoteName('close') . ' > 0 AND wkn = ' . $wkn );
        $query->order($db->quoteName('datetime') . ' asc');
        $db->setQuery($query);
        return array_slice($db->loadAssocList(),199);
    }

    private static function update_data_200d($wkn, $datetime, $value){
        $import = self::instance();
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $fields = array(
            $db->quoteName('200d') . ' = ' . $db->quote($value),
        );

        // Conditions for which records should be updated.
        $conditions = array(
            $db->quoteName('wkn') . ' = ' . $db->quote($wkn),
            $db->quoteName('datetime') . ' = ' . $db->quote($datetime)
        );

        $query->update($db->quoteName('#__pms_finance_data'))->set($fields)->where($conditions);

        $db->setQuery($query);
        return $db->execute();
    }


    private static function get_200d_average($wkn, $datetime)
    {
        $import = self::instance();

        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query = 'SELECT count(*), SUM(close) as sum ';
        $query .= 'FROM (SELECT * FROM `#__pms_finance_data`  ';
        $query .= 'WHERE `wkn` = "'.$wkn.'" AND `datetime` <= ' . $datetime;
        $query .= ' ORDER BY `datetime` desc LIMIT 200 ) as second';
        $db->setQuery($query);

        $sum200d = $db->loadAssoc();
        return $sum200d['sum'] / 200;
    }
}


processinghistoricaldata::process_data();