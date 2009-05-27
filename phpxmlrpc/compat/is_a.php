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
 * Replace function is_a()
 *
 * @category    PHP
 * @package     PHP_Compat
 * @link        http://php.net/function.is_a
 * @author      Aidan Lister <aidan@php.net>
 * @version     $Revision: 1.2 $
 * @since       PHP 4.2.0
 * @require     PHP 4.0.0 (user_error) (is_subclass_of)
 */
if (!function_exists('is_a')) {
    function is_a($object, $class)
    {
        if (!is_object($object)) {
            return false;
        }

        if (get_class($object) == strtolower($class)) {
            return true;
        } else {
            return is_subclass_of($object, $class);
        }
    }
}

?>