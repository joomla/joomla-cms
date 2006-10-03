<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Evaluates to true if the index is in a given array of indexes
 * The array has the indexes in key (so you may want to call
 * array_flip if your array has indexes as value)
 *
 * PHP versions 4 and 5
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330,Boston,MA 02111-1307 USA
 *
 * @category   File Formats
 * @package    File_Archive
 * @author     Vincent Lascaux <vincentlascaux@php.net>
 * @copyright  1997-2005 The PHP Group
 * @license    http://www.gnu.org/copyleft/lesser.html  LGPL
 * @version    CVS: $Id: Index.php,v 1.1 2005/05/30 19:44:53 vincentlascaux Exp $
 * @link       http://pear.php.net/package/File_Archive
 */

//require_once "File/Archive/Predicate.php";
jimport('pear.File.Archive.Predicate');

/**
 * Evaluates to true if the index is in a given array of indexes
 * The array has the indexes in key (so you may want to call
 * array_flip if your array has indexes as value)
 */
class File_Archive_Predicate_Index extends File_Archive_Predicate
{
    var $indexes;
    var $pos = 0;

    /**
     * @param $extensions array or comma separated string of allowed extensions
     */
    function File_Archive_Predicate_Index($indexes)
    {
        $this->indexes = $indexes;
    }
    /**
     * @see File_Archive_Predicate::isTrue()
     */
    function isTrue(&$source)
    {
        return isset($this->indexes[$this->pos++]);
    }
}

?>