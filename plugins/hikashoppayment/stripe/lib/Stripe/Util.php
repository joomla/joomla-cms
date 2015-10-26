<?php
/**
 * @package	HikaShop for Joomla!
 * @version	2.6.0
 * @author	hikashop.com
 * @copyright	(C) 2010-2015 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php

abstract class Stripe_Util
{
  public static function isList($array)
  {
    if (!is_array($array))
      return false;

    foreach (array_keys($array) as $k) {
      if (!is_numeric($k))
        return false;
    }
    return true;
  }

  public static function convertStripeObjectToArray($values)
  {
    $results = array();
    foreach ($values as $k => $v) {
      if ($k[0] == '_') {
        continue;
      }
      if ($v instanceof Stripe_Object) {
        $results[$k] = $v->__toArray(true);
      } else if (is_array($v)) {
        $results[$k] = self::convertStripeObjectToArray($v);
      } else {
        $results[$k] = $v;
      }
    }
    return $results;
  }

  public static function convertToStripeObject($resp, $apiKey)
  {
    $types = array(
      'card' => 'Stripe_Card',
      'charge' => 'Stripe_Charge',
      'coupon' => 'Stripe_Coupon',
      'customer' => 'Stripe_Customer',
      'list' => 'Stripe_List',
      'invoice' => 'Stripe_Invoice',
      'invoiceitem' => 'Stripe_InvoiceItem',
      'event' => 'Stripe_Event',
      'transfer' => 'Stripe_Transfer',
      'plan' => 'Stripe_Plan',
      'recipient' => 'Stripe_Recipient',
      'refund' => 'Stripe_Refund',
      'subscription' => 'Stripe_Subscription',
      'fee_refund' => 'Stripe_ApplicationFeeRefund',
      'bitcoin_receiver' => 'Stripe_BitcoinReceiver',
      'bitcoin_transaction' => 'Stripe_BitcoinTransaction'
    );
    if (self::isList($resp)) {
      $mapped = array();
      foreach ($resp as $i)
        array_push($mapped, self::convertToStripeObject($i, $apiKey));
      return $mapped;
    } else if (is_array($resp)) {
      if (isset($resp['object'])
          && is_string($resp['object'])
          && isset($types[$resp['object']])) {
        $class = $types[$resp['object']];
      } else {
        $class = 'Stripe_Object';
      }
      return Stripe_Object::scopedConstructFrom($class, $resp, $apiKey);
    } else {
      return $resp;
    }
  }
}
