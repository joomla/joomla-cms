<?php
// $HeadURL: https://joomgallery.org/svn/joomgallery/JG-1.5/JG/trunk/administrator/components/com_joomgallery/admin.joomgallery.php $
// $Id: admin.joomgallery.php 2566 2010-11-03 21:10:42Z mab $
/****************************************************************************************\
**   JoomGallery 2                                                                      **
**   By: JoomGallery::ProjectTeam                                                       **
**   Copyright (C) 2008 - 2011  JoomGallery::ProjectTeam                                **
**   Based on: JoomGallery 1.0.0 by JoomGallery::ProjectTeam                            **
**   Released under GNU GPL Public License                                              **
**   License: http://www.gnu.org/copyleft/gpl.html or have a look                       **
**   at administrator/components/com_joomgallery/LICENSE.TXT                            **
\****************************************************************************************/

defined('_JEXEC') or die('Direct Access to this location is not allowed.');

/**
 * JoomGallery JSON Response class
 *
 * @package JoomGallery
 * @since   2.1
 */
class JoomJsonResponse
{
  /**
   * Determines whether the request was successful
   *
   * var  boolean
   */
  public $success   = true;

  /**
   * Determines whether the request wasn't successful.
   * This is always the negation of $this->success,
   * so you can use both flags equivalently.
   *
   * var  boolean
   */
  public $error     = false;

  /**
   * The main response message
   *
   * var  boolean
   */
  public $message   = null;

  /**
   * Array of messages gathered in the JApplication object
   *
   * var  array
   */
  public $messages  = null;

  /**
   * The response data
   *
   * var  array/object
   */
  public $data      = null;

  /**
   * Constructor
   *
   * @return  void
   * @since   2.1
   */
  public function __construct($response = null, $message = null, $error = false)
  {
    $this->message = $message;

    // Get the message queue
    $messages = JFactory::getApplication()->getMessageQueue();

    // Build the sorted message list
    if(is_array($messages) && count($messages))
    {
      foreach($messages as $message)
      {
        if(isset($message['type']) && isset($message['message']))
        {
          $lists[$message['type']][] = $message['message'];
        }
      }
    }

    // If messages exist add them to the output
    if(isset($lists) && is_array($lists))
    {
      $this->messages = $lists;
    }

    // Check if we are dealing with an error
    if(JError::isError($response))
    {
      // Prepare the error response
      $this->success  = false;
      $this->error    = true;
      $this->message  = $response->getMessage();
    }
    else
    {
      // Prepare the response data
      $this->success  = !$error;
      $this->error    = $error;
      $this->data     = $response;
    }
  }

  /**
   * Magic toString method for sending the response in JSON format
   *
   * @return  string  The response in JSON format
   * @since   2.1
   */
  public function __toString()
  {
    return json_encode($this);
  }
}