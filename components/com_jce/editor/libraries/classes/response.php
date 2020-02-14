<?php

/**
 * @copyright 	Copyright (c) 2009-2019 Ryan Demmer. All rights reserved
 * @license   	GNU/GPL 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * JCE is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses
 */
defined('JPATH_PLATFORM') or die;

final class WFResponse
{
    private $content = null;

    private $id = null;

    private $error = null;

    private $headers = array(
      'Content-Type' => 'text/json;charset=UTF-8',
  );

  /**
   * Constructor.
   *
   * @param $id Request id
   * @param null $content Response content
   * @param array $headers Optional headers
   */
  public function __construct($id, $content = null, $headers = array())
  {
      // et response content
      $this->setContent($content);

      // set id
      $this->id = $id;

      // set header
      $this->setHeaders($headers);

      return $this;
  }

  /**
   * Send response.
   *
   * @param array $data
   */
  public function send($data = array())
  {
      $data = array_merge($data, array(
          'jsonrpc' => '2.0',
          'id' => $this->id,
          'result' => $this->getContent(),
          'error' => $this->getError(),
      ));

      ob_start();

      // set custom headers
      foreach ($this->headers as $key => $value) {
          header($key.': '.$value);
      }

      // set output headers
      header('Expires: Mon, 4 April 1984 05:00:00 GMT');
      header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
      header('Cache-Control: no-store, no-cache, must-revalidate');
      header('Cache-Control: post-check=0, pre-check=0', false);
      header('Pragma: no-cache');

      // only echo response if an id is set
      if (!empty($this->id)) {
          echo json_encode($data);
      }

      exit(ob_get_clean());
  }

    public function getHeader()
    {
        return $this->headers;
    }

    public function setHeaders($headers)
    {
        foreach ($headers as $key => $value) {
            $this->headers[$key] = $value;
        }

        return $this;
    }

  /**
   * @param array $error
   */
  public function setError($error = array('code' => -32603, 'message' => 'Internal error'))
  {
      $this->error = $error;

      return $this;
  }

    public function getError()
    {
        return $this->error;
    }

    public function getContent()
    {
        return $this->content;
    }

    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }
}
