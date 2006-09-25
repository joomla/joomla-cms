<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | PHP version 4                                                        |
// +----------------------------------------------------------------------+
// | Copyright (c) 1997-2002 The PHP Group                                |
// +----------------------------------------------------------------------+
// | This source file is subject to version 3.0 of the PHP license,       |
// | that is bundled with this package in the file LICENSE, and is        |
// | available at through the world-wide-web at                           |
// | http://www.php.net/license/3_0.txt.                                  |
// | If you did not receive a copy of the PHP license and are unable to   |
// | obtain it through the world-wide-web, please send a note to          |
// | license@php.net so we can mail you a copy immediately.               |
// +----------------------------------------------------------------------+
// | Authors: Ian Eure <ieure@php.net>                                    |
// +----------------------------------------------------------------------+
//
// $Id: Parameter.php,v 1.1 2004/06/16 08:46:19 ieure Exp $

/**
 * Class for working with MIME type parameters
 *
 * @version @version@
 * @package @package@
 * @author Ian Eure <ieure@php.net>
 */
class MIME_Type_Parameter {
    /**
     * Parameter name
     *
     * @var string
     */
    var $name;
    
    /**
     * Parameter value
     *
     * @var string
     */
    var $value;
    
    /**
     * Parameter comment
     *
     * @var string
     */
    var $comment;


    /**
     * Constructor.
     *
     * @param  string $param MIME parameter to parse, if set.
     * @return void
     */
    function MIME_Type_Parameter($param = false)
    {
        if ($param) {
            $this->parse($param);
        }
    }

    
    /**
     * Parse a MIME type parameter and set object fields
     *
     * @param  string $param MIME type parameter to parse
     * @return void
     */
    function parse($param)
    {
        $this->name = $this->getAttribute($param);
        $this->value = $this->getValue($param);
        if ($this->hasComment($param)) {
            $this->comment = $this->getComment($param);
        }
    }


    /**
     * Get a parameter attribute (e.g. name)
     *
     * @param  string MIME type parameter
     * @return string Attribute name
     * @static
     */
    function getAttribute($param)
    {
        $tmp = explode('=', $param);
        return trim($tmp[0]);
    }


    /**
     * Get a parameter value
     *
     * @param  string $param MIME type parameter
     * @return string Value
     * @static
     */
    function getValue($param)
    {
        $tmp = explode('=', $param);
        $value = $tmp[1];
        if (MIME_Type_Parameter::hasComment($param)) {
            $cs = strpos($value, '(');
            $value = substr($value, 0, $cs);
        }
        return trim($value, '" ');
    }


    /**
     * Get a parameter comment
     *
     * @param  string $param MIME type parameter
     * @return string Parameter comment
     * @see getComment()
     * @static
     */
    function getComment($param)
    {
        $cs = strpos($param, '(');
        $comment = substr($param, $cs);
        return trim($comment, '() ');
    }


    /**
     * Does this parameter have a comment?
     *
     * @param  string  $param MIME type parameter
     * @return boolean true if $param has a comment, false otherwise
     * @static
     */
    function hasComment($param)
    {
        if (strstr($param, '(')) {
            return true;
        }
        return false;
    }


    /**
     * Get a string representation of this parameter
     *
     * This function performs the oppsite of parse()
     *
     * @return string String representation of parameter
     */
    function get()
    {
        $val = $this->name.'="'.$this->value.'"';
        if ($this->comment) {
            $val .= ' ('.$this->comment.')';
        }
        return $val;
    }
}
?>