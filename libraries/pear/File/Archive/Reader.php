<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Abstract base class for all the readers
 *
 * A reader is a compilation of serveral files that can be read
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
 * @version    CVS: $Id: Reader.php,v 1.33 2005/07/07 12:24:57 vincentlascaux Exp $
 * @link       http://pear.php.net/package/File_Archive
 */

//require_once "PEAR.php";
jimport('pear.PEAR');

/**
 * Abstract base class for all the readers
 *
 * A reader is a compilation of serveral files that can be read
 */
class File_Archive_Reader
{
    /**
     * Move to the next file in the reader
     *
     * @return bool false iif no more files are available
     */
    function next()
    {
        return false;
    }

    /**
     * Move to the next file whose name is in directory $filename
     * or is exactly $filename
     *
     * @param string $filename Name of the file to find in the archive
     * @param bool $close If true, close the reader and search from the first file
     * @return bool whether the file was found in the archive or not
     */
    function select($filename, $close = true)
    {
        $std = $this->getStandardURL($filename);

        if ($close) {
            $error = $this->close();
            if (PEAR::isError($error)) {
                return $error;
            }
        }
        while (($error = $this->next()) === true) {
            $sourceName = $this->getFilename();
            if (
                  empty($std) ||

                //$std is a file
                  $std == $sourceName ||

                //$std is a directory
                strncmp($std.'/', $sourceName, strlen($std)+1) == 0
               ) {
                return true;
            }
        }
        return $error;
    }

    /**
     * Returns the standard path
     * Changes \ to /
     * Removes the .. and . from the URL
     * @param string $path a valid URL that may contain . or .. and \
     * @static
     */
    function getStandardURL($path)
    {
        if ($path == '.') {
            return '';
        }
        $std = str_replace("\\", "/", $path);
        while ($std != ($std = preg_replace("/[^\/:?]+\/\.\.\//", "", $std))) ;
        $std = str_replace("/./", "", $std);
        if (strncmp($std, "./", 2) == 0) {
            return substr($std, 2);
        } else {
            return $std;
        }
    }

    /**
     * Returns the name of the file currently read by the reader
     *
     * Warning: undefined behaviour if no call to next have been
     * done or if last call to next has returned false
     *
     * @return string Name of the current file
     */
    function getFilename()
    {
        return PEAR::raiseError("Reader abstract function call (getFilename)");
    }

    /**
     * Returns the list of filenames from the current pos to the end of the source
     * The source will be closed after having called this function
     * This function goes through the whole archive (which may be slow).
     * If you intend to work on the reader, doing it in one pass would be faster
     *
     * @return array filenames from the current pos to the end of the source
     */
    function getFileList()
    {
        $result = array();
        while ( ($error = $this->next()) === true) {
            $result[] = $this->getFilename();
        }
        $this->close();
        if (PEAR::isError($error)) {
            return $error;
        } else {
            return $result;
        }
    }

    /**
     * Returns an array of statistics about the file
     * (see the PHP stat function for more information)
     *
     * The returned array may be empty, even if readers should try
     * their best to return as many data as possible
     */
    function getStat() { return array(); }

    /**
     * Returns the MIME associated with the current file
     * The default function does that by looking at the extension of the file
     */
    function getMime()
    {
//        require_once "File/Archive/Reader/MimeList.php";
        jimport('pear.File.Archive.Reader.MimeList');
        return File_Archive_Reader_GetMime($this->getFilename());
    }

    /**
     * If the current file of the archive is a physical file,
     *
     * @return the name of the physical file containing the data
     *         or null if no such file exists
     *
     * The data filename may not be the same as the filename.
     */
    function getDataFilename() { return null; }

    /**
     * Reads some data from the current file
     * If the end of the file is reached, returns null
     * If $length is not specified, reads up to the end of the file
     * If $length is specified reads up to $length
     */
    function getData($length = -1)
    {
        return PEAR::raiseError("Reader abstract function call (getData)");
    }

    /**
     * Skip some data and returns how many bytes have been skipped
     * This is strictly equivalent to
     *  return strlen(getData($length))
     * But could be far more efficient
     */
    function skip($length = -1)
    {
        $data = $this->getData($length);
        if (PEAR::isError($data)) {
            return $data;
        } else {
            return strlen($data);
        }
    }

    /**
     * Move the current position back of a given amount of bytes.
     * Not all readers may implement this function (a PEAR error will
     * be returned if the reader can't rewind)
     *
     * @param int $length number of bytes to seek before the current pos
     *        or -1 to move back to the begining of the current file
     * @return the number of bytes really rewinded (which may be less than
     *        $length if the current pos is less than $length
     */
    function rewind($length = -1)
    {
        return PEAR::raiseError('Rewind function is not implemented on this reader');
    }

    /**
     * Returns the current offset in the current file
     */
    function tell()
    {
        $offset = $this->rewind();
        $this->skip($offset);
        return $offset;
    }

    /**
     * Put back the reader in the state it was before the first call
     * to next()
     */
    function close()
    {
    }

