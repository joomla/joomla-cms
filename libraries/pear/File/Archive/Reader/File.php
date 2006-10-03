<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Reader that represents a single file
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
 * @version    CVS: $Id: File.php,v 1.30 2005/07/11 11:53:53 vincentlascaux Exp $
 * @link       http://pear.php.net/package/File_Archive
 */

//require_once "File/Archive/Reader.php";
jimport('pear.File.Archive.Reader');
//require_once "MIME/Type.php";
jimport('pear.MIME.Type');

/**
 * Reader that represents a single file
 */
class File_Archive_Reader_File extends File_Archive_Reader
{
    /**
     * @var object Handle to the file being read
     * @access private
     */
    var $handle = null;
    /**
     * @var string Name of the physical file being read
     * @access private
     */
    var $filename;
    /**
     * @var string Name of the file returned by the reader
     * @access private
     */
    var $symbolic;
    /**
     * @var array Stats of the file
     *      Will only be set after a call to $this->getStat()
     * @access private
     */
    var $stat = null;
    /**
     * @var string Mime type of the file
     *      Will only be set after a call to $this->getMime()
     */
    var $mime = null;
    /**
     * @var boolean Has the file already been read
     * @access private
     */
    var $alreadyRead = false;

    /**
     * $filename is the physical file to read
     * $symbolic is the name declared by the reader
     * If $symbolic is not specified, $filename is assumed
     */
    function File_Archive_Reader_File($filename, $symbolic = null, $mime = null)
    {
        $this->filename = $filename;
        $this->mime = $mime;
        if ($symbolic === null) {
            $this->symbolic = $this->getStandardURL($filename);
        } else {
            $this->symbolic = $this->getStandardURL($symbolic);
        }
    }
    /**
     * @see File_Archive_Reader::close()
     *
     * Close the file handle
     */
    function close()
    {
        $this->alreadyRead = false;
        if ($this->handle !== null) {
            fclose($this->handle);
            $this->handle = null;
        }
    }
    /**
     * @see File_Archive_Reader::next()
     *
     * The first time next is called, it will open the file handle and return
     * true. Then it will return false
     * Raise an error if the file does not exist
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
    function getFilename() { return $this->symbolic; }
    /**
     * @see File_Archive_Reader::getDataFilename()
     *
     * Return the name of the file
     */
    function getDataFilename() { return $this->filename; }
    /**
     * @see File_Archive_Reader::getStat() stat()
     */
    function getStat()
    {
        if ($this->stat === null) {
            $this->stat = @stat($this->filename);

            //If we can't use the stat function
            if ($this->stat === false) {
                $this->stat = array();
            }
        }
        return $this->stat;
    }

    /**
     * @see File_Archive_Reader::getMime
     */
    function getMime()
    {
        if ($this->mime === null) {
            PEAR::pushErrorHandling(PEAR_ERROR_RETURN);
            $this->mime = MIME_Type::autoDetect($this->getDataFilename());
            PEAR::popErrorHandling();

            if (PEAR::isError($this->mime)) {
                $this->mime = parent::getMime();
            }
        }
        return $this->mime;
    }

    /**
     * Opens the file if it was not already opened
     */
    function _ensureFileOpened()
    {
        if ($this->handle === null) {
            $this->handle = @fopen($this->filename, "r");

            if (!is_resource($this->handle)) {
                $this->handle = null;
                return PEAR::raiseError("Can't open {$this->filename} for reading");
            }
            if ($this->handle === false) {
                $this->handle = null;
                return PEAR::raiseError("File {$this->filename} not found");
            }
        }
    }

    /**
     * @see File_Archive_Reader::getData()
     */
    function getData($length = -1)
    {
        $error = $this->_ensureFileOpened();
        if (PEAR::isError($error)) {
            return $error;
        }

        if (feof($this->handle)) {
            return null;
        }
        if ($length == -1) {
            $contents = '';
            $blockSize = File_Archive::getOption('blockSize');
            while (!feof($this->handle)) {
                $contents .= fread($this->handle, $blockSize);
            }
            return $contents;
        } else {
            if ($length == 0) {
                return "";
            } else {
                return fread($this->handle, $length);
            }
        }
    }

    /**
     * @see File_Archive_Reader::skip()
     */
    function skip($length = -1)
    {
        $error = $this->_ensureFileOpened();
        if (PEAR::isError($error)) {
            return $error;
        }

        $before = ftell($this->handle);
        if (($length == -1 && @fseek($this->handle, 0, SEEK_END) === -1) ||
            ($length >= 0  && @fseek($this->handle, $length, SEEK_CUR) === -1)) {
            return parent::skip($length);
        } else {
            return ftell($this->handle) - $before;
        }
    }

    /**
     * @see File_Archive_Reader::rewind
     */
    function rewind($length = -1)
    {
        if ($this->handle === null) {
            return 0;
        }

        $before = ftell($this->handle);
        if (($length == -1 && @fseek($this->handle, 0, SEEK_SET) === -1) ||
            ($length >= 0  && @fseek($this->handle, -$length, SEEK_CUR) === -1)) {
            return parent::rewind($length);
        } else {
            return $before - ftell($this->handle);
        }
    }

    /**
     * @see File_Archive_Reader::tell()
     */
    function tell()
    {
        if ($this->handle === null) {
            return 0;
        } else {
            return ftell($this->handle);
        }
    }


    /**
     * @see File_Archive_Reader::makeWriterRemoveFiles()
     */
    function makeWriterRemoveFiles($pred)
    {
        return PEAR::raiseError(
            'File_Archive_Reader_File represents a single file, you cant remove it');
    }

    /**
     * @see File_Archive_Reader::makeWriterRemoveBlocks()
     */
    function makeWriterRemoveBlocks($blocks, $seek = 0)
    {
//        require_once "File/Archive/Writer/Files.php";
        jimport('pear.File.Archive.Writer.Files');

        $writer = new File_Archive_Writer_Files();

        $file = $this->getDataFilename();
        $pos = $this->tell();
        $this->close();

        $writer->openFileRemoveBlock($file, $pos + $seek, $blocks);

        return $writer;
    }

    /**
     * @see File_Archive_Reader::makeAppendWriter
     */
    function makeAppendWriter()
    {
        return PEAR::raiseError(
            'File_Archive_Reader_File represents a single file.'.
            ' makeAppendWriter cant be executed on it'
        );
    }
}

?>