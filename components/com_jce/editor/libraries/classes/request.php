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

final class WFRequest extends JObject
{
    protected static $instance;

    protected $requests = array();

    /**
     * Constructor activating the default information of the class.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Returns a reference to a WFRequest object.
     *
     * This method must be invoked as:
     *    <pre>  $request = WFRequest::getInstance();</pre>
     *
     * @return object WFRequest
     */
    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Set Request function.
     *
     * @param array $function An array containing the function and object
     */
    public function register($function)
    {
        $object = new stdClass();

        if (is_array($function)) {
            $ref = array_shift($function);
            $name = array_shift($function);

            $object->fn = $name;
            $object->ref = $ref;

            $this->requests[$name] = $object;
        } else {
            $object->fn = $function;
            $this->requests[$function] = $object;
        }
    }

    private function isRegistered($function)
    {
        return array_key_exists($function, $this->requests);
    }

    /**
     * Get a request function.
     *
     * @param string $function
     */
    public function getFunction($function)
    {
        return $this->requests[$function];
    }

    /**
     * Check if the HTTP Request is a WFRequest.
     *
     * @return bool
     */
    private function isRequest()
    {
        return (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') || (isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'multipart') !== false);
    }

    public function setRequest($request)
    {
        return $this->register($request);
    }

    /**
     * Check a request query for bad stuff.
     *
     * @param array $query
     */
    private function checkQuery($query)
    {
        if (is_string($query)) {
            $query = array($query);
        }

        // check for null byte
        foreach ($query as $key => $value) {
            if (is_array($value) || is_object($value)) {
                return self::checkQuery($value);
            }

            if (is_array($key)) {
                return self::checkQuery($key);
            }

            if (strpos($key, '\u0000') !== false || strpos($value, '\u0000') !== false) {
                JError::raiseError(403, 'RESTRICTED');
            }
        }
    }

    /**
     * Process an ajax call and return result.
     *
     * @return string
     */
    public function process($array = false)
    {
        if ($this->isRequest() === false) {
            return false;
        }

        // Check for request forgeries
        JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

        $app = JFactory::getApplication();

        // empty arguments
        $args = array();

        $json = $app->input->getVar('json', '', 'POST', 'STRING', 2);
        $method = $app->input->getWord('method');

        // get and encode json data
        if ($json) {
            // remove slashes
          $json = stripslashes($json);

          // convert to JSON object
          $json = json_decode($json);
        }

        // get current request id
        $id = empty($json->id) ? $app->input->getWord('id') : $json->id;

        // create response
        $response = new WFResponse($id);

        if ($method || $json) {
            // set request flag
            define('JCE_REQUEST', 1);

            // check if valid json object
            if (is_object($json)) {
                // no function call
                if (isset($json->method) === false) {
                    $response->setError(array('code' => -32600, 'message' => 'Invalid Request'))->send();
                }

                // get function call
                $fn = $json->method;

                // clean function
                $fn = JFilterInput::getInstance()->clean($fn, 'cmd');

                // pass params to input and flatten
                if (!empty($json->params)) {
                    // check query
                    $this->checkQuery($json->params);

                    // merge array with args
                    if (is_array($json->params)) {
                        $args = array_merge($args, $json->params);
                    // pass through string or object
                    } else {
                        $args[] = $json->params;
                    }
                }
            } else {
                $fn = $method;
                $response->setHeaders(array('Content-type' => 'text/html;charset=UTF-8'));
            }

            if (empty($fn) || $this->isRegistered($fn) === false) {
                $response->setError(array('code' => -32601, 'message' => 'Method not found'))->send();
            }

            // get method
            $request = $this->getFunction($fn);

            // create callable function
            $callback = array($request->ref, $request->fn);

            // check function is callable
            if (is_callable($callback) === false) {
                $response->setError(array('code' => -32601, 'message' => 'Method not found'))->send();
            }

            // create empty result
            $result = '';

            try {
                $result = call_user_func_array($callback, (array) $args);

                if (is_array($result) && !empty($result['error'])) {
                    if (is_array($result['error'])) {
                        $result['error'] = implode("\n", $result['error']);
                    }

                    $response->setError(array('message' => $result['error']))->send();
                }
            } catch (Exception $e) {
                $response->setError(array('code' => $e->getCode(), 'message' => $e->getMessage()))->send();
            }

            $response->setContent($result)->send();
        }

        // default response
        $response->setError(array('code' => -32601, 'message' => 'The server returned an invalid response'))->send();
    }
}
