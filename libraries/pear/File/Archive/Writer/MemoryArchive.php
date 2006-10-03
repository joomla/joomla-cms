<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Base class for all the archiveWriters that can only work on complete files
 * (the write data function may be called with small chunks of data)
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
 * @version    CVS: $Id: MemoryArchive.php,v 1.16 2005/06/02 12:24:43 vincentlascaux Exp $
 * @link       http://pear.php.net/package/File_Archive
 */

//require_once "File/Archive/Writer/Archive.php";
jimport('pear.File.Archive.Writer.Archive');
//require_once "File/Archive/Writer/Memory.php";
jimport('pear.File.Archive.Writer.Memory');

/**
 * Base class for all the archiveWriters that can only work on complete files
 * (the write data function may be called with small chunks of data)
 */
class File_Archive_Writer_MemoryArchive extends File_Archive_Writer_Archive
{
    /**
     * @var    File_Archive_Writer_Memory A buffer where the data will be put
     *         waiting for the file to be complete
     * @access private
     */
    var $buffer = '';
    /**
     * @var    string Name of the file which data are coming
     * @access private
     */
    var $currentFilename = null;
    /**
     * @var    array Stats of the file which data are coming
     * @access private
     */
    var $currentStat = null;
    /**
     * @var    string URL of the file being treated if it is a physical file
     * @access private
     */
    var $currentDataFile = null;
    /**
     * @var    int Number of times newFile function has been called
     * @access protected
     */
    var $nbFiles = 0;

    /**
     * @see File_Archive_Writer::File_Archive_Writer()
     */
    function File_Archive_Writer_MemoryArchive
                ($filename, &$t, $stat = array(), $autoClose = true)
    {
        parent::File_Archive_Writer_Archive($filename, $t, $stat, $autoClose);
    }

    /**
     * @see File_Archive_Writer::newFile()
     */
    function newFile($filename, $stat = array(),
                     $mime = "application/octet-stream")
    {
        if ($this->nbFiles == 0) {
            $error = $this->sendHeader();
            if (PEAR::isError($error)) {
                return $error;
            }
        } else {
            $error = $this->flush();
            if (PEAR::isError($error)) {
                return $error;
            }
        }

        $this->nbFiles++;

        $this->currentFilename = $filename;
        $this->currentStat = $stat;

        return true;
    }
    /**
     * @see File_Archive_Writer::close()
     */
    function close()
    {
        $error = $this->flush();
        if (PEAR::isError($error)) {
            return $error;
        }
        $error = $this->sendFooter();
        if (PEAR::isError($error)) {
            return $error;
        }

        return parent::close();
    }
    /**
     * Indicate that all the data have been read from the current file
     * and send it to appendFileData
     * Send the current data to the appendFileData function
     *
     * @access private
     */
    function flush()
    {
        if ($this->currentFilename !== null) {
            if ($this->currentDataFile !== null) {
                $error = $this->appendFile($this->currentFilename,
                                  $this->currentDataFile);
            } else {
                $error = $this->appendFileData($this->currentFilename,
                                 $this->currentStat,
                                 $this->buffer);
            }
            if (PEAR::isError($error)) {
                return $error;
            }

            $this->currentFilename = null;
            $this->currentDataFile = null;
            $this->buffer = '';
        }
    }
    /**
     * @see File_Archive_Writer::writeData()
     */
    function writeData($data)
    {
        if ($this->currentDataFile !== null) {
            $this->buffer .= file_get_contents($this->currentDataFile);
            $this->currentDataFile = null;
        }
        $this->buffer .= $data;
    }
    /**
     * @see File_Archive_Writer::writeFile()
     */
    function writeFile($filename)
    {
        if ($this->currentDataFile === null && empty($this->buffer)) {
            $this->currentDataFile = $filename;
        } else {
            if ($this->currentDataFile !== null) {
                $this->buffer .= file_get_contents($this->currentDataFile);
                $this->currentDataFile = null;
            }
            $this->buffer .= file_get_contents($filename);
        }
    }

//MUST REWRITE FUNCTIONS
    /**
     * The subclass must treat the data $data
     * $data is the entire data of the filename $filename
     * $stat is the stat of the file
     *
     * @access protected
     */
    function appendFileData($filename, $stat, &$data) { }

//SHOULD REWRITE FUNCTIONS
    /**
     * The subclass may rewrite the sendHeader function if it needs to execute
     * code before the first file
     *
     * @access protected
     */
    function sendHeader() { }
    /**
     * The subclass may rewrite the sendFooter function if it needs to execute
     * code before closing the archive
     *
     * @access protected
     */
    function sendFooter() { }
    /**
     * The subclass may rewrite this class if it knows an efficient way to treat
     * a physical file.
     *
     * @access protected
     */
    function appendFile($filename, $dataFilename)
    {
        return $this->appendFileData(
                            $filename,
                            stat($dataFilename),
                            file_get_contents($dataFilename));
    }
}

?>