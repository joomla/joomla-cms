<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Write the concatenation of the files in a buffer
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
 * @version    CVS: $Id: Memory.php,v 1.14 2005/06/02 12:24:43 vincentlascaux Exp $
 * @link       http://pear.php.net/package/File_Archive
 */

//require_once "File/Archive/Writer.php";
jimport('pear.File.Archive.Writer');

/**
 * Write the concatenation of the files in a buffer
 */
class File_Archive_Writer_Memory extends File_Archive_Writer
{
    /**
     * @var string $data The buffer
     * @access private
     */
    var $data = "";
    /**
     * Information about the file being written into this writer
     * @access private
     */
    var $filename;
    var $stat;
    var $mime;

    /**
     * @param reference $data If provided, the data will be output in this
     *        variable. Any existent data in $data will be overwritten by the
     *        actual data of the writer. You should not modify manually this
     *        variable while using this writer (you can safely use all the
     *        functions of the archive, like clear for example)
     * @param int keptData is the offset from where to start writing in $data
     *        Any data located after $seek will be erased
     *        The default value is 0
     */
    function File_Archive_Writer_Memory(&$data, $seek = 0)
    {
        $this->data =& $data;
        $this->data = substr($data, 0, $seek);
    }

    function writeData($d) { $this->data .= $d; }

    /**
     * @see File_Archive_Writer::newFile()
     */
    function newFile($filename, $stat, $mime = "application/octet-stream")
    {
        $this->filename = $filename;
        $this->stat = $stat;
        $this->mime = $mime;
    }
    /**
     * @see File_Archive_Writer::newFileNeedsMIME
     */
    function newFileNeedsMIME()
    {
        return true;
    }

    /**
     * Retrieve the concatenated data
     * The value is returned by reference for performance problems, but you
     * should not manually modify it
     *
     * @return string buffer
     */
    function &getData() { return $this->data; }

    /**
     * Clear the buffer
     */
    function clear() { $this->data = ""; }

    /**
     * Returns true iif the buffer is empty
     */
    function isEmpty() { return empty($this->data); }

    /**
     * Create a reader from this writer
     *
     * @param string $filename Name of the file provided by the reader
     * @param array $stat Statistics of the file provided by the reader
     * @param string $mime Mime type of the file provided by the reader
     *
     * Any unspecified parameter will be set to the value of the last file
     * written in this writer
     */
    function makeReader($filename = null, $stat = null, $mime = null)
    {
//        require_once "File/Archive/Reader/Memory.php";
        jimport('pear.File.Archive.Reader.Memory');
        return new File_Archive_Reader_Memory(
            $this->data,
            $filename === null ? $this->filename : $filename,
            $stat     === null ? $this->stat     : $stat,
            $mime     === null ? $this->mime     : $mime);
    }
}

?>