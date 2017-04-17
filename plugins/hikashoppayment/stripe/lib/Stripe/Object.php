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

class Stripe_Object implements ArrayAccess
{
  public static $permanentAttributes;
  public static $nestedUpdatableAttributes;

  public static function init()
  {
    self::$permanentAttributes = new Stripe_Util_Set(array('_apiKey', 'id'));
    self::$nestedUpdatableAttributes = new Stripe_Util_Set(array('metadata'));
  }

  protected $_apiKey;
  protected $_values;
  protected $_unsavedValues;
  protected $_transientValues;
  protected $_retrieveOptions;

  public function __construct($id=null, $apiKey=null)
  {
    $this->_apiKey = $apiKey;
    $this->_values = array();
    $this->_unsavedValues = new Stripe_Util_Set();
    $this->_transientValues = new Stripe_Util_Set();

    $this->_retrieveOptions = array();
    if (is_array($id)) {
      foreach ($id as $key => $value) {
        if ($key != 'id') {
          $this->_retrieveOptions[$key] = $value;
        }
      }
      $id = $id['id'];
    }

    if ($id !== null) {
      $this->id = $id;
    }
  }

  public function __set($k, $v)
  {
    if ($v === "") {
      throw new InvalidArgumentException(
          'You cannot set \''.$k.'\'to an empty string. '
          .'We interpret empty strings as NULL in requests. '
          .'You may set obj->'.$k.' = NULL to delete the property'
      );
    }

    if (self::$nestedUpdatableAttributes->includes($k)
        && isset($this->$k) && is_array($v)) {
      $this->$k->replaceWith($v);
    } else {
      $this->_values[$k] = $v;
    }
    if (!self::$permanentAttributes->includes($k))
      $this->_unsavedValues->add($k);
  }
  public function __isset($k)
  {
    return isset($this->_values[$k]);
  }
  public function __unset($k)
  {
    unset($this->_values[$k]);
    $this->_transientValues->add($k);
    $this->_unsavedValues->discard($k);
  }
  public function __get($k)
  {
    if (array_key_exists($k, $this->_values)) {
      return $this->_values[$k];
    } else if ($this->_transientValues->includes($k)) {
      $class = get_class($this);
      $attrs = join(', ', array_keys($this->_values));
      $message = "Stripe Notice: Undefined property of $class instance: $k. "
               . "HINT: The $k attribute was set in the past, however. "
               . "It was then wiped when refreshing the object "
               . "with the result returned by Stripe's API, "
               . "probably as a result of a save(). The attributes currently "
               . "available on this object are: $attrs";
      error_log($message);
      return null;
    } else {
      $class = get_class($this);
      error_log("Stripe Notice: Undefined property of $class instance: $k");
      return null;
    }
  }

  public function offsetSet($k, $v)
  {
    $this->$k = $v;
  }

  public function offsetExists($k)
  {
    return array_key_exists($k, $this->_values);
  }

  public function offsetUnset($k)
  {
    unset($this->$k);
  }
  public function offsetGet($k)
  {
    return array_key_exists($k, $this->_values) ? $this->_values[$k] : null;
  }

  public function keys()
  {
    return array_keys($this->_values);
  }

  public static function scopedConstructFrom($class, $values, $apiKey=null)
  {
    $obj = new $class(isset($values['id']) ? $values['id'] : null, $apiKey);
    $obj->refreshFrom($values, $apiKey);
    return $obj;
  }

  public static function constructFrom($values, $apiKey=null)
  {
    return self::scopedConstructFrom(__CLASS__, $values, $apiKey);
  }

  public function refreshFrom($values, $apiKey, $partial=false)
  {
    $this->_apiKey = $apiKey;

    if ($partial) {
      $removed = new Stripe_Util_Set();
    } else {
      $removed = array_diff(array_keys($this->_values), array_keys($values));
    }

    foreach ($removed as $k) {
      if (self::$permanentAttributes->includes($k))
        continue;
      unset($this->$k);
    }

    foreach ($values as $k => $v) {
      if (self::$permanentAttributes->includes($k) && isset($this[$k]))
        continue;

      if (self::$nestedUpdatableAttributes->includes($k) && is_array($v)) {
        $this->_values[$k] = Stripe_Object::scopedConstructFrom(
            'Stripe_AttachedObject', $v, $apiKey
        );
      } else {
        $this->_values[$k] = Stripe_Util::convertToStripeObject($v, $apiKey);
      }

      $this->_transientValues->discard($k);
      $this->_unsavedValues->discard($k);
    }
  }

  public function serializeParameters()
  {
    $params = array();
    if ($this->_unsavedValues) {
      foreach ($this->_unsavedValues->toArray() as $k) {
        $v = $this->$k;
        if ($v === NULL) {
          $v = '';
        }
        $params[$k] = $v;
      }
    }

    foreach (self::$nestedUpdatableAttributes->toArray() as $property) {
      if (isset($this->$property)
          && $this->$property instanceOf Stripe_Object) {
        $params[$property] = $this->$property->serializeParameters();
      }
    }
    return $params;
  }

  protected function _lsb($method)
  {
    $class = get_class($this);
    $args = array_slice(func_get_args(), 1);
    return call_user_func_array(array($class, $method), $args);
  }
  protected static function _scopedLsb($class, $method)
  {
    $args = array_slice(func_get_args(), 2);
    return call_user_func_array(array($class, $method), $args);
  }

  public function __toJSON()
  {
    if (defined('JSON_PRETTY_PRINT')) {
      return json_encode($this->__toArray(true), JSON_PRETTY_PRINT);
    } else {
      return json_encode($this->__toArray(true));
    }
  }

  public function __toString()
  {
    $class = get_class($this);
    return $class . ' JSON: ' . $this->__toJSON();
  }

  public function __toArray($recursive=false)
  {
    if ($recursive) {
      return Stripe_Util::convertStripeObjectToArray($this->_values);
    } else {
      return $this->_values;
    }
  }
}


Stripe_Object::init();
