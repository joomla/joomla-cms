<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * A writer wrapper that will remove the files the eventual duplicate
 * files from the reader to keep only the new ones
 * When calling newFile, the file with the highest index in the reader
 * and the same filename will be removed
 * Note that it ensure that the archive won't have duplicates only if
 * it didn't have duplicates before, and if no two calls to newFile with
 * the same filename is done
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
 * @version    CVS: $Id: UniqueAppender.php,v 1.1 2005/05/30 19:44:53 vincentlascaux Exp $
 * @link       http://pear.php.net/package/File_Archive
 */

//require_once "File/Archive/Writer.php";
jimport('pear.File.Archive.Writer');
//require_once "File/Archive/Reader.php";
jimport('pear.File.Archive.Reader');
//require_once "File/Archive/Predicate/Index.php";
jimport('pear.File.Archive.Predicate.Index');

/**
 * A writer wrapper that will remove the files the eventual duplicate
 * files from the reader to keep only the new ones
 * If there were already some duplications in the provided reader, not
 * all duplication will be removed
 * If you use newFile with the same filename several file, only the latest
 * write will be kept (no time comparision is done)
 */
class File_Archive_Writer_UniqueAppender extends File_Archive_Writer
{
    var $reader;
    var $writer;
    var $fileList = array();
    var $toDelete = array();

    /**
     * Construct a unique writer that will write to the specified writer
     * and remove duplicate files from the reader on close
     */
    function File_Archive_Writer_UniqueAppender(&$reader)
    {
        $reader->close();
        $pos = 0;
        while ($reader->next()) {
            $this->fileList[$reader->getFilename()] = $pos++;
        }

        $this->reader =& $reader;
        $this->writer = $reader->makeAppendWriter();
    }

    /**
     * @see File_Archive_Writer::newFile()
     */
    function newFile($filename, $stat = array(), $mime = "application/octet-stream")
    {
        if (isset($this->fileList[$filename])) {
            $this->toDelete[$this->fileList[$filename]] = true;
        }

        return $this->writer->newFile($filename, $stat, $mime);
    }

    /**
     * @see File_Archive_Writer::newFromTempFile()
     */
    function newFromTempFile($tmpfile, $filename, $stat = array(), $mime = "application/octet-stream")
    {
        if (isset($this->fileList[$filename])) {
            $this->toDelete[$this->fileList[$filename]] = true;
        }

        return $this->writer->newFromTempFile($tmpfile, $filename, $stat, $mime);
    }

    /**
     * @see File_Archive_Writer::newFileNeedsMIME()
     */
    function newFileNeedsMIME()
    {
        return $this->writer->newFileNeedsMIME();
    }

    /**
     * @see File_Archive_Writer::writeData()
     */
    function writeData($data)
    {
        return $this->writer->writeData($data);
    }

    /**
     * @see File_Archive_Writer::writeFile()
     */
    function writeFile($filename)
    {
        return $this->writer->writeFile($filename);
    }

    /**
     * Close the writer, eventually flush the data, write the footer...
     * This function must be called before the end of the script
     */
    function close()
    {
        $error = $this->writer->close();
        if (PEAR::isError($error)) {
            return $error;
        }

        if (!empty($this->toDelete)) {
            $tmp = $this->reader->makeWriterRemoveFiles(
                new File_Archive_Predicate_Index($this->toDelete)
            );
            if (PEAR::isError($tmp)) {
                return $tmp;
            }

            return $tmp->close();
        }
    }
}

?>