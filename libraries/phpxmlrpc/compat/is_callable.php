<?php
/**
 * Replace function is_callable()
 *
 * @category    PHP
 * @package     PHP_Compat
 * @link        http://php.net/function.is_callable
 * @author      Gaetano Giunta <giunta.gaetano@sea-aeroportimilano.it>
 * @version     $Id: is_callable.php,v 1.3 2006/08/21 14:03:15 ggiunta Exp $
 * @since       PHP 4.0.6
 * @require     PHP 4.0.0 (true, false, etc...)
 * @todo        add the 3rd parameter syntax...
 */
if (!function_exists('is_callable')) {
    function is_callable($var, $syntax_only=false)
    {
        if ($syntax_only)
        {
            /* from The Manual:
            * If the syntax_only argument is TRUE the function only verifies
            * that var might be a function or method. It will only reject simple
            * variables that are not strings, or an array that does not have a
            * valid structure to be used as a callback. The valid ones are
            * supposed to have only 2 entries, the first of which is an object
            * or a string, and the second a string
            */
            return (is_string($var) || (is_array($var) && count($var) == 2 && is_string(end($var)) && (is_string(reset($var)) || is_object(reset($var)))));
        }
        else
        {
            if (is_string($var))
            {
                return function_exists($var);
            }
            else if (is_array($var) && count($var) == 2 && is_string($method = end($var)))
            {
                $obj = reset($var);
                if (is_string($obj))
                {
                    $methods = get_class_methods($obj);
                    return (bool)(is_array($methods) && in_array(strtolower($method), $methods));
                }
                else if (is_object($obj))
                {
                    return method_exists($obj, $method);
                }
            }
            return false;
        }
    }
}

?>