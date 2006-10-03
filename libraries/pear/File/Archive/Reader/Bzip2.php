<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Uncompress a file that was compressed in the Bzip2 format
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
 * @version    CVS: $Id: Bzip2.php,v 1.19 2005/07/26 09:06:03 vincentlascaux Exp $
 * @link       http://pear.php.net/package/File_Archive
 */

//require_once "File/Archive/Reader/Archive.php";
jimport('pear.File.Archive.Reader.Archive');
//require_once "File/Archive/Writer/Files.php";
jimport('pear.File.Archive.Writer.Files');

/**
 * Uncompress a file that was compressed in the Bzip2 format
 */
class File_Archive_Reader_Bzip2 extends File_Archive_Reader_Archive
{
    var $nbRead = 0;
    var $bzfile = null;
    var $tmpName = null;
    var $filePos = 0;

    /**
     * @see File_Archive_Reader::close()
     */
    function close($innerClose = true)
    {
        if ($this->bzfile !== null)
            bzclose($this->bzfile);
        if ($this->tmpName !== null)
            unlink($this->tmpName);

        $this->bzfile = null;
        $this->tmpName = null;
        $this->nbRead = 0;
        $this->filePos = 0;
        return parent::close($innerClose);
    }

    /**
     * @see File_Archive_Reader::next()
     */
    function next()
    {
        if (!parent::next()) {
            return false;
        }

        $this->nbRead++;
        if ($this->nbRead > 1) {
            return false;
        }

        $dataFilename = $this->source->getDataFilename();
        if ($dataFilename !== null)
        {
            $this->tmpName = null;
            $this->bzfile = @bzopen($dataFilename, 'r');
            if ($this->bzfile === false) {
                return PEAR::raiseError("bzopen failed to open $dataFilename");
            }
        } else {
            $this->tmpName = tempnam(File_Archive::getOption('tmpDirectory'), 'far');

            //Generate the tmp data
            $dest = new File_Archive_Writer_Files();
            $dest->newFile($this->tmpName);
            $this->source->sendData($dest);
            $dest->close();

            $this->bzfile = bzopen($this->tmpName, 'r');
        }

        return true;
    }
    /**
     * Return the name of the single file contained in the archive
     * deduced from the name of the archive (the extension is removed)
     *
     * @see File_Archive_Reader::getFilename()
     */
    function getFilename()
    {
        $name = $this->source->getFilename();
        $pos = strrpos($name, ".");
        if ($pos === false || $pos === 0) {
            return $name;
        } else {
            return substr($name, 0, $pos);
        }
    }
    /**
     * @see File_Archive_Reader::getData()
     */
    function getData($length = -1)
    {
        if ($length == -1) {
            $data = '';
            do {
                $newData = bzread($this->bzfile);
                $data .= $newData;
            } while ($newData != '');
            $this->filePos += strlen($data);
        } else if ($length == 0) {
            return '';
        } else {
            $data = '';

            //The loop is here to correct what appears to be a bzread bug
            while (strlen($data) < $length) {
                $newData = bzread($this->bzfile, $length - strlen($data));
                if ($newData == '') {
                    break;
                }
                $data .= $newData;
            }
            $this->filePos += strlen($data);
        }

        return $data == '' ? null : $data;
    }

    /**
     * @see File_Archive_Reader::rewind
     */
    function rewind($length = -1)
    {
        $before = $this->filePos;

        bzclose($this->bzfile);
        if ($this->tmpName === null) {
            $this->bzfile = bzopen($this->source->getDataFilename(), 'r');
        } else {
            $this->bzfile = bzopen($this->tmpName, 'r');
        }
        $this->filePos = 0;

        if ($length != -1) {
            $this->skip($before - $length);
        }
        return $before - $this->filePos;
    }

    /**
     * @see File_Archive_Reader::tell()
     */
    function tell()
    {
        return $this->filePos;
    }

    /**
     * @see File_Archive_Reader::makeAppendWriter()
     */
    function makeAppendWriter()
    {
        return PEAR::raiseError('Unable to append files to a bzip2 archive');
    }

    /**
     * @see File_Archive_Reader::makeWriterRemoveFiles()
     */
    function makeWriterRemoveFiles($pred)
    {
        return PEAR::raiseError('Unable to remove files from a bzip2 archive');
    }

    /**
     * @see File_Archive_Reader::makeWriterRemoveBlocks()
     */
    function makeWriterRemoveBlocks($blocks, $seek = 0)
    {
//        require_once "File/Archive/Writer/Bzip2.php";
        jimport('pear.File.Archive.Writer.Bzip2');

        if ($this->nbRead == 0) {
            return PEAR::raiseError('No file selected');
        }

        //Uncompress data to a temporary file
        $tmp = tmpfile();
        $expectedPos = $this->filePos + $seek;

        $this->rewind();

        //Read the begining of the file
        while ($this->filePos < $expectedPos &&
               ($data = $this->getData(min($expectedPos - $this->filePos, 8192))) !== null) {
            fwrite($tmp, $data);
        }

        $keep = false;
        foreach ($blocks as $length) {
            if ($keep) {
                $expectedPos = $this->filePos + $length;
                while ($this->filePos < $expectedPos &&
                       ($data = $this->getData(min($expectedPos - $this->filePos, 8192))) !== null) {
                    fwrite($tmp, $data);
                }
            } else {
                $this->skip($length);
            }
            $keep = !$keep;
        }
        if ($keep) {
            //Read the end of the file
            while(($data = $this->getData(8192)) !== null) {
                fwrite($tmp, $data);
            }
        }
        fseek($tmp, 0);

        //Create the writer
        $this->source->rewind();
        $innerWriter = $this->source->makeWriterRemoveBlocks(array());   //Truncate the source
        unset($this->source);
        $writer = new File_Archive_Writer_Bzip2(null, $innerWriter);

        //And compress data from the temporary file
        while (!feof($tmp)) {
            $data = fread($tmp, 8192);
            $writer->writeData($data);
        }
        fclose($tmp);

        //Do not close inner writer since makeWriter was called
        $this->close();

        return $writer;
    }
}

?>