    /**
     * Sends the current file to the Writer $writer
     * The data will be sent by chunks of at most $bufferSize bytes
     * If $bufferSize <= 0 (default), the blockSize option is used
     */
    function sendData(&$writer, $bufferSize = 0)
    {
        if (PEAR::isError($writer)) {
            return $writer;
        }
        if ($bufferSize <= 0) {
            $bufferSize = File_Archive::getOption('blockSize');
        }

        $filename = $this->getDataFilename();
        if ($filename !== null) {
            $error = $writer->writeFile($filename);
            if (PEAR::isError($error)) {
                return $error;
            }
        } else {
            while (($data = $this->getData($bufferSize)) !== null) {
                if (PEAR::isError($data)) {
                    return $data;
                }
                $error = $writer->writeData($data);
                if (PEAR::isError($error)) {
                    return $error;
                }
            }
        }
    }

    /**
     * Sends the whole reader to $writer and close the reader
     *
     * @param File_Archive_Writer $writer Where to write the files of the reader
     * @param bool $autoClose If true, close $writer at the end of the function.
     *        Default value is true
     * @param int $bufferSize Size of the chunks that will be sent to the writer
     *        If $bufferSize <= 0 (default value), the blockSize option is used
     */
    function extract(&$writer, $autoClose = true, $bufferSize = 0)
    {
        if (PEAR::isError($writer)) {
            $this->close();
            return $writer;
        }

        while (($error = $this->next()) === true) {
            if ($writer->newFileNeedsMIME()) {
                $mime = $this->getMime();
            } else {
                $mime = null;
            }
            $error = $writer->newFile(
                $this->getFilename(),
                $this->getStat(),
                $mime
            );
            if (PEAR::isError($error)) {
                break;
            }
            $error = $this->sendData($writer, $bufferSize);
            if (PEAR::isError($error)) {
                break;
            }
        }
        $this->close();
        if ($autoClose) {
            $writer->close();
        }
        if (PEAR::isError($error)) {
            return $error;
        }
    }

    /**
     * Extract only one file (given by the URL)
     *
     * @param string $filename URL of the file to extract from this
     * @param File_Archive_Writer $writer Where to write the file
     * @param bool $autoClose If true, close $writer at the end of the function
     *        Default value is true
     * @param int $bufferSize Size of the chunks that will be sent to the writer
     *        If $bufferSize <= 0 (default value), the blockSize option is used
     */
    function extractFile($filename, &$writer,
                         $autoClose = true, $bufferSize = 0)
    {
        if (PEAR::isError($writer)) {
            return $writer;
        }

        if (($error = $this->select($filename)) === true) {
            $result = $this->sendData($writer, $bufferSize);
            if (!PEAR::isError($result)) {
                $result = true;
            }
        } else if ($error === false) {
            $result = PEAR::raiseError("File $filename not found");
        } else {
            $result = $error;
        }
        if ($autoClose) {
            $error = $writer->close();
            if (PEAR::isError($error)) {
                return $error;
            }
        }
        return $result;
    }

    /**
     * Return a writer that allows appending files to the archive
     * After having called makeAppendWriter, $this is closed and should not be
     * used until the returned writer is closed.
     *
     * @return a writer that will allow to append files to an existing archive
     * @see makeWriter
     */
    function makeAppendWriter()
    {
//        require_once "File/Archive/Predicate/False.php";
        jimport('pear.File.Archive.Predicate.False');
        return $this->makeWriterRemoveFiles(new File_Archive_Predicate_False());
    }

    /**
     * Return a writer that has the same properties as the one returned by
     * makeAppendWriter, but after having removed all the files that follow a
     * given predicate.
     * After a call to makeWriterRemoveFiles, $this is closed and should not
     * be used until the returned writer is closed
     *
     * @param File_Archive_Predicate $pred the predicate verified by removed files
     * @return File_Archive_Writer that allows to append files to the archive
     */
    function makeWriterRemoveFiles($pred)
    {
        return PEAR::raiseError("Reader abstract function call (makeWriterRemoveFiles)");
    }

    /**
     * Returns a writer that removes the current file
     * This is a syntaxic sugar for makeWriterRemoveFiles(new File_Archive_Predicate_Current());
     */
    function makeWriterRemove()
    {
//        require_once "File/Archive/Predicate/Current.php";
        jimport('pear.File.Archive.Predicate.Current');
        return $this->makeWriterRemoveFiles(new File_Archive_Predicate_Current());
    }

    /**
     * Removes the current file from the reader
     */
    function remove()
    {
        $writer = $this->makeWriterRemove();
        if (PEAR::isError($writer)) {
            return $writer;
        }
        $writer->close();
    }

    /**
     * Return a writer that has the same properties as the one returned by makeWriter, but after
     * having removed a block of data from the current file. The writer will append data to the current file
     * no data (other than the block) will be removed
     *
     * @param array Lengths of the blocks. The first one will be discarded, the second one kept, the third
     *        one discarded... If the sum of the blocks is less than the size of the file, the comportment is the
     *        same as if a last block was set in the array to reach the size of the file
     *        if $length is -1, the file is truncated from the specified pos
     *        It is possible to specify blocks of size 0
     * @param int $seek relative pos of the block
     */
    function makeWriterRemoveBlocks($blocks, $seek = 0)
    {
        return PEAR::raiseError("Reader abstract function call (makeWriterRemoveBlocks)");
    }
}

?>