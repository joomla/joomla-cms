<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Keep only the files that have a specific MIME type
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
 * @version    CVS: $Id: MIME.php,v 1.5 2005/04/21 10:01:46 vincentlascaux Exp $
 * @link       http://pear.php.net/package/File_Archive
 */

//require_once "File/Archive/Predicate.php";
jimport('pear.File.Archive.Predicate');
//require_once "MIME/Type.php";
jimport('pear.MIME.Type');

/**
 * Keep only the files that have a specific MIME type
 *
 * @see        File_Archive_Predicate, File_Archive_Reader_Filter
 */
class File_Archive_Predicate_MIME extends File_Archive_Predicate
{
    var $mimes;

    /**
     * @param $extensions array or comma separated string of allowed extensions
     */
    function File_Archive_Predicate_MIME($mimes)
    {
        if (is_string($mimes)) {
            $this->mimes = explode(",",$mimes);
        } else {
            $this->mimes = $mimes;
        }
    }
    /**
     * @see File_Archive_Predicate::isTrue()
     */
    function isTrue(&$source)
    {
        $sourceMIME = $source->getMIME();
        foreach ($this->mimes as $mime) {
            if (MIME_Type::isWildcard($mime)) {
                $result = MIME_Type::wildcardMatch($mime, $sourceMIME);
            } else {
                $result = ($mime == $sourceMIME);
            }
            if ($result !== false) {
                return $result;
            }
        }
        return false;
    }
}

?>