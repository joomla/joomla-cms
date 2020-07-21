<?php

/**
 * Joomla! component pemasoft_finance
 *
 * @version $Id: helper.php 2020-07-15 23:21 svn $
 * @copyright 2020 PeMaSoft - Peter Mayer, All rights reserved.
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 * @package com_pemasoft_finance
 *
 */

// no direct access
defined('_JEXEC') or die('Restircted access');

require_once JPATH_BASE . '/administrator/components/com_pemasoft_finance/helpers/tests.php';

// Set your Bot ID and Chat ID.
$telegrambot = '1382704037:AAG1cYd8RgPCnsh4-c3z80LklnBC89LJRqQ';
$telegramchatid = 697407558;
$orderoptions = [];

class PeMaSoftFinanceHelper
{
    public static function check_orders($wkn, $price)
    {
        $orders = self::get_orders($wkn);
        foreach ($orders as $order) {
            $orderoptions = json_decode($order['options'], true);
            switch ($orderoptions['ordertyp']) {
                case "buy":
                    if (self::check_buy_order($orderoptions, $price, $order["id"])) {
                        $stockinfo = self::get_stock_info($order['wkn']);
                        $msg = "**BUY** \n\n Das Wertpapier mit der WKN " . $order['wkn'] . " hat die Buy-Grenze überschritten! Bitte werden Sie tätig! \n";
                        $msg .=" (Kurs: ".$price. "; Trailing Limit: ".$orderoptions['trailinglimit']. ") \n URL: " . $stockinfo['url'];
                        if(self::get_notification_ordertyp_status($orderoptions['ordertyp'])){
                            self::sendtelegram($msg);
                            // self::set_notification_ordertyp_status("sell", 1);
                        }
                        // Reset Buy-Options.
                        $orderoptions['stoplimit'] = 100000;
                        $orderoptions['trailinglimit'] = 100000;
                        $orderoptions['status'] = 0;
                        self::update_order($order["id"], $orderoptions);
                    }
                    break;
                case "sell":
                    if (self::check_sell_order($orderoptions, $price, $order["id"])) {
                        $stockinfo = self::get_stock_info($order['wkn']);
                        $msg = "**SELL** \n\n Das Wertpapier mit der WKN " . $order['wkn'] . " hat die Sell-Grenze unterschritten und sollte verkauft worden sein! Bitte überprüfen!\n";
                        $msg .=" (Kurs: ".$price. "; Trailing Limit: ".$orderoptions['trailinglimit']. ") \n URL: " . $stockinfo['url'];
                        if(self::get_notification_ordertyp_status($orderoptions['ordertyp'])){
                            self::sendtelegram($msg);
                            // self::set_notification_ordertyp_status("buy", 1);
                        }
                        // Reset Sell-Options.
                        $orderoptions['trailinglimit'] = 0;
                        $orderoptions['stoplimit'] = 0;
                        $orderoptions['status'] = 0;
                        self::update_order($order["id"], $orderoptions);
                    }
                    break;
            }
        }
    }

    public static function get_stock_info($wkn)
    {
        global $testcase;

        if (!empty($testcase)) {
            return PeMaSoftFinanceTestHelper::get_test_stock_info();
        }

        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select('*');
        $query->from($db->quoteName('#__pms_finance_stocks'));
        $query->where($db->quoteName('wkn') . "=" . $db->quote($wkn));
        $db->setQuery($query);

        return $db->loadAssoc();
    }


    public static function check_buy_order($orderoptions, $price, $orderid)
    {

        $orderoptions = self::checklimitstatus($orderoptions, $price, $orderid);
        $orderoptions = self::get_trailing_limit($orderoptions, $price, $orderid);

        if ($orderoptions['status'] && $orderoptions['trailinglimit'] < $price) {
            return true;
        }

        return false;
    }

    public static function check_sell_order($orderoptions, $price, $orderid)
    {
        $orderoptions = self::checklimitstatus($orderoptions, $price, $orderid);
        $orderoptions = self::get_trailing_limit($orderoptions, $price, $orderid);

        if (($orderoptions['status'] && $orderoptions['trailinglimit'] > $price) || $price < $orderoptions['buyprice'] - $orderoptions['limitoffsetloss']) {
            return true;
        }

        return false;
    }

    private static function checklimitstatus($orderoptions, $price, $orderid)
    {
        // Activate limit if necessary and possible.
        if ($orderoptions['ordertyp'] == "buy" && ($orderoptions['status'] || $orderoptions['stoplimit'] > $price)) {
            $orderoptions['status'] = 1;
        }
        if ($orderoptions['ordertyp'] == "sell" && ($orderoptions['status'] || $orderoptions['stoplimit'] < $price)) {
            $orderoptions['status'] = 1;
        }

        return self::update_order($orderid, $orderoptions);
    }

    private static function get_trailing_limit($orderoptions, $price, $orderid)
    {

        // Check if trailinglimit is set.
        if (empty($orderoptions['trailinglimit'])) {
            $orderoptions['trailinglimit'] = $orderoptions['stoplimit'];
        }

        $trailinglimit = $orderoptions['trailinglimit'];

        if ($orderoptions['limitoffsettype'] == "€") {
            $margin = $orderoptions['limitoffset'];
        } else {
            $margin = $trailinglimit * $orderoptions['limitoffset'];
        }

        switch ($orderoptions['ordertyp']) {
            case "buy":
                $trailinglimit_new =  $price + $margin;
                if ($trailinglimit_new < $trailinglimit) {
                    $trailinglimit = $trailinglimit_new;
                }
                break;
            case "sell":
                $trailinglimit_new =  $price - $margin;
                if ($trailinglimit_new > $trailinglimit) {
                    $trailinglimit = $trailinglimit_new;
                }
                break;
        }
        $orderoptions['trailinglimit'] = $trailinglimit;
        self::update_order($orderid, $orderoptions);
        return $orderoptions;
    }

