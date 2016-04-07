<?php

/**
 * Akeeba Restore
 * A JSON-powered JPA, JPS and ZIP archive extraction library
 *
 * @copyright   2010-2014 Nicholas K. Dionysopoulos / Akeeba Ltd.
 * @license     GNU GPL v2 or - at your option - any later version
 * @package     akeebabackup
 * @subpackage  kickstart
 */

/**
 * The base class of Akeeba Engine objects. Allows for error and warnings logging
 * and propagation. Largely based on the Joomla! 1.5 JObject class.
 */
abstract class AKAbstractObject
{
  /** @var  array  An array of errors */
  private $_errors = array();

  /** @var  array  The queue size of the $_errors array. Set to 0 for infinite size. */
  protected $_errors_queue_size = 0;

  /** @var  array  An array of warnings */
  private $_warnings = array();

  /** @var  array  The queue size of the $_warnings array. Set to 0 for infinite size. */
  protected $_warnings_queue_size = 0;

  /**
   * Public constructor, makes sure we are instantiated only by the factory class
   */
  public function __construct()
  {
    /*
    // Assisted Singleton pattern
    if(function_exists('debug_backtrace'))
    {
      $caller=debug_backtrace();
      if(
        ($caller[1]['class'] != 'AKFactory') &&
        ($caller[2]['class'] != 'AKFactory') &&
        ($caller[3]['class'] != 'AKFactory') &&
        ($caller[4]['class'] != 'AKFactory')
      ) {
        var_dump(debug_backtrace());
        trigger_error("You can't create direct descendants of ".__CLASS__, E_USER_ERROR);
      }
    }
    */
  }

  /**
   * Get the most recent error message
   * @param  integer  $i Optional error index
   * @return  string  Error message
   */
  public function getError($i = null)
  {
    return $this->getItemFromArray($this->_errors, $i);
  }

  /**
   * Return all errors, if any
   * @return  array  Array of error messages
   */
  public function getErrors()
  {
    return $this->_errors;
  }

  /**
   * Add an error message
   * @param  string $error Error message
   */
  public function setError($error)
  {
    if($this->_errors_queue_size > 0)
    {
      if(count($this->_errors) >= $this->_errors_queue_size)
      {
        array_shift($this->_errors);
      }
    }
    array_push($this->_errors, $error);
  }

  /**
   * Resets all error messages
   */
  public function resetErrors()
  {
    $this->_errors = array();
  }

  /**
   * Get the most recent warning message
   * @param  integer  $i Optional warning index
   * @return  string  Error message
   */
  public function getWarning($i = null)
  {
    return $this->getItemFromArray($this->_warnings, $i);
  }

  /**
   * Return all warnings, if any
   * @return  array  Array of error messages
   */
  public function getWarnings()
  {
    return $this->_warnings;
  }

  /**
   * Add an error message
   * @param  string $error Error message
   */
  public function setWarning($warning)
  {
    if($this->_warnings_queue_size > 0)
    {
      if(count($this->_warnings) >= $this->_warnings_queue_size)
      {
        array_shift($this->_warnings);
      }
    }

    array_push($this->_warnings, $warning);
  }

  /**
   * Resets all warning messages
   */
  public function resetWarnings()
  {
    $this->_warnings = array();
  }

  /**
   * Propagates errors and warnings to a foreign object. The foreign object SHOULD
   * implement the setError() and/or setWarning() methods but DOESN'T HAVE TO be of
   * AKAbstractObject type. For example, this can even be used to propagate to a
   * JObject instance in Joomla!. Propagated items will be removed from ourselves.
   * @param object $object The object to propagate errors and warnings to.
   */
  public function propagateToObject(&$object)
  {
    // Skip non-objects
    if(!is_object($object)) return;

    if( method_exists($object,'setError') )
    {
      if(!empty($this->_errors))
      {
        foreach($this->_errors as $error)
        {
          $object->setError($error);
        }
        $this->_errors = array();
      }
    }

    if( method_exists($object,'setWarning') )
    {
      if(!empty($this->_warnings))
      {
        foreach($this->_warnings as $warning)
        {
          $object->setWarning($warning);
        }
        $this->_warnings = array();
      }
    }
  }

  /**
   * Propagates errors and warnings from a foreign object. Each propagated list is
   * then cleared on the foreign object, as long as it implements resetErrors() and/or
   * resetWarnings() methods.
   * @param object $object The object to propagate errors and warnings from
   */
  public function propagateFromObject(&$object)
  {
    if( method_exists($object,'getErrors') )
    {
      $errors = $object->getErrors();
      if(!empty($errors))
      {
        foreach($errors as $error)
        {
          $this->setError($error);
        }
      }
      if(method_exists($object,'resetErrors'))
      {
        $object->resetErrors();
      }
    }

    if( method_exists($object,'getWarnings') )
    {
      $warnings = $object->getWarnings();
      if(!empty($warnings))
      {
        foreach($warnings as $warning)
        {
          $this->setWarning($warning);
        }
      }
      if(method_exists($object,'resetWarnings'))
      {
        $object->resetWarnings();
      }
    }
  }

  /**
   * Sets the size of the error queue (acts like a LIFO buffer)
   * @param int $newSize The new queue size. Set to 0 for infinite length.
   */
  protected function setErrorsQueueSize($newSize = 0)
  {
    $this->_errors_queue_size = (int)$newSize;
  }

  /**
   * Sets the size of the warnings queue (acts like a LIFO buffer)
   * @param int $newSize The new queue size. Set to 0 for infinite length.
   */
  protected function setWarningsQueueSize($newSize = 0)
  {
    $this->_warnings_queue_size = (int)$newSize;
  }

  /**
   * Returns the last item of a LIFO string message queue, or a specific item
   * if so specified.
   * @param array $array An array of strings, holding messages
   * @param int $i Optional message index
   * @return mixed The message string, or false if the key doesn't exist
   */
  private function getItemFromArray($array, $i = null)
  {
    // Find the item
    if ( $i === null) {
      // Default, return the last item
      $item = end($array);
    }
    else
    if ( ! array_key_exists($i, $array) ) {
      // If $i has been specified but does not exist, return false
      return false;
    }
    else
    {
      $item  = $array[$i];
    }

    return $item;
  }

}

