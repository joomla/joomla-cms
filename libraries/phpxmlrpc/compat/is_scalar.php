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
//
// $Id: is_scalar.php,v 1.2 2005/11/21 10:57:23 ggiunta Exp $


/**
 * Replace is_scalar()
 *
 * @category    PHP
 * @package     PHP_Compat
 * @link        http://php.net/function.is_scalar
 * @author      Gaetano Giunta
 * @version     $Revision: 1.2 $
 * @since       PHP 4.0.5
 * @require     PHP 4 (is_bool)
 */
if (!function_exists('is_scalar')) {
    function is_scalar($val)
    {
        // Check input
        return (is_bool($val) || is_int($val) || is_float($val) || is_string($val));
    }
}

?>