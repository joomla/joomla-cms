<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * A predicate is an object that can evaluate to true or false depending on the
 * file currently read by a File_Archive_Reader
 *
 * @see File_Archive_Reader_Filter
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
 * @version    CVS: $Id: Predicate.php,v 1.7 2005/05/26 21:30:18 vincentlascaux Exp $
 * @link       http://pear.php.net/package/File_Archive
 */

//require_once "File/Archive/Reader.php";
jimport('pear.File.Archive.Reader');

/**
 * A predicate is an object that can evaluate to true or false depending on the
 * file currently read by a File_Archive_Reader
 *
 * @see File_Archive_Reader_Filter
 */
class File_Archive_Predicate
{
    /**
     * Indicates whether the current file from the reader should be kept
     *
     * @param File_Archive_Reader $source Reader which will be filtered
     * @return bool False iif the current file must be filtered out
     */
    function isTrue(&$source)
    {
        return PEAR::raiseError("Predicat abstract function call");
    }
}

?>