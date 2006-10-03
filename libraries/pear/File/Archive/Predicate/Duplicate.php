<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Evaluates to true if a for the files for which a newer version
 * can be found in a specified archive
 * Comparison is by default made on dates of the files, or position
 * in the archive (if two files have the same date or the date of a
 * file is not specified).
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
 * @version    CVS: $Id: Duplicate.php,v 1.1 2005/05/30 17:18:11 vincentlascaux Exp $
 * @link       http://pear.php.net/package/File_Archive
 */

//require_once "File/Archive/Predicate.php";
jimport('pear.File.Archive.Predicate');

/**
 * Evaluates to true if a for the files for which a newer version
 * can be found in a specified archive
 * Comparison is by default made on dates of the files, or position
 * in the archive (if two files have the same date or the date of a
 * file is not specified).
 */
class File_Archive_Predicate_Duplicate extends File_Archive_Predicate
{
    /**
     * @var array Key is the filename, value is an array of date (index 0) and
     *      position in the archive (index) 1 of the newest entry with this filename
     */
    var $newest = array();

    /**
     * @var int The current position of the file in the source
     */
    var $pos = 0;

    /**
     * @param File_Archive_Reader $source The source will be inspected to find
     *        the date of old files
     *        The predicate should then be used on the same source to remove the
     *        old duplicate files
     */
    function File_Archive_Predicate_Duplicate(&$source)
    {
        //Ensure we are at the begining of the file
        $source->close();
        $pos = 0;
        while ($source->next()) {
            $filename = $source->getFilename();
            $stat = $source->getStat();
            $value = isset($this->newest[$filename]) ? $this->newest[$filename] : null;

            if ($value === null ||
                $this->compare($stat[9], $value[0]) >= 0
               ) {
                $this->newest[$filename] = array($stat[9], $pos);
            }
            $pos++;
        }
    }

    /**
     * Compare the dates of two files. null is considered infinitely old
     *
     * @return int < 0 if $a can be considered older than $b
     *             = 0 if $a and $b can be considered same age
     *             > 0 if $a can be considered newer than $b
     */
    function compare($a, $b) {
        return ($a === null ? -1 : $a) - ($b === null ? -1 : $b);
    }

    /**
     * @see File_Archive_Predicate::isTrue()
     */
    function isTrue(&$source)
    {
        $filename = $source->getFilename();
        $stat = $source->getStat();
        $value = isset($this->newest[$filename]) ? $this->newest[$filename] : null;
        if ($value === null) {
            $delete = false;
        } else {
            $comp = $this->compare($stat[9], $value[0]);

            $delete = $comp < 0 ||
                  ($comp == 0 && $this->pos != $value[1]);

        }
        $this->pos++;
        return $delete;
    }
}

?>