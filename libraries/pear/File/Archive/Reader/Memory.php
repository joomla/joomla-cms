<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * A reader that takes its input from a memory buffer
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
 * @version    CVS: $Id: Memory.php,v 1.19 2005/06/19 20:09:57 vincentlascaux Exp $
 * @link       http://pear.php.net/package/File_Archive
 */

//require_once "File/Archive/Reader.php";
jimport('pear.File.Archive.Reader');

/**
 * A reader that takes its input from a memory buffer
 */
class File_Archive_Reader_Memory extends File_Archive_Reader
{
    /**
     * @var String Name of the file exported by this reader
     * @access private
     */
    var $filename;
    /**
     * @var Array Stat of the file exported by this reader
     * @access private
     */
    var $stat;
    /**
     * @var String MIME type of the file exported by this reader
     * @access private
     */
    var $mime;
    /**
     * @var String Memory buffer that contains the data of the file
     * @access private
     */
    var $memory;
    /**
     * @var Int Current position in the file
     * @access private
     */
    var $offset = 0;
    /**
     * @var Boolean Has the file already been read
     * @access private
     */
    var $alreadyRead = false;

    /**
     * @param string $memory is the content of the file.
     *        This parameter is passed as a reference for performance
     *        reasons. The content should not be changer after the constructor
     * @param string $filename is the name of the file
     * @param array  $stat are the statistics of the file. The size will be
     *        recomputed from $memory
     * @param string $mime is the mime type of the file
     */
    function File_Archive_Reader_Memory(&$memory, $filename,
                                        $stat=array(), $mime=null)
    {
        $this->memory = &$memory;
        $this->filename = $this->getStandardURL($filename);
        $this->stat = $stat;
        $this->stat[7] = $this->stat['size'] = strlen($this->memory);
        $this->mime = $mime;
    }

    /**
     * The subclass should overwrite this function to change the filename, stat
     * and memory
     */
    function next()
    {
        if ($this->alreadyRead) {
            return false;
        } else {
            $this->alreadyRead = true;
            return true;
        }
    }

    /**
     * @see File_Archive_Reader::getFilename()
     */
    function getFilename() { return $this->filename; }
    /**
     * @see File_Archive_Reader::getStat()
     */
    function getStat() { return $this->stat; }
    /**
     * @see File_Archive_Reader::getMime()
     */
    function getMime()
    {
        return $this->mime==null ? parent::getMime() : $this->mime;
    }

    /**
     * @see File_Archive_Reader::getData()
     */
    function getData($length = -1)
    {
        if ($this->offset == strlen($this->memory)) {
            return null;
        }
        if ($length == -1) {
            $actualLength = strlen($this->memory) - $this->offset;
        } else {
            $actualLength = min($length, strlen($this->memory) - $this->offset);
        }
        $result = substr($this->memory, $this->offset, $actualLength);
        $this->offset += $actualLength;
        return $result;
    }

    /**
     * @see File_Archive_Reader::skip()
     */
    function skip($length = -1)
    {
        if ($length == -1) {
            $length = strlen($this->memory) - $this->offset;
        } else {
            $length = min($length, strlen($this->memory) - $this->offset);
        }
        $this->offset += $length;
        return $length;
    }

    /**
     * @see File_Archive_Reader::rewind()
     */
    function rewind($length = -1)
    {
        if ($length == -1) {
            $tmp = $this->offset;
            $this->offset = 0;
            return $tmp;
        } else {
            $length = min($length, $this->offset);
            $this->offset -= $length;
            return $length;
        }
    }

    /**
     * @see File_Archive_Reader::tell()
     */
    function tell()
    {
        return $this->offset;
    }

    /**
     * @see File_Archive_Reader::close()
     */
    function close()
    {
        $this->offset = 0;
        $this->alreadyRead = false;
    }

    /**
     * @see File_Archive_Reader::makeAppendWriter()
     */
    function makeAppendWriter()
    {
        return PEAR::raiseError('Unable to append files to a memory archive');
    }

    /**
     * @see File_Archive_Reader::makeWriterRemoveFiles()
     */
    function makeWriterRemoveFiles($pred)
    {
        return PEAR::raiseError('Unable to remove files from a memory archive');
    }

    /**
     * @see File_Archive_Reader::makeWriterRemoveBlocks()
     */
    function makeWriterRemoveBlocks($blocks, $seek = 0)
    {
//        require_once "File/Archive/Writer/Memory.php";
        jimport('pear.File.Archive.Writer.Memory');
        $data = substr($this->memory, 0, $this->offset + $seek);
        $this->memory = substr($this->memory, $this->offset + $seek);

        $keep = false;
        foreach ($blocks as $length) {
            if ($keep) {
                $data .= substr($this->memory, 0, $length);
            }
            $this->memory = substr($this->memory, $length);
            $keep = !$keep;
        }
        if ($keep) {
            $this->memory = $data . $this->memory;
        } else {
            $this->memory = $data;
        }
        $this->close();
        return new File_Archive_Writer_Memory($this->memory, strlen($this->memory));
    }
}

?>