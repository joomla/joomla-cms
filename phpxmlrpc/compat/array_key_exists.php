<?php
// +----------------------------------------------------------------------+
// | PHP Version 4                                                        |
// +----------------------------------------------------------------------+
// | Copyright (c) 1997-2004 The PHP Group                                |
// +----------------------------------------------------------------------+
// | This source file is subject to version 3.0 of the PHP license,       |
// | that is bundled with this package in the file LICENSE, and is        |
// | available at through the world-wide-web at                           |
// | http://www.php.net/license/3_0.txt.                                  |
// | If you did not receive a copy of the PHP license and are unable to   |
// | obtain it through the world-wide-web, please send a note to          |
// | license@php.net so we can mail you a copy immediately.               |
// +----------------------------------------------------------------------+
// | Authors: Aidan Lister <aidan@php.net>                                |
// +----------------------------------------------------------------------+
//
// $Id$


/**
 * Replace array_key_exists()
 *
 * @category    PHP
 * @package     PHP_Compat
 * @link        http://php.net/function.array_key_exists
 * @author      Aidan Lister <aidan@php.net>
 * @version     $Revision: 1.1 $
 * @since       PHP 4.1.0
 * @require     PHP 4.0.0 (user_error)
 */
if (!function_exists('array_key_exists')) {
    function array_key_exists($key, $search)
    {
        if (!is_scalar($key)) {
            user_error('array_key_exists() The first argument should be either a string or an integer',
                E_USER_WARNING);
            return false;
        }

        if (is_object($search)) {
            $search = get_object_vars($search);
        }

        if (!is_array($search)) {
            user_error('array_key_exists() The second argument should be either an array or an object',
                E_USER_WARNING);
            return false;
        }

        return in_array($key, array_keys($search));
    }
}

?>