    public static function get_orders($wkn)
    {
        global $testcase, $test_order_data;

        if (!empty($testcase)) {
            $test_order_data = PeMaSoftFinanceTestHelper::get_test_order_data();
            return $test_order_data;
        }

        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select('*');
        $query->from($db->quoteName('#__pms_finance_orders'));
        $query->where($db->quoteName('wkn') . "=" . $db->quote($wkn));
        $db->setQuery($query);
        return $db->loadAssocList();
    }

    private static function update_order($id, $options)
    {
        global $testcase, $test_order_data;

        if (!empty($testcase)) {
            PeMaSoftFinanceTestHelper::update_order_test($id, $options);
            return $options;
        }

        $jsonoptions = json_encode($options);

        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        // Fields to update.
        $fields = array(
            $db->quoteName('options') . ' = ' . $db->quote($jsonoptions)
        );

        // Conditions for which records should be updated.
        $conditions = array(
            $db->quoteName('id') . ' = ' . $db->quote($id)
        );

        $query->update($db->quoteName('#__pms_finance_orders'))->set($fields)->where($conditions);
        $db->setQuery($query);
        $db->execute();
        return $options;
    }

    public static function set_notification_ordertyp_status($ordertyp, $status)
    {

        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query = "Insert into " . $db->quoteName('#__pms_finance_notification_settings') . "(ordertyp, status) VALUES (".$db->quote($ordertyp).",".$db->quote($status).") On DUPLICATE KEY UPDATE status=".$db->quote($status);
echo $query ."\n";
        $db->setQuery($query);
        $db->execute();
    }

    public static function get_notification_ordertyp_status($ordertyp){
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select('*');
        $query->from($db->quoteName('#__pms_finance_notification_settings'));
        $query->where($db->quoteName('ordertyp') . "=" . $db->quote($ordertyp));
        $db->setQuery($query);
        $result = $db->loadAssoc();
        return $result['status'];
        }

    public static function toggle_notification_ordertyp_status($ordertyp)
    {

        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        // Fields to update.
        $fields = array(
            $db->quoteName('status') . ' = !' . $db->quoteName('status')
        );

        // Conditions for which records should be updated.
        $conditions = array(
            $db->quoteName('ordertyp') . ' = ' . $db->quote($ordertyp)
        );

        $query->update($db->quoteName('#__pms_finance_notification_settings'))->set($fields)->where($conditions);
        $db->setQuery($query);
        $db->execute();
    }

    public static function sendtelegram($msg)
    {
        global $telegrambot, $telegramchatid, $testcase;

        // if (!empty($testcase)) {
        //     return PeMaSoftFinanceTestHelper::sendtelegram_test($msg);
        // }
        // 1382704037:AAG1cYd8RgPCnsh4-c3z80LklnBC89LJRqQ';
        // $telegramchatid = 697407558
        $url = 'https://api.telegram.org/bot' . $telegrambot . '/sendMessage';
        // https://api.telegram.org/bot1382704037:AAG1cYd8RgPCnsh4-c3z80LklnBC89LJRqQ/getUpdates
        $inlinekeyboard = ['inline_keyboard' => [
            [
                ['text' => 'Buy pausieren', 'callback_data' => json_encode(['ordertyp' => "buy", "newstatus" => 0])],
                ['text' => 'Sell pausieren', 'callback_data' => json_encode(['ordertyp' => "sell", "newstatus" => 0])]
            ], [
                ['text' => 'Buy resetten', 'callback_data' => json_encode(['ordertyp' => "buy", "newstatus" => 1])],
                ['text' => 'Sell resetten', 'callback_data' => json_encode(['ordertyp' => "sell", "newstatus" => 1])]
            ],
        ]];

        $data = [
            'chat_id' => $telegramchatid,
            'text' => $msg,
            'reply_markup' => json_encode($inlinekeyboard)
        ];
        $options = array('http' => array('method' => 'POST', 'header' => "Content-Type:application/x-www-form-urlencoded\r\n", 'content' => http_build_query($data),),);
        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);
        return $result;
    }

    public static function get_telegram_updates()
    {
        global $telegrambot, $telegramchatid, $testcase;
        $url = 'https://api.telegram.org/bot' . $telegrambot . '/getUpdates';
        $result = file_get_contents($url, false);

        $updates = json_decode($result, true);

        foreach ($updates['result'] as $update) {
            // if (!empty($update['callback_query']) && !empty($update['callback_query']['data']['ordertyp']) && !empty($update['callback_query']['data']['newstatus'])) {
                if (!empty($update['callback_query'])) {
                    $data = json_decode($update['callback_query']['data'],true);
                    self::set_notification_ordertyp_status($data['ordertyp'], $data['newstatus']);

                // self::toggle_notification_ordertyp_status($update['callback_query']['data']);
                // echo $update['callback_query']['chat_instance'] . "\n" . $update['callback_query']['data'];
            }
        }
    }
